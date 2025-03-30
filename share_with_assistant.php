<?php
session_start();
header("Content-Type: application/json");

require_once 'DB_Connect.php';
require_once 'vendor/autoload.php';
use Google\Client;
use Google\Service\Drive;

function getGoogleClient()
{
    $client = new Client();
    $client->setAuthConfig(__DIR__ . '/credentials.json');
    $client->addScope(Drive::DRIVE);
    $client->setAccessType('offline');
    return $client;
}

function giveAccessToUsers($fileId, $emails)
{
    $client = getGoogleClient();
    $drive = new Drive($client);

    foreach ($emails as $email) {
        try {
            $permission = new Drive\Permission([
                'type' => 'user',
                'role' => 'reader',
                'emailAddress' => $email
            ]);
            $drive->permissions->create($fileId, $permission, ['sendNotificationEmail' => false]);
            error_log("✅ Доступ выдан: $email к файлу $fileId");
        } catch (Exception $e) {
            error_log("❌ Ошибка доступа для $email к $fileId: " . $e->getMessage());
            return false;
        }
    }
    return true;
}

$db = new DB_Connect();
$conn = $db->connect();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lawyer') {
    echo json_encode(["success" => false, "message" => "Доступ запрещен."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);
$id = isset($data['id']) ? intval($data['id']) : 0;

if ($id <= 0) {
    echo json_encode(["success" => false, "message" => "Неверный ID заявки."]);
    exit();
}

$stmt = $conn->prepare("SELECT file_links FROM form_submissions WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    echo json_encode(["success" => false, "message" => "Заявка не найдена."]);
    exit();
}

$fileLinks = json_decode($row['file_links'], true);

// 📩 Список помощников, которым надо дать доступ
$assistantEmails = [
    "starovoitova.ann@gmail.com",
    "tashoglo00@mail.ru"
];

foreach ($fileLinks as $file) {
    if (isset($file['url']) && preg_match("#drive\.google\.com\/file\/d\/([^/]+)#", $file['url'], $matches)) {
        $fileId = $matches[1];
        $success = giveAccessToUsers($fileId, $assistantEmails);
        if (!$success) {
            echo json_encode(["success" => false, "message" => "Ошибка при выдаче доступа к файлу: {$file['name']}"]);
            exit();
        }
    }
}

$stmtUpdate = $conn->prepare("UPDATE form_submissions SET visible_to_assistant = 1, assistant_sent_at = NOW() WHERE id = ?");
$stmtUpdate->bind_param("i", $id);

if ($stmtUpdate->execute()) {
    echo json_encode(["success" => true, "message" => "Заявка успешно отправлена помощникам."]);
} else {
    echo json_encode(["success" => false, "message" => "Ошибка при обновлении заявки."]);
}

$stmtUpdate->close();
$conn->close();


?>
