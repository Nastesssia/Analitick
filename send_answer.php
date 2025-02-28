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
$subject = $data['subject'] ?? '';
$answer_text = $data['answer_text'] ?? '';
$surname = $data['surname'] ?? '';
$name = $data['name'] ?? '';
$patronymic = $data['patronymic'] ?? '';
$phone = $data['phone'] ?? '';
$email = $data['email'] ?? '';
$problem = $data['problem'] ?? '';
$file_links = json_decode($data['file_links'] ?? '[]', true);

// Логирование данных
error_log("📦 Полученные данные: " . json_encode($data, JSON_UNESCAPED_UNICODE));
error_log("📂 Полученные файлы: " . json_encode($_FILES, JSON_UNESCAPED_UNICODE));

// Проверка обязательных данных
if (empty($subject) || empty($answer_text) || empty($email) || empty($problem)) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные для отправки ответа.']);
    exit();
}

// Подключение к базе данных
$db = new DB_Connect();
$conn = $db->connect();

// Поиск заявки по email и тексту проблемы
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
    $mail->Subject = "Ответ на заявку: {$subject}";

    // Формирование тела письма
    $mailContent = "<h2>Копия заявки:</h2>
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
        <p><strong>Ответ:</strong> {$answer_text}</p>
        <hr>
        <p><strong>Приложенные файлы:</strong></p>
        <ul>";

    // Обработка прикрепленных файлов
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

  // Обновление статуса заявки и установка времени решения в базе данных
$stmt = $conn->prepare("UPDATE form_submissions SET resolved = 1, visible_to_assistant = 0, assistant_resolved_at = NOW() WHERE id = ?");
$stmt->bind_param("i", $submission_id);
$stmt->execute();

    echo json_encode(['success' => true, 'message' => 'Ответ успешно отправлен.']);
    error_log("✅ Заявка ID {$submission_id} успешно обновлена.");

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при отправке письма: ' . $e->getMessage()]);
    error_log("❌ Ошибка при отправке письма: " . $e->getMessage());
}

$conn->close();

?>
