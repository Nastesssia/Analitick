<?php
session_start();
header("Content-Type: application/json");

require_once 'DB_Connect.php';
$db = new DB_Connect();
$conn = $db->connect();

$data = json_decode(file_get_contents("php://input"), true);
$username = $data['username'] ?? '';
$password = $data['password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(["success" => false, "message" => "Введите логин и пароль"]);
    exit();
}

// Проверяем пользователя в базе данных
$stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(["success" => false, "message" => "Неверный логин или пароль"]);
    exit();
}

// Проверка пароля
if (password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    echo json_encode(["success" => true, "role" => $user['role']]);
} else {
    echo json_encode(["success" => false, "message" => "Неверный логин или пароль"]);
}
?>
