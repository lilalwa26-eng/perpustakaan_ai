<?php
require_once __DIR__ . '/../models/Database.php';

class CategoryController {
    private $db;
    public function __construct(){
        $this->db = Database::getInstance()->pdo();
    }

    public function index(){
        $stmt = $this->db->query('SELECT * FROM categories ORDER BY name');
        return ['data'=>$stmt->fetchAll()];
    }

    public function show($id){
        $stmt = $this->db->prepare('SELECT * FROM categories WHERE id = ?');
        $stmt->execute([$id]);
        return ['data'=>$stmt->fetch()];
    }

    public function store($data){
        $stmt = $this->db->prepare('INSERT INTO categories (name, description) VALUES (?, ?)');
        $stmt->execute([$data['name'] ?? 'Unnamed', $data['description'] ?? null]);
        return ['success'=>true,'id'=>$this->db->lastInsertId()];
    }

    public function update($id,$data){
        $stmt = $this->db->prepare('UPDATE categories SET name=?, description=? WHERE id=?');
        $stmt->execute([$data['name'] ?? 'Unnamed', $data['description'] ?? null, $id]);
        return ['success'=>true];
    }

    public function delete($id){
        $stmt = $this->db->prepare('DELETE FROM categories WHERE id=?');
        $stmt->execute([$id]);
        return ['success'=>true];
    }
}
