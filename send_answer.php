<?php
session_start();
header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';
require_once 'DB_Connect.php';

// Логирование ошибок
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/php_errors.log');
error_reporting(E_ALL);

// Проверка авторизации
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'assistant') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен.']);
    exit();
}

// Получение данных формы
$data = $_POST;
error_log("📦 Полученные данные: " . json_encode($data, JSON_UNESCAPED_UNICODE));

$subject = trim($data['subject'] ?? '');
$answer_text = trim($data['answer_text'] ?? '');
$surname = trim($data['surname'] ?? '');
$name = trim($data['name'] ?? '');
$patronymic = trim($data['patronymic'] ?? '');
$phone = trim($data['phone'] ?? '');
$email = trim($data['email'] ?? '');
$problem = trim($data['problem'] ?? '');
$file_links = json_decode($data['file_links'] ?? '[]', true);
$revision_comment = trim($data['revision_comment'] ?? ''); // Комментарий на доработку

// 🔹 Теперь **файлы не влияют** на определение доработки
$is_revision = !empty($revision_comment);
error_log("🔍 Это доработка? " . ($is_revision ? "Да" : "Нет"));

// Проверка обязательных данных
if (empty($subject) || empty($answer_text) || empty($email) || empty($problem)) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные для отправки ответа.']);
    exit();
}

// Подключение к базе данных
$db = new DB_Connect();
$conn = $db->connect();

// Поиск заявки по email и проблеме
$stmt = $conn->prepare("SELECT id FROM form_submissions WHERE email = ? AND problem = ? LIMIT 1");
$stmt->bind_param("ss", $email, $problem);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $submission = $result->fetch_assoc();
    $submission_id = $submission['id'];
    error_log("✅ Найдена заявка ID: {$submission_id}");
} else {
    echo json_encode(['success' => false, 'message' => 'Заявка не найдена.']);
    exit();
}

// Инициализация PHPMailer
$mail = new PHPMailer(true);
try {
    // SMTP настройки
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'analitikgrupp@gmail.com';
    $mail->Password = 'rhkt eykw wrrk fevo';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';

    // От кого
    $mail->setFrom('i@aleksandr-kabanov.ru', 'Кабинет помощника');

    // Кому
    $mail->addAddress('i@aleksandr-kabanov.ru', 'Юрист');
    $mail->addCC('analitikgrupp@gmail.com');

    // Тема письма
    $mail->Subject = "Ответ на заявку: {$subject}" . ($is_revision ? " (Доработка)" : "");

    // Формирование тела письма
    $mailContent = "<h2>Копия заявки клиента:</h2>
        <p><strong>Фамилия:</strong> {$surname}</p>
        <p><strong>Имя:</strong> {$name}</p>
        <p><strong>Отчество:</strong> {$patronymic}</p>
        <p><strong>Телефон:</strong> {$phone}</p>
        <p><strong>Email:</strong> {$email}</p>
        <p><strong>Проблема:</strong> {$problem}</p>
        <hr>
        <p><strong>Файлы клиента:</strong></p>
        <ul>";

    foreach ($file_links as $file) {
        $mailContent .= "<li><a href='{$file['url']}' target='_blank'>{$file['name']}</a></li>";
    }

    $mailContent .= "</ul>
        <hr>
        <h2>Ответ помощника:</h2>
        <p><strong>Тема:</strong> {$subject}</p>
        <p><strong>Ответ:</strong> {$answer_text}</p>";

    // Если заявка была доработана, добавляем комментарий
    if ($is_revision) {
        error_log("🛠 Обновляем заявку ID {$submission_id} с доработкой...");

        $mailContent .= "<hr><h3>🔄 Комментарий юриста к доработке:</h3>
                         <p><strong>Комментарий:</strong> {$revision_comment}</p>";
    }

    $mailContent .= "<hr><p><strong>Приложенные файлы помощника:</strong></p><ul>";

    // Прикрепленные файлы
    foreach ($_FILES as $file) {
        if ($file['error'] === UPLOAD_ERR_OK) {
            $mail->addAttachment($file['tmp_name'], $file['name']);
            $mailContent .= "<li>{$file['name']}</li>";
        }
    }

    $mailContent .= "</ul>";
    $mail->isHTML(true);
    $mail->Body = $mailContent;

    // Отправка письма
    $mail->send();
    error_log("📧 Письмо успешно отправлено.");

    // Обновление статуса заявки в БД
    if ($is_revision) {
        // Если есть комментарий на доработку → фиксируем `revision_completed_at`
        $stmt = $conn->prepare("
            UPDATE form_submissions 
            SET revision_comment = ?, 
                revision_completed_at = NOW(), 
                visible_to_assistant = 0, 
                resolved = 1
            WHERE id = ?
        ");
        $stmt->bind_param("si", $revision_comment, $submission_id);
        $stmt->execute();
        error_log("✅ Заявка ID {$submission_id} отмечена как доработка.");
    } else {
        // Если нет комментария → просто закрываем заявку
        $stmt = $conn->prepare("
            UPDATE form_submissions 
            SET resolved = 1, 
                visible_to_assistant = 0, 
                assistant_resolved_at = IFNULL(assistant_resolved_at, NOW())
            WHERE id = ?
        ");
        $stmt->bind_param("i", $submission_id);
        $stmt->execute();
        error_log("✅ Заявка ID {$submission_id} успешно решена.");
    }

    echo json_encode(['success' => true, 'message' => 'Ответ успешно отправлен.']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при отправке письма: ' . $e->getMessage()]);
    error_log("❌ Ошибка при отправке письма: " . $e->getMessage());
}

$conn->close();
?>
