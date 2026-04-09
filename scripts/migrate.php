<?php

$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_NAME') ?: 'subscriber_db';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASS') ?: 'secret';

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS repositories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            owner VARCHAR(255) NOT NULL,
            repo VARCHAR(255) NOT NULL,
            last_seen_tag VARCHAR(255) DEFAULT NULL,
            UNIQUE KEY full_name (owner, repo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS subscriptions (
            id INT AUTO_INCREMENT PRIMARY KEY,
            repository_id INT NOT NULL,
            email VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY repo_email (repository_id, email),
            FOREIGN KEY (repository_id) REFERENCES repositories(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    echo "Migrations executed successfully.\n";

} catch (\PDOException $e) {
    echo "Migration failed: " . $e->getMessage() . "\n";
    exit(1);
}
