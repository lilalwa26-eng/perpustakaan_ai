<?php
// Simple reports controller: CSV & PDF export
require_once __DIR__ . '/../models/Database.php';

class ReportController {
    private $db;
    public function __construct(){
        $this->db = Database::getInstance()->pdo();
    }

    public function exportBooksCsv(){
        $rows = $this->db->query('SELECT id,isbn,title,published_year,stock FROM books')->fetchAll();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="books.csv"');
        $out = fopen('php://output','w');
        fputcsv($out, ['id','isbn','title','year','stock']);
        foreach ($rows as $r) fputcsv($out, [$r['id'],$r['isbn'],$r['title'],$r['published_year'],$r['stock']]);
        fclose($out);
        exit;
    }
}
