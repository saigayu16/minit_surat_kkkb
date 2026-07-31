<?php
include('db.php'); // Menjangkakan sambungan menggunakan $pdo

if (!isset($_GET['id'])) { die("ID tidak dijumpai."); }
$id = intval($_GET['id']);

// Mengambil data surat menggunakan PDO prepared statement dengan senarai kolum penuh secara eksplisit
$stmt = $pdo->prepare("SELECT id, no_rujukan, tarikh_terima, daripada, kepada, perkara, perkara_surat, kolej, didaftarkan_oleh, status, fail_surat, tempoh_tindakan, tandatangan_fail, tarikh_disahkan, target_role, catatan, tandatangan, arahan_pilihan, maklum_kepada, tandatangan_data, drive_file_id, arahan, created_at, staf_dimaklumkan FROM minit_surat WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) { die("Data surat tidak ditemui."); }

// Tentukan sumber fail untuk dipaparkan
$fail_tempatan = "uploads/" . ($row['fail_surat'] ?? '');
$guna_drive = false;

// Logik paparan: Guna uploads/ jika fail wujud, jika tidak guna Drive
if (!empty($row['fail_surat']) && file_exists($fail_tempatan)) {
    $sumber_fail = $fail_tempatan;
} else if (!empty($row['drive_file_id']) && $row['drive_file_id'] !== "GAGAL_UPLOAD") {
    $sumber_fail = "https://drive.google.com/file/d/" . $row['drive_file_id'] . "/preview";
    $guna_drive = true;
} else {
    $sumber_fail = null;
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Paparan Rasmi - <?= htmlspecialchars($row['no_rujukan'] ?? '-') ?></title>
    <style>
        body { 
            font-family: 'Segoe UI', sans-serif; 
            margin: 0;
            position: relative;
            overflow-x: hidden;
            min-height: 100vh;
        }

        /* Lapisan khas untuk imej latar belakang dengan kesan blur */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(0, 0, 0, 0.4), rgba(0, 0, 0, 0.4)), url('background.jpg'); 
            background-size: cover;
            background-position: center;
            filter: blur(8px);
            transform: scale(1.1);
            z-index: -1;
        }

        .wrapper { max-width: 1200px; margin: auto; display: grid; grid-template-columns: 1fr 400px; gap: 20px; padding: 20px; }
        .card { background: rgba(255, 255, 255, 0.95); padding: 25px; border-radius: 12px; box-shadow: 0 8px 16px rgba(0,0,0,0.1); }
        .info-row { display: flex; margin-bottom: 10px; }
        .info-label { width: 150px; font-weight: bold; color: #4a5568; }
        .btn-nav { padding: 8px 15px; background: #4a5568; color: white; text-decoration: none; border-radius: 5px; }
        .sig-box { margin-top: 20px; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px; background: #f8fafc; }
    </style>
</head>
<body>

<div class="wrapper">
    <div class="card">
        <h3>📄 Dokumen: <?= htmlspecialchars($row['no_rujukan'] ?? '-') ?></h3>
        
        <?php if ($sumber_fail): ?>
            <iframe src="<?= $sumber_fail ?>" width="100%" height="600px" style="border:1px solid #ddd;"></iframe>
        <?php else: ?>
            <div style="height: 600px; display: flex; align-items: center; justify-content: center; background: #eee;">
                <p>Dokumen tidak dijumpai di server mahupun di Google Drive.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h2>BORANG MINIT</h2>
        <div class="info-row"><div class="info-label">Rujukan:</div> <div><?= htmlspecialchars($row['no_rujukan'] ?? '-') ?></div></div>
        <div class="info-row"><div class="info-label">Kolej:</div> <div><?= htmlspecialchars($row['kolej'] ?? '-') ?></div></div>
        <hr>
        
        <p><strong>Catatan:</strong><br> <?= nl2br(htmlspecialchars($row['catatan'] ?? 'Tiada')) ?></p>
        
        <?php if (!empty($row['arahan_pilihan'])): ?>
            <p><strong>Arahan:</strong><br> <span style="color: #2563eb; font-weight:bold;"><?= htmlspecialchars($row['arahan_pilihan']) ?></span></p>
        <?php endif; ?>

        <?php if (!empty($row['tandatangan'])): ?>
            <div class="sig-box">
                <small>Tandatangan Pengarah:</small><br>
                <img src="<?= $row['tandatangan'] ?>" alt="Tandatangan" style="width: 200px; height: auto; margin-top: 5px;">
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 20px; display: flex; justify-content: space-between;">
            <?php
            // Cari ID sebelum (Previous ID dalam PostgreSQL)
            $stmt_prev = $pdo->prepare("SELECT id FROM minit_surat WHERE id < ? ORDER BY id DESC LIMIT 1");
            $stmt_prev->execute([$id]);
            $prev = $stmt_prev->fetch(PDO::FETCH_ASSOC);

            // Cari ID selepas (Next ID dalam PostgreSQL)
            $stmt_next = $pdo->prepare("SELECT id FROM minit_surat WHERE id > ? ORDER BY id ASC LIMIT 1");
            $stmt_next->execute([$id]);
            $next = $stmt_next->fetch(PDO::FETCH_ASSOC);
            ?>
            <a href="view_surat.php?id=<?= $prev['id'] ?? $id ?>" class="btn-nav" <?= !$prev ? 'style="visibility:hidden"' : '' ?>>⬅ Sebelumnya</a>
            <a href="view_surat.php?id=<?= $next['id'] ?? $id ?>" class="btn-nav" <?= !$next ? 'style="visibility:hidden"' : '' ?>>Seterusnya ➡</a>
        </div>
        <br>
        <a href="homeadmin.php">⬅ Kembali ke Dashboard</a>
    </div>
</div>

</body>
</html>
