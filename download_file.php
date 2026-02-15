<?php
session_start();

ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/php_errors.log");
error_reporting(E_ALL);

require_once __DIR__ . '/DB_Connect.php';
require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/yadisk_lib.php';

function die_text(int $code, string $msg): void {
    http_response_code($code);
    echo $msg;
    exit;
}

$role = $_SESSION['role'] ?? '';
if (!isset($_SESSION['user_id']) || !in_array($role, ['lawyer','assistant'], true)) {
    die_text(403, "Доступ запрещен.");
}

if (!function_exists('curl_init')) {
    die_text(500, "Ошибка сервера: нет расширения curl.");
}

$submissionId = (int)($_GET['submission_id'] ?? 0);
$fileId = (string)($_GET['file_id'] ?? '');
$kind = (string)($_GET['kind'] ?? 'main'); 

$fileId = strtolower(preg_replace('/[^a-f0-9]/', '', $fileId));

if ($submissionId <= 0 || $fileId === '') {
    die_text(400, "Неверные параметры.");
}

$db = new DB_Connect();
$conn = $db->connect();

$stmt = $conn->prepare("SELECT file_links, revision_files, visible_to_assistant FROM form_submissions WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $submissionId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$row) {
    die_text(404, "Заявка не найдена.");
}

if ($role === 'assistant' && (int)$row['visible_to_assistant'] !== 1) {
    die_text(403, "Заявка недоступна для помощника.");
}

$json = ($kind === 'revision') ? ($row['revision_files'] ?? '[]') : ($row['file_links'] ?? '[]');
$items = json_decode($json, true);
if (!is_array($items)) $items = [];

$item = null;
foreach ($items as $f) {
    if (is_array($f) && isset($f['id']) && strtolower((string)$f['id']) === $fileId) {
        $item = $f;
        break;
    }
}

if (!$item) {
    die_text(404, "Файл не найден.");
}

$remotePath = '';
if ($role === 'assistant' && !empty($item['assistant_path'])) {
    $remotePath = (string)$item['assistant_path'];
} elseif (!empty($item['path'])) {
    $remotePath = (string)$item['path'];
}

if ($remotePath === '' && !empty($item['url']) && is_string($item['url']) && preg_match('~^https?://~', $item['url'])) {
    header('Location: ' . $item['url']);
    exit;
}

if ($remotePath === '') {
    die_text(404, "Нет пути к файлу.");
}

$filename = (string)($item['name'] ?? basename($remotePath));
$filename = str_replace(["\r","\n","\""], ["","",""], $filename);
$fallback = preg_replace('/[^A-Za-z0-9._-]/', '_', $filename);

set_time_limit(0);
while (ob_get_level()) { ob_end_clean(); }

$url = YD_WEBDAV_BASE . yd_encode_path($remotePath);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERPWD, YD_LOGIN . ':' . YD_APP_PASSWORD);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
$rawHead = curl_exec($ch);
$headCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($headCode !== 200) {
    error_log("YD HEAD failed: path={$remotePath} code={$headCode}");
    die_text(404, "Файл не найден на Я.Диске.");
}

$ctype = 'application/octet-stream';
$clen = null;
if (is_string($rawHead)) {
    foreach (explode("\n", $rawHead) as $line) {
        $line = trim($line);
        if (stripos($line, 'Content-Type:') === 0) $ctype = trim(substr($line, 13));
        if (stripos($line, 'Content-Length:') === 0) $clen = trim(substr($line, 15));
    }
}

header('Content-Type: ' . $ctype);
header("Content-Disposition: attachment; filename=\"{$fallback}\"; filename*=UTF-8''" . rawurlencode($filename));
header('X-Content-Type-Options: nosniff');
if ($clen !== null && ctype_digit((string)$clen)) header('Content-Length: ' . $clen);

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
curl_setopt($ch, CURLOPT_USERPWD, YD_LOGIN . ':' . YD_APP_PASSWORD);
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $data) {
    echo $data;
    return strlen($data);
});

curl_exec($ch);
$code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($code !== 200) {
    error_log("YD GET failed: path={$remotePath} code={$code} err={$err}");
}
exit;
