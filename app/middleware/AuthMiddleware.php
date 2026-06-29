<?php
// Middleware style auth check (basic)
require_once __DIR__ . '/../controllers/AuthController.php';

class AuthMiddleware {
    public static function ensureLoggedIn(){
        if(!AuthController::check()){
            http_response_code(401);
            echo json_encode(['error'=>'Unauthorized']);
            exit;
        }
    }

    public static function ensureRole($roles){
        if(!AuthController::requireRole($roles)){
            http_response_code(403);
            echo json_encode(['error'=>'Forbidden']);
            exit;
        }
    }
}
