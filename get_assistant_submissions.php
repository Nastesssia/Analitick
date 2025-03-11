<?php
session_start();
header('Content-Type: application/json');

require_once 'DB_Connect.php';
$db = new DB_Connect();
$conn = $db->connect();

// 🔹 Логирование
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/assistant_errors.log');
error_reporting(E_ALL);

error_log("🚦 [get_assistant_submissions.php] Скрипт запущен");

// 🔹 Проверяем авторизацию
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'assistant') {
    error_log("🚫 [Ошибка доступа] Роль: " . ($_SESSION['role'] ?? 'не определена'));
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен.']);
    exit();
}

// 🔹 Параметры пагинации
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 10;

$offset = ($page - 1) * $itemsPerPage;

error_log("📄 [Пагинация] Страница: {$page}, Кол-во на странице: {$itemsPerPage}, Смещение: {$offset}");

// 🔹 Подсчет общего количества заявок
$countQuery = "SELECT COUNT(*) as total FROM form_submissions WHERE visible_to_assistant = 1 AND resolved = 0";
$countResult = $conn->query($countQuery);

if (!$countResult) {
    error_log("❌ [Ошибка SQL] Запрос: {$countQuery}, Ошибка: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Ошибка при получении количества заявок.']);
    exit();
}

$totalCount = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalCount / $itemsPerPage);

error_log("📊 [Статистика] Всего заявок: {$totalCount}, Всего страниц: {$totalPages}");

// 🔹 Запрос заявок с учетом пагинации
$sql = "SELECT id, surname, name, patronymic, phone, email, problem, file_links, created_at, assistant_sent_at, 
        revision_requested_at, revision_comment, revision_files
        FROM form_submissions 
        WHERE visible_to_assistant = 1 AND resolved = 0
        ORDER BY id DESC
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log("❌ [Ошибка подготовки SQL] Ошибка: " . $conn->error);
    echo json_encode(['success' => false, 'message' => 'Ошибка запроса к базе данных.']);
    exit();
}

$stmt->bind_param("ii", $offset, $itemsPerPage);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    error_log("❌ [Ошибка выполнения SQL] Ошибка: " . $stmt->error);
    echo json_encode(['success' => false, 'message' => 'Ошибка выполнения запроса.']);
    exit();
}

$submissions = [];
while ($row = $result->fetch_assoc()) {
    // 🔹 Обработка file_links
    $row['file_links'] = json_decode($row['file_links'], true) ?: [];
    if (is_array($row['file_links'])) {
        $row['file_links'] = array_map(fn($link) => is_string($link) ? ['url' => $link, 'name' => basename($link)] : $link, $row['file_links']);
        $row['file_links'] = array_filter($row['file_links']);
    }

    // 🔹 Обработка revision_files
    $row['revision_files'] = json_decode($row['revision_files'], true) ?: [];
    if (is_array($row['revision_files'])) {
        $row['revision_files'] = array_map(fn($link) => is_string($link) ? ['url' => $link, 'name' => basename($link)] : $link, $row['revision_files']);
        $row['revision_files'] = array_filter($row['revision_files']);
    }

    $submissions[] = $row;
}

// 🔹 Логируем, какие данные отправляем
error_log("✅ [Ответ JSON] " . json_encode([
    'success' => true,
    'submissions' => $submissions,
    'totalCount' => $totalCount,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'itemsPerPage' => $itemsPerPage
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

echo json_encode([
    'success' => true,
    'submissions' => $submissions,
    'totalCount' => $totalCount,
    'totalPages' => $totalPages,
    'currentPage' => $page,
    'itemsPerPage' => $itemsPerPage
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

$stmt->close();
$conn->close();

?>
