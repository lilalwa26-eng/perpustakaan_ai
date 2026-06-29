<?php
require_once __DIR__ . '/../../app/models/Database.php';

$db = Database::getInstance()->pdo();

// Extended seeder: loans, returns, chatbot & nlp, stopwords, synonyms
// This script complements run_seeders.php. Run after it.

// Create stopwords
$sw = $db->prepare('INSERT INTO stopwords (word) VALUES (?)');
$common = ['dan','di','ke','yang','dari','ke','untuk','adalah','ini','itu'];
foreach ($common as $w) $sw->execute([$w]);

// Generate 100 chatbot dataset rows
$cs = $db->prepare('INSERT INTO chatbot_dataset (intent, sample, response, created_at) VALUES (?, ?, ?, NOW())');
for ($i=1;$i<=100;$i++) {
    $cs->execute(["greeting", "Halo saya ingin tanya $i", "Halo! Bagaimana saya bisa membantu tentang perpustakaan? ($i)"]); 
}

// Generate nlp_dataset
$ns = $db->prepare('INSERT INTO nlp_dataset (text, created_at) VALUES (?, NOW())');
for ($i=1;$i<=100;$i++) {
    $ns->execute(["Deskripsi buku tentang pemrograman contoh nomor $i. Ini berisi materi pemrograman dasar dan lanjutan."]); 
}

// Generate synonyms (simple)
$syn = $db->prepare('INSERT INTO synonyms (word, synonym) VALUES (?, ?)');
$syn->execute(['komputer','pc']);
$syn->execute(['buku','kitab']);

// Generate some copies and random loans/returns
$books = $db->query('SELECT id FROM books')->fetchAll();
$copyStmt = $db->prepare('INSERT INTO copies (book_id, barcode, status, created_at) VALUES (?, ?, ?, NOW())');
$loanStmt = $db->prepare('INSERT INTO loans (copy_id, member_id, loaned_by, loan_date, due_date, returned_date, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())');

foreach ($books as $b) {
    // create 1-3 copies
    $n = rand(1,3);
    for ($c=0;$c<$n;$c++) {
        $barcode = 'BC' . uniqid();
        $copyStmt->execute([$b['id'], $barcode, 'available']);
    }
}

// create 100 loans (some returned)
$memberIds = $db->query('SELECT id FROM members')->fetchAll(PDO::FETCH_COLUMN);
$copyIds = $db->query('SELECT id FROM copies')->fetchAll(PDO::FETCH_COLUMN);
for ($i=0;$i<100;$i++){
    $copy = $copyIds[array_rand($copyIds)];
    $member = $memberIds[array_rand($memberIds)];
    $loan_date = date('Y-m-d', strtotime('-' . rand(1,90) . ' days'));
    $due_date = date('Y-m-d', strtotime($loan_date . ' +7 days'));
    $returned = (rand(0,1) ? date('Y-m-d', strtotime($loan_date . ' + ' . rand(1,14) . ' days')) : null);
    $status = $returned ? 'returned' : 'ongoing';
    $loanStmt->execute([$copy, $member, 2, $loan_date, $due_date, $returned, $status]);
    if ($returned) $db->prepare('UPDATE copies SET status = ? WHERE id = ?')->execute(['available', $copy]);
    else $db->prepare('UPDATE copies SET status = ? WHERE id = ?')->execute(['loaned', $copy]);
}

echo "Extended seeding complete\n";
