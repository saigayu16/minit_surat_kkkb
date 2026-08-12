<?php 
ob_start();
session_start();
include('db.php'); 
$id = $_GET['id'] ?? ''; 

if (empty($id)) {
    die("ID Surat tidak sah.");
}

// Proses tambah staf baharu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_staf_baru'])) {
    $nama_baru = trim($_POST['nama_baru'] ?? '');
    $email_baru = trim($_POST['email_baru'] ?? '');

    if (!empty($nama_baru) && !empty($email_baru)) {
        try {
            $stmt_check = $pdo->prepare("SELECT id FROM staff WHERE email = ?");
            $stmt_check->execute([$email_baru]);
            
            if ($stmt_check->rowCount() > 0) {
                echo "<script>alert('Ralat: E-mel staf ini sudah wujud dalam pangkalan data!'); window.location.href='?id=" . htmlspecialchars($id) . "';</script>";
                exit;
            } else {
                $stmt_insert = $pdo->prepare("INSERT INTO staff (nama, email) VALUES (?, ?)");
                $stmt_insert->execute([$nama_baru, $email_baru]);
                
                echo "<script>alert('Staf baru berjaya disimpan dalam database!'); window.location.href='?id=" . htmlspecialchars($id) . "';</script>";
                exit;
            }
        } catch (PDOException $e) {
            echo "<script>alert('Ralat Database: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// Proses padam staf
if (isset($_GET['padam_id'])) {
    $padam_id = $_GET['padam_id'];
    try {
        $stmt_delete = $pdo->prepare("DELETE FROM staff WHERE id = ?");
        $stmt_delete->execute([$padam_id]);
        
        echo "<script>alert('Staf berjaya dipadam dari database!'); window.location.href='?id=" . htmlspecialchars($id) . "';</script>";
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Ralat Database: " . addslashes($e->getMessage()) . "'); window.location.href='?id=" . htmlspecialchars($id) . "';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maklum Kepada Staf</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            font-family: 'Quicksand', sans-serif; 
            display: flex; 
            flex-direction: column;
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
            padding: 20px 0;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('daftarsurat.jpg'); 
            background-size: cover;         
            background-position: center;     
            background-attachment: fixed;    
            background-repeat: no-repeat;
            filter: blur(8px); 
            transform: scale(1.1); 
            z-index: -1; 
        }

        .box { 
            background: #fff9c4; 
            padding: 30px 40px; 
            border-radius: 2px 20px 2px 20px; 
            width: 100%; 
            max-width: 600px; 
            box-shadow: 15px 15px 30px rgba(0,0,0,0.15); 
            position: relative;
            margin: 20px 0;
        }

        h3 { margin: 0 0 15px 0; color: #5d4037; text-align: center; font-weight: 700; }
        h4 { color: #5d4037; border-bottom: 2px dashed #fbc02d; padding-bottom: 8px; margin-top: 25px; }
        
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 4px; color: #795548; font-size: 0.85rem; font-weight: 600; }
        
        input, select { 
            width: 100%; 
            padding: 10px; 
            border: 2px dashed #fbc02d; 
            border-radius: 5px; 
            background: rgba(255,255,255,0.4);
            box-sizing: border-box; 
            font-family: inherit;
            font-size: 0.9rem;
        }

        .staff-checkbox-container {
            max-height: 140px;
            overflow-y: auto;
            border: 2px dashed #fbc02d;
            border-radius: 5px;
            background: rgba(255,255,255,0.4);
            padding: 8px;
            box-sizing: border-box;
        }

        .staff-checkbox-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 0.85rem;
            color: #5d4037;
        }

        .staff-left {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            flex-grow: 1;
        }

        .staff-checkbox-item input[type="checkbox"] {
            width: auto;
            cursor: pointer;
        }

        .btn-delete-staff {
            background: #e53935;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 3px 6px;
            font-size: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s;
        }
        .btn-delete-staff:hover { background: #c62828; }

        button { 
            width: 100%; 
            padding: 11px; 
            background: #f57c00; 
            color: white; 
            border: none; 
            border-radius: 50px; 
            font-weight: 600; 
            cursor: pointer; 
            margin-top: 10px; 
            transition: 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        button:hover { background: #e65100; transform: scale(1.02); }

        .btn-secondary {
            background: #795548;
            font-size: 0.85rem;
            padding: 8px;
            margin-top: 5px;
        }
        .btn-secondary:hover { background: #5d4037; }

        .toggle-section {
            background: rgba(255, 243, 224, 0.6);
            border: 1px dashed #ffa726;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .pin {
            width: 25px;
            height: 25px;
            background: radial-gradient(circle at 30% 30%, #ef5350, #b71c1c);
            border-radius: 50%;
            position: absolute;
            top: -10px;
            left: 50%;
            margin-left: -12.5px;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
        }

        .bukti-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 0.85rem;
            background: rgba(255,255,255,0.6);
            border-radius: 5px;
            overflow: hidden;
        }
        .bukti-table th, .bukti-table td {
            border: 1px solid #fbc02d;
            padding: 8px;
            text-align: left;
            color: #5d4037;
        }
        .bukti-table th {
            background: #ffe082;
        }
        .btn-back-home {
            display: inline-block;
            margin-top: 15px;
            text-align: center;
            background: #4e342e;
            color: white;
            text-decoration: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 0.9rem;
            font-weight: 600;
        }
        .btn-back-home:hover { background: #3e2723; }
    </style>
</head>
<body>

    <div class="box">
        <div class="pin"></div>
        <h3><i class="fa-solid fa-note-sticky"></i> Nota Makluman</h3>
        
        <!-- Bahagian Optional: Daftar Staf Baru -->
        <div class="toggle-section">
            <details>
                <summary style="cursor: pointer; color: #d84315; font-weight: 600; font-size: 0.9rem;">
                    <i class="fa-solid fa-user-plus"></i> Staf tiada dalam senarai? Klik sini untuk daftar baru
                </summary>
                <form method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="tambah_staf_baru" value="1">
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label>Nama Staf Baru:</label>
                        <input type="text" name="nama_baru" placeholder="Masukkan nama penuh" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label>E-mel Staf Baru:</label>
                        <input type="email" name="email_baru" placeholder="Masukkan e-mel" required>
                    </div>
                    <button type="submit" class="btn-secondary">Simpan Staf Baru</button>
                </form>
            </details>
        </div>

        <!-- Borang Utama Makluman -->
        <form action="proses_makluman.php" method="POST" enctype="multipart/form-data" id="maklumanForm" onsubmit="handleFormSubmit(event)">
            <input type="hidden" name="surat_id" value="<?= htmlspecialchars($id) ?>">
            
            <div class="form-group">
                <label>Pilih Nama Staf (Boleh pilih lebih daripada satu):</label>
                <div class="staff-checkbox-container">
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT id, nama, email FROM staff ORDER BY nama ASC");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $staff_id = $row['id'];
                            $nama_attr = htmlspecialchars($row['nama']);
                            $email_attr = htmlspecialchars($row['email']);
                            
                            echo '<div class="staff-checkbox-item">
                                    <label class="staff-left">
                                        <input type="checkbox" name="nama_staf[]" value="' . $nama_attr . '" data-email="' . $email_attr . '" onchange="updateEmails()"> ' . $nama_attr . '
                                    </label>
                                    <a href="?id=' . htmlspecialchars($id) . '&padam_id=' . $staff_id . '" class="btn-delete-staff" onclick="return confirm(\'Adakah anda pasti mahu memadam staf ' . $nama_attr . '?\')"><i class="fa-solid fa-trash"></i></a>
                                </div>';
                        }
                    } catch (PDOException $e) {
                        echo '<span style="color:red; font-size:0.8rem;">Ralat: ' . $e->getMessage() . '</span>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="form-group">
                <label>E-mel Staf (Auto-popup):</label>
                <input type="text" name="email" id="email" required readonly style="background: rgba(255,255,255,0.2); cursor: not-allowed;" placeholder="Akan terpapar secara automatik">
            </div>
            
            <div class="form-group">
                <label>Dokumen Asal (Boleh pilih lebih daripada satu):</label>
                <input type="file" name="dokumen_asal[]" id="dokumen_asal" accept=".pdf,.jpg,.png" multiple required>
            </div>

            <div class="form-group">
                <label>Borang Minit Ceraian:</label>
                <input type="file" name="dokumen_minit" id="dokumen_minit" accept=".pdf,.jpg,.png" required>
            </div>
            
            <!-- 1 BUTANG UTAMA SAHAJA (HANTAR & SIMPAN DRIVE SEKALI) -->
            <button type="submit" id="submitBtn"><i class="fa-solid fa-paper-plane"></i> Hantar Sekarang & Simpan ke Drive!</button>
        </form>

        <!-- SEKSYEN BUKTI / REKOD MAKLUMAN YANG TELAH DIHANTAR -->
        <h4><i class="fa-solid fa-clipboard-check"></i> Bukti / Rekod Makluman Terdahulu</h4>
        <div style="max-height: 180px; overflow-y: auto;">
            <table class="bukti-table">
                <thead>
                    <tr>
                        <th>Tarikh & Masa</th>
                        <th>Pihak / Staf Penerima</th>
                        <th>Dokumen / Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    try {
                        $stmt_bukti = $pdo->prepare("SELECT * FROM makluman_log WHERE surat_id = ? ORDER BY id DESC");
                        $stmt_bukti->execute([$id]);
                        $log_rows = $stmt_bukti->fetchAll(PDO::FETCH_ASSOC);

                        if ($log_rows && count($log_rows) > 0) {
                            foreach ($log_rows as $log) {
                                $tarikh_hantar = htmlspecialchars($log['created_at'] ?? '-');
                                $penerima = htmlspecialchars($log['nama_staf'] ?? '-');
                                $info_doc = htmlspecialchars($log['keterangan'] ?? 'Berjaya Dimaklumkan');
                                
                                echo "<tr>
                                        <td>{$tarikh_hantar}</td>
                                        <td>{$penerima}</td>
                                        <td><span style='color: #2e7d32; font-weight: bold;'><i class='fa-solid fa-circle-check'></i> {$info_doc}</span></td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' style='text-align: center; color: #795548;'>Tiada rekod makluman dihantar lagi untuk surat ini.</td></tr>";
                        }
                    } catch (PDOException $e) {
                        echo "<tr><td colspan='3' style='text-align: center; color: #c62828;'>Sila pastikan jadual log makluman wujud dalam database untuk memaparkan bukti.</td></tr>";
                    }
                    ?>
                </tbody>
          </table>
        </div>

        <div style="text-align: center;">
            <a href="homeadmin.php" class="btn-back-home"><i class="fa-solid fa-arrow-left"></i> Kembali ke Dashboard</a>
        </div>
    </div>

    <script>
        function updateEmails() {
            const checkboxes = document.querySelectorAll('input[name="nama_staf[]"]:checked');
            const emails = [];
            
            checkboxes.forEach((checkbox) => {
                const email = checkbox.getAttribute('data-email');
                if (email) {
                    emails.push(email);
                }
            });
            
            document.getElementById('email').value = emails.join(', ');
        }

        // Fungsi Pintar: Hantar ke Google Drive dahulu via AJAX, kemudian teruskan hantar form PHP
        async function handleFormSubmit(event) {
            event.preventDefault(); // Hentikan form daripada terus submit sekelip mata

            const dokumenAsalInput = document.getElementById('dokumen_asal');
            const dokumenMinitInput = document.getElementById('dokumen_minit');
            const emailField = document.getElementById('email').value;

            if (!emailField) {
                alert('Sila pilih sekurang-kurangnya seorang staf penerima.');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sedang hantar emel & simpan ke Drive...';

            try {
                let filesData = [];

                // Tukar Dokumen Asal ke Base64
                for (let i = 0; i < dokumenAsalInput.files.length; i++) {
                    let file = dokumenAsalInput.files[i];
                    let base64 = await toBase64(file);
                    filesData.push({
                        name: file.name,
                        mimeType: file.type,
                        data: base64
                    });
                }

                // Tukar Dokumen Minit ke Base64
                let minitFile = dokumenMinitInput.files[0];
                let minitBase64 = await toBase64(minitFile);
                filesData.push({
                    name: minitFile.name,
                    mimeType: minitFile.type,
                    data: minitBase64
                });

                // URL Google Apps Script Web App anda
                const scriptURL = 'https://script.google.com/macros/s/AKfycby2K31kMjzReZQU6YK63GGScf3RuLWbp8LrU2YRecHuv4FGx3VtJsKcXD6mbujL5w7j7w/exec'; 
                
                // Hantar fail ke Google Drive secara senyap di latar belakang
                await fetch(scriptURL, {
                    method: 'POST',
                    mode: 'no-cors', // Penting untuk elak ralat CORS dengan Google Apps Script
                    body: JSON.stringify({
                        action: 'mergeAndUpload',
                        suratId: '<?= htmlspecialchars($id) ?>',
                        files: filesData
                    })
                  });

                // Selepas selesai hantar ke Drive, sambung proses PHP asal (hantar emel/simpan log)
                document.getElementById('maklumanForm').submit();

            } catch (error) {
                console.error(error);
                alert('Ralat semasa proses fail ke Drive: ' + error.message);
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> Hantar Sekarang & Simpan ke Drive!';
            }
        }

        // Helper untuk convert File kepada Base64
        function toBase64(file) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.readAsDataURL(file);
                reader.onload = () => resolve(reader.result.split(',')[1]);
                reader.onerror = error => reject(error);
            });
        }
    </script>

</body>
</html>
<?php ob_end_flush(); ?>
