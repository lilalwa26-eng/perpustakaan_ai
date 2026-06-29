<?php
require_once __DIR__ . '/../app/models/Database.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/BookController.php';
require_once __DIR__ . '/../app/controllers/MemberController.php';
require_once __DIR__ . '/../app/controllers/LoanController.php';
require_once __DIR__ . '/../app/controllers/AIController.php';
require_once __DIR__ . '/../app/controllers/QRController.php';
require_once __DIR__ . '/../app/helpers/response_helper.php';

header('Content-Type: application/json');
$uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($uri, PHP_URL_PATH);
$base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
$endpoint = substr($path, strlen($base));
$endpoint = '/' . trim($endpoint, '/');

// Simple routing
switch (true) {
    case $endpoint === '/api/dashboard' && $method === 'GET':
        $c = new DashboardController();
        echo json_encode($c->stats());
        break;
    case $endpoint === '/api/books' && $method === 'GET':
        $c = new BookController();
        echo json_encode($c->index());
        break;
    case $endpoint === '/api/books' && $method === 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $c = new BookController();
        echo json_encode($c->store($data));
        break;
    case preg_match('#^/api/books/([0-9]+)$#', $endpoint, $m) && $method === 'GET':
        $c = new BookController();
        echo json_encode($c->show((int)$m[1]));
        break;
    case $endpoint === '/api/members' && $method === 'GET':
        $c = new MemberController();
        echo json_encode($c->index());
        break;
    case $endpoint === '/api/loans' && $method === 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $c = new LoanController();
        echo json_encode($c->loan($data));
        break;
    case preg_match('#^/api/loans/([0-9]+)/return$#', $endpoint, $m) && $method === 'POST':
        $c = new LoanController();
        echo json_encode($c->return((int)$m[1]));
        break;
    case $endpoint === '/api/ai/search' && $method === 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $c = new AIController();
        echo json_encode($c->search($data['q'] ?? ''));
        break;
    case preg_match('#^/api/qr/generate/([0-9]+)$#', $endpoint, $m) && $method === 'GET':
        $c = new QRController();
        $file = $c->generate((int)$m[1]);
        echo json_encode(['file' => $file]);
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
}
