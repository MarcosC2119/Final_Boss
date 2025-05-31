<?php
class Database {
    private static $instance = null;
    private $connection;
    
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "roomit";
    
    private function __construct() {
        try {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            if ($this->connection->connect_error) {
                throw new Exception("Error de conexión: " . $this->connection->connect_error);
            }
            
            $this->connection->set_charset("utf8");
        } catch (Exception $e) {
            die("Error de base de datos: " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->connection;
    }
}

// Función para escribir logs
function writeLog($message) {
    error_log("[" . date('Y-m-d H:i:s') . "] " . $message);
}
?>