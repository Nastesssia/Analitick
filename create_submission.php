<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require_once __DIR__ . '/DB_Connect.php';
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/yadisk_lib.php';

function out($arr, int $code = 200) {
    http_response_code($code);
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'lawyer') {
    out(["success" => false, "message" => "Доступ запрещен."], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    out(["success" => false, "message" => "Нужен POST."], 405);
}


if (!defined('YD_LOGIN') || !defined('YD_APP_PASSWORD')) {
    out(["success" => false, "message" => "Ошибка сервера: не настроены YD_LOGIN / YD_APP_PASSWORD."], 500);
}

$SUBMISSIONS_DIR = defined('YD_SUBMISSIONS_DIR') ? YD_SUBMISSIONS_DIR : '/analitikgroup/submissions';

function safe_remote_name(string $name): string {
    $name = trim($name);
    if ($name === '') return 'file';
    $safe = preg_replace('/[^a-zA-Z0-9._-]/', '_', $name);
    $safe = preg_replace('/_+/', '_', $safe);
    return $safe ?: 'file';
}

$surname     = trim((string)($_POST['surname'] ?? ''));
$name        = trim((string)($_POST['name'] ?? ''));
$patronymic  = trim((string)($_POST['patronymic'] ?? ''));
$phone       = trim((string)($_POST['phone'] ?? ''));
$email       = trim((string)($_POST['email'] ?? ''));
$problem     = trim((string)($_POST['problem'] ?? ''));

if ($problem === '') {
    out(["success" => false, "message" => "Заполните поле 'Проблема'."], 400);
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    out(["success" => false, "message" => "Неверный формат email."], 400);
}

$db = new DB_Connect();
$conn = $db->connect();

$fileLinksJson = json_encode([], JSON_UNESCAPED_UNICODE);

$stmt = $conn->prepare("
    INSERT INTO form_submissions (surname, name, patronymic, phone, email, problem, file_links, created_at, deleted, visible_to_assistant, resolved)
    VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), 0, 0, 0)
");
$stmt->bind_param("sssssss", $surname, $name, $patronymic, $phone, $email, $problem, $fileLinksJson);

if (!$stmt->execute()) {
    $stmt->close();
    $conn->close();
    out(["success" => false, "message" => "Ошибка БД при создании заявки."], 500);
}

$submissionId = (int)$conn->insert_id;
$stmt->close();

$fileLinks = [];
$errors = [];

if (!empty($_FILES['files']['name'][0])) {
    $baseDir = rtrim($SUBMISSIONS_DIR, '/') . '/' . $submissionId;
    yd_mkdir_p($baseDir);

    foreach ($_FILES['files']['tmp_name'] as $i => $tmp) {
        $origName = (string)($_FILES['files']['name'][$i] ?? '');
        if (!is_uploaded_file($tmp)) continue;

        $fileId = bin2hex(random_bytes(8));
        $safeName = safe_remote_name($origName);
        $remotePath = $baseDir . '/' . $fileId . '_' . $safeName;

        if (!yd_put_file($tmp, $remotePath)) {
            $errors[] = $origName;
            continue;
        }

        $fileLinks[] = [
            "id"   => $fileId,
            "name" => $origName,
            "path" => $remotePath
        ];
    }
}

$newJson = json_encode($fileLinks, JSON_UNESCAPED_UNICODE);

$upd = $conn->prepare("UPDATE form_submissions SET file_links = ? WHERE id = ?");
$upd->bind_param("si", $newJson, $submissionId);
$okUpd = $upd->execute();
$upd->close();
$conn->close();

if (!$okUpd) {
    out(["success" => false, "message" => "Заявка создана, но не удалось сохранить файлы в БД."], 500);
}

$msg = "Заявка создана.";
if (!empty($errors)) $msg .= " Не удалось загрузить: " . implode(', ', $errors);

out([
    "success" => true,
    "message" => $msg,
    "id" => $submissionId
]);
