<?php
// Complete connection script using PDO for Neon PostgreSQL
$dsn = "pgsql:host=ep-damp-shadow-adjtp5ul-pooler.c-2.us-east-1.aws.neon.tech;port=5432;dbname=neondb;user=neondb_owner;password=npg_H8FhZ2piYdSE;sslmode=require";

try {
    // Create a PDO connection for PostgreSQL
    $pdo = new PDO($dsn);
    
    // Set error mode to exception for easier debugging
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // Handle connection failure securely
    die("Sambungan Gagal: " . $e->getMessage());
}
?>
