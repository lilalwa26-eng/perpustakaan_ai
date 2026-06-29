<?php
require_once __DIR__ . '/../models/Database.php';

class LanguageController {
    private $db;
    public function __construct(){
        $this->db = Database::getInstance()->pdo();
    }
    public function index(){
        $stmt = $this->db->query('SELECT * FROM languages ORDER BY name');
        return ['data'=>$stmt->fetchAll()];
    }
    public function store($data){
        $stmt = $this->db->prepare('INSERT INTO languages (code, name) VALUES (?, ?)');
        $stmt->execute([$data['code'] ?? 'id', $data['name'] ?? 'Indonesia']);
        return ['success'=>true,'id'=>$this->db->lastInsertId()];
    }
}
