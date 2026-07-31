<?php
// Complete connection script using PDO for Neon PostgreSQL
$dsn = "pgsql:host=ep-damp-shadow-adjtp5ul-pooler.c-2.us-east-1.aws.neon.tech;port=5432;dbname=neondb;user=neondb_owner;password=npg_7WhEkHOfIDLV;sslmode=require";

try {
    $pdo = new PDO($dsn);
    echo "<h3>Success! Connected to Neon PostgreSQL database successfully.</h3>";
} catch (PDOException $e) {
    echo "<h3>Connection failed:</h3> " . $e->getMessage();
}
?>