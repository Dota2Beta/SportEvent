<?php

$config = require __DIR__ . '/../config.php';

$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $config['db_host'],
    $config['db_name'],
    $config['db_charset']
);

function tableExists(PDO $pdo, string $tableName): bool
{
    $stmt = $pdo->prepare('SHOW TABLES LIKE ?');
    $stmt->execute([$tableName]);
    return (bool)$stmt->fetchColumn();
}

function columnExists(PDO $pdo, string $tableName, string $columnName): bool
{
    $stmt = $pdo->prepare('SHOW COLUMNS FROM `' . $tableName . '` LIKE ?');
    $stmt->execute([$columnName]);
    return (bool)$stmt->fetchColumn();
}

try {
    $pdo = new PDO($dsn, $config['db_user'], $config['db_pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);

    // Совместимость со старыми версиями БД проекта.
    if (!tableExists($pdo, 'reviews')) {
        $pdo->exec('CREATE TABLE reviews (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id INT UNSIGNED NULL,
            author_name VARCHAR(120) NOT NULL,
            content TEXT NOT NULL,
            rating TINYINT UNSIGNED NOT NULL DEFAULT 5,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
    }

    if (!tableExists($pdo, 'feedback_messages')) {
        $pdo->exec('CREATE TABLE feedback_messages (
            id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            client_name VARCHAR(120) NOT NULL,
            client_email VARCHAR(190) NOT NULL,
            message TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )');
    }

    if (tableExists($pdo, 'users') && !columnExists($pdo, 'users', 'is_banned')) {
        $pdo->exec('ALTER TABLE users ADD COLUMN is_banned TINYINT(1) NOT NULL DEFAULT 0');
    }

    if (tableExists($pdo, 'reviews') && !columnExists($pdo, 'reviews', 'user_id')) {
        $pdo->exec('ALTER TABLE reviews ADD COLUMN user_id INT UNSIGNED NULL AFTER id');
    }

    if (tableExists($pdo, 'reviews') && !columnExists($pdo, 'reviews', 'rating')) {
        $pdo->exec('ALTER TABLE reviews ADD COLUMN rating TINYINT UNSIGNED NOT NULL DEFAULT 5 AFTER content');
    }
} catch (PDOException $e) {
    die('Ошибка подключения к базе данных: ' . htmlspecialchars($e->getMessage()));
}
