<?php
require_once 'DB_Connect.php'; // Подключаем базу

$db = new DB_Connect();
$conn = $db->connect();

// Обновляем пароль для юриста
$hashedPassword1 = password_hash("85vR8T65GAKqzzr", PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'lawyer'");
$stmt->bind_param("s", $hashedPassword1);
$stmt->execute();
$stmt->close();

// Обновляем пароль для помощника
$hashedPassword2 = password_hash("jal9PECIexuIrYB", PASSWORD_DEFAULT);
$stmt = $conn->prepare("UPDATE users SET password = ? WHERE username = 'assistant'");
$stmt->bind_param("s", $hashedPassword2);
$stmt->execute();
$stmt->close();

echo "Пароли обновлены!";
?>
