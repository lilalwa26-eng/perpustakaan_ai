<?php
require_once __DIR__ . '/../models/Database.php';

class BookController {
    private $db;
    public function __construct(){
        $this->db = Database::getInstance()->pdo();
    }

    public function index(){
        $stmt = $this->db->query('SELECT b.*, c.name as category, p.name as publisher, r.name as rack FROM books b LEFT JOIN categories c ON c.id=b.category_id LEFT JOIN publishers p ON p.id=b.publisher_id LEFT JOIN racks r ON r.id=b.rack_id ORDER BY b.id DESC LIMIT 200');
        return ['data' => $stmt->fetchAll()];
    }

    public function show($id){
        $stmt = $this->db->prepare('SELECT * FROM books WHERE id = ?');
        $stmt->execute([$id]);
        $book = $stmt->fetch();
        if (!$book) return ['error' => 'Not found'];
        return ['data' => $book];
    }

    public function store($data){
        $stmt = $this->db->prepare('INSERT INTO books (isbn, title, category_id, rack_id, publisher_id, language_id, published_year, pages, stock, cover, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $data['isbn'] ?? null,
            $data['title'] ?? 'Untitled',
            $data['category_id'] ?? null,
            $data['rack_id'] ?? null,
            $data['publisher_id'] ?? null,
            $data['language_id'] ?? 1,
            $data['published_year'] ?? null,
            $data['pages'] ?? 0,
            $data['stock'] ?? 1,
            $data['cover'] ?? null
        ]);
        $id = $this->db->lastInsertId();
        return ['success' => true, 'id' => $id];
    }
}
