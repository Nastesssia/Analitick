<?php
session_start();
header("Content-Type: application/json");

require_once 'DB_Connect.php';
$db = new DB_Connect();
$conn = $db->connect();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lawyer') {
    echo json_encode(["success" => false, "message" => "Доступ запрещен."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$id = isset($data['id']) ? intval($data['id']) : 0;

if ($id > 0) {
    $stmt = $conn->prepare("UPDATE form_submissions SET visible_to_assistant = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Заявка успешно возвращена юристу."]);
    } else {
        echo json_encode(["success" => false, "message" => "Ошибка при возврате заявки."]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Неверный ID заявки."]);
}

$conn->close();
?>
