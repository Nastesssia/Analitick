<?php
session_start();
header("Content-Type: application/json");

require_once 'DB_Connect.php';
$db = new DB_Connect();
$conn = $db->connect();

// Проверяем авторизацию пользователя
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lawyer') {
    echo json_encode(["success" => false, "message" => "Доступ запрещен."]);
    exit();
}

// Получение текущей страницы и количества записей на страницу
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 25;
$offset = ($page - 1) * $itemsPerPage;

// Получение активных заявок (не отправленных помощнику и не удаленных)
$sql = "SELECT id, surname, name, patronymic, phone, email, problem, file_links, deleted, created_at, visible_to_assistant 
        FROM form_submissions 
        WHERE visible_to_assistant = 0 AND deleted = 0
        ORDER BY id DESC 
        LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $itemsPerPage);
$stmt->execute();
$result = $stmt->get_result();

$submissions = [];
while ($row = $result->fetch_assoc()) {
    $row['file_links'] = !empty($row['file_links']) ? json_decode($row['file_links']) : [];
    $submissions[] = $row;
}

// Заявки, отправленные помощнику
$sqlAssistant = "SELECT id, surname, name, patronymic, phone, email, problem, file_links, deleted, created_at 
                 FROM form_submissions 
                 WHERE visible_to_assistant = 1 AND deleted = 0
                 ORDER BY id DESC";
$resultAssistant = $conn->query($sqlAssistant);

$assistantSubmissions = [];
while ($row = $resultAssistant->fetch_assoc()) {
    $row['file_links'] = !empty($row['file_links']) ? json_decode($row['file_links']) : [];
    $assistantSubmissions[] = $row;
}

// Удаленные заявки
$sqlDeleted = "SELECT id, surname, name, patronymic, phone, email, problem, file_links, deleted, created_at 
               FROM form_submissions 
               WHERE deleted = 1
               ORDER BY id DESC";
$resultDeleted = $conn->query($sqlDeleted);

$deletedSubmissions = [];
while ($row = $resultDeleted->fetch_assoc()) {
    $row['file_links'] = !empty($row['file_links']) ? json_decode($row['file_links']) : [];
    $deletedSubmissions[] = $row;
}
// Заявки, отправленные помощнику
$sqlAssistant = "SELECT id, surname, name, patronymic, phone, email, problem, file_links, deleted, created_at, assistant_sent_at 
                 FROM form_submissions 
                 WHERE visible_to_assistant = 1 AND deleted = 0
                 ORDER BY id DESC";
$resultAssistant = $conn->query($sqlAssistant);

$assistantSubmissions = [];
while ($row = $resultAssistant->fetch_assoc()) {
    $row['file_links'] = !empty($row['file_links']) ? json_decode($row['file_links']) : [];
    $assistantSubmissions[] = $row;
}

// Подсчет общего количества активных заявок
$countResult = $conn->query("SELECT COUNT(*) as total FROM form_submissions WHERE visible_to_assistant = 0 AND deleted = 0");
$totalCount = $countResult->fetch_assoc()['total'];


// Решенные заявки (resolved = 1)
$sqlResolved = "SELECT id, surname, name, patronymic, phone, email, problem, file_links, deleted, created_at, assistant_sent_at, resolved
                FROM form_submissions 
                WHERE resolved = 1 AND deleted = 0
                ORDER BY id DESC";
$resultResolved = $conn->query($sqlResolved);

$resolvedSubmissions = [];
while ($row = $resultResolved->fetch_assoc()) {
    $row['file_links'] = !empty($row['file_links']) ? json_decode($row['file_links']) : [];
    $resolvedSubmissions[] = $row;
}

echo json_encode([
    "success" => true,
    "submissions" => $submissions,
    "assistantSubmissions" => $assistantSubmissions,
    "deletedSubmissions" => $deletedSubmissions,
    "resolvedSubmissions" => $resolvedSubmissions,
    "totalCount" => $totalCount
]);

$stmt->close();
$conn->close();
?>