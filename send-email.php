<?php
// Включаем логирование ошибок в файл
ini_set("log_errors", 1);
ini_set("error_log", "C:\Users\alexa\OneDrive\Рабочий стол\Сайт готовый\Analitic/php-error.log");

require_once 'DB_Functions.php';
$db = new DB_Functions();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Очистка и валидация входных данных
    $surname = strip_tags(trim($_POST["surname"]));
    $name = strip_tags(trim($_POST["name"]));
    $patronymic = strip_tags(trim($_POST["patronymic"]));
    $phone = strip_tags(trim($_POST["phone"]));
    $email = filter_var(trim($_POST["email"]), FILTER_VALIDATE_EMAIL);
    $problem = strip_tags(trim($_POST["problem"]));

    if (!$email) {
        echo "Ошибка: Неверный формат email.";
        exit;
    }

    // Сохраняем данные в базу
    if (!$db->saveSubmission($surname, $name, $patronymic, $phone, $email, $problem)) {
        error_log("Не удалось сохранить данные в базу.");
        echo "Ошибка: Не удалось сохранить данные.";
        exit;
    }

    // Назначаем получателя письма
    $recipient = "kabanova.anasteischa@yandex.ru"; // Ваш email

    // Содержимое письма
    $subject = "Новая заявка с вашего сайта";
    $email_content = "Фамилия: $surname\n";
    $email_content .= "Имя: $name\n";
    $email_content .= "Отчество: $patronymic\n";
    $email_content .= "Телефон: $phone\n";
    $email_content .= "Email: $email\n";
    $email_content .= "Проблема: $problem\n";

    // Заголовки
    $email_headers = "From: kabanova.anasteischa@yandex.ru";

    // Отправка письма на ваш email
    mail($recipient, $subject, $email_content, $email_headers);

    // Отправка копии письма клиенту
    mail($email, "Копия вашей заявки", $email_content, $email_headers);

    // Обработка файлов
    if (!empty($_FILES['files']['name'][0])) {
        $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
        $files = $_FILES['files'];

        for ($i = 0; $i < count($files['name']); $i++) {
            if (in_array($files['type'][$i], $allowed_types)) {
                $path = '/path/to/uploads/' . basename($files['name'][$i]);
                if ($files['size'][$i] <= 30000000 && move_uploaded_file($files['tmp_name'][$i], $path)) {
                    $email_content .= "Файл: " . $files['name'][$i] . " успешно загружен.\n";
                } else {
                    $email_content .= "Ошибка загрузки файла: " . $files['name'][$i] . ".\n";
                }
            } else {
                $email_content .= "Недопустимый тип файла: " . $files['name'][$i] . ".\n";
            }
        }

        // Повторная отправка писем с информацией о файлах
        mail($recipient, $subject, $email_content, $email_headers);
        mail($email, "Копия вашей заявки с файлами", $email_content, $email_headers);
    }

    echo "Спасибо! Ваше сообщение было отправлено.";
} else {
    echo "Ошибка: необходимо отправить POST запрос.";
}
?>
