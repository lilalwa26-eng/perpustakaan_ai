<?php
require_once __DIR__ . '/../models/Database.php';

class AuthorController {
    private $db;
    public function __construct(){
        $this->db = Database::getInstance()->pdo();
    }
    public function index(){
        $stmt = $this->db->query('SELECT * FROM authors ORDER BY name LIMIT 500');
        return ['data'=>$stmt->fetchAll()];
    }
    public function store($data){
        $stmt = $this->db->prepare('INSERT INTO authors (name) VALUES (?)');
        $stmt->execute([$data['name'] ?? 'Unknown']);
        return ['success'=>true,'id'=>$this->db->lastInsertId()];
    }
}
