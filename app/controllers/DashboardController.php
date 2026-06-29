<?php
require_once __DIR__ . '/../models/Database.php';

class DashboardController {
    private $db;
    public function __construct(){
        $this->db = Database::getInstance()->pdo();
    }

    public function stats(){
        $stmt = $this->db->query('SELECT * FROM view_dashboard_stats LIMIT 1');
        $stats = $stmt->fetch();

        $recentBooks = $this->db->query('SELECT id, title, created_at FROM books ORDER BY created_at DESC LIMIT 5')->fetchAll();
        $popular = $this->db->query('SELECT * FROM view_popular_books')->fetchAll();
        $recentActivity = $this->db->query('SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 10')->fetchAll();

        return [
            'stats' => $stats,
            'recent_books' => $recentBooks,
            'popular_books' => $popular,
            'recent_activity' => $recentActivity
        ];
    }
}
