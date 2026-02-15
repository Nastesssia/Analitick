<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"] ?? '/',
        $params["domain"] ?? '',
        (bool)($params["secure"] ?? false),
        (bool)($params["httponly"] ?? true)
    );
}

session_destroy();

echo json_encode(['success' => true], JSON_UNESCAPED_UNICODE);
exit();
