<?php
include('db.php'); // Menjangkakan sambungan menggunakan $pdo

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_staf = $_POST['email'] ?? '';
    $nama_staf  = $_POST['nama_staf'] ?? '';
    $id_surat   = $_POST['id_surat'] ?? $_GET['id'] ?? null; // Pastikan ID surat diterima untuk Semak Perkara

    // Ambil nilai 'perkara' daripada database berdasarkan id surat (jika ada)
    $perkara_surat = "Dokumen Asal dan Minit Surat Baharu"; // Nilai default jika tiada ID
    if ($id_surat) {
        $stmt_perkara = $pdo->prepare("SELECT perkara FROM minit_surat WHERE id = ? LIMIT 1");
        $stmt_perkara->execute([$id_surat]);
        $row_perkara = $stmt_perkara->fetch(PDO::FETCH_ASSOC);
        if ($row_perkara && !empty($row_perkara['perkara'])) {
            $perkara_surat = $row_perkara['perkara'];
        }
    }
 
    // 1. Semak staf dalam database menggunakan PDO prepared statement
    $stmt = $pdo->prepare("SELECT nama FROM staff WHERE email = ? LIMIT 1");
    $stmt->execute([$email_staf]);
    $staf = $stmt->fetch(PDO::FETCH_ASSOC);
 
    if ($staf) {
        // 2. Sediakan array untuk menampung senarai fail lampiran
        $attachments = [];
 
        // Lampiran 1: Dokumen Asal
        if (isset($_FILES['dokumen_asal']) && $_FILES['dokumen_asal']['error'] == 0) {
            $nama_asal = $_FILES['dokumen_asal']['name'];
            $base64_asal = base64_encode(file_get_contents($_FILES['dokumen_asal']['tmp_name']));
            
            $attachments[] = [
                "content" => $base64_asal,
                "name" => $nama_asal
            ];
        }
 
        // Lampiran 2: Dokumen Minit
        if (isset($_FILES['dokumen_minit']) && $_FILES['dokumen_minit']['error'] == 0) {
            $nama_minit = $_FILES['dokumen_minit']['name'];
            $base64_minit = base64_encode(file_get_contents($_FILES['dokumen_minit']['tmp_name']));
            
            $attachments[] = [
                "content" => $base64_minit,
                "name" => $nama_minit
            ];
        }
 
        // 3. Sediakan struktur data untuk API Brevo
        $api_key = getenv('BREVO_API_KEY');
        
        $data = [
    "sender" => ["email" => "kkkepalabatasminit2026@gmail.com", "name" => "Sistem Minit Digital"],
    "to" => [["email" => $email_staf]],
    "subject" => "Notifikasi Surat: " . $perkara, // Mengambil nilai tajuk/perkara yang ditaip admin
    "htmlContent" => "
        <p>Assalamualaikum wbt, <b>" . htmlspecialchars($staf['nama']) . "</b>,</p>
        <p>Sila semak dokumen asal surat serta dokumen minit yang telah dilampirkan bersama e-mel ini untuk tindakan selanjutnya.</p>
        <p><b>Perkara:</b> " . htmlspecialchars($perkara) . "</p>
        <p>Sekian, terima kasih.</p>
    "
];
 
        // Masukkan lampiran hanya jika fail wujud
        if (!empty($attachments)) {
            $data["attachment"] = $attachments;
        }
 
        // 4. Hantar e-mel menggunakan cURL ke Brevo API
        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['api-key: ' . $api_key, 'Content-Type: application/json']);
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
 
        if ($http_code == 201 || $http_code == 200) {
            echo "<script>alert('E-mel beserta Dokumen Asal dan Dokumen Minit berjaya dihantar kepada staf!'); window.location='homeadmin.php';</script>";
        } else {
            echo "E-mel gagal dihantar. Sila semak API Key Brevo atau saiz fail lampiran di pelayan.";
        }
 
    } else {
        echo "Staf tidak dijumpai di dalam pangkalan data.";
    }
}
?>
