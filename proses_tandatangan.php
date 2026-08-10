<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $catatan = $_POST['catatan'] ?? '';
    $arahan = $_POST['arahan_pilihan'] ?? '';
    $image = $_POST['image'] ?? '';
    
    // Ambil data pegawai dan salinan_kepada dari borang POST
    $pegawai = $_POST['pegawai'] ?? '';
    $salinan_kepada = $_POST['salinan_kepada'] ?? '';

    // Mengemas kini status beserta kolum pegawai dan salinan_kepada
    try {
        $stmt = $pdo->prepare("UPDATE minit_surat SET 
                                status = 'SELESAI TANDATANGAN', 
                                catatan = ?, 
                                arahan_pilihan = ?, 
                                tandatangan = ?,
                                pegawai = ?,
                                salinan_kepada = ?
                                WHERE id = ?");
        
        $execute_success = $stmt->execute([$catatan, $arahan, $image, $pegawai, $salinan_kepada, $id]);

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
