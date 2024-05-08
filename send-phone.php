<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

// Проверяем, было ли уже отправлено сообщение
if (!isset($_SESSION['phone_sent'])) {
    $mail = new PHPMailer(true);

    // Конфигурация SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.yandex.ru';
    $mail->SMTPAuth = true;
    $mail->Username = 'i@aleksandr-kabanov.ru';
    $mail->Password = 'jqkovuasyhyfpolt';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8'; // Установка кодировки

    // Отправитель
    $mail->setFrom($email, 'АналитикГрупп');


    // Получатель - юрист
    $mail->addAddress('i@aleksandr-kabanov.ru', 'Имя получателя');

    // Содержимое
    $mail->isHTML(false); // Установите в true, если нужно отправить HTML-письмо
    $mail->Subject = 'Новый номер телефона с вашего сайта';
    $mail->Body = 'Номер телефона: ' . $_POST['phone'] . "\nIP адрес: " . $_SERVER['REMOTE_ADDR'];


    try {
        $mail->send();
        echo 'Сообщение отправлено';

        // Устанавливаем флаг, чтобы указать, что сообщение уже было отправлено
        $_SESSION['phone_sent'] = true;
    } catch (Exception $e) {
        echo 'Ошибка отправки сообщения. Ошибка: ', $mail->ErrorInfo;
    }
} else {
    echo 'Сообщение уже было отправлено';
}
?>