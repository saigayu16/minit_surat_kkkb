<?php 
ob_start();
session_start();
include('db.php'); 

if (!isset($_GET['id']) || empty($_GET['id'])) { die("ID Dokumen tidak sah."); }

$id = intval($_GET['id']);

// Mengambil data asas dahulu untuk mengelakkan ralat kolum tidak wujud
$stmt = $pdo->prepare("SELECT * FROM minit_surat WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) { die("Rekod tidak ditemui."); }

// Data Formatting
$status = strtoupper(trim($row['status'] ?? 'TIADA STATUS'));
$no_rujukan = htmlspecialchars($row['no_rujukan'] ?? '-');
$tarikh_terima = !empty($row['tarikh_terima']) ? date('d/m/Y', strtotime($row['tarikh_terima'])) : '-';
$daripada = htmlspecialchars($row['daripada'] ?? '-');
$didaftarkan_oleh = htmlspecialchars($row['didaftarkan_oleh'] ?? 'Admin');
$catatan = !empty($row['catatan']) ? nl2br(htmlspecialchars($row['catatan'])) : '<em>Tiada catatan diberikan.</em>';
$arahan = htmlspecialchars($row['arahan_pilihan'] ?? 'TIADA ARAHAN');

// Semak wujud atau tidak kolum baru bagi mengelakkan error
$pegawai = htmlspecialchars($row['pegawai'] ?? '-');
$salinan_kepada = htmlspecialchars($row['salinan_kepada'] ?? '-');

$tarikh_sah = !empty($row['tarikh_disahkan']) ? date('d/m/Y', strtotime($row['tarikh_disahkan'])) : (!empty($row['tarikh_sah']) ? date('d/m/Y', strtotime($row['tarikh_sah'])) : date('d/m/Y'));
$signature_data = $row['tandatangan'] ?? ''; 
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <title>Kertas Minit - <?= $no_rujukan ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 20px; font-family: 'Segoe UI', sans-serif; position: relative; overflow-x: hidden; min-height: 100vh; box-sizing: border-box; }

        body::before {
            content: ''; position: fixed; top: 0; left: 0; width: 100%; height: 100%;
            background-image: url('daftarsurat.jpg'); background-size: cover; background-position: center; 
            background-attachment: fixed; background-repeat: no-repeat; filter: blur(8px);
            transform: scale(1.1); z-index: -1;
        }
         
        .page-box { 
            background: rgba(255, 255, 255, 0.95);
            width: 210mm; margin: 0 auto 100px auto; padding: 25mm; 
            border: 1px solid #e2e8f0; box-shadow: 0 4px 15px rgba(0,0,0,0.2);
            min-height: 297mm; position: relative; box-sizing: border-box;
        }
         
        .header-title { font-size: 26px; font-weight: 800; color: #1e293b; text-align: center; text-transform: uppercase; margin-bottom: 5px; }
        .office-header { font-size: 18px; font-weight: 700; color: #1e293b; text-align: center; margin-bottom: 25px; border-bottom: 2px solid #1e293b; padding-bottom: 15px; }
         
        .sticky-note { 
            background: #fffbeb; padding: 25px; border-radius: 4px; border-left: 10px solid #f59e0b; 
            box-shadow: 5px 5px 15px rgba(0,0,0,0.1); margin: 20px 0; position: relative;
        }
        .sticky-note::after { content: "PENTING"; position: absolute; top: 10px; right: 10px; font-size: 10px; color: #b45309; font-weight: bold; }
        .arahan-badge { background: #f59e0b; color: #fff; padding: 4px 12px; border-radius: 4px; font-size: 12px; font-weight: bold; margin-bottom: 10px; display: inline-block; }

        .stamp-box { 
            border: 3px solid #1e293b; padding: 15px; width: 220px; text-align: center; 
            float: right; margin-top: 40px; background: #fff; position: relative; clear: both;
        }
        .stamp-box::before { content: "TANDATANGAN RASMI"; position: absolute; top: -12px; background: white; padding: 0 5px; font-size: 9px; font-weight: bold; color: #1e293b; }
        .sig-image { max-height: 60px; display: block; margin: 0 auto 5px auto; }

        .btn-container { position: fixed; bottom: 30px; right: 30px; display: flex; gap: 10px; z-index: 100; }
        .btn-action { padding: 15px 30px; border-radius: 50px; border: none; cursor: pointer; font-weight: 600; box-shadow: 0 4px 10px rgba(0,0,0,0.3); transition: 0.3s; text-decoration: none; display: inline-block; }
        .btn-print { background: #0f172a; color: white; }
        .btn-back { background: #e2e8f0; color: #475569; }
        .btn-action:hover { transform: scale(1.05); }

        @media print { .no-print { display: none !important; } body::before { display: none; } body { background: white; } .page-box { box-shadow: none; border: none; margin: 0 auto; } }
    </style>
</head>
<body>

<div class="page-box">
    <div class="header-title">Kertas Minit</div>
    <div class="office-header">PEJABAT PENGARAH<br>KOLEJ KOMUNITI KEPALA BATAS</div>
     
    <table width="100%" cellpadding="10" border="0" style="border-collapse: collapse; margin-bottom: 20px;">
        <tr>
            <td width="50%" style="border: 1px solid #e2e8f0;"><strong>No. Rujukan:</strong><br><?= $no_rujukan ?></td>
            <td width="50%" style="border: 1px solid #e2e8f0;"><strong>Tarikh Terima:</strong><br><?= $tarikh_terima ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0;"><strong>Daripada:</strong><br><?= $daripada ?></td>
            <td style="border: 1px solid #e2e8f0;"><strong>Didaftarkan Oleh:</strong><br><?= $didaftarkan_oleh ?></td>
        </tr>
        <tr>
            <td style="border: 1px solid #e2e8f0;"><strong>Pegawai:</strong><br><?= $pegawai ?></td>
            <td style="border: 1px solid #e2e8f0;"><strong>Salinan Kepada:</strong><br><?= $salinan_kepada ?></td>
        </tr>
    </table>

    <div class="sticky-note">
        <div class="arahan-badge"><i class="fa-solid fa-bolt"></i> ARAHAN: <?= $arahan ?></div>
        <div style="font-size: 16px; color: #451a03; line-height: 1.6;"><?= $catatan ?></div>
    </div>

    <?php if (!empty($signature_data)): ?>
        <div class="stamp-box">
            <img src="<?= $signature_data ?>" class="sig-image">
            <div style="border-top: 1px solid #000; font-size: 11px; font-weight: bold; padding-top: 5px;">
                PENGARAH<br><?= $tarikh_sah ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="btn-container no-print">
    <a href="homeadmin.php" class="btn-action btn-back">
        <i class="fa-solid fa-arrow-left"></i> KEMBALI
    </a>
    <button class="btn-action btn-print" onclick="window.print()">
        <i class="fa-solid fa-print"></i> CETAK BORANG RASMI
    </button>
</div>

</body>
</html>
<?php ob_end_flush(); ?>
