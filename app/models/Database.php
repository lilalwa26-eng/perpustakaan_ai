<?php
/**
 * Simple Database connection wrapper using PDO
 */
class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $cfg = require __DIR__ . '/../../config/database.php';
        $dsn = "mysql:host={$cfg['DB_HOST']};dbname={$cfg['DB_NAME']};charset={$cfg['DB_CHAR']}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $this->pdo = new PDO($dsn, $cfg['DB_USER'], $cfg['DB_PASS'], $options);
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function pdo() {
        return $this->pdo;
    }
}
