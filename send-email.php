<?php

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = [
    'https://analitikgroup.ru',
    'https://www.analitikgroup.ru'
];

if (in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/php_errors.log");
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

require __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpWord\TemplateProcessor;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/Config.php';
require_once __DIR__ . '/DB_Functions.php';
require_once __DIR__ . '/yadisk_lib.php';

function out(array $arr, int $code = 200): void {
    http_response_code($code);
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

function upload_err_text(int $code): string {
    $map = [
        UPLOAD_ERR_OK => 'OK',
        UPLOAD_ERR_INI_SIZE => 'Превышен upload_max_filesize (php.ini)',
        UPLOAD_ERR_FORM_SIZE => 'Превышен MAX_FILE_SIZE (форма)',
        UPLOAD_ERR_PARTIAL => 'Файл загружен частично',
        UPLOAD_ERR_NO_FILE => 'Файл не передан',
        UPLOAD_ERR_NO_TMP_DIR => 'Нет временной папки (upload_tmp_dir)',
        UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск',
        UPLOAD_ERR_EXTENSION => 'Загрузка остановлена расширением PHP',
    ];
    return $map[$code] ?? ('Неизвестная ошибка: ' . $code);
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    out(['status' => 'error', 'message' => 'Ошибка: нужен POST запрос.'], 405);
}

// --- входные данные ---
$surname     = strip_tags(trim($_POST["surname"] ?? ''));
$name        = strip_tags(trim($_POST["name"] ?? ''));
$patronymic  = strip_tags(trim($_POST["patronymic"] ?? ''));
$phone       = strip_tags(trim($_POST["phone"] ?? ''));
$email       = filter_var(trim($_POST["email"] ?? ''), FILTER_VALIDATE_EMAIL);
$problem     = strip_tags(trim($_POST["problem"] ?? ''));

if (!$email) out(['status' => 'error', 'message' => 'Ошибка: Неверный формат email.'], 400);

// curl обязательно для WebDAV
if (!function_exists('curl_init')) {
    error_log("FATAL: curl extension missing");
    out(['status'=>'error','message'=>'Ошибка сервера: нет расширения curl (нужно для Яндекс.Диска).'], 500);
}

$db = new DB_Functions();

// 1) Создаём заявку сначала (чтобы получить ID)
$submissionId = $db->saveSubmission($surname, $name, $patronymic, $phone, $email, $problem, '[]');
if ($submissionId === false) {
    out(['status' => 'error', 'message' => 'Ошибка при сохранении данных в базу данных.'], 500);
}

$fileLinks = [];      // [{id,name,path}]
$fileNamesHtml = [];  // <li>...</li> для писем

$linkExtensions = ['7z', 'zip', 'rar'];
$MAX_ATTACH_BYTES = 18 * 1024 * 1024; // 18MB (большие архивы не прикрепляем письмом)

// 2) Загружаем файлы на Яндекс.Диск в /uploads/<ID>/...
if (!empty($_FILES['files']['name'][0])) {
    foreach ($_FILES['files']['tmp_name'] as $i => $tmpPath) {
        $origName = (string)($_FILES['files']['name'][$i] ?? '');
        $size     = (int)($_FILES['files']['size'][$i] ?? 0);
        $err      = (int)($_FILES['files']['error'][$i] ?? UPLOAD_ERR_NO_FILE);

        if ($origName === '') continue;

        if ($err !== UPLOAD_ERR_OK) {
            error_log("UPLOAD_ERR: {$origName} => " . upload_err_text($err));
            $fileNamesHtml[] = "<li>" . htmlspecialchars($origName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . " (" . htmlspecialchars(upload_err_text($err), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . ")</li>";
            continue;
        }

        if (!is_uploaded_file($tmpPath)) {
            error_log("UPLOAD_SKIP (not uploaded file): {$origName} tmp={$tmpPath}");
            $fileNamesHtml[] = "<li>" . htmlspecialchars($origName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . " (не удалось прочитать файл)</li>";
            continue;
        }

        $safeName = str_replace(["\0", "/", "\\"], "_", $origName);
        $fileId   = bin2hex(random_bytes(16));

        // путь на диске
        $remotePath = rtrim(YD_UPLOADS_DIR, '/') . '/' . $submissionId . '/' . time() . '_' . $fileId . '_' . $safeName;

        $dir = dirname($remotePath);
        if (!yd_mkcol_recursive($dir)) {
            error_log("YADISK MKCOL failed: dir={$dir}");
            $fileNamesHtml[] = "<li>" . htmlspecialchars($origName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . " (ошибка создания папки на Я.Диске)</li>";
            continue;
        }

        $put = yd_request('PUT', $remotePath, ['Content-Type: application/octet-stream'], $tmpPath);
        if (!in_array((int)$put['code'], [201, 204], true)) {
            $bodyShort = mb_substr((string)$put['body'], 0, 300);
            error_log("YADISK PUT failed: name={$origName} path={$remotePath} code={$put['code']} err={$put['err']} body={$bodyShort}");
            $fileNamesHtml[] = "<li>" . htmlspecialchars($origName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . " (ошибка загрузки на Я.Диск)</li>";
            continue;
        }

        $fileLinks[] = [
            'id'   => $fileId,
            'name' => $origName,
            'path' => $remotePath
        ];

        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if (in_array($ext, $linkExtensions, true) && $size > $MAX_ATTACH_BYTES) {
            $fileNamesHtml[] = "<li>" . htmlspecialchars($origName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . " (архив, скачивание в кабинете по ID заявки)</li>";
        } else {
            $fileNamesHtml[] = "<li>" . htmlspecialchars($origName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</li>";
        }
    }
} else {
    $fileNamesHtml[] = "<li>Файлы не приложены</li>";
}

// 3) Обновляем file_links в БД
$db->updateSubmissionFiles((int)$submissionId, json_encode($fileLinks, JSON_UNESCAPED_UNICODE));

// 4) Генерация DOCX
$generatedDocPath = null;
$templatePath = __DIR__ . '/Фирменный бланк АналитикГрупп.docx';

if (file_exists($templatePath)) {
    $dateNow = new DateTime('now', new DateTimeZone('Europe/Kaliningrad'));
    $ДатаПоступления = $dateNow->format('d.m.Y');
    $ДатаПоступленияВремяВчасах = $dateNow->format('H-i');

    $template = new TemplateProcessor($templatePath);
    $template->setValue('Фамилия', $surname);
    $template->setValue('Имя', $name);
    $template->setValue('Отчество', $patronymic);
    $template->setValue('Телефон', $phone);
    $template->setValue('Почта', $email);
    $template->setValue('ДатаПоступления', $ДатаПоступления);
    $template->setValue('ДатаПоступленияВремяВчасах', $ДатаПоступленияВремяВчасах);

    $wrappedProblem = wordwrap($problem, 100, "\n", true);
    $template->setValue('ОписаниеПроблемы', htmlspecialchars($wrappedProblem));

    $fileText = '';
    if (!empty($fileLinks)) {
        foreach ($fileLinks as $f) $fileText .= "• " . $f['name'] . "\n";
        $fileText = wordwrap($fileText, 120, "\n", true);
    } else {
        $fileText = 'Файлы не приложены';
    }
    $template->setValue('Файлы', htmlspecialchars($fileText));

    $safeSurname = preg_replace('/[^a-zA-Z0-9а-яА-Я_\-]/u', '_', $surname);
    $generatedDocPath = __DIR__ . '/Ответ_' . $safeSurname . '_ID' . $submissionId . '.docx';
    $template->saveAs($generatedDocPath);
}

// 5) Тело писем
$filesHtml = !empty($fileNamesHtml) ? implode("\n", $fileNamesHtml) : "<li>Файлы не приложены</li>";

$mainHtmlBody = "
<h3 style='margin-bottom: 10px;'>Заполнена заявка на сайте <a href='https://analitikgroup.ru/'>analitikgroup.ru</a></h3>
<hr style='border: 1px solid #970E0E; margin-bottom: 10px;'>
<p style='margin: 4px 0;'><strong>ID заявки:</strong> {$submissionId}</p>
<p style='margin: 4px 0;'><strong>Фамилия:</strong> {$surname}</p>
<p style='margin: 4px 0;'><strong>Имя:</strong> {$name}</p>
<p style='margin: 4px 0;'><strong>Отчество:</strong> {$patronymic}</p>
<p style='margin: 4px 0;'><strong>Телефон:</strong> {$phone}</p>
<p style='margin: 4px 0;'><strong>Email:</strong> {$email}</p>
<p style='margin: 4px 0;'><strong>Проблема:</strong> {$problem}</p>
<hr style='border: 1px solid #970E0E; margin-top: 10px;'>
<p style='margin: 4px 0;'><strong>Файлы:</strong></p>
<ul>{$filesHtml}</ul>
<hr style='border: 1px solid #970E0E; margin-top: 10px;'>
<p>Я, <strong>{$surname} {$name} {$patronymic}</strong>, выражаю согласие на передачу и обработку персональных данных.</p>
<p><strong>Отправлено с IP адреса:</strong> " . ($_SERVER['REMOTE_ADDR'] ?? '') . "</p>
<p><em>Это сообщение создается автоматически. Не отвечайте на него кнопкой reply.</em></p>
";

$htmlBodyClient = "
<h3>Спасибо за вашу заявку на сайте <a href='https://analitikgroup.ru/'>analitikgroup.ru</a></h3>
<p>Мы получили вашу заявку со следующими данными:</p>
<p><strong>ID заявки:</strong> {$submissionId}</p>
<p><strong>Фамилия:</strong> {$surname}</p>
<p><strong>Имя:</strong> {$name}</p>
<p><strong>Отчество:</strong> {$patronymic}</p>
<p><strong>Телефон:</strong> {$phone}</p>
<p><strong>Email:</strong> {$email}</p>
<p><strong>Проблема:</strong> {$problem}</p>
<p>В ближайшее время с вами свяжутся.</p>
<p><strong>Файлы, которые вы отправили:</strong></p>
<ul>{$filesHtml}</ul>
<hr>
<p><em>Это письмо создано автоматически. Не отвечайте на него.</em></p>
";

$fileListText = "";
foreach ($fileLinks as $f) $fileListText .= "- " . $f['name'] . "\n";

$textBodyClient = "Спасибо за вашу заявку на сайте analitikgroup.ru\n\n"
    . "ID заявки: {$submissionId}\n"
    . "Фамилия: {$surname}\n"
    . "Имя: {$name}\n"
    . "Отчество: {$patronymic}\n"
    . "Телефон: {$phone}\n"
    . "Email: {$email}\n"
    . "Проблема: {$problem}\n\n"
    . "Файлы, которые вы отправили:\n"
    . ($fileListText ?: "- Файлы не приложены\n")
    . "\nВ ближайшее время с вами свяжутся.\n"
    . "---\n"
    . "Это письмо создано автоматически. Не отвечайте на него.";

try {
    // 6) Отправка через Яндекс SMTP (юрист + клиент)
    $mailer = new PHPMailer(true);
    $mailer->isSMTP();
    $mailer->Host = 'smtp.yandex.ru';
    $mailer->SMTPAuth = true;
    $mailer->Username = YANDEX_SMTP_USER;
    $mailer->Password = YANDEX_SMTP_PASS;
    $mailer->SMTPSecure = 'ssl';
    $mailer->Port = 465;
    $mailer->CharSet = 'UTF-8';

    // 6.1 Юрист
    $mailer->setFrom(YANDEX_SMTP_USER, 'АналитикГрупп');
    $mailer->addReplyTo($email, $surname . ' ' . $name);
    $mailer->addAddress(LAWYER_EMAIL, 'Юрист');

    if ($generatedDocPath && file_exists($generatedDocPath)) {
        $mailer->addAttachment($generatedDocPath);
    }

    // Прикрепляем файлы (кроме слишком больших архивов)
    if (!empty($_FILES['files']['name'][0])) {
        foreach ($_FILES['files']['tmp_name'] as $i => $tmpPath) {
            $origName = (string)($_FILES['files']['name'][$i] ?? '');
            $size = (int)($_FILES['files']['size'][$i] ?? 0);
            $err  = (int)($_FILES['files']['error'][$i] ?? UPLOAD_ERR_NO_FILE);

            if ($origName === '' || $err !== UPLOAD_ERR_OK || !is_uploaded_file($tmpPath)) continue;

            $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
            if (in_array($ext, $linkExtensions, true) && $size > $MAX_ATTACH_BYTES) {
                continue; // большой архив не цепляем письмом
            }
            $mailer->addAttachment($tmpPath, $origName);
        }
    }

    $mailer->isHTML(true);
    $mailer->Subject = "Новая заявка с сайта (ID {$submissionId})";
    $mailer->Body = $mainHtmlBody;
    $mailer->send();

    // 6.2 Клиент
    $mailer->clearAllRecipients();
    $mailer->clearAttachments();
    $mailer->clearReplyTos();
    $mailer->clearCCs();
    $mailer->clearBCCs();

    $mailer->setFrom(YANDEX_SMTP_USER, 'АналитикГрупп');
    $mailer->addReplyTo(LAWYER_EMAIL, 'АналитикГрупп');
    $mailer->addAddress($email);

    $mailer->isHTML(true);
    $mailer->Subject = "Копия вашей заявки с сайта АналитикГрупп (ID {$submissionId})";
    $mailer->Body = $htmlBodyClient;
    $mailer->AltBody = $textBodyClient;
    $mailer->send();

    if ($generatedDocPath && file_exists($generatedDocPath)) unlink($generatedDocPath);

    out([
        'status' => 'success',
        'message' => 'Спасибо! Ваше сообщение было отправлено.',
        'id' => $submissionId,
        'uploaded' => count($fileLinks)
    ], 200);

} catch (Exception $e) {
    if ($generatedDocPath && file_exists($generatedDocPath)) unlink($generatedDocPath);
    error_log('send-email error: ' . $e->getMessage());
    out(['status' => 'error', 'message' => 'Ошибка отправки сообщения.'], 500);
}
