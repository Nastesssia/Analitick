<?php
session_start();
header('Content-Type: application/json');

// Отладка сессии
error_log("SESSION DATA: " . print_r($_SESSION, true));

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit();
}

// Возвращаем роль пользователя
echo json_encode([
    'success' => true,
    'user_id' => $_SESSION['user_id'],
    'role' => $_SESSION['role']
]);
?>
