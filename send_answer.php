<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

ini_set("display_errors", "0");
ini_set("log_errors", "1");
ini_set("error_log", __DIR__ . "/php_errors.log");
error_reporting(E_ALL);

ob_start();
function out(array $arr, int $code = 200): void {
    if (ob_get_length()) { @ob_end_clean(); }
    http_response_code($code);
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

register_shutdown_function(function () {
    $err = error_get_last();
    if (!$err) return;

    $fatalTypes = [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR];
    if (!in_array($err['type'], $fatalTypes, true)) return;

    $buffer = ob_get_clean();
    if (!headers_sent()) {
        header("Content-Type: application/json; charset=utf-8");
    }
    http_response_code(500);

    echo json_encode([
        "success" => false,
        "message" => "Ошибка сервера (fatal): " . ($err['message'] ?? 'Unknown fatal'),
        "file"    => $err['file'] ?? null,
        "line"    => $err['line'] ?? null,
        "output"  => $buffer ? mb_substr($buffer, 0, 2000) : null
    ], JSON_UNESCAPED_UNICODE);
    exit;
});

require_once __DIR__ . "/DB_Connect.php";
require_once __DIR__ . "/Config.php";
require_once __DIR__ . "/yadisk_lib.php";
require_once __DIR__ . "/vendor/autoload.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'assistant') {
    out(["success" => false, "message" => "Доступ запрещен."], 403);
}

/* ===== Я.Диск: совместимость имён функций ===== */
if (!function_exists('yd_mkcol') && function_exists('yd_mkcol_recursive')) {
    function yd_mkcol(string $dirPath) { return yd_mkcol_recursive($dirPath); }
}
if (!function_exists('yd_put') && function_exists('yd_put_file')) {
    function yd_put(string $localFile, string $remotePath) { return yd_put_file($localFile, $remotePath); }
}
if (!function_exists('yd_put_file') && function_exists('yd_put')) {
    function yd_put_file(string $localFile, string $remotePath) { return yd_put($localFile, $remotePath); }
}
if (!function_exists('yd_mkcol_recursive') && !function_exists('yd_mkcol')) {
    out(["success" => false, "message" => "Ошибка сервера: функция создания папок Я.Диска не найдена."], 500);
}
if (!function_exists('yd_put_file') && !function_exists('yd_put')) {
    out(["success" => false, "message" => "Ошибка сервера: функция загрузки файлов на Я.Диск не найдена."], 500);
}
if (!defined('YD_ANSWERS_DIR')) {
    define('YD_ANSWERS_DIR', '/analitikgroup/answers');
}

function safe_filename(string $name): string {
    $name = preg_replace('/[^\p{L}\p{N}\s\.\-_]+/u', '_', $name);
    $name = preg_replace('/\s+/u', ' ', trim($name));
    if ($name === '') $name = 'file';
    return $name;
}

function yd_ensure_dir(string $dir): bool {
    $dir = '/' . ltrim($dir, '/');
    if (function_exists('yd_mkcol_recursive')) {
        return (bool)yd_mkcol_recursive($dir);
    }
    return (bool)yd_mkcol($dir);
}

function yd_upload_answer_file(int $submissionId, string $tmpPath, string $origName): ?array {
    $origName = safe_filename($origName);

    $remoteDir = rtrim(YD_ANSWERS_DIR, '/') . '/' . $submissionId;
    if (!yd_ensure_dir($remoteDir)) {
        error_log("YADISK: cannot create dir: {$remoteDir}");
        return null;
    }

    $ts = date('Ymd_His');
    $remotePath = $remoteDir . '/' . $ts . '_' . $origName;

    $ok = false;
    if (function_exists('yd_put_file')) {
        $ok = (bool)yd_put_file($tmpPath, $remotePath);
    } else {
        $ok = (bool)yd_put($tmpPath, $remotePath);
    }

    if (!$ok) {
        error_log("YADISK: upload failed: {$remotePath}");
        return null;
    }

    return [
        "id"   => bin2hex(random_bytes(16)),
        "name" => $origName,
        "path" => $remotePath
    ];
}

/* ===== входные данные ===== */
$submission_id = (int)($_POST['submission_id'] ?? 0);
$subject       = trim((string)($_POST['subject'] ?? ''));
$answer_text   = trim((string)($_POST['answer_text'] ?? ''));

