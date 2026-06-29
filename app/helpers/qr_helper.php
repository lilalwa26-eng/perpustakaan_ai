<?php
// Simple helper for generating QR codes (uses endroid/qr-code if installed via composer)
require_once __DIR__ . '/../../app/models/Database.php';

function generate_member_qr($member_id) {
    $text = "member:" . $member_id;
    // If composer library available, generate png file; otherwise return text
    if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
        require __DIR__ . '/../../vendor/autoload.php';
        $qr = new \Endroid\QrCode\QrCode($text);
        $path = __DIR__ . '/../../storage/qr';
        if (!is_dir($path)) mkdir($path, 0755, true);
        $file = $path . '/member_' . $member_id . '.png';
        $qr->writeFile($file);
        return $file;
    }
    return $text;
}
