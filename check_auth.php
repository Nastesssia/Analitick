<?php
session_start();

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$role = isset($_SESSION['role']) ? strtolower(trim((string)$_SESSION['role'])) : '';
$userId = isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0;

if ($userId <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация'], JSON_UNESCAPED_UNICODE);
    exit();
}

if (!in_array($role, ['lawyer', 'assistant'], true)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен'], JSON_UNESCAPED_UNICODE);
    exit();
}

http_response_code(200);
echo json_encode(['success' => true, 'user_id' => $userId, 'role' => $role], JSON_UNESCAPED_UNICODE);
exit();
