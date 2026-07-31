<?php
// Retrieve environment variables set in your hosting/Neon setup
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

// Fallback or direct connection configuration if environment variables aren't set
// (You can also replace these with your direct Neon connection string if preferred)
$dsn = "pgsql:host=$host;port=$port;dbname=$db;sslmode=require";

try {
    // Create a PDO connection for PostgreSQL
    $pdo = new PDO($dsn, $user, $pass);
    
    // Set error mode to exception for easier debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Handle connection failure securely
    die("Sambungan Gagal: " . $e->getMessage());
}
?>
