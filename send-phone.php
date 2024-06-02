<?php
ini_set("log_errors", 1);
ini_set("error_log", "/home/ana6087438/analitikgroup.ru/docs/php_errors.log");

require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = strip_tags(trim($_POST["phone"]));
    if (empty($phone)) {
        $response['status'] = 'error';
        $response['message'] = 'Ошибка: номер телефона не указан.';
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $mail = new PHPMailer(true);
    try {
        // Настройка SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'analitikgrupp@gmail.com';
        $mail->Password = 'rhkt eykw wrrk fevo';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        // Отправитель
        $mail->setFrom('analitikgrupp@gmail.com', 'Новый телефон');

        // Получатель - юрист
        $mail->addAddress('i@aleksandr-kabanov.ru', 'Юрист');

        // Текст письма
        $mail->isHTML(false);
        $mail->Subject = 'Новый номер телефона с вашего сайта';
        $mail->Body = 'Номер телефона: ' . $phone . "\nIP адрес: " . $_SERVER['REMOTE_ADDR'];

        // Отправляем письмо юристу
        $mail->send();

        // Отправляем копию на рабочую почту
        $mail->clearAddresses();
        $mail->addAddress('analitikgrupp@gmail.com', 'АналитикГрупп');
        $mail->send();

        $response['status'] = 'success';
        $response['message'] = 'Сообщение отправлено';
    } catch (Exception $e) {
        error_log('Ошибка отправки сообщения: ' . $mail->ErrorInfo);
        $response['status'] = 'error';
        $response['message'] = 'Ошибка отправки сообщения: ' . $mail->ErrorInfo;
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
