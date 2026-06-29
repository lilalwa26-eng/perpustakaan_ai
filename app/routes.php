<?php
// Simple route map (skeleton) — expand controllers as needed
return [
    '/' => ['controller' => 'DashboardController', 'action' => 'index'],
    '/auth/login' => ['controller' => 'AuthController', 'action' => 'login'],
    '/auth/logout' => ['controller' => 'AuthController', 'action' => 'logout'],
    // API endpoints
    '/api/ai/search' => ['controller' => 'AIController', 'action' => 'search'],
    '/api/qr/scan' => ['controller' => 'QRController', 'action' => 'scan']
];
