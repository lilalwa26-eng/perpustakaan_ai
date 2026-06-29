<?php
require_once __DIR__ . '/../models/Database.php';
require_once __DIR__ . '/../helpers/upload_helper.php';

class MemberController {
    private $db;
    public function __construct(){
        $this->db = Database::getInstance()->pdo();
    }

    public function index(){
        $stmt = $this->db->query('SELECT * FROM members ORDER BY id DESC LIMIT 500');
        return ['data'=>$stmt->fetchAll()];
    }

    public function show($id){
        $stmt = $this->db->prepare('SELECT * FROM members WHERE id = ?');
        $stmt->execute([$id]);
        return ['data'=>$stmt->fetch()];
    }

    public function store($data, $file=null){
        $stmt = $this->db->prepare('INSERT INTO members (nisn, full_name, email, phone, address, status, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())');
        $stmt->execute([
            $data['nisn'] ?? null,
            $data['full_name'] ?? 'No Name',
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['status'] ?? 'active'
        ]);
        $id = $this->db->lastInsertId();
        if($file){
            $path = upload_member_photo($file, $id);
            if($path) $this->db->prepare('UPDATE members SET photo = ? WHERE id = ?')->execute([$path, $id]);
        }
        return ['success'=>true,'id'=>$id];
    }

    public function update($id, $data, $file=null){
        $stmt = $this->db->prepare('UPDATE members SET nisn=?, full_name=?, email=?, phone=?, address=?, status=? WHERE id=?');
        $stmt->execute([
            $data['nisn'] ?? null,
            $data['full_name'] ?? 'No Name',
            $data['email'] ?? null,
            $data['phone'] ?? null,
            $data['address'] ?? null,
            $data['status'] ?? 'active',
            $id
        ]);
        if($file){
            $path = upload_member_photo($file, $id);
            if($path) $this->db->prepare('UPDATE members SET photo = ? WHERE id = ?')->execute([$path, $id]);
        }
        return ['success'=>true];
    }

    public function delete($id){
        $this->db->prepare('DELETE FROM members WHERE id = ?')->execute([$id]);
        return ['success'=>true];
    }
}
