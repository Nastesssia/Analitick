<?php
ini_set("log_errors", 1);
ini_set("error_log", "/home/ana6087438/analitikgroup.ru/docs/php_errors.log");

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'DB_Functions.php';
$db = new DB_Functions();

header('Content-Type: application/json');

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $surname = strip_tags(trim($_POST["surname"]));
    $name = strip_tags(trim($_POST["name"]));
    $patronymic = strip_tags(trim($_POST["patronymic"]));
    $phone = strip_tags(trim($_POST["phone"]));
    $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
    $problem = strip_tags(trim($_POST["problem"]));

    if (!$email) {
        error_log("Ошибка: Неверный формат email: " . $_POST["email"]);
        $response['status'] = 'error';
        $response['message'] = 'Ошибка: Неверный формат email.';
        echo json_encode($response);
        exit;
    }

    if (!$db->saveSubmission($surname, $name, $patronymic, $phone, $email, $problem)) {
        error_log("Не удалось сохранить данные в базу.");
        $response['status'] = 'error';
        $response['message'] = 'Ошибка: Не удалось сохранить данные.';
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
        $mail->CharSet = 'UTF-8'; // Установка кодировки

        // Отправитель
        $mail->setFrom('i@aleksandr-kabanov.ru', $email);

        // Получатель - юрист
        $mail->addAddress('i@aleksandr-kabanov.ru', 'Юрист');

        // Ответ пользователю
        $mail->addReplyTo($email, $email);

        // Сбор имен файлов
        $fileNames = [];
        if (!empty($_FILES['files']['name'][0])) {
            $allowed_types = [
                'image/jpeg',
                'image/png',
                'image/gif', // для изображений
                'application/pdf',
                'application/x-rar-compressed',
                'application/rar',
                'application/zip',
                'application/x-zip-compressed', // для архивов
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // для документов Word
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // для документов Excel
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation', // для презентаций PowerPoint
                'application/octet-stream',
                'text/plain',
                'text/csv',
                'application/xml',
                'text/html',
                'audio/mpeg',
                'audio/wav',
                'video/mp4', // для прочих файлов
                'application/json',
                'application/vnd.ms-access',
                'application/x-shockwave-flash',
                'image/svg+xml',
                'application/x-7z-compressed',
                'application/x-tar',
                'application/x-iwork-pages-sffpages' // для специфичных файлов
            ];

            $upload_dir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';
            foreach ($_FILES['files']['name'] as $i => $file_name) {
                $fileSize = $_FILES['files']['size'][$i];
                $fileType = $_FILES['files']['type'][$i];
                error_log("Тип файла: $fileType, Размер файла: $fileSize");

                if ($fileSize < 30000000 && in_array($fileType, $allowed_types)) {
                    $target = $upload_dir . basename($file_name);
                    if (move_uploaded_file($_FILES['files']['tmp_name'][$i], $target)) {
                        $mail->addAttachment($target, $file_name);
                        $fileNames[] = $file_name;
                        error_log("Файл успешно загружен: $file_name, Размер: $fileSize");
                    } else {
                        $mail->Body .= "Ошибка загрузки файла: $file_name.\n";
                        error_log("Ошибка загрузки файла: $file_name");
                    }
                } else {
                    $mail->Body .= "Недопустимый тип или размер файла: $file_name.\n";
                    error_log("Недопустимый тип или размер файла: $file_name, Тип: $fileType, Размер: $fileSize");
                }
            }
        }

        $fileNamesString = implode("\n", $fileNames);

        // Текст письма
        $mail->isHTML(true);
        $mail->Subject = "Новая заявка с вашего сайта";
        $mail->Body = "<h3>Заполнена заявка на сайте http://analitikgroup.ru/</h3>
                      <hr style='border: 1px solid #970E0E;'>
                      <p style='margin: 2px 0;'><strong>Фамилия:</strong> $surname</p>
                      <p style='margin: 2px 0;'><strong>Имя:</strong> $name</p>
                      <p style='margin: 2px 0;'><strong>Отчество:</strong> $patronymic</p>
                      <p style='margin: 2px 0;'><strong>Телефон:</strong> $phone</p>
                      <p style='margin: 2px 0;'><strong>Email:</strong> $email</p>
                      <p style='margin: 2px 0;'><strong>Проблема:</strong> $problem</p>
                 
                      <p style='margin: 4px 0;'><strong>Файлы:</strong></p>
                      <ul style='margin: 2px 0;'>
                      " . implode("\n", array_map(fn($file) => "<li>$file</li>", $fileNames)) . "
                      </ul>
                      <hr style='border: 1px solid #970E0E;'>
                      <p>Я, <strong>$surname $name $patronymic</strong>, выражаю согласие на передачу и обработку персональных данных в соответствии с Политикой конфиденциальности.</p>
                      <p><strong>Отправлено с IP адреса:</strong> " . $_SERVER['REMOTE_ADDR'] . "</p>
                      <p><em>Внимание! Это сообщение создается автоматически! Не отвечайте на него с помощью кнопки 'ответить (reply)'. Для связи с автором используйте контактные данные, указанные в теле письма.</em></p>";

        // Отправляем письмо юристу
        $mail->send();

        // Отправляем копию пользователю
        $mail->clearAddresses();
        $mail->addAddress($email, $name); // Email пользователя
        $mail->Subject = "Копия Вашей заявки с сайта АналитикГрупп";
        $mail->send();

        // Отправляем копию на почту Analitik Grupp
        $mail->clearAddresses();
        $mail->addAddress('analitikgrupp@gmail.com', 'АналитикГрупп');
        $mail->Subject = "Копия заявки с сайта";
        $mail->send();

        $response['status'] = 'success';
        $response['message'] = 'Спасибо! Ваше сообщение было отправлено.';
    } catch (Exception $e) {
        error_log('Ошибка отправки сообщения: ' . $mail->ErrorInfo);
        $response['status'] = 'error';
        $response['message'] = 'Ошибка отправки сообщения: ' . $mail->ErrorInfo;
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Ошибка: необходимо отправить POST запрос.';
}

echo json_encode($response);
?>
