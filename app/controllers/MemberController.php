<?php
require_once __DIR__ . '/../models/Database.php';

class MemberController {
    private $db;
    public function __construct(){
        $this->db = Database::getInstance()->pdo();
    }

    public function index(){
        $stmt = $this->db->query('SELECT * FROM members ORDER BY id DESC LIMIT 200');
        return ['data' => $stmt->fetchAll()];
    }
}
