<?php
// Improved AuthController with register, login, logout, and role check
require_once __DIR__ . '/../models/Database.php';

class AuthController {
    private $db;
    public function __construct(){
        $this->db = Database::getInstance()->pdo();
        if(session_status()===PHP_SESSION_NONE) session_start();
    }

    public function login($email, $password){
        $stmt = $this->db->prepare('SELECT id, full_name, email, password, role_id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])){
            // regenerate session id
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'=>$user['id'],
                'name'=>$user['full_name'],
                'email'=>$user['email'],
                'role_id'=>$user['role_id']
            ];
            // log activity
            $this->db->prepare('INSERT INTO activity_logs (user_id, action, context, ip, created_at) VALUES (?, ?, ?, ?, NOW())')
                ->execute([$user['id'],'login','user_login',$_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            return ['success'=>true];
        }
        return ['error'=>'Invalid credentials'];
    }

    public function logout(){
        if(session_status()===PHP_SESSION_ACTIVE){
            $uid = $_SESSION['user']['id'] ?? null;
            session_unset();
            session_destroy();
            if($uid){
                $this->db->prepare('INSERT INTO activity_logs (user_id, action, context, ip, created_at) VALUES (?, ?, ?, ?, NOW())')
                    ->execute([$uid,'logout','user_logout',$_SERVER['REMOTE_ADDR'] ?? '127.0.0.1']);
            }
        }
        return ['success'=>true];
    }

    public function registerAdminIfEmpty(){
        // Ensure at least one admin exists; used on first run
        $stmt = $this->db->query('SELECT COUNT(*) FROM users WHERE role_id = 1');
        $cnt = (int)$stmt->fetchColumn();
        if($cnt===0){
            $pass = password_hash('Admin123!', PASSWORD_DEFAULT);
            $this->db->prepare('INSERT INTO users (full_name,email,password,role_id,created_at) VALUES (?, ?, ?, ?, NOW())')
                ->execute(['Administrator','admin@sekolah.local',$pass,1]);
            return true;
        }
        return false;
    }

    public static function check(){
        if(session_status()===PHP_SESSION_NONE) session_start();
        return isset($_SESSION['user']);
    }

    public static function user(){
        if(session_status()===PHP_SESSION_NONE) session_start();
        return $_SESSION['user'] ?? null;
    }

    public static function requireRole($roleIds){
        if(session_status()===PHP_SESSION_NONE) session_start();
        if(!isset($_SESSION['user'])) return false;
        $rid = $_SESSION['user']['role_id'];
        if(is_array($roleIds)) return in_array($rid, $roleIds);
        return $rid == $roleIds;
    }
}
