<?php
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

$allowedOrigins = [
    'https://analitikgroup.ru',
    'https://www.analitikgroup.ru'
];

if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
}

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/php_errors.log");


require 'vendor/autoload.php';
require_once 'DB_Functions.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Google\Client;
use Google\Service\Drive;

header('Content-Type: application/json');

$response = [];
$db = new DB_Functions();
// Функция для авторизации в Google API
function getGoogleClient()
{
    $client = new Client();
    $client->setAuthConfig(__DIR__ . '/credentials.json');

    $client->addScope(Drive::DRIVE_FILE);
    $client->setAccessType('offline');
    return $client;
}

// Функция загрузки файла на Google Drive и получения ссылки
function uploadFileToDrive($filePath, $fileName, $parentFolderId = null)
{
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




    return "https://drive.google.com/file/d/" . $file->id . "/view";
}


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
    $fileLinks = [];
    $fileNames = [];
    $parentFolderId = '1m1IQWVmhz7BZFXw_g8st2BI5sCGhjSdi';
    // Расширения для загрузки на Google Drive
    $linkExtensions = ['7z', 'zip', 'rar'];
    $mail = new PHPMailer(true);

    
    if (!empty($_FILES['files']['name'][0])) {
        foreach ($_FILES['files']['tmp_name'] as $i => $fileTmpPath) {
            $fileName = $_FILES['files']['name'][$i];
            $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    
            if (is_uploaded_file($fileTmpPath)) {
                // Загружаем файл на Google Drive и получаем ссылку
                $link = uploadFileToDrive($fileTmpPath, $fileName, $parentFolderId);
                error_log("Ссылка на файл: " . $link);
                if ($link) {
                    $fileLinks[] = [
                        'url' => $link,
                        'name' => $fileName
                    ]; // Сохраняем ссылку и название файла в базу данных
    
                    // В письме: ссылки только для архивов, остальные - как вложения
                    if (in_array($fileExtension, $linkExtensions)) {
                        $fileNames[] = "<li><a href='$link' target='_blank'>$fileName</a></li>";
                    } else {
                        $mail->addAttachment($fileTmpPath, $fileName);
                        $fileNames[] = "<li>$fileName</li>";
                    }
                }
            }
        }
    }
    
    
    $fileLinksJson = json_encode($fileLinks);
    error_log("JSON ссылок на файлы: " . $fileLinksJson);
    // Сохранение данных в базу данных
    $saveResult = $db->saveSubmission($surname, $name, $patronymic, $phone, $email, $problem, $fileLinksJson);
    error_log("Результат сохранения данных в БД: " . ($saveResult ? 'успех' : 'ошибка'));

    if (!$saveResult) {
        $response['status'] = 'error';
        $response['message'] = 'Ошибка при сохранении данных в базу данных.';
        echo json_encode($response);
        exit;
    }
    error_log("Перед отправкой письма. Ссылки на файлы: " . $fileLinksJson);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'analitikgrupp@gmail.com';
        $mail->Password = 'rhkt eykw wrrk fevo';
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;
        $mail->CharSet = 'UTF-8';

        $mail->setFrom('i@aleksandr-kabanov.ru', $email);
        $mail->addAddress('i@aleksandr-kabanov.ru', 'Юрист');
        $mail->addReplyTo($email, $email);

     
        // Создание содержимого Word "на лету"
        $wordContent = "
