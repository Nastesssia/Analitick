<?php

class DB_Functions {

    private $conn;

    // Constructor
    function __construct() {
        require_once 'DB_Connect.php';
        // Connecting to database
        $db = new Db_Connect();
        $this->conn = $db->connect();
    }

    // Destructor
    function __destruct() {
        $this->conn->close();
    }

    /**
     * Storing new submission
     * returns boolean true on success
     */
    public function saveSubmission($surname, $name, $patronymic, $phone, $email, $problem) {
        $stmt = $this->conn->prepare("INSERT INTO form_submissions (surname, name, patronymic, phone, email, problem, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())");
        $stmt->bind_param("ssssss", $surname, $name, $patronymic, $phone, $email, $problem);
        $result = $stmt->execute();
        $stmt->close();
        return $result;
    }
}

?>
