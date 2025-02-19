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

// Проверяем пользователя в базе
$stmt = $conn->prepare("SELECT id, password, role, failed_attempts, last_failed_attempt FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user) {
    echo json_encode(["success" => false, "message" => "Неверный логин или пароль"]);
    exit();
}

// Проверяем, прошло ли 5 минут с последней ошибки
$now = time();
$lastAttemptTime = strtotime($user['last_failed_attempt']);

if ($user['failed_attempts'] >= 5 && ($now - $lastAttemptTime) < 300) { // 300 секунд = 5 минут
    echo json_encode(["success" => false, "message" => "Слишком много попыток. Подождите 5 минут."]);
    exit();
}

// Проверяем пароль
if (password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['role'] = $user['role'];

    // Сбрасываем счетчик попыток после успешного входа
    $stmt = $conn->prepare("UPDATE users SET failed_attempts = 0, last_failed_attempt = NULL WHERE id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => true, "role" => $user['role']]);
} else {
    // Увеличиваем счетчик неудачных попыток
    $stmt = $conn->prepare("UPDATE users SET failed_attempts = failed_attempts + 1, last_failed_attempt = NOW() WHERE id = ?");
    $stmt->bind_param("i", $user['id']);
    $stmt->execute();
    $stmt->close();

    echo json_encode(["success" => false, "message" => "Неверный логин или пароль"]);
}
?>
