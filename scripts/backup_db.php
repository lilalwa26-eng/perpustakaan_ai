<?php
// Simple backup script to create SQL dump (MySQL client must be available)
$config = require __DIR__ . '/../config/database.php';
$filename = __DIR__ . '/../storage/backups/perpus_ai_' . date('Ymd_His') . '.sql';
if (!is_dir(__DIR__ . '/../storage/backups')) mkdir(__DIR__ . '/../storage/backups', 0755, true);

$cmd = sprintf('mysqldump -h%s -u%s -p"%s" %s > %s', $config['DB_HOST'], $config['DB_USER'], $config['DB_PASS'], $config['DB_NAME'], $filename);
exec($cmd, $output, $ret);
if ($ret === 0) echo "Backup saved to: $filename\n";
else echo "Backup failed. Command: $cmd\n";
