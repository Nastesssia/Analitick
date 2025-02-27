<?php
session_start();
header('Content-Type: application/json');

require_once 'DB_Connect.php';
$db = new DB_Connect();
$conn = $db->connect();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Необходима авторизация']);
    exit();
}

$id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Некорректный идентификатор заявки.']);
    exit();
}

$query = "UPDATE form_submissions SET deleted = 1 WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Заявка успешно удалена.']);
} else {
    echo json_encode(['success' => false, 'message' => 'Ошибка при удалении заявки: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>
