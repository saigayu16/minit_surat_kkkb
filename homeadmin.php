<?php
ob_start();
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('db.php'); 

if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['user_name']) && !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$user_name = $_SESSION['user_name'] ?? ($_SESSION['username'] ?? 'Admin Sistem');

// PROSES PADAM REKOD (DELETE FUNCTIONALITY)
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);
    try {
        $stmt = $pdo->prepare("DELETE FROM minit_surat WHERE id = ?");
        $stmt->execute([$delete_id]);
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?'));
        exit;
    } catch (PDOException $e) {
        // Handle error quietly or let it pass
    }
}

// KIRA STATISTIK
$count_all = $pdo->query("SELECT COUNT(*) as total FROM minit_surat");
$total_surat = ($count_all) ? $count_all->fetch(PDO::FETCH_ASSOC)['total'] : 0;

$count_wait = $pdo->query("SELECT COUNT(*) as total FROM minit_surat WHERE status != 'SELESAI TANDATANGAN' AND status != 'DIMAKLUM'");
$total_wait = ($count_wait) ? $count_wait->fetch(PDO::FETCH_ASSOC)['total'] : 0;

$count_done = $pdo->query("SELECT COUNT(*) as total FROM minit_surat WHERE status = 'SELESAI TANDATANGAN' OR status = 'DIMAKLUM'");
$total_done = ($count_done) ? $count_done->fetch(PDO::FETCH_ASSOC)['total'] : 0;
?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sistem Minit Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #1e293b;
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

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('homeadmin.jpg'); 
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            background-position: center center;
            filter: blur(8px);
            transform: scale(1.1);
            z-index: -1;
        }

        .navbar { background: var(--primary-color); color: white; padding: 1.2rem 2rem; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
        .container { max-width: 1300px; margin: 40px auto; padding: 0 20px; }
        .admin-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 35px; }
        .stat-card { background: var(--card-bg); padding: 24px; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .stat-info h4 { margin: 0; color: var(--text-muted); font-size: 0.85rem; text-transform: uppercase; }
        .stat-info p { margin: 8px 0 0 0; font-size: 1.8rem; font-weight: 700; color: #0f172a; }
        .table-container { background: var(--card-bg); border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); border: 1px solid var(--border-color); overflow: hidden; }
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; color: var(--text-muted); padding: 16px; font-size: 0.85rem; font-weight: 600; text-transform: uppercase; border-bottom: 1px solid var(--border-color); }
        td { padding: 16px; border-bottom: 1px solid var(--border-color); font-size: 0.95rem; color: #334155; max-width: 250px; word-wrap: break-word; }
        .status-badge { padding: 6px 12px; border-radius: 50px; font-size: 0.75rem; font-weight: 600; text-transform: uppercase; }
        .wait { background: #fee2e2; color: #991b1b; }
        .selesai-badge { background: #e0e7ff; color: #4338ca; }
        
        .row-done { background-color: #f0fdf4 !important; } /* Hijau lembut jika sudah dimaklum */

        .btn-view { display: inline-block; padding: 6px 12px; background: #e0e7ff; color: #4338ca; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: 600; margin-bottom: 5px; }
        .btn-print { display: inline-block; padding: 6px 12px; background: #dcfce7; color: #166534; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: 600; margin-bottom: 5px; }
        .btn-delete { display: inline-block; padding: 6px 12px; background: #fee2e2; color: #991b1b; border-radius: 4px; text-decoration: none; font-size: 0.8rem; font-weight: 600; }
        .btn-delete:hover { background: #f87171; color: white; }
        .btn-daftar { background: #059669; color: white; padding: 10px 18px; border-radius: 6px; text-decoration: none; font-size: 0.9rem; font-weight: 600; transition: background 0.3s; }
        .btn-daftar:hover { background: #047857; }
        .header-actions { display: flex; align-items: center; gap: 20px; }
    </style>
</head>
<body>

<nav class="navbar">
    <h2><i class="fa-solid fa-folder-open"></i> Sistem Minit Digital</h2>
    <div class="header-actions">
        <span style="color:white;"><i class="fa-solid fa-user"></i> <?= htmlspecialchars($user_name) ?></span>
        <a href="daftar_surat.php" class="btn-daftar"><i class="fa-solid fa-plus"></i> Daftar Surat Masuk</a>
        <a href="logout.php" style="color:#f87171; text-decoration:none;"><i class="fa-solid fa-right-from-bracket"></i> Log Keluar</a>
    </div>
</nav>

<div class="container">
    <div class="admin-stats">
        <div class="stat-card"><div class="stat-info"><h4>Jumlah Surat</h4><p><?= $total_surat ?></p></div></div>
        <div class="stat-card"><div class="stat-info"><h4>Menunggu</h4><p><?= $total_wait ?></p></div></div>
        <div class="stat-card"><div class="stat-info"><h4>Selesai</h4><p><?= $total_done ?></p></div></div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Tarikh</th>
                    <th>No. Rujukan</th>
                    <th>Daripada</th>
                    <th>Perkara</th>
                    <th>Status</th>
                    <th>Maklum Kepada</th>
                    <th>Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php
                try {
                    $sql = "SELECT id, no_rujukan, tarikh_terima, created_at, daripada, perkara, status, maklum_kepada FROM minit_surat ORDER BY id DESC";
                    $res = $pdo->query($sql);
                    $rows = $res->fetchAll(PDO::FETCH_ASSOC);

                    if ($rows && count($rows) > 0) {
                        foreach($rows as $row) {
                            $is_done = !empty($row['maklum_kepada']);
                            $row_class = $is_done ? 'row-done' : '';
                            
                            $status = trim($row['status'] ?? 'Menunggu');
                            $badge = ($status == 'SELESAI TANDATANGAN' || $status == 'DIMAKLUM') ? 'selesai-badge' : 'wait';
                            
                            $tarikh_raw = $row['tarikh_terima'] ?? ($row['created_at'] ?? '');
                            $tarikh = !empty($tarikh_raw) ? date('d/m/Y', strtotime($tarikh_raw)) : '-';
                            
                            $rujukan = htmlspecialchars($row['no_rujukan'] ?? '-');
                            $daripada = htmlspecialchars($row['daripada'] ?? '-');
                            $perkara = htmlspecialchars($row['perkara'] ?? '-');

                            // Logik Paparan Kolum Maklum Kepada (Auto-Tick jika ada, dan butang untuk maklum semula)
                            if ($is_done) {
                                $maklum_display = "<span style='color:#059669; font-weight:bold;'><i class='fa-solid fa-circle-check'></i> " . htmlspecialchars($row['maklum_kepada']) . "</span><br>" .
                                                  "<a href='maklum.php?id={$row['id']}' style='font-size:0.8rem; color:#7c3aed; text-decoration:none;'><i class='fa-solid fa-paper-plane'></i> Maklum Semula</a>";
                            } else {
                                $maklum_display = "<a href='maklum.php?id={$row['id']}' style='color:#7c3aed; text-decoration:none; font-weight:bold;'><i class='fa-solid fa-paper-plane'></i> Maklum</a>";
                            }

                            echo "<tr class='{$row_class}'>
                                <td>{$tarikh}</td>
                                <td>{$rujukan}</td>
                                <td>{$daripada}</td>
                                <td>{$perkara}</td>
                                <td><span class='status-badge {$badge}'>{$status}</span></td>
                                <td>{$maklum_display}</td>
                                <td>
                                    <a href='view_surat.php?id={$row['id']}' class='btn-view'><i class='fa-solid fa-eye'></i> Lihat</a><br>
                                    <a href='cetak_minit.php?id={$row['id']}' target='_blank' class='btn-print'><i class='fa-solid fa-print'></i> Cetak</a><br>
                                    <a href='?delete_id={$row['id']}' class='btn-delete' onclick=\"return confirm('Adakah anda pasti mahu memadam rekod surat ini?');\"><i class='fa-solid fa-trash'></i> Padam</a>
                                </td>
                            </tr>";
                        }
                    } else {
                        echo "<tr><td colspan='7' style='text-align:center; padding: 30px; color: #64748b;'>📂 Tiada rekod surat dijumpai dalam pangkalan data.</td></tr>";
                    }
                } catch (PDOException $e) {
                    echo "<tr><td colspan='7' style='text-align:center; padding: 30px; color: #991b1b;'>Ralat pangkalan data: " . htmlspecialchars($e->getMessage()) . "</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
</body>
</html>
<?php ob_end_flush(); ?>
