<?php
$config = include __DIR__ . '/config.php';
try {
    $pdo = new PDO(
        "mysql:host={$config['db']['host']};dbname={$config['db']['name']};charset=utf8mb4",
        $config['db']['user'],
        $config['db']['pass'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch(PDOException $e) {
    die("DB Connection failed: " . $e->getMessage());
}
