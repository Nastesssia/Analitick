<?php
require_once 'DB_Connect.php'; // Подключаем базу

$db = new DB_Connect();
$conn = $db->connect();

// Обновляем пароль для юриста
$hashedPassword1 = password_hash("123", PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'lawyer123'");
$stmt->bind_param("s", $hashedPassword1);
$stmt->execute();
$stmt->close();

// Обновляем пароль для помощника
$hashedPassword2 = password_hash("321", PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'assistant321'");
$stmt->bind_param("s", $hashedPassword2);
$stmt->execute();
$stmt->close();

echo "Пароли обновлены!";
?>
