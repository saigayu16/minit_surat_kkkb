<?php
session_start();
include('db.php');

if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id'])) { die("ID tidak sah"); }
$id = intval($_GET['id']);

// Menggunakan PDO untuk mendapatkan maklumat surat
$stmt = $pdo->prepare("SELECT * FROM minit_surat WHERE id = ?");
$stmt->execute([$id]);
$surat = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$surat) { die("Dokumen tidak ditemui"); }
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Tandatangan Digital</title>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            padding: 20px;
            margin: 0;
            position: relative;
            overflow-x: hidden;
            min-height: 100vh;
            box-sizing: border-box;
        }

        /* Lapisan khas untuk imej latar belakang dengan kesan blur */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)), url('homedirector.jpg'); 
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            background-position: center center;
            filter: blur(8px); /* Ubah nilai 8px ini jika mahu lebih atau kurang kabur */
            transform: scale(1.1); /* Mengelakkan kesan putih di tepi akibat blur */
            z-index: -1; /* Memastikan latar belakang berada di lapisan paling bawah */
        }

        .container { max-width: 1000px; margin: auto; display: grid; grid-template-columns: 1.2fr 0.8fr; gap: 20px; }
        .panel { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .sticky-note { background: #fef08a; padding: 15px; border-radius: 4px; box-shadow: 3px 3px 5px rgba(0,0,0,0.1); margin-bottom: 20px; }
        #signature-pad { border: 2px dashed #cbd5e0; width: 100%; height: 180px; border-radius: 8px; cursor: crosshair; background-color: #fafafa; margin-bottom: 15px; }
        .btn { padding: 12px 20px; cursor: pointer; border: none; border-radius: 6px; font-weight: bold; width: 48%; transition: opacity 0.2s; }
        textarea, input[type="text"] { width: 100%; padding: 10px; border: 1px solid #cbd5e0; border-radius: 6px; box-sizing: border-box; margin-bottom: 15px; }
        textarea { height: 80px; resize: vertical; }

        .sticky-note-box { background: #f0f7ff; padding: 20px; border-radius: 8px; border-left: 5px solid #2563eb; box-shadow: 0 4px 6px rgba(0,0,0,0.05); margin-bottom: 20px; }
        .arahan-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 15px; }
        .arahan-grid label { display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 500; color: #1e293b; padding: 8px; background: white; border-radius: 6px; border: 1px solid #e2e8f0; }
        .arahan-grid label:hover { border-color: #2563eb; }
    </style>
</head>
<body>

<div class="container">
    <div class="panel">
        <h3><i class="fa-solid fa-file-pdf"></i> Dokumen Rujukan</h3>
        <?php 
        // Logik Paparan Google Drive
        if (!empty($surat['drive_file_id']) && $surat['drive_file_id'] !== 'GAGAL_UPLOAD') {
            echo '<iframe src="https://drive.google.com/file/d/' . htmlspecialchars($surat['drive_file_id']) . '/preview" width="100%" height="600px" style="border:none;"></iframe>';
        } else {
            // Fallback kepada cara asal jika drive_id tiada
            echo '<iframe src="uploads/' . htmlspecialchars($surat['fail_surat']) . '" width="100%" height="600px" style="border:none;"></iframe>';
        }
        ?>
    </div>

    <div class="panel">
        <div class="sticky-note">
            <h4><i class="fa-solid fa-note-sticky"></i> Ringkasan Minit</h4>
            <p><strong>No Rujukan:</strong> <?= htmlspecialchars($surat['no_rujukan']) ?></p>
            <p><strong>Perkara:</strong> <?= htmlspecialchars($surat['perkara'] ?? $surat['perkara_surat']) ?></p>
        </div>

        <label><strong>Catatan:</strong></label>
        <textarea id="catatan" placeholder="Tulis ulasan atau arahan di sini..."></textarea>
        
        <div class="sticky-note-box">
            <h4 style="margin: 0 0 10px 0; color: #1e293b;"><i class="fa-solid fa-clipboard-check" style="color: #2563eb;"></i> Arahan Minit</h4>
            <div class="pejabat-header" style="text-align: center; margin-bottom: 20px; font-weight: 700; color: #1e293b;">
                PEJABAT PENGARAH<br>KOLEJ KOMUNITI KEPALA BATAS
            </div>
            <div class="arahan-grid">
                <label><input type="checkbox" name="arahan" value="Untuk Tindakan"> <i class="fa-solid fa-bolt" style="color: #ea580c;"></i> Untuk Tindakan</label>
                <label><input type="checkbox" name="arahan" value="Untuk Makluman"> <i class="fa-solid fa-circle-info" style="color: #0891b2;"></i> Untuk Makluman</label>
                <label><input type="checkbox" name="arahan" value="Sila Hadir"> <i class="fa-solid fa-calendar-check" style="color: #059669;"></i> Sila Hadir</label>
                <label><input type="checkbox" name="arahan" value="Untuk Edaran"> <i class="fa-solid fa-bullhorn" style="color: #4f46e5;"></i> Untuk Edaran</label>
                <label><input type="checkbox" name="arahan" value="Untuk Pertimbangan / Kajian"> <i class="fa-solid fa-magnifying-glass-chart" style="color: #7c3aed;"></i> Untuk Pertimbangan / Kajian</label>
                <label><input type="checkbox" name="arahan" value="Untuk Simpanan Fail"> <i class="fa-solid fa-folder-closed" style="color: #d97706;"></i> Untuk Simpanan Fail</label>
            </div>
        </div>

        <!-- Kolom Pegawai dan Salinan Kepada -->
        <label><strong>Pegawai:</strong></label>
        <input type="text" id="pegawai" placeholder="Masukkan nama pegawai...">

        <label><strong>Salinan Kepada:</strong></label>
        <input type="text" id="salinan_kepada" placeholder="Masukkan nama pihak berkaitan...">

        <p>Sila turunkan tandatangan digital di bawah:</p>
        <canvas id="signature-pad"></canvas>
        
        <div style="display: flex; justify-content: space-between;">
            <button class="btn" onclick="signaturePad.clear()" style="background:#e53e3e; color:white;">Padam</button>
            <button class="btn" id="save" style="background:#38a169; color:white;">Minit & Sahkan ke Drive</button>
        </div>
    </div>
</div>

<script>
    const canvas = document.getElementById('signature-pad');
    canvas.width = canvas.offsetWidth;
    canvas.height = canvas.offsetHeight;
    const signaturePad = new SignaturePad(canvas);
    const idSurat = "<?= $id ?>";

    document.getElementById('save').addEventListener('click', function() {
        if (signaturePad.isEmpty()) { alert("Sila turunkan tandatangan terlebih dahulu!"); return; }
        
        const btnSave = document.getElementById('save');
        btnSave.innerText = "Memproses...";
        btnSave.disabled = true;

        const formData = new FormData();
        formData.append('id', idSurat);
        formData.append('image', signaturePad.toDataURL('image/png'));
        formData.append('catatan', document.getElementById('catatan').value);
        formData.append('pegawai', document.getElementById('pegawai').value);
        formData.append('salinan_kepada', document.getElementById('salinan_kepada').value);
        
        const selectedArahan = [];
        document.querySelectorAll('input[name="arahan"]:checked').forEach((cb) => selectedArahan.push(cb.value));
        formData.append('arahan_pilihan', selectedArahan.join(', '));

        fetch('proses_tandatangan.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(data => {
            if (data.trim() === 'success') {
                window.location.href = 'homedirector.php';
            } else {
                alert("Gagal menyimpan: " + data);
                btnSave.innerText = "Minit & Sahkan ke Drive";
                btnSave.disabled = false;
            }
        });
    });
</script>
</body>
</html>
