<?php
// Simple CSRF helper
if (session_status() === PHP_SESSION_NONE) session_start();
function csrf_token(){
    if(empty($_SESSION['_csrf'])) $_SESSION['_csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['_csrf'];
}
function csrf_check($token){
    if(session_status()===PHP_SESSION_NONE) session_start();
    return isset($_SESSION['_csrf']) && hash_equals($_SESSION['_csrf'], $token);
}
