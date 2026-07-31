<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dsn = "pgsql:host=ep-damp-shadow-adjtp5ul-pooler.c-2.us-east-1.aws.neon.tech;port=5432;dbname=neondb;user=neondb_owner;password=npg_ywILa5u2sOtv;sslmode=require";

try {
    // Tukar daripada $pdo kepada $conn di sini
    $conn = new PDO($dsn, null, null, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
} catch (PDOException $e) {
    // Handle connection failure securely
    die("Sambungan Gagal: " . $e->getMessage());
}
?>s
