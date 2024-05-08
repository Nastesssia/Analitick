<?php
ini_set("log_errors", 1);
ini_set("error_log", "/home/ana6087438/analitikgroup.ru/docs/php_errors.log");

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'DB_Functions.php';
$db = new DB_Functions();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $surname = strip_tags(trim($_POST["surname"]));
    $name = strip_tags(trim($_POST["name"]));
    $patronymic = strip_tags(trim($_POST["patronymic"]));
    $phone = strip_tags(trim($_POST["phone"]));
    $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
    $problem = strip_tags(trim($_POST["problem"]));


    if (!$email) {
        error_log("Ошибка: Неверный формат email: " . $_POST["email"]);
        echo "Ошибка: Неверный формат email.";
        exit;
    }

    if (!$db->saveSubmission($surname, $name, $patronymic, $phone, $email, $problem)) {
        error_log("Не удалось сохранить данные в базу.");
        echo "Ошибка: Не удалось сохранить данные.";
        exit;
    }

    $mail = new PHPMailer(true);
    try {
        // Настройка SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.yandex.ru';
        $mail->SMTPAuth = true;
        $mail->Username = 'i@aleksandr-kabanov.ru';
        $mail->Password = 'jqkovuasyhyfpolt';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8'; // Установка кодировки

        // Отправитель
        $mail->setFrom('i@aleksandr-kabanov.ru', $email);


        // Получатель - юрист
        $mail->addAddress('i@aleksandr-kabanov.ru', $name);

        // Ответ пользователю
        $mail->addReplyTo($email, $name);

        // Текст письма
        $mail->isHTML(false);
        $mail->Subject = "Новая заявка с вашего сайта";
        $mail->Body = "Фамилия: $surname\nИмя: $name\nОтчество: $patronymic\nТелефон: $phone\nEmail: $email\nПроблема: $problem\nIP адрес: " . $_SERVER['REMOTE_ADDR'] . "\n";


        // Вложения
        if (!empty($_FILES['files']['name'][0])) {
            $allowed_types = [
                'image/jpeg',
                'image/png',
                'application/pdf',
                'application/x-rar-compressed', // для RAR файлов
                'application/rar', // для RAR файлов
                'application/msword', // для старых версий Word файлов (.doc)
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // для новых Word файлов (.docx)
                'application/vnd.ms-excel', // для старых версий Excel файлов (.xls)
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // для новых Excel файлов (.xlsx)
                'application/vnd.ms-powerpoint', // для старых версий PowerPoint файлов (.ppt)
                'application/vnd.openxmlformats-officedocument.presentationml.presentation', // для новых PowerPoint файлов (.pptx)
                'application/zip', // для ZIP файлов
                'application/x-zip-compressed', // для ZIP файлов
                'application/octet-stream', // добавлен для поддержки общих двоичных файлов
                'text/plain' // для TXT файлов
            ];




            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
            foreach ($_FILES['files']['name'] as $i => $name) {
                $fileSize = $_FILES['files']['size'][$i];
                $fileType = $_FILES['files']['type'][$i];
                error_log("Тип файла: $fileType, Размер файла: $fileSize");

                if ($fileSize < 30000000 && in_array($fileType, $allowed_types)) {
                    $target = $upload_dir . basename($name);
                    if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $target)) {
                        $mail->addAttachment($target, $name);
                    } else {
                        $mail->Body .= "Ошибка загрузки файла: $name.\n";
                    }
                } else {
                    $mail->Body .= "Недопустимый тип или размер файла: $name.\n";
                }
            }


        }

        // Отправляем письмо юристу
        $mail->send();

        // Отправляем копию пользователю
        $mail->clearAddresses();
        $mail->addAddress($email, $name); // Email пользователя
        $mail->send();

        echo "Спасибо! Ваше сообщение было отправлено.";
    } catch (Exception $e) {
        echo 'Ошибка отправки сообщения: ', $mail->ErrorInfo;
    }
} else {
    echo "Ошибка: необходимо отправить POST запрос.";
}
?>