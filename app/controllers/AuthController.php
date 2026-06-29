<?php
/**
 * Basic Auth controller (skeleton)
 */
require_once __DIR__ . '/../models/Database.php';

class AuthController {
    private $db;
    public function __construct() {
        $this->db = Database::getInstance()->pdo();
    }

    public function login($email, $password) {
        $stmt = $this->db->prepare('SELECT id, full_name, email, password, role_id FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            // set session
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['full_name'],
                'role_id' => $user['role_id']
            ];
            return true;
        }
        return false;
    }

    public function logout() {
        session_unset();
        session_destroy();
    }
}
