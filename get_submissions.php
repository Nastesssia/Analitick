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

$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;
$offset = ($page - 1) * $itemsPerPage; 

$sql = "SELECT id, surname, name, patronymic, phone, email, problem, file_links, deleted, created_at, visible_to_assistant 
        FROM form_submissions 
        WHERE deleted = 0 AND (resolved = 0 OR resolved IS NULL) AND visible_to_assistant = 0
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

$countResult = $conn->query("SELECT COUNT(*) as total FROM form_submissions WHERE visible_to_assistant = 0 AND deleted = 0");
$totalCount = $countResult->fetch_assoc()['total'];

$countResultAssistant = $conn->query("SELECT COUNT(*) as total FROM form_submissions WHERE visible_to_assistant = 1 AND deleted = 0");
$totalCountAssistant = $countResultAssistant->fetch_assoc()['total'];


$countResultDeleted = $conn->query("SELECT COUNT(*) as total FROM form_submissions WHERE deleted = 1");
$totalCountDeleted = $countResultDeleted->fetch_assoc()['total'];


$countResultResolved = $conn->query("SELECT COUNT(*) as total FROM form_submissions WHERE resolved = 1 AND deleted = 0");
$totalCountResolved = $countResultResolved->fetch_assoc()['total'];


$sqlResolved = "SELECT id, surname, name, patronymic, phone, email, problem, file_links, deleted, created_at, 
                       assistant_sent_at, assistant_resolved_at, resolved, revision_requested_at, 
                       revision_completed_at, revision_comment, revision_files, answer_files
                FROM form_submissions 
                WHERE resolved = 1 AND deleted = 0
                ORDER BY id DESC";


$resultResolved = $conn->query($sqlResolved);

$resolvedSubmissions = [];
while ($row = $resultResolved->fetch_assoc()) {
    $row['file_links'] = !empty($row['file_links']) ? json_decode($row['file_links']) : [];
    $row['revision_files'] = !empty($row['revision_files']) ? json_decode($row['revision_files']) : [];
   $row['answer_files'] = !empty($row['answer_files']) ? json_decode($row['answer_files']) : [];

    $resolvedSubmissions[] = $row;
}

error_log("📄 Решенные заявки: " . json_encode($resolvedSubmissions, JSON_UNESCAPED_UNICODE));

echo json_encode([
    
    "success" => true,
    "submissions" => $submissions,
    "assistantSubmissions" => $assistantSubmissions,
    "deletedSubmissions" => $deletedSubmissions,
    "resolvedSubmissions" => $resolvedSubmissions,
"totalCount" => [
    "active" => $totalCount,
    "assistant" => $totalCountAssistant,
    "deleted" => $totalCountDeleted,
    "resolved" => $totalCountResolved
],

]);

$stmt->close();
$conn->close();
?>