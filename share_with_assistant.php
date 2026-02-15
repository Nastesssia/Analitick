<?php
session_start();

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/php_errors.log");
error_reporting(E_ALL);

require_once __DIR__ . '/DB_Connect.php';
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/yadisk_lib.php';

function out(array $arr, int $code = 200): void {
    http_response_code($code);
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit();
}

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'lawyer') {
    out(["success" => false, "message" => "Доступ запрещен."], 403);
}

if (!function_exists('curl_init')) {
    out(["success" => false, "message" => "Ошибка сервера: нет расширения curl (нужно для Яндекс.Диска)."], 500);
}

if (!defined('YD_LOGIN') || !defined('YD_APP_PASSWORD')) {
    error_log("FATAL: YD_LOGIN / YD_APP_PASSWORD not defined in Config.php");
    out(["success" => false, "message" => "Ошибка сервера: не настроены параметры Яндекс.Диска (YD_LOGIN/YD_APP_PASSWORD)."], 500);
}

$assistantRoot = defined('YD_ASSISTANT_DIR') ? YD_ASSISTANT_DIR : '/assistant';

$data = json_decode(file_get_contents("php://input"), true);
if (!is_array($data)) $data = [];
$id = (int)($data['id'] ?? 0);

if ($id <= 0) {
    out(["success" => false, "message" => "Неверный ID заявки."], 400);
}

$db = new DB_Connect();
$conn = $db->connect();

$stmt = $conn->prepare("SELECT file_links FROM form_submissions WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    out(["success" => false, "message" => "Заявка не найдена."], 404);
}

$fileLinks = json_decode($row['file_links'] ?? '[]', true);
if (!is_array($fileLinks)) $fileLinks = [];

$copied = 0;
$failed = 0;

foreach ($fileLinks as $idx => $f) {
    if (!is_array($f)) continue;

    // новый формат: path
    $src = (string)($f['path'] ?? '');
    if ($src === '') {
        // если вдруг уже есть assistant_path — ок
        $src = (string)($f['assistant_path'] ?? '');
    }
    if ($src === '') continue;

    $base = basename($src);
    $dest = rtrim($assistantRoot, '/') . '/' . $id . '/shared/' . $base;

    // Пытаемся COPY (быстро)
    $destUrl = YD_WEBDAV_BASE . yd_encode_path($dest);
    $r = yd_request('COPY', $src, [
        'Destination: ' . $destUrl,
        'Overwrite: T'
    ]);

    if (in_array((int)$r['code'], [201, 204], true)) {
        $fileLinks[$idx]['assistant_path'] = $dest;
        $copied++;
        continue;
    }

    // Если COPY не поддержан/не прошёл — fallback: не падаем, просто даём доступ к исходному пути
    $failed++;
    $fileLinks[$idx]['assistant_path'] = $src;

    $bodyShort = mb_substr((string)$r['body'], 0, 300);
    error_log("YADISK COPY failed: id={$id} src={$src} dest={$dest} code={$r['code']} err={$r['err']} body={$bodyShort}");
}

$newJson = json_encode($fileLinks, JSON_UNESCAPED_UNICODE);

$upd = $conn->prepare("UPDATE form_submissions
                       SET visible_to_assistant = 1,
                           assistant_sent_at = NOW(),
                           file_links = ?
                       WHERE id = ?");
$upd->bind_param("si", $newJson, $id);

if ($upd->execute()) {
    out([
        "success" => true,
        "message" => "Отправлено помощнику. Скопировано: {$copied}, fallback: {$failed}",
        "copied"  => $copied,
        "failed"  => $failed
    ], 200);
} else {
    error_log("DB update failed: " . $conn->error);
    out(["success" => false, "message" => "Ошибка при обновлении заявки."], 500);
}

$upd->close();
$conn->close();
