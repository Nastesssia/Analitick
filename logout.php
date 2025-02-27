<?php
session_start();

// Удаление всех данных сессии
$_SESSION = [];

// Удаление куки сессии
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Завершение сессии
session_destroy();

header('Content-Type: application/json');
echo json_encode(['success' => true, 'message' => 'Вы успешно вышли из системы']);
