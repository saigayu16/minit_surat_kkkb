<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
include('db.php'); // Menjangkakan sambungan menggunakan $pdo

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $surat_id = $_POST['surat_id'] ?? ''; // Ambil ID surat dari form
    $email_staf_input = $_POST['email'] ?? ''; 
    $nama_staf_array  = $_POST['nama_staf'] ?? []; // Array nama staf yang dipilih
  
    // Auto-baca teks 'perkara' daripada surat yang PALING TERKINI atau mengikut ID surat
    $perkara = "Notifikasi Dokumen Asal dan Minit Surat Baharu"; // Nilai default jika tiada rekod
    
    // Jika ada surat_id, guna rujukan ID tersebut, jika tidak guna yang latest
    if (!empty($surat_id)) {
        $stmt_perkara = $pdo->prepare("SELECT perkara, no_rujukan FROM minit_surat WHERE id = ? LIMIT 1");
        $stmt_perkara->execute([$surat_id]);
    } else {
        $stmt_perkara = $pdo->prepare("SELECT perkara, no_rujukan FROM minit_surat ORDER BY id DESC LIMIT 1");
        $stmt_perkara->execute();
    }
    
    $row_perkara = $stmt_perkara->fetch(PDO::FETCH_ASSOC);
    $no_rujukan_surat = "-";
    
    if ($row_perkara) {
        if (!empty($row_perkara['perkara'])) {
            $perkara = $row_perkara['perkara'];
        }
        if (!empty($row_perkara['no_rujukan'])) {
            $no_rujukan_surat = $row_perkara['no_rujukan'];
        }
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 120); // Elakkan isu timeout 30 saat
            
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($http_code == 201 || $http_code == 200) {
                // GABUNGKAN NAMA STAF UNTUK DISIMPAN DALAM DATABASE & GOOGLE SHEETS
                $senarai_staf_string = implode(', ', $nama_staf_array);
                $surat_id_val = !empty($surat_id) ? intval($surat_id) : 0;

                try {
                    // A. Simpan rekod ke dalam database PostgreSQL (jadual makluman_log)
                    $stmt_log = $pdo->prepare("INSERT INTO makluman_log (surat_id, nama_staf, keterangan) VALUES (?, ?, ?)");
                    $stmt_log->execute([$surat_id_val, $senarai_staf_string, 'Berjaya Dimaklumkan']);
                } catch (PDOException $e) {
                    // Abaikan jika ralat log
                }

                // B. Hantar data secara auto ke Google Sheets dengan semakan keselamatan respons
                $url_google_script = "https://script.google.com/macros/s/AKfycbzcrzX07aLWHi2krdCqIGTvDSFAaFmp5YjRSdUDDsfAFIrHjV1rywUCHyDmnDDcxVGy2w/exec"; 
                if (!empty($url_google_script)) {
                    $data_to_sheets = [
                        'no_rujukan' => $no_rujukan_surat,
                        'perkara'    => $perkara,
                        'nama_staf'  => $senarai_staf_string
                    ];
                    
                    $ch_gs = curl_init($url_google_script);
                    curl_setopt($ch_gs, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch_gs, CURLOPT_POSTFIELDS, json_encode($data_to_sheets));
                    curl_setopt($ch_gs, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_setopt($ch_gs, CURLOPT_TIMEOUT, 60);
                    
                    $response_gs = curl_exec($ch_gs);
                    $http_code_gs = curl_getinfo($ch_gs, CURLINFO_HTTP_CODE);

                    // Mengelakkan ralat Unexpected token '<' jika Google mengembalikan paparan HTML ralat
                    if ($http_code_gs == 200) {
                        $json_check = json_decode($response_gs, true);
                        if (json_last_error() !== JSON_ERROR_NONE && strpos($response_gs, '<html') !== false) {
                            // Paparkan amaran mesra jika Google Script gagal memproses
                            echo "<script>alert('Amaran: E-mel berjaya dihantar tetapi Google Apps Script membalas ralat HTML. Sila semak saiz data.');</script>";
                        }
                    }
                }

                // Redirect semula ke muka surat maklum dengan membawa ID surat asal
                $redirect_id = !empty($surat_id) ? $surat_id : '';
                echo "<script>alert('E-mel dan makluman berjaya dihantar kepada staf!'); window.location='maklum.php?id=" . $redirect_id . "';</script>";
                exit;

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
