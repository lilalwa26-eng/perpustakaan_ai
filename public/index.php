<?php
// Very small front controller for SIPERPUS AI
// Put your Google Stitch UI files in public/ui/

session_start();

// Load config
$config = require __DIR__ . '/../config/database.php';

// Simple router
$path = $_SERVER['REQUEST_URI'];
$base = dirname($_SERVER['SCRIPT_NAME']);
$uri = substr($path, strlen($base));
$uri = strtok($uri, '?');

// If UI exists, serve it
$uiIndex = __DIR__ . '/ui/index.html';
if (file_exists($uiIndex)) {
    // Serve UI index
    readfile($uiIndex);
    exit;
}

// Otherwise show starter page
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>SIPERPUS AI — Setup</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 text-gray-800">
<div class="max-w-3xl mx-auto p-6">
  <h1 class="text-2xl font-bold mb-4">SIPERPUS AI — Backend ready</h1>
  <p class="mb-4">Frontend (Google Stitch UI) belum diunggah. Tempatkan berkas UI Anda di <code>public/ui/</code>.</p>
  <h2 class="text-lg font-semibold mt-6">Next steps</h2>
  <ol class="list-decimal ml-6 mt-2">
    <li>Import <code>database/perpus_schema.sql</code> ke MySQL (buat database <code>perpus_ai</code>).</li>
    <li>Edit <code>config/database.php</code> untuk kredensial database Anda.</li>
    <li>Letakkan project di <code>htdocs/perpus_ai</code> atau set virtual host.</li>
    <li>Buka <a href="/perpus_ai/public">http://localhost/perpus_ai/public</a>.</li>
  </ol>

  <div class="mt-6 p-4 bg-white rounded shadow">
    <h3 class="font-medium">Seeder</h3>
    <p class="text-sm">Untuk menjalankan seeder PHP (menghasilkan data awal dan password hashed) jalankan: <code>php database/seeders/run_seeders.php</code></p>
  </div>
</div>
</body>
</html>
