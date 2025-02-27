<?php
session_start();
header('Content-Type: application/json');

require_once 'DB_Connect.php';
$db = new DB_Connect();
$conn = $db->connect();

// Логирование ошибок в php_errors.log
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

error_log("🚦 get_assistant_submissions.php: Скрипт запущен");

// Проверяем авторизацию пользователя
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'assistant') {
    error_log("🚫 Доступ запрещен. Роль: " . ($_SESSION['role'] ?? 'не определена'));
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен.']);
    exit();
}

// Параметры пагинации
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$itemsPerPage = isset($_GET['itemsPerPage']) ? intval($_GET['itemsPerPage']) : 25;
$offset = ($page - 1) * $itemsPerPage;

error_log("📄 Параметры пагинации: Страница {$page}, Кол-во на странице {$itemsPerPage}, Смещение {$offset}");

// Получение заявок, отправленных помощнику и не решенных
$sql = "SELECT id, surname, name, patronymic, phone, email, problem, file_links, created_at, assistant_sent_at 
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
    // Обработка file_links
    if (!empty($row['file_links'])) {
        $fileLinks = json_decode($row['file_links'], true);

        if (is_array($fileLinks)) {
            $row['file_links'] = array_map(function ($link) {
                if (is_string($link)) {
                    return ['url' => $link, 'name' => basename($link)];
                } elseif (is_array($link) && isset($link['url'], $link['name'])) {
                    return $link;
                }
                return null;
            }, $fileLinks);
            
            // Фильтрация возможных пустых значений
            $row['file_links'] = array_filter($row['file_links']);
        } else {
            $row['file_links'] = [];
        }
    } else {
        $row['file_links'] = [];
    }
    
    $submissions[] = $row;
}

// Подсчет общего количества заявок для правильного отображения страниц
$countResult = $conn->query("SELECT COUNT(*) as total FROM form_submissions WHERE visible_to_assistant = 1 AND resolved = 0");
$totalCount = $countResult->fetch_assoc()['total'];

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