<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Заявка с сайта АналитикГрупп</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h3 { color: #970E0E; }
        p, li { margin: 5px 0; }
        hr { border: 1px solid #970E0E; margin: 10px 0; }
    </style>
</head>
<body>
    <h3>Заявка на сайте <a href='http://analitikgroup.ru/'>analitikgroup.ru</a></h3>
    <hr>
    <p><strong>Фамилия:</strong> $surname</p>
    <p><strong>Имя:</strong> $name</p>
    <p><strong>Отчество:</strong> $patronymic</p>
    <p><strong>Телефон:</strong> $phone</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Проблема:</strong> $problem</p>
    <hr>
    <p><strong>Файлы:</strong></p>
    <ul>" . implode("\n", $fileNames) . "</ul>
    <hr>
    <p>Я, <strong>$surname $name $patronymic</strong>, выражаю согласие на передачу и обработку персональных данных.</p>
    <p><strong>Отправлено с IP адреса:</strong> " . $_SERVER['REMOTE_ADDR'] . "</p>
    <p><em>Внимание! Это сообщение создается автоматически! Не отвечайте на него с помощью кнопки 'ответить (reply)'. Для связи с автором используйте контактные данные, указанные в теле письма.</em></p>
</body>
</html>
";
        // Добавление Word-документа как вложения
        $mail->addStringAttachment($wordContent, 'Онлайн заявка по средствам интернет_' . $surname . '.doc', 'base64', 'application/msword');




        $mail->isHTML(true);
        $mail->Subject = "Новая заявка с вашего сайта";
        $mail->Body = "
                      <h3 style='margin-bottom: 10px;'>Заполнена заявка на сайте <a href='http://analitikgroup.ru/'>analitikgroup.ru</a></h3>
                      <hr style='border: 1px solid #970E0E; margin-bottom: 10px;'>
                      <p style='margin: 4px 0;'><strong>Фамилия:</strong> $surname</p>
                      <p style='margin: 4px 0;'><strong>Имя:</strong> $name</p>
                      <p style='margin: 4px 0;'><strong>Отчество:</strong> $patronymic</p>
                      <p style='margin: 4px 0;'><strong>Телефон:</strong> $phone</p>
                      <p style='margin: 4px 0;'><strong>Email:</strong> $email</p>
                      <p style='margin: 4px 0;'><strong>Проблема:</strong> $problem</p>
                      <hr style='border: 1px solid #970E0E; margin-top: 10px;'>
                      <p style='margin: 4px 0;'><strong>Файлы:</strong></p>
                       <ul>"
            . implode("\n", $fileNames) .
            "</ul>
                      <hr style='border: 1px solid #970E0E; margin-top: 10px;'>
                      <p>Я, <strong>$surname $name $patronymic</strong>, выражаю согласие на передачу и обработку персональных данных.</p>
                      <p><strong>Отправлено с IP адреса:</strong> " . $_SERVER['REMOTE_ADDR'] . "</p>
                      <p><em>Внимание! Это сообщение создается автоматически! Не отвечайте на него с помощью кнопки 'ответить (reply)'. Для связи с автором используйте контактные данные, указанные в теле письма.</em></p>
                      ";

        // Отправляем письмо юристу
        $mail->send();

// Отправляем копию письма пользователю, который оставил заявку
$mail->clearAllRecipients();
$mail->clearAttachments();  // <-- Очищаем вложения
$mail->addAddress($email);
$mail->setFrom('i@aleksandr-kabanov.ru', 'АналитикГрупп');
$mail->Subject = "Копия вашей заявки с сайта АналитикГрупп";

$htmlBody = "
    <h3>Спасибо за вашу заявку на сайте <a href='http://analitikgroup.ru/'>analitikgroup.ru</a></h3>
    <p>Мы получили вашу заявку со следующими данными:</p>
    <p><strong>Фамилия:</strong> $surname</p>
    <p><strong>Имя:</strong> $name</p>
    <p><strong>Отчество:</strong> $patronymic</p>
    <p><strong>Телефон:</strong> $phone</p>
    <p><strong>Email:</strong> $email</p>
    <p><strong>Проблема:</strong> $problem</p>
    <p>В ближайшее время с вами свяжутся.</p>
    <p><strong>Файлы, которые вы отправили:</strong></p>
<ul>" . implode("\n", $fileNames) . "</ul>
    <hr>
    <p><em>Это письмо создано автоматически. Не отвечайте на него.</em></p>
";
$fileListText = "";
foreach ($fileLinks as $file) {
    $fileListText .= "- " . $file['name'] . "\n";
}
$textBody = "Спасибо за вашу заявку на сайте analitikgroup.ru\n\n"
    . "Фамилия: $surname\n"
    . "Имя: $name\n"
    . "Отчество: $patronymic\n"
    . "Телефон: $phone\n"
    . "Email: $email\n"
    . "Проблема: $problem\n\n"
       . "Файлы, которые вы отправили:\n"
    . $fileListText . "\n"
    . "В ближайшее время с вами свяжутся.\n"
    . "---\n"
    . "Это письмо создано автоматически. Не отвечайте на него.";

$mail->isHTML(true);
$mail->Body = $htmlBody;
$mail->AltBody = $textBody;

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