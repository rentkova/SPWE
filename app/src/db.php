<?php
$host = $_ENV['DB_HOST'] ?? getenv('DB_HOST') ?: 'mariadb';
$port = $_ENV['DB_PORT'] ?? getenv('DB_PORT') ?: '3306';
$db   = $_ENV['DB_NAME'] ?? getenv('DB_NAME') ?: 'hosting_db';
$user = $_ENV['DB_USER'] ?? getenv('DB_USER') ?: 'appuser';
$pass = $_ENV['DB_PASSWORD'] ?? getenv('DB_PASSWORD') ?: '';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $ex) {
    http_response_code(500);
    // V produkci nezobrazovat interní chybu uživateli
    die("Chyba připojení k databázi.");
}

return $pdo;
