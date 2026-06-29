<?php
/**
 * Seeder runner that uses PHP to generate hashed passwords and insert many rows.
 * Run: php database/seeders/run_seeders.php
 */
require_once __DIR__ . '/../../app/models/Database.php';

$db = Database::getInstance()->pdo();

// Create roles
$roles = ['Administrator', 'Petugas', 'Siswa'];
$stmt = $db->prepare('INSERT INTO roles (name, description) VALUES (?, ?)');
foreach ($roles as $r) $stmt->execute([$r, "$r role"]);

// Create admin user
$adminPass = password_hash('Admin123!', PASSWORD_DEFAULT);
$db->prepare('INSERT INTO users (full_name, email, password, role_id, created_at) VALUES (?, ?, ?, ?, NOW())')
   ->execute(['Administrator', 'admin@sekolah.local', $adminPass, 1]);

// Create 5 petugas
$petugasPass = password_hash('Petugas123!', PASSWORD_DEFAULT);
for ($i=1;$i<=5;$i++) {
    $db->prepare('INSERT INTO users (full_name, email, password, role_id, created_at) VALUES (?, ?, ?, ?, NOW())')
       ->execute(["Petugas $i", "petugas{$i}@sekolah.local", $petugasPass, 2]);
}

// Create 100 anggota (members) with hashed passwords
$memberPass = password_hash('Member123!', PASSWORD_DEFAULT);
$insert = $db->prepare('INSERT INTO members (nisn, full_name, email, phone, created_at) VALUES (?, ?, ?, ?, NOW())');
for ($i=1;$i<=100;$i++) {
    $nisn = str_pad($i, 8, '0', STR_PAD_LEFT);
    $insert->execute([$nisn, "Siswa $i", "siswa{$i}@example.local", '08' . rand(10000000,99999999)]);
}

// Create categories, racks, publishers, authors and books (small demo sets)
$catStmt = $db->prepare('INSERT INTO categories (name) VALUES (?)');
for ($i=1;$i<=20;$i++) $catStmt->execute(["Kategori $i"]);

$rackStmt = $db->prepare('INSERT INTO racks (code, name) VALUES (?, ?)');
for ($i=1;$i<=20;$i++) $rackStmt->execute(["R$i", "Rak $i"]);

$pubStmt = $db->prepare('INSERT INTO publishers (name) VALUES (?)');
for ($i=1;$i<=50;$i++) $pubStmt->execute(["Penerbit $i"]);

$authStmt = $db->prepare('INSERT INTO authors (name) VALUES (?)');
for ($i=1;$i<=100;$i++) $authStmt->execute(["Penulis $i"]);

// Insert 100 books
$bookStmt = $db->prepare('INSERT INTO books (isbn, title, category_id, rack_id, publisher_id, language_id, published_year, stock, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())');
for ($i=1;$i<=100;$i++) {
    $isbn = '978' . str_pad($i, 9, '0', STR_PAD_LEFT);
    $title = "Buku Sampel $i";
    $cat = rand(1,20);
    $rack = rand(1,20);
    $pub = rand(1,50);
    $lang = 1;
    $year = rand(2000,2024);
    $stock = rand(1,10);
    $bookStmt->execute([$isbn, $title, $cat, $rack, $pub, $lang, $year, $stock]);
}

echo "Seeding finished. Admin login: admin@sekolah.local / Admin123!\n";
