<?php
require_once dirname(__DIR__) . '/config.php';

class Database {
    private $connection;
    private static $instance;

    private function __construct() {
        try {
            $this->connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($this->connection->connect_error) {
                throw new Exception('Database Connection Error: ' . $this->connection->connect_error);
            }
            
            $this->connection->set_charset('utf8mb4');
        } catch (Exception $e) {
            // For AJAX requests, return JSON error
            if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $e->getMessage()]);
                exit;
            }
            
            // For normal requests, show error page
            die("Database error: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }

    public function query($sql) {
        $result = $this->connection->query($sql);
        if (!$result && $this->isAjaxRequest()) {
            $this->handleAjaxError("Query error: " . $this->connection->error);
        }
        return $result;
    }

    public function prepare($sql) {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt && $this->isAjaxRequest()) {
            $this->handleAjaxError("Prepare error: " . $this->connection->error);
        }
        return $stmt;
    }

    public function escapeString($string) {
        return $this->connection->real_escape_string($string);
    }

    public function getLastInsertId() {
        return $this->connection->insert_id;
    }

    public function getAffectedRows() {
        return $this->connection->affected_rows;
    }

    public function close() {
        return $this->connection->close();
    }
    
    private function isAjaxRequest() {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    private function handleAjaxError($message) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $message]);
        exit;
    }
}

// Get database connection
function getDB() {
    return Database::getInstance()->getConnection();
}