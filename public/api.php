<?php
// Enhanced API router to include CRUD endpoints
require_once __DIR__ . '/../app/models/Database.php';
require_once __DIR__ . '/../app/controllers/AuthController.php';
require_once __DIR__ . '/../app/controllers/DashboardController.php';
require_once __DIR__ . '/../app/controllers/BookController.php';
require_once __DIR__ . '/../app/controllers/MemberController.php';
require_once __DIR__ . '/../app/controllers/LoanController.php';
require_once __DIR__ . '/../app/controllers/AIController.php';
require_once __DIR__ . '/../app/controllers/QRController.php';
require_once __DIR__ . '/../app/controllers/CategoryController.php';
require_once __DIR__ . '/../app/controllers/PublisherController.php';
require_once __DIR__ . '/../app/controllers/AuthorController.php';
require_once __DIR__ . '/../app/controllers/LanguageController.php';
require_once __DIR__ . '/../app/middleware/AuthMiddleware.php';

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

    // Auth
    case $endpoint === '/api/auth/login' && $method === 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $a = new AuthController();
        echo json_encode($a->login($data['email'] ?? '', $data['password'] ?? ''));
        break;
    case $endpoint === '/api/auth/logout' && $method === 'POST':
        $a = new AuthController();
        echo json_encode($a->logout());
        break;

    // Books
    case $endpoint === '/api/books' && $method === 'GET':
        $c = new BookController(); echo json_encode($c->index()); break;
    case preg_match('#^/api/books$#', $endpoint) && $method === 'POST':
        AuthMiddleware::ensureLoggedIn(); AuthMiddleware::ensureRole([1,2]);
        $data = json_decode(file_get_contents('php://input'), true);
        $c = new BookController(); echo json_encode($c->store($data)); break;
    case preg_match('#^/api/books/([0-9]+)$#', $endpoint, $m) && $method === 'GET':
        $c = new BookController(); echo json_encode($c->show((int)$m[1])); break;

    // Categories
    case $endpoint === '/api/categories' && $method === 'GET':
        $c = new CategoryController(); echo json_encode($c->index()); break;
    case $endpoint === '/api/categories' && $method === 'POST':
        AuthMiddleware::ensureLoggedIn(); AuthMiddleware::ensureRole([1,2]);
        $data = json_decode(file_get_contents('php://input'), true);
        $c = new CategoryController(); echo json_encode($c->store($data)); break;

    // Publishers
    case $endpoint === '/api/publishers' && $method === 'GET':
        $c = new PublisherController(); echo json_encode($c->index()); break;
    case $endpoint === '/api/publishers' && $method === 'POST':
        AuthMiddleware::ensureLoggedIn(); AuthMiddleware::ensureRole([1,2]);
        $data = json_decode(file_get_contents('php://input'), true);
        $c = new PublisherController(); echo json_encode($c->store($data)); break;

    // Authors
    case $endpoint === '/api/authors' && $method === 'GET':
        $c = new AuthorController(); echo json_encode($c->index()); break;
    case $endpoint === '/api/authors' && $method === 'POST':
        AuthMiddleware::ensureLoggedIn(); AuthMiddleware::ensureRole([1,2]);
        $data = json_decode(file_get_contents('php://input'), true);
        $c = new AuthorController(); echo json_encode($c->store($data)); break;

    // Languages
    case $endpoint === '/api/languages' && $method === 'GET':
        $c = new LanguageController(); echo json_encode($c->index()); break;
    case $endpoint === '/api/languages' && $method === 'POST':
        AuthMiddleware::ensureLoggedIn(); AuthMiddleware::ensureRole([1,2]);
        $data = json_decode(file_get_contents('php://input'), true);
        $c = new LanguageController(); echo json_encode($c->store($data)); break;

    // Members
    case $endpoint === '/api/members' && $method === 'GET':
        $c = new MemberController(); echo json_encode($c->index()); break;
    case $endpoint === '/api/members' && $method === 'POST':
        AuthMiddleware::ensureLoggedIn(); AuthMiddleware::ensureRole([1,2]);
        $data = $_POST ?: json_decode(file_get_contents('php://input'), true);
        $file = $_FILES['photo'] ?? null;
        $c = new MemberController(); echo json_encode($c->store($data, $file)); break;
    case preg_match('#^/api/members/([0-9]+)$#', $endpoint, $m) && $method === 'GET':
        $c = new MemberController(); echo json_encode($c->show((int)$m[1])); break;

    // Loans
    case $endpoint === '/api/loans' && $method === 'POST':
        AuthMiddleware::ensureLoggedIn(); AuthMiddleware::ensureRole([1,2]);
        $data = json_decode(file_get_contents('php://input'), true);
        $c = new LoanController(); echo json_encode($c->loan($data)); break;
    case preg_match('#^/api/loans/([0-9]+)/return$#', $endpoint, $m) && $method === 'POST':
        AuthMiddleware::ensureLoggedIn(); AuthMiddleware::ensureRole([1,2]);
        $c = new LoanController(); echo json_encode($c->return((int)$m[1])); break;

    // AI
    case $endpoint === '/api/ai/search' && $method === 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        $c = new AIController(); echo json_encode($c->search($data['q'] ?? '')); break;

    // QR
    case preg_match('#^/api/qr/generate/([0-9]+)$#', $endpoint, $m) && $method === 'GET':
        $c = new QRController(); $file = $c->generate((int)$m[1]); echo json_encode(['file'=>$file]); break;

    default:
        http_response_code(404); echo json_encode(['error'=>'Endpoint not found']);
}
