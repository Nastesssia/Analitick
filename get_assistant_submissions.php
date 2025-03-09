<?php
session_start();
header('Content-Type: application/json');

require_once 'DB_Connect.php';
$db = new DB_Connect();
$conn = $db->connect();

// 🔹 Логирование ошибок
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

error_log("🚦 get_assistant_submissions.php: Скрипт запущен");

// 🔹 Проверяем авторизацию
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'assistant') {
    error_log("🚫 Доступ запрещен. Роль: " . ($_SESSION['role'] ?? 'не определена'));
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен.']);
    exit();
}

// 🔹 Параметры пагинации
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 25;
$offset = ($page - 1) * $itemsPerPage;

error_log("📄 Параметры пагинации: Страница {$page}, Кол-во на странице {$itemsPerPage}, Смещение {$offset}");

// 🔹 Запрос к БД: получаем заявки, отправленные помощнику и не решенные
$sql = "SELECT id, surname, name, patronymic, phone, email, problem, file_links, created_at, assistant_sent_at, 
        revision_requested_at, revision_comment, revision_files
        FROM form_submissions 
        WHERE visible_to_assistant = 1 AND resolved = 0
        ORDER BY id DESC
        LIMIT ?, ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $itemsPerPage);
$stmt->execute();
$result = $stmt->get_result();

$submissions = [];
while ($row = $result->fetch_assoc()) {
    // 🔹 Обработка file_links
    $row['file_links'] = json_decode($row['file_links'], true);

    if (is_array($row['file_links'])) {
        $row['file_links'] = array_map(function ($link) {
            if (is_string($link)) {
                return ['url' => $link, 'name' => basename($link)];
            } elseif (is_array($link) && isset($link['url'], $link['name'])) {
                return $link;
            }
            return null;
        }, $row['file_links']);

        // Фильтрация пустых значений
        $row['file_links'] = array_filter($row['file_links']);
    } else {
        $row['file_links'] = [];
    }

    // 🔹 Обработка revision_files (файлы для доработки)
    $row['revision_files'] = json_decode($row['revision_files'], true);

    if (is_array($row['revision_files'])) {
        $row['revision_files'] = array_map(function ($link) {
            if (is_string($link)) {
                return ['url' => $link, 'name' => basename($link)];
            } elseif (is_array($link) && isset($link['url'], $link['name'])) {
                return $link;
            }
            return null;
        }, $row['revision_files']);

        // Фильтрация пустых значений
        $row['revision_files'] = array_filter($row['revision_files']);
    } else {
        $row['revision_files'] = [];
    }

    $submissions[] = $row;
}

// 🔹 Подсчет общего количества заявок
$countResult = $conn->query("SELECT COUNT(*) as total FROM form_submissions WHERE visible_to_assistant = 1 AND resolved = 0");
$totalCount = $countResult->fetch_assoc()['total'];

// 🔹 Формируем ответ
$response = [
    'success' => true,
    'submissions' => $submissions,
    'totalCount' => $totalCount
];

error_log("✅ Ответ JSON: " . json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
echo json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);

$stmt->close();
$conn->close();
?>
