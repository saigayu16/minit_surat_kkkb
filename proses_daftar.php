<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('db.php'); // Menjangkakan sambungan menggunakan $pdo

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Semak jika data POST kosong disebabkan had saiz fail pelayan dilangkau
    if (empty($_POST) && empty($_FILES)) {
        die("Ralat: Saiz fail yang dimuat naik melebihi had yang dibenarkan oleh pelayan (Server POST limit). Sila semak fail php.ini.");
    }

    // 1. Ambil input dengan selamat
    $no_rujukan       = $_POST['no_rujukan'] ?? '';
    $tarikh_terima    = $_POST['tarikh_terima'] ?? '';
    $daripada         = $_POST['daripada'] ?? '';
    $tarikh_surat     = $_POST['tarikh_surat'] ?? '';
    $perkara          = $_POST['perkara'] ?? ''; 
    $kolej            = $_POST['kolej'] ?? '';
    $target_role      = $_POST['target_role'] ?? '';
    
    // Ambil nama admin yang sedang log masuk dari session
    $didaftarkan_oleh = $_SESSION['user_name'] ?? 'Admin Sistem';

    // 2. Dapatkan Emel Penerima Berdasarkan Role menggunakan PDO ($pdo)
    $stmt_email = $pdo->prepare("SELECT email FROM users WHERE role = ? LIMIT 1");
    $stmt_email->execute([$target_role]);
    $user_row = $stmt_email->fetch(PDO::FETCH_ASSOC);
    $email_penerima = $user_row ? $user_row['email'] : null;
    
    if (!$email_penerima) {
        die("Ralat: Tiada emel didaftarkan untuk peranan (role) $target_role");
    }

    // Sediakan data fail untuk e-mel Brevo (jika ada dimuat naik secara lokal untuk dilampirkan)
    $base64_file = null;
    $file_name = null;

    if (isset($_FILES['fail_surat']) && $_FILES['fail_surat']['error'] == 0) {
        $file_name = $_FILES['fail_surat']['name'];
        $base64_file = base64_encode(file_get_contents($_FILES['fail_surat']['tmp_name']));
    }

    // 3. Simpan ke Database Neon (PostgreSQL) menggunakan RETURNING id tanpa Google Drive
    $sql = "INSERT INTO minit_surat (no_rujukan, tarikh_terima, daripada, perkara, tarikh_surat, kolej, target_role, status, didaftarkan_oleh)  
            VALUES (?, ?, ?, ?, ?, ?, ?, 'BARU', ?) RETURNING id";
    $stmt = $pdo->prepare($sql);
    
    $success = $stmt->execute([
        $no_rujukan, 
        $tarikh_terima, 
        $daripada, 
        $perkara, 
        $tarikh_surat,
        $kolej, 
        $target_role, 
        $didaftarkan_oleh
    ]);
    
    if ($success) {
        $row_inserted = $stmt->fetch(PDO::FETCH_ASSOC);
        $id_surat_baru = $row_inserted['id']; 

        // 4. Hantar data ke Google Spreadsheet secara automatik (Tanpa terima_daripada)
        $url_google_script = "https://script.google.com/macros/s/AKfycbwfYyFrdbeh-IoWKsOVOmZ3M7drqRT6fJ7hXXNvzoU4tUA09wKr82cnUa-LD6fe1Ret/exec"; 

        $data_to_send = [
            'tarikh_penerimaan' => $tarikh_terima,
            'no_surat'          => $no_rujukan,
            'no_fail'           => $kolej,
            'tarikh_surat'      => $tarikh_surat,
            'daripada_siapa'    => $daripada,
            'perkara'           => $perkara,
            'dirujuk_kepada'    => strtoupper($target_role)
        ];

        $ch_sheet = curl_init($url_google_script);
        curl_setopt($ch_sheet, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch_sheet, CURLOPT_POSTFIELDS, json_encode($data_to_send));
        curl_setopt($ch_sheet, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_exec($ch_sheet);
        curl_close($ch_sheet);

        // 5. Tentukan Halaman Dashboard Mengikut Logik Anda
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'];
        
        $halaman_tujuan = ""; 
        $role = strtolower(trim($target_role)); 
        
        if (strpos($role, 'tpp') !== false) {
            $halaman_tujuan = "hometpp.php"; 
        } elseif (strpos($role, 'tpa') !== false) {
            $halaman_tujuan = "hometpa.php"; 
        } elseif (strpos($role, 'pengarah') !== false) {
            $halaman_tujuan = "homedirector.php"; 
        }

        if (empty($halaman_tujuan)) {
            die("Ralat: Kategori peranan ('$target_role') tidak sah.");
        }

        $link_sistem = $protocol . "://$host/$halaman_tujuan?id=" . $id_surat_baru; 

        // 6. Integrasi API Brevo (Kekal seperti asal anda)
        $api_key = getenv('BREVO_API_KEY');
        
        $data = [
            "sender" => ["email" => "kkkepalabatasminit2026@gmail.com", "name" => "Sistem Minit Digital"],
            "to" => [["email" => $email_penerima]],
            "subject" => "Notifikasi Surat: " . $perkara,
            "htmlContent" => "
                Assalamualaikum Dan Selamat Sejahtera<br><br>
                Merujuk Perkara Di Atas Adalah Untuk Tindakan Dan Makluman Pihak Tuan/Puan <b>{$no_rujukan}</b>.</p>
                <p><b>Perkara:</b> {$perkara}</p>
                <p>Sila klik butang di bawah untuk masuk ke dashboard anda dan menyemak surat:</p>
                Sekian Terima Kasih<br><br>
                <b>\"MALAYSIA MADANI\"</b><br><br>
                <b>\"BERKHIDMAT UNTUK NEGARA\"</b>
                <p>Sila klik butang di bawah untuk masuk ke dashboard anda dan menyemak surat:</p>
                <p><a href='{$link_sistem}' style='background: #f57c00; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; display: inline-block; font-weight: bold;'>Buka Dashboard Sistem</a></p>
                <p>Atau salin pautan ini ke pelayar anda: <br><a href='{$link_sistem}'>{$link_sistem}</a></p>
            "
        ];
        
        if ($base64_file && $file_name) {
            $data["attachment"] = [["content" => $base64_file, "name" => $file_name]];
        }

        $ch = curl_init('https://api.brevo.com/v3/smtp/email');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['api-key: ' . $api_key, 'Content-Type: application/json']);
        curl_exec($ch);
        curl_close($ch);

        echo "<script>alert('Surat telah didaftarkan, dimasukkan ke Spreadsheet, dan e-mel berjaya dihantar!'); window.location='homeadmin.php';</script>";
    } else {
        $errorInfo = $stmt->errorInfo();
        echo "Ralat Database: " . $errorInfo[2];
    }
}
?>
