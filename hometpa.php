<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Panggil fail sambungan DB (Neon PostgreSQL menggunakan PDO)
include('db.php'); 

// 1. SEMAK SESI & ROLE TPA
// Pastikan hanya user dengan role 'tpa' sahaja boleh akses halaman ini
if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'tpa') {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? 'Timbalan Pengarah';
$user_role = "Timbalan Pengarah Akademik (TPA)";

// 2. KIRA STATISTIK TPA (Menggunakan PDO)
$total_perlu_sahkan = 0;
$total_selesai = 0;
$total_semua = 0;

// Kira Dokumen Menunggu Pengesahan TPA (Fokus pada target_role = 'tpa')
$count_wait = $pdo->query("SELECT COUNT(*) as total FROM minit_surat WHERE target_role = 'tpa' AND status NOT LIKE '%Sudah Disahkan%'");
if($count_wait) {
    $total_perlu_sahkan = $count_wait->fetch(PDO::FETCH_ASSOC)['total'];
}

// Kira Dokumen Selesai Disahkan TPA
$count_done = $pdo->query("SELECT COUNT(*) as total FROM minit_surat WHERE target_role = 'tpa' AND status LIKE '%Sudah Disahkan%'");
if($count_done) {
    $total_selesai = $count_done->fetch(PDO::FETCH_ASSOC)['total'];
}

// Kira Jumlah Keseluruhan Dokumen TPA
$count_all = $pdo->query("SELECT COUNT(*) as total FROM minit_surat WHERE target_role = 'tpa'");
if($count_all) {
    $total_semua = $count_all->fetch(PDO::FETCH_ASSOC)['total'];
}
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard TPA - Sistem Minit Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0f172a; /* Gelap Eksekutif */
            --accent-sign: #2563eb; /* Biru Tandatangan */
            --accent-view: #ea580c; /* Oren Lihat */
            --bg-color: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #ffffff;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            padding: 0;
            color: var(--text-main);
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
            background-image: linear-gradient(rgba(0, 0, 0, 0.55), rgba(0, 0, 0, 0.55)), url('homedirector.jpg'); 
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            background-position: center center;
            filter: blur(8px); /* Ubah nilai 8px ini jika mahu lebih atau kurang kabur */
            transform: scale(1.1); /* Mengelakkan kesan putih di tepi akibat blur */
            z-index: -1; /* Memastikan latar belakang berada di lapisan paling bawah */
        }

        /* Navbar Gaya Eksekutif */
        .navbar { 
            background: var(--primary-color); 
            color: white; 
            padding: 1.2rem 2rem; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05); 
        }
        
        .navbar h2 {
            margin: 0;
            font-size: 1.3rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .navbar h2 i {
            color: #fbbf24; /* Ikon Merit Emas */
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 0.95rem;
        }

        .role-badge {
            background: #3b82f6;
            color: white;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .btn-logout {
            color: #f87171;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s;
        }
        .btn-logout:hover {
            color: #ef4444;
        }

        /* Container */
        .container { 
            max-width: 1300px; 
            margin: 40px auto; 
            padding: 0 20px; 
        }

        /* Seksyen Kriteria & Statistik */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 35px;
        }

        .stat-card {
            background: var(--card-bg);
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.02);
            border: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-info h4 {
            margin: 0;
            color: var(--text-muted);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .stat-info p {
            margin: 8px 0 0 0;
            font-size: 1.8rem;
            font-weight: 700;
            color: #0f172a; 
        }

        .stat-icon {
            font-size: 1.8rem;
            padding: 14px;
            border-radius: 10px;
        }

        .icon-sign { background: #eff6ff; color: #2563eb; }
        .icon-done { background: #f0fdf4; color: #16a34a; }
        .icon-all { background: #fef3c7; color: #d97706; }

        /* Tajuk Utama Halaman */
        .table-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--text-main);
        }

        /* Jadual Dokumen */
        .table-container {
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.02);
            border: 1px solid var(--border-color);
            overflow: hidden;
        }

        table { 
            width: 100%; 
            border-collapse: collapse; 
            text-align: left; 
        }

        th { 
            background: #f8fafc; 
            color: var(--text-muted); 
            padding: 16px; 
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            border-bottom: 1px solid var(--border-color);
        }

        td { 
            padding: 16px; 
            border-bottom: 1px solid var(--border-color); 
            font-size: 0.95rem;
            color: #334155;
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background-color: #f8fafc;
        }

        /* Status Badge */
        .status-badge { 
            padding: 6px 12px; 
            border-radius: 50px; 
            font-size: 0.75rem; 
            font-weight: 600; 
            display: inline-flex;
            align-items: center;
            gap: 5px;
            text-transform: uppercase;
        }

        .wait { background: #fee2e2; color: #991b1b; }
        .wait::before { content: '●'; color: #ef4444; }

        .done { background: #d1fae5; color: #065f46; }
        .done::before { content: '●'; color: #10b981; }

        /* Butang Tindakan Khas */
        .btn-action { 
            padding: 8px 14px; 
            border-radius: 6px; 
            text-decoration: none; 
            font-size: 0.85rem; 
            font-weight: 600; 
            display: inline-flex; 
            align-items: center;
            gap: 6px;
            transition: all 0.2s; 
        }
        
        .btn-view { 
            background: #fff7ed; 
            color: var(--accent-view); 
            border: 1px solid #ffedd5;
        }
        .btn-view:hover { 
            background: var(--accent-view); 
            color: white; 
        }
        
        .btn-sign { 
            background: var(--accent-sign); 
            color: white; 
            box-shadow: 0 4px 6px rgba(37, 99, 235, 0.15);
        }
        .btn-sign:hover { 
            background: #1d4ed8; 
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<nav class="navbar">
    <h2><i class="fa-solid fa-graduation-cap"></i> Sistem Minit Digital</h2>
    <div class="user-info">
        <span><i class="fa-solid fa-user-tie"></i> Tuan. <strong><?= htmlspecialchars($user_name) ?></strong></span>
        <span class="role-badge"><?= $user_role ?></span>
        | 
        <a href="logout.php" class="btn-logout"><i class="fa-solid fa-right-from-bracket"></i> Log Keluar</a>
    </div>
</nav>

<div class="container">
    
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-info">
                <h4>Perlu Semakan Anda</h4>
                <p><?= $total_perlu_sahkan ?></p>
            </div>
            <div class="stat-icon icon-sign">
                <i class="fa-solid fa-file-signature"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h4>Sudah Disahkan</h4>
                <p><?= $total_selesai ?></p>
            </div>
            <div class="stat-icon icon-done">
                <i class="fa-solid fa-circle-check"></i>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-info">
                <h4>Jumlah Keseluruhan</h4>
                <p><?= $total_semua ?></p>
            </div>
            <div class="stat-icon icon-all">
                <i class="fa-solid fa-folder-open"></i>
            </div>
        </div>
    </div>

    <div class="table-title">
        <i class="fa-solid fa-folder-tree" style="color: #38bdf8;"></i>
        Senarai Dokumen Semakan & Pengesahan TPA
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Rujukan</th>
                    <th>Pendaftar (Admin)</th>
                    <th>Asal Kolej</th>
                    <th>Perkara / Perincian Surat</th>
                    <th>Status Minit</th>
                    <th>Tindakan Eksekutif</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // TAPISAN UTAMA: Hanya papar surat untuk TPA menggunakan PDO
                $res = $pdo->query("SELECT * FROM minit_surat WHERE target_role = 'tpa' ORDER BY id DESC");
                
                if ($res) {
                    $rows = $res->fetchAll(PDO::FETCH_ASSOC);
                    if (count($rows) > 0) {
                        foreach($rows as $row) {
                            $status = trim($row['status'] ?? 'BARU');
                            
                            $is_done = stripos($status, 'Sudah Disahkan') !== false;
                            $badge = $is_done ? 'done' : 'wait';
                            
                            $rujukan = htmlspecialchars($row['no_rujukan'] ?? '-');
                            $pengirim = htmlspecialchars($row['didaftarkan_oleh'] ?? 'Admin');
                            $kolej = htmlspecialchars($row['kolej'] ?? '-');
                            $perkara = htmlspecialchars($row['perkara_surat'] ?? ($row['perkara'] ?? '-'));

                            echo "<tr>
                                <td style='font-weight:600; font-family:monospace; font-size:0.9rem;'>$rujukan</td>
                                <td style='font-size:0.88rem;'><i class='fa-solid fa-user-gear'></i> $pengirim</td>
                                <td><span style='font-weight:500; color:#475569;'>$kolej</span></td>
                                <td style='max-width: 300px; line-height: 1.4;'>$perkara</td>
                                <td><span class='status-badge $badge'>$status</span></td>
                                <td>";
                            
                            // Pertukaran paparan butang mengikut status
                            if ($is_done) {
                                echo '<a href="view_surat.php?id='.$row['id'].'" class="btn-action btn-view"><i class="fa-solid fa-eye"></i> Lihat</a>';
                            } else {
                                echo '<a href="tandatangan.php?id='.$row['id'].'" class="btn-action btn-sign"><i class="fa-solid fa-pen-nib"></i> Sahkan</a>';
                            }
                            echo "</td></tr>";
                        }
                    } else {
                        echo "<tr><td colspan='6' style='text-align:center; padding: 30px; color: var(--text-muted);'>📂 Tiada dokumen untuk disemak oleh TPA buat masa ini.</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='6' style='text-align:center; padding: 30px; color: var(--text-muted);'>📂 Tiada dokumen untuk disemak oleh TPA buat masa ini.</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>
