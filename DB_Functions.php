<?php

class DB_Functions {

    private $conn;

    function __construct() {
        require_once 'DB_Connect.php';
        $db = new Db_Connect(); // можно оставить как было (имя класса не критично по регистру)
        $this->conn = $db->connect();
    }

    function __destruct() {
        $this->conn->close();
    }

    // Возвращает ID заявки (int) или false
    public function saveSubmission($surname, $name, $patronymic, $phone, $email, $problem, $fileLinksJson) {
        $stmt = $this->conn->prepare(
            "INSERT INTO form_submissions (surname, name, patronymic, phone, email, problem, file_links, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())"
        );
        $stmt->bind_param("sssssss", $surname, $name, $patronymic, $phone, $email, $problem, $fileLinksJson);
        $ok = $stmt->execute();
        $id = $ok ? (int)$this->conn->insert_id : 0;
        $stmt->close();
        return $ok ? $id : false;
    }

    public function updateSubmissionFiles(int $submissionId, string $fileLinksJson): bool {
        $stmt = $this->conn->prepare("UPDATE form_submissions SET file_links = ? WHERE id = ?");
        $stmt->bind_param("si", $fileLinksJson, $submissionId);
        $ok = $stmt->execute();
        $stmt->close();
        return (bool)$ok;
    }
}

?>
