<?php
session_start();
header("Content-Type: application/json");

require_once 'DB_Connect.php';
require_once 'vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

// 🔹 Логирование ошибок (включаем для отладки)
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/php_errors.log");

// 🔹 Функция для Google Drive
function getGoogleClient()
{
    $client = new Client();
    $client->setAuthConfig('credentials.json');
    $client->addScope(Drive::DRIVE_FILE);
    $client->setAccessType('offline');
    return $client;
}

// 🔹 Функция загрузки файла на Google Drive
function uploadFileToDrive($filePath, $fileName, $parentFolderId = null)
{
    try {
        $client = getGoogleClient();
        $driveService = new Drive($client);

        $fileMetadata = new Drive\DriveFile([
            'name' => $fileName,
            'parents' => $parentFolderId ? [$parentFolderId] : []
        ]);

        $content = file_get_contents($filePath);
        $file = $driveService->files->create($fileMetadata, [
            'data' => $content,
            'mimeType' => mime_content_type($filePath),
            'uploadType' => 'multipart',
            'fields' => 'id'
        ]);

        // Доступ для всех
        $driveService->permissions->create($file->id, new Drive\Permission([
            'type' => 'anyone',
            'role' => 'reader'
        ]));

        return [
            "url" => "https://drive.google.com/file/d/" . $file->id . "/view",
            "name" => $fileName
        ];
    } catch (Exception $e) {
        error_log("Ошибка загрузки файла в Google Drive: " . $e->getMessage());
        return null;
    }
}

// 🔹 Проверка авторизации
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'lawyer') {
    echo json_encode(["success" => false, "message" => "Доступ запрещен."]);
    exit();
}

// 🔹 Подключение к БД
$db = new DB_Connect();
$conn = $db->connect();

// 🔹 Получение данных
$submission_id = $_POST['submission_id'] ?? null;
$revision_comment = trim($_POST['revision_comment'] ?? '');
$revision_files = [];

if (!$submission_id || empty($revision_comment)) {
    echo json_encode(["success" => false, "message" => "Некорректные данные."]);
    exit();
}

// 🔹 Логируем файлы (проверяем, приходят ли они)
error_log("🔍 Файлы, полученные на сервере: " . print_r($_FILES, true));

$parentFolderId = '1m1IQWVmhz7BZFXw_g8st2BI5sCGhjSdi';

// 🔹 Проверяем, пришли ли файлы
if (!empty($_FILES['files']['name'][0])) {  
    foreach ($_FILES['files']['tmp_name'] as $i => $fileTmpPath) {
        $fileName = $_FILES['files']['name'][$i];

        if (is_uploaded_file($fileTmpPath)) {
            $uploadedFile = uploadFileToDrive($fileTmpPath, $fileName, $parentFolderId);
            if ($uploadedFile) {
                $revision_files[] = $uploadedFile;
                error_log("✅ Файл загружен: " . json_encode($uploadedFile));
            } else {
                error_log("❌ Ошибка загрузки файла: $fileName");
            }
        } else {
            error_log("❌ Ошибка: файл $fileName не был корректно загружен.");
        }
    }
}

// 🔹 Кодируем файлы в JSON
$fileLinksJson = json_encode($revision_files, JSON_UNESCAPED_UNICODE);

// 🔹 Обновляем запись в БД
$stmt = $conn->prepare("
    UPDATE form_submissions 
    SET revision_requested_at = NOW(), 
        revision_comment = ?, 
        revision_files = ?,
        resolved = 0, 
        visible_to_assistant = 1
    WHERE id = ?
");
$stmt->bind_param("ssi", $revision_comment, $fileLinksJson, $submission_id);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    echo json_encode([
        "success" => true, 
        "message" => "Заявка отправлена на доработку.", 
        "files" => $revision_files
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Ошибка при обновлении заявки."]);
}

// 🔹 Закрываем соединение
$stmt->close();
$conn->close();
?>
