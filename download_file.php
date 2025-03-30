<?php
session_start();

// 🔒 Проверка авторизации
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'assistant') {
    http_response_code(403);
    echo "Доступ запрещен.";
    exit;
}

require_once 'DB_Connect.php';
$db = new DB_Connect();
$conn = $db->connect();

$submissionId = isset($_GET['submission_id']) ? intval($_GET['submission_id']) : 0;
$fileName = isset($_GET['file']) ? basename($_GET['file']) : '';

if ($submissionId <= 0 || empty($fileName)) {
    http_response_code(400);
    echo "Неверные параметры.";
    exit;
}

// 🔍 Получаем заявку
$stmt = $conn->prepare("SELECT file_links, visible_to_assistant FROM form_submissions WHERE id = ?");
$stmt->bind_param("i", $submissionId);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row || !$row['visible_to_assistant']) {
    http_response_code(403);
    echo "Заявка недоступна для помощника.";
    exit;
}

// 🔍 Проверяем, что файл есть в file_links
$fileLinks = json_decode($row['file_links'], true);
$allowed = false;

foreach ($fileLinks as $file) {
    if (basename($file['url']) === $fileName) {
        $allowed = true;
        break;
    }
}

if (!$allowed) {
    http_response_code(403);
    echo "Нет доступа к этому файлу.";
    exit;
}

// 📂 Путь к файлу
$fullPath = $_SERVER['DOCUMENT_ROOT'] . '/uploads/' . $fileName;

if (!file_exists($fullPath)) {
    http_response_code(404);
    echo "Файл не найден.";
    exit;
}

// ✅ Отдаём файл
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . filesize($fullPath));
readfile($fullPath);
exit;
?>