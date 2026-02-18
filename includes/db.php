<?php

$config = require __DIR__ . '/../config.php';

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $config['db_host'],
    $config['db_name'],
    $config['db_charset']
);

try {
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Небольшая авто-миграция, чтобы проект продолжал работать на уже существующей БД.
    $pdo->exec('CREATE TABLE IF NOT EXISTS reviews (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id INT UNSIGNED NULL,
        author_name VARCHAR(120) NOT NULL,
        content TEXT NOT NULL,
        rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )');
    $pdo->exec('ALTER TABLE users ADD COLUMN IF NOT EXISTS is_banned TINYINT(1) NOT NULL DEFAULT 0');
    $pdo->exec('ALTER TABLE reviews ADD COLUMN IF NOT EXISTS rating TINYINT UNSIGNED NOT NULL DEFAULT 5');
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . htmlspecialchars($e->getMessage()));
}
