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
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'assistant') {
    echo json_encode(['success' => false, 'message' => 'Доступ запрещен.'], JSON_UNESCAPED_UNICODE);
    exit();
}

// Получение данных формы
$data = $_POST;
error_log("📦 Полученные данные: " . json_encode($data, JSON_UNESCAPED_UNICODE));

$subject       = trim($data['subject'] ?? '');
$answer_text   = trim($data['answer_text'] ?? '');
$submission_id = (int)($data['submission_id'] ?? 0);

$surname     = trim($data['surname'] ?? '');
$name        = trim($data['name'] ?? '');
$patronymic  = trim($data['patronymic'] ?? '');
$phone       = trim($data['phone'] ?? '');
$email       = trim($data['email'] ?? '');
$problem     = trim($data['problem'] ?? '');
$file_links  = json_decode($data['file_links'] ?? '[]', true);
$revision_comment = trim($data['revision_comment'] ?? ''); // Комментарий на доработку

if (!is_array($file_links)) $file_links = [];

// 🔹 Теперь **файлы не влияют** на определение доработки
$is_revision = !empty($revision_comment);
error_log("🔍 Это доработка? " . ($is_revision ? "Да" : "Нет"));

// Проверка обязательных данных
if ($submission_id <= 0 || $subject === '' || $answer_text === '') {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные для отправки ответа.'], JSON_UNESCAPED_UNICODE);
    exit();
}

// Подключение к базе данных
$db = new DB_Connect();
$conn = $db->connect();

// Проверка существования заявки по ID
$stmt = $conn->prepare("SELECT id FROM form_submissions WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $submission_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    error_log("✅ Найдена заявка ID: {$submission_id}");
} else {
    echo json_encode(['success' => false, 'message' => 'Заявка не найдена.'], JSON_UNESCAPED_UNICODE);
    $stmt->close();
    $conn->close();
    exit();
}
$stmt->close();

// Инициализация PHPMailer (Яндекс)
$mail = new PHPMailer(true);

try {
    // SMTP настройки Яндекс
    $mail->isSMTP();
    $mail->Host       = 'smtp.yandex.ru';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'alexander-kabanov@yandex.ru';
    $mail->Password   = 'mtbhefenluxjicxg';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;
    $mail->CharSet    = 'UTF-8';

    // Важно: FROM должен быть тем же ящиком, которым логинимся в SMTP
    $mail->setFrom('alexander-kabanov@yandex.ru', 'Кабинет помощника');

    // Кому (как у вас было)
    $mail->addAddress('i@aleksandr-kabanov.ru', 'Юрист');

    // Если нужно копию на Яндекс — оставьте, иначе можно убрать
    $mail->addCC('alexander-kabanov@yandex.ru');

    // Тема письма
    $mail->Subject = "Ответ на заявку: {$subject}" . ($is_revision ? " (Доработка)" : "");

    // Формирование тела письма
    $mailContent = "<h2>Копия заявки клиента:</h2>
        <p><strong>Фамилия:</strong> " . htmlspecialchars($surname) . "</p>
        <p><strong>Имя:</strong> " . htmlspecialchars($name) . "</p>
        <p><strong>Отчество:</strong> " . htmlspecialchars($patronymic) . "</p>
        <p><strong>Телефон:</strong> " . htmlspecialchars($phone) . "</p>
        <p><strong>Email:</strong> " . htmlspecialchars($email) . "</p>
        <p><strong>Проблема:</strong> " . nl2br(htmlspecialchars($problem)) . "</p>
        <hr>
        <p><strong>Файлы клиента:</strong></p>
        <ul>";

    foreach ($file_links as $file) {
        $url  = htmlspecialchars($file['url'] ?? '');
        $nameF = htmlspecialchars($file['name'] ?? $url);
        if ($url !== '') {
            $mailContent .= "<li><a href='{$url}' target='_blank'>{$nameF}</a></li>";
        }
    }

    $mailContent .= "</ul>
        <hr>
        <h2>Ответ помощника:</h2>
        <p><strong>Тема:</strong> " . htmlspecialchars($subject) . "</p>
        <p><strong>Ответ:</strong><br>" . nl2br(htmlspecialchars($answer_text)) . "</p>";

    if ($is_revision) {
        $mailContent .= "<hr><h3>🔄 Комментарий юриста к доработке:</h3>
                         <p><strong>Комментарий:</strong><br>" . nl2br(htmlspecialchars($revision_comment)) . "</p>";
    }

    $mailContent .= "<hr><p><strong>Приложенные файлы помощника:</strong></p><ul>";

    // Прикрепленные файлы (из Vue вы отправляете file_0, file_1, ...)
    foreach ($_FILES as $file) {
        if (!is_array($file) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) continue;
        $mail->addAttachment($file['tmp_name'], $file['name']);
        $mailContent .= "<li>" . htmlspecialchars($file['name']) . "</li>";
    }

    $mailContent .= "</ul>";

    $mail->isHTML(true);
    $mail->Body = $mailContent;

    // Отправка письма
    $mail->send();
    error_log("📧 Письмо успешно отправлено через Яндекс.");

    // Обновление статуса заявки в БД
    if ($is_revision) {
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
        $stmt->close();
        error_log("✅ Заявка ID {$submission_id} отмечена как доработка.");
    } else {
        $stmt = $conn->prepare("
            UPDATE form_submissions
            SET resolved = 1,
                visible_to_assistant = 0,
                assistant_resolved_at = IFNULL(assistant_resolved_at, NOW())
            WHERE id = ?
        ");
        $stmt->bind_param("i", $submission_id);
        $stmt->execute();
        $stmt->close();
        error_log("✅ Заявка ID {$submission_id} успешно решена.");
    }

    $conn->close();

    echo json_encode(['success' => true, 'message' => 'Ответ успешно отправлен.'], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    error_log("❌ Ошибка при отправке письма (Яндекс): " . $e->getMessage());
    $conn->close();
    echo json_encode(['success' => false, 'message' => 'Ошибка при отправке письма: ' . $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
?>
