<?php
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../helpers/qr_helper.php';

class QRController {
    private $db;
    public function __construct(){
        $this->db = Database::getInstance()->pdo();
    }

    public function generate($member_id){
        $file = generate_member_qr($member_id);
        return $file;
    }
}
