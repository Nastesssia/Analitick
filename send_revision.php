<?php
session_start();
header("Content-Type: application/json; charset=utf-8");

require_once 'DB_Connect.php';
require_once 'Config.php';
require_once 'yadisk_lib.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'lawyer') {
    echo json_encode(["success" => false, "message" => "Доступ запрещен."], JSON_UNESCAPED_UNICODE);
    exit();
}

$db = new DB_Connect();
$conn = $db->connect();

$submission_id = isset($_POST['submission_id']) ? (int)$_POST['submission_id'] : 0;
$revision_comment = trim($_POST['revision_comment'] ?? '');

if ($submission_id <= 0 || $revision_comment === '') {
    echo json_encode(["success" => false, "message" => "Некорректные данные."], JSON_UNESCAPED_UNICODE);
    exit();
}

$revision_files = [];

if (!empty($_FILES['files']['name'][0])) {
    foreach ($_FILES['files']['tmp_name'] as $i => $tmp) {
        $origName = $_FILES['files']['name'][$i] ?? '';
        if ($origName === '' || !is_uploaded_file($tmp)) continue;

        $safeName = str_replace(["\0", "/", "\\"], "_", $origName);
        $fileId = bin2hex(random_bytes(16));
        $remotePath = rtrim(YD_ASSISTANT_DIR, '/') . '/' . $submission_id . '/revision/' . time() . '_' . $fileId . '_' . $safeName;

        if (!yd_put_file($tmp, $remotePath)) {
            echo json_encode(["success" => false, "message" => "Не удалось загрузить файл: {$origName}"], JSON_UNESCAPED_UNICODE);
            exit();
        }

        $revision_files[] = ['id' => $fileId, 'name' => $origName, 'path' => $remotePath];
    }
}

$fileLinksJson = json_encode($revision_files, JSON_UNESCAPED_UNICODE);

$stmt = $conn->prepare(
    "UPDATE form_submissions
     SET revision_requested_at = NOW(),
         revision_comment = ?,
         revision_files = ?,
         resolved = 0,
         visible_to_assistant = 1
     WHERE id = ?"
);
$stmt->bind_param("ssi", $revision_comment, $fileLinksJson, $submission_id);
$stmt->execute();

echo json_encode(["success" => true, "message" => "Отправлено на доработку."], JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();
?>
