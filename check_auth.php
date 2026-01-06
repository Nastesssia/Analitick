<?php
session_start();
header('Content-Type: application/json');

// Включение логирования в php_errors.log
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

error_log("🚦 check_auth.php: Скрипт запущен");

// Отладка данных сессии
error_log("SESSION DATA: " . print_r($_SESSION, true));

// Проверяем, авторизован ли пользователь
if (!isset($_SESSION['user_id'])) {
    error_log("❌ Доступ запрещен: Пользователь не авторизован.");
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit();
}

// Проверка корректности роли пользователя
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['lawyer', 'assistant'])) {
    error_log("❌ Доступ запрещен: Неверная роль пользователя. Роль: " . ($_SESSION['role'] ?? 'undefined'));
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен. Неверная роль пользователя.']);
    exit();
}

// Возвращаем успешный ответ с ролью пользователя
error_log("✅ Доступ разрешен: Роль пользователя - " . $_SESSION['role']);
echo json_encode([
    'success' => true,
    'user_id' => $_SESSION['user_id'],
    'role' => $_SESSION['role']
]);
?>
