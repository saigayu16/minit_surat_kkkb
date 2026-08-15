<?php
// Sembunyikan amaran deprecated untuk PHP versi baharu
error_reporting(E_ALL & ~E_DEPRECATED);
ini_set('display_errors', 1);

session_start();
include('db.php'); // Menjangkakan sambungan menggunakan $pdo

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    // Cegah hantaran berganda (Double Submission)
    $form_token = $_POST['form_token'] ?? '';
    if (isset($_SESSION['last_form_token']) && $_SESSION['last_form_token'] === $form_token) {
        header("Location: maklum.php");
        exit;
    }
    if (!empty($form_token)) {
        $_SESSION['last_form_token'] = $form_token;
    }

    $surat_id = $_POST['surat_id'] ?? ''; 
    $nama_staf_array  = $_POST['nama_staf'] ?? []; 
  
    $perkara = "Notifikasi Dokumen Asal dan Minit Surat Baharu"; 
    
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
        $attachments = [];
        $files_to_drive = [];

        // 1. DOKUMEN ASAL 
        if (isset($_FILES['dokumen_asal']) && !empty($_FILES['dokumen_asal']['name'][0])) {
            $total_files = count($_FILES['dokumen_asal']['name']);
            
            if ($_FILES['dokumen_asal']['error'][0] == 0) {
                $files_to_drive[] = [
                    "name" => $_FILES['dokumen_asal']['name'][0],
                    "mimeType" => $_FILES['dokumen_asal']['type'][0],
                    "data" => base64_encode(file_get_contents($_FILES['dokumen_asal']['tmp_name'][0]))
                ];
            }

            for ($i = 0; $i < $total_files; $i++) {
                if ($_FILES['dokumen_asal']['error'][$i] == 0) {
                    $attachments[] = [
                        "content" => base64_encode(file_get_contents($_FILES['dokumen_asal']['tmp_name'][$i])),
                        "name" => $_FILES['dokumen_asal']['name'][$i]
                    ];
                }
            }
        }

        // 2. DOKUMEN MINIT
        if (isset($_FILES['dokumen_minit']) && $_FILES['dokumen_minit']['error'] == 0) {
            $base64_minit = base64_encode(file_get_contents($_FILES['dokumen_minit']['tmp_name']));
            
            $attachments[] = [
                "content" => $base64_minit,
                "name" => $_FILES['dokumen_minit']['name']
            ];

            $files_to_drive[] = [
                "name" => $_FILES['dokumen_minit']['name'],
                "mimeType" => $_FILES['dokumen_minit']['type'],
                "data" => $base64_minit
            ];
        }

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
            $api_key = getenv('BREVO_API_KEY'); 
            
            if (empty($api_key)) {
                echo "<script>alert('Ralat: BREVO_API_KEY tidak dijumpai.'); window.history.back();</script>";
                exit;
            }
            
            $data = [
                "sender" => ["email" => "kkkepalabatasminit2026@gmail.com", "name" => "Sistem Minit Digital"],
                "to" => $to_recipients,
                "subject" => $perkara,
                "htmlContent" => "Assalamualaikum Dan Selamat Sejahtera<br><br>Merujuk perkara di atas adalah untuk tindakan dan makluman pihak tuan/puan.<br><br>Sekian Terima Kasih<br><br><b>\"MALAYSIA MADANI\"</b><br><br><b>\"BERKHIDMAT UNTUK NEGARA\"</b>"
            ];

            if (!empty($attachments)) {
                $data["attachment"] = $attachments;
            }

            // Hantar e-mel ke Brevo
            $ch = curl_init('https://api.brevo.com/v3/smtp/email');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['api-key: ' . $api_key, 'Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 120);
            $response = curl_exec($ch);
            $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($http_code == 201 || $http_code == 200) {
                $senarai_staf_string = implode(', ', $nama_staf_array);
                
                // Pastikan ID surat diambil dengan betul untuk proses kemaskini status
                if (!empty($surat_id)) {
                    $surat_id_val = intval($surat_id);
                } else {
                    $stmt_last = $pdo->query("SELECT id FROM minit_surat ORDER BY id DESC LIMIT 1");
                    $surat_id_val = $stmt_last->fetchColumn() ?: 0;
                }

                try {
                    // Masukkan ke log makluman
                    $stmt_log = $pdo->prepare("INSERT INTO makluman_log (surat_id, nama_staf, keterangan) VALUES (?, ?, ?)");
                    $stmt_log->execute([$surat_id_val, $senarai_staf_string, 'Berjaya Dimaklumkan']);

                    // Kemaskini status minit_surat kepada DIMAKLUM
                    if ($surat_id_val > 0) {
                        $stmt_update = $pdo->prepare("UPDATE minit_surat SET maklum_kepada = ?, status = 'DIMAKLUM' WHERE id = ?");
                        $stmt_update->execute([$senarai_staf_string, $surat_id_val]);
                    }
                } catch (PDOException $e) {}

                $url_google_script = "https://script.google.com/macros/s/AKfycbwDYnT6Znzq6PKH63O2VBGsNAi-Vdi3rMUdPAg346WeHzZQVxOVBI8kvYRWaJU6TKSY6g/exec"; 
                
                // 1. Hantar ke Google Sheets
                $data_to_sheets = ['no_rujukan' => $no_rujukan_surat, 'perkara' => $perkara, 'nama_staf' => $senarai_staf_string];
                $ch_gs = curl_init($url_google_script);
                curl_setopt($ch_gs, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch_gs, CURLOPT_POSTFIELDS, json_encode($data_to_sheets));
                curl_setopt($ch_gs, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_exec($ch_gs);

                // 2. Hantar ke Google Drive
                if (!empty($files_to_drive)) {
                    $data_drive = ["action" => "mergeAndUpload", "suratId" => $surat_id_val, "files" => $files_to_drive];
                    $ch_drive = curl_init($url_google_script);
                    curl_setopt($ch_drive, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch_drive, CURLOPT_POSTFIELDS, json_encode($data_drive));
                    curl_setopt($ch_drive, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                    curl_exec($ch_drive);
                }

                $redirect_id = !empty($surat_id) ? $surat_id : '';
                echo "<script>alert('Berjaya dihantar!'); window.location='maklum.php?id=" . $redirect_id . "';</script>";
                exit;

            } else {
                echo "<script>alert('E-mel gagal dihantar.'); window.history.back();</script>";
            }
        } else {
            echo "<script>alert('Tiada e-mel staf yang sah.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Sila pilih sekurang-kurangnya seorang staf.'); window.history.back();</script>";
    }
}
?>
