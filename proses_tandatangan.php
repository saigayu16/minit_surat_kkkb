<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $catatan = $_POST['catatan'] ?? '';
    $arahan = $_POST['arahan_pilihan'] ?? '';
    $image = $_POST['image'] ?? '';

    // Mengemas kini status menggunakan PDO prepared statement untuk Neon PostgreSQL
    try {
        $stmt = $pdo->prepare("UPDATE minit_surat SET 
                                status = 'SELESAI TANDATANGAN', 
                                catatan = ?, 
                                arahan_pilihan = ?, 
                                tandatangan = ? 
                                WHERE id = ?");
        
        $execute_success = $stmt->execute([$catatan, $arahan, $image, $id]);

        if ($execute_success) {
            echo "success";
        } else {
            echo "Gagal mengemaskini pangkalan data.";
        }
    } catch (PDOException $e) {
        echo "Ralat: " . $e->getMessage();
    }
}
?>