$surname       = trim((string)($_POST['surname'] ?? ''));
$name          = trim((string)($_POST['name'] ?? ''));
$patronymic    = trim((string)($_POST['patronymic'] ?? ''));
$phone         = trim((string)($_POST['phone'] ?? ''));
$email         = trim((string)($_POST['email'] ?? ''));
$problem       = trim((string)($_POST['problem'] ?? ''));

$revision_comment = (string)($_POST['revision_comment'] ?? '');
$is_revision = trim($revision_comment) !== '';

$file_links = [];
if (!empty($_POST['file_links'])) {
    $tmp = json_decode((string)$_POST['file_links'], true);
    if (is_array($tmp)) $file_links = $tmp;
}

if ($submission_id <= 0) out(["success" => false, "message" => "Неверный ID заявки."], 400);
if ($subject === '' || $answer_text === '') out(["success" => false, "message" => "Заполните тему и ответ."], 400);

/* ===== БД ===== */
$db = new DB_Connect();
$conn = $db->connect();
if (!$conn) out(["success" => false, "message" => "Ошибка подключения к БД."], 500);

$stmt = $conn->prepare("SELECT id, answer_files, revision_requested_at, revision_completed_at FROM form_submissions WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    $conn->close();
    out(["success" => false, "message" => "Заявка не найдена."], 404);
}

$existingAnswer = [];
if (!empty($row['answer_files'])) {
    $decoded = json_decode($row['answer_files'], true);
    if (is_array($decoded)) $existingAnswer = $decoded;
}

/* ===== загрузка файлов ответа на Я.Диск ===== */
$newAnswerFiles = [];
$filesCount = 0;

foreach ($_FILES as $f) {
    if (!is_array($f)) continue;
    if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
    if (!is_uploaded_file($f['tmp_name'])) continue;

    $filesCount++;
    if ($filesCount > 5) break;

    if (($f['size'] ?? 0) > 25 * 1024 * 1024) continue;

    $meta = yd_upload_answer_file($submission_id, $f['tmp_name'], $f['name']);
    if ($meta) $newAnswerFiles[] = $meta;
}

$mergedAnswerFiles = array_values(array_merge($existingAnswer, $newAnswerFiles));
$answerFilesJson = json_encode($mergedAnswerFiles, JSON_UNESCAPED_UNICODE);

$LAWYER_EMAIL = defined('LAWYER_EMAIL') ? LAWYER_EMAIL : 'i@aleksandr-kabanov.ru';
$CC_EMAIL     = defined('CC_EMAIL') ? CC_EMAIL : '';


$smtpUser = '';
$smtpPass = '';

if ($smtpUser === '' && defined('YANDEX_SMTP_USER') && trim((string)YANDEX_SMTP_USER) !== '') {
    $smtpUser = (string)YANDEX_SMTP_USER;
}
if ($smtpPass === '' && defined('YANDEX_SMTP_PASS') && trim((string)YANDEX_SMTP_PASS) !== '') {
    $smtpPass = (string)YANDEX_SMTP_PASS;
}

if ($smtpUser === '' && defined('SMTP_USER') && trim((string)SMTP_USER) !== '') $smtpUser = (string)SMTP_USER;
if ($smtpPass === '' && defined('SMTP_PASS') && trim((string)SMTP_PASS) !== '') $smtpPass = (string)SMTP_PASS;

if ($smtpUser === '' && defined('YD_LOGIN') && trim((string)YD_LOGIN) !== '') $smtpUser = (string)YD_LOGIN;
if ($smtpPass === '' && defined('YD_APP_PASSWORD') && trim((string)YD_APP_PASSWORD) !== '') $smtpPass = (string)YD_APP_PASSWORD;

$SMTP_HOST = defined('SMTP_HOST') ? (string)SMTP_HOST : 'smtp.yandex.ru';
$SMTP_PORT = defined('SMTP_PORT') ? (int)SMTP_PORT : 465;
$SMTP_SEC  = defined('SMTP_SEC')  ? (string)SMTP_SEC  : 'ssl';

$mailStatus = 'skipped';
try {
    if ($smtpUser !== '' && $smtpPass !== '') {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = $SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = $smtpUser;
        $mail->Password   = $smtpPass;
        $mail->SMTPSecure = $SMTP_SEC;
        $mail->Port       = $SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        $mail->setFrom($smtpUser, 'Кабинет помощника');
        $mail->addAddress($LAWYER_EMAIL, 'Юрист');
        if ($CC_EMAIL !== '') $mail->addCC($CC_EMAIL);

        $mail->Subject = "Ответ на заявку #{$submission_id}: {$subject}" . ($is_revision ? " (Доработка)" : "");

        $body = "<h2>Копия заявки клиента:</h2>
            <p><strong>Фамилия:</strong> " . htmlspecialchars($surname) . "</p>
            <p><strong>Имя:</strong> " . htmlspecialchars($name) . "</p>
            <p><strong>Отчество:</strong> " . htmlspecialchars($patronymic) . "</p>
            <p><strong>Телефон:</strong> " . htmlspecialchars($phone) . "</p>
            <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
            <p><strong>Проблема:</strong> " . nl2br(htmlspecialchars($problem)) . "</p>
            <hr>
            <p><strong>Файлы клиента:</strong></p><ul>";

        foreach ($file_links as $ff) {
            $u = htmlspecialchars((string)($ff['url'] ?? ''));
            $n = htmlspecialchars((string)($ff['name'] ?? $u));
            if ($u !== '') $body .= "<li><a href='{$u}' target='_blank'>{$n}</a></li>";
        }

        $body .= "</ul><hr>
            <h2>Ответ помощника:</h2>
            <p><strong>Тема:</strong> " . htmlspecialchars($subject) . "</p>
            <p><strong>Ответ:</strong><br>" . nl2br(htmlspecialchars($answer_text)) . "</p>";

        if ($is_revision) {
            $body .= "<hr><h3>Комментарий юриста к доработке:</h3>
                <p>" . nl2br(htmlspecialchars($revision_comment)) . "</p>";
        }

        $attached = [];
        foreach ($_FILES as $f) {
            if (!is_array($f)) continue;
            if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
            if (!is_uploaded_file($f['tmp_name'])) continue;
            if (($f['size'] ?? 0) > 25 * 1024 * 1024) continue;

            $mail->addAttachment($f['tmp_name'], $f['name']);
            $attached[] = (string)$f['name'];
        }

        $body .= "<hr><p><strong>Файлы ответа (вложены):</strong></p>";
        if (count($attached)) {
            $body .= "<ul>";
            foreach ($attached as $fn) $body .= "<li>" . htmlspecialchars($fn) . "</li>";
            $body .= "</ul>";
        } else {
            $body .= "<p>—</p>";
        }

        $mail->isHTML(true);
        $mail->Body = $body;
        $mail->send();
        $mailStatus = 'sent';
    } else {
        error_log("MAIL: SMTP creds empty (SMTP_USER/SMTP_PASS and fallback YD_LOGIN/YD_APP_PASSWORD are empty) -> skip sending");
        $mailStatus = 'skipped_empty_creds';
    }
} catch (Exception $e) {
    error_log("MAIL ERROR: " . $e->getMessage());
    $mailStatus = 'failed: ' . $e->getMessage();
}

/* ===== обновление заявки ===== */
$needRevisionCompleted = false;
if ($is_revision) {
    if (!empty($row['revision_requested_at']) && empty($row['revision_completed_at'])) {
        $needRevisionCompleted = true;
    }
}

if ($needRevisionCompleted) {
    $upd = $conn->prepare("
        UPDATE form_submissions
        SET answer_files = ?,
            revision_completed_at = NOW(),
            visible_to_assistant = 0,
            resolved = 1,
            assistant_resolved_at = NOW()
        WHERE id = ?
    ");
    $upd->bind_param("si", $answerFilesJson, $submission_id);
} else {
    $upd = $conn->prepare("
        UPDATE form_submissions
        SET answer_files = ?,
            visible_to_assistant = 0,
            resolved = 1,
            assistant_resolved_at = NOW()
        WHERE id = ?
    ");
    $upd->bind_param("si", $answerFilesJson, $submission_id);
}

if (!$upd->execute()) {
    $err = $upd->error;
    $upd->close();
    $conn->close();
    out(["success" => false, "message" => "Ошибка обновления заявки: " . $err], 500);
}

$upd->close();
$conn->close();

out([
    "success" => true,
    "message" => "Ответ отправлен. Файлов добавлено: " . count($newAnswerFiles),
    "answer_files_added" => $newAnswerFiles,
    "mail_status" => $mailStatus
]);
