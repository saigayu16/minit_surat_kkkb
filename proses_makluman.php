<?php
include('db.php'); // Menjangkakan sambungan menggunakan $pdo

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email_staf_input = $_POST['email'] ?? ''; // Mengandungi senarai e-mel dipisahkan koma (cth: a@mail.com, b@mail.com)
    $nama_staf_array  = $_POST['nama_staf'] ?? []; // Array nama staf yang dipilih
  
    // Auto-baca teks 'perkara' daripada surat yang PALING TERKINI (baru sahaja didaftarkan oleh admin)
    $perkara = "Notifikasi Dokumen Asal dan Minit Surat Baharu"; // Nilai default jika tiada rekod
    
    $stmt_perkara = $pdo->prepare("SELECT perkara FROM minit_surat ORDER BY id DESC LIMIT 1");
    $stmt_perkara->execute();
    $row_perkara = $stmt_perkara->fetch(PDO::FETCH_ASSOC);
    
    if ($row_perkara && !empty($row_perkara['perkara'])) {
        $perkara = $row_perkara['perkara']; // Paparkan tajuk sebenar yang ditaip admin
    }

    if (!empty($nama_staf_array)) {
        // 2. Sediakan array untuk menampung senarai fail lampiran (Dokumen Asal & Dokumen Minit)
        $attachments = [];

        // Lampiran 1: Pelbagai Dokumen Asal (Multiple files)
        if (isset($_FILES['dokumen_asal']) && !empty($_FILES['dokumen_asal']['name'][0])) {
            $total_files = count($_FILES['dokumen_asal']['name']);
            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['dokumen_asal']['error'][$i] == 0) {
                    $nama_asal = $_FILES['dokumen_asal']['name'][$i];
                    $base64_asal = base64_encode(file_get_contents($_FILES['dokumen_asal']['tmp_name'][$i]));
                    
                    $attachments[] = [
                        "content" => $base64_asal,
                        "name" => $nama_asal
                    ];
                }
            }
        }

        // Lampiran 2: Dokumen Minit (Single file)
        if (isset($_FILES['dokumen_minit']) && $_FILES['dokumen_minit']['error'] == 0) {
            $nama_minit = $_FILES['dokumen_minit']['name'];
            $base64_minit = base64_encode(file_get_contents($_FILES['dokumen_minit']['tmp_name']));
            
            $attachments[] = [
                "content" => $base64_minit,
                "name" => $nama_minit
            ];
        }

        // Sediakan senarai penerima emel dalam bentuk array untuk Brevo API ("to")
        $to_recipients = [];
        foreach ($nama_staf_array as $nama_staf_item) {
            $stmt = $pdo->prepare("SELECT email FROM staff WHERE nama = ? LIMIT 1");
            $stmt->execute([$nama_staf_item]);
            $staf_row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($staf_row && !empty($staf_row['email'])) {
                $to_recipients[] = ["email" => $staf_row['email']];
            }
        }

        if (!empty($to_recipients)) {
            // 3. Sediakan struktur data untuk API Brevo
            $api_key = getenv('BREVO_API_KEY');
            
            $data = [
                "sender" => ["email" => "kkkepalabatasminit2026@gmail.com", "name" => "Sistem Minit Digital"],
                "to" => $to_recipients,
                "subject" => $perkara,
                "htmlContent" => "
                    Assalamualaikum Dan Selamat Sejahtera<br><br>
                    Merujuk Perkara Di Atas Adalah Untuk Tindakan Dan Makluman Pihak Tuan/Puan.<br><br>
                    <b>Perkara:</b> {$perkara}<br><br>
                    Sekian Terima Kasih<br><br>
                    <b>\"MALAYSIA MADANI\"</b><br><br>
                    <b>\"BERKHIDMAT UNTUK NEGARA\"</b>
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
                echo "<script>alert('E-mel beserta Dokumen Asal (Multiple) dan Dokumen Minit berjaya dihantar kepada staf!'); window.location='homeadmin.php';</script>";
            } else {
                echo "E-mel gagal dihantar. Sila semak API Key Brevo atau saiz keseluruhan fail lampiran di pelayan. Response: " . $response;
            }
        } else {
            echo "Tiada e-mel staf yang sah dijumpai untuk dihantar.";
        }

    } else {
        echo "Sila pilih sekurang-kurangnya seorang staf.";
    }
}
?>
