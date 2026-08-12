<?php 
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

include('db.php'); 

// Pastikan Admin sudah login untuk mengambil nama mereka
$admin_name = $_SESSION['user_name'] ?? 'Admin Sistem';
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Minit Surat - Daftar Surat</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --success-color: #10b981;
            --success-hover: #059669;
            --text-main: #ffffff; 
            --text-muted: #e2e8f0;
            --border-color: rgba(255, 255, 255, 0.4);
        }

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            padding: 40px 20px;
            color: var(--text-main);
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
            background-image: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('daftarsurat.jpg'); 
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            background-repeat: no-repeat;
            filter: blur(8px);
            transform: scale(1.1);
            z-index: -1;
        }

        .form-container { 
            max-width: 650px; 
            background: transparent; 
            margin: 0 auto; 
            padding: 20px 40px; 
            position: relative;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: -20px;
            left: 40px;
            width: 40px;
            height: 35px;
            background: #ef4444; 
            border-radius: 0 0 4px 4px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            z-index: 10;
        }

        .sticky-note {
            background: #fef08a; 
            color: #713f12;
            padding: 15px 20px;
            border-radius: 4px;
            box-shadow: 3px 5px 15px rgba(0,0,0,0.2);
            transform: rotate(-1deg);
            margin-bottom: 25px;
            border-left: 5px solid #eab308;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sticky-note i {
            font-size: 1.2rem;
            color: #ca8a04;
        }

        .action-box {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .action-text {
            font-size: 0.9rem;
            color: var(--text-muted);
        }

        .action-text strong {
            color: #ffffff;
            display: block;
            font-size: 1rem;
            margin-bottom: 2px;
        }

        .btn-docs {
            background-color: var(--success-color);
            color: white;
            padding: 10px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }

        .btn-docs:hover {
            background-color: var(--success-hover);
            transform: translateY(-1px);
        }

        .header-title {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 5px;
        }

        .header-title i {
            font-size: 1.8rem;
            color: #ffffff;
            background: var(--primary-color);
            padding: 12px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.2);
        }

        h2 { 
            color: var(--text-main); 
            margin: 0;
            font-weight: 700;
            font-size: 1.8rem;
            text-shadow: 1px 1px 5px rgba(0,0,0,0.5); 
        }
        
        .sub-title {
            color: var(--text-muted);
            margin-top: 5px;
            margin-bottom: 30px;
            font-size: 0.95rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }

        .form-group { 
            margin-bottom: 22px; 
        }

        label { 
            display: block; 
            font-weight: 600; 
            margin-bottom: 8px; 
            color: var(--text-main);
            font-size: 0.95rem;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
        }

        .input-wrapper {
            position: relative;
        }

        .input-wrapper i {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 1rem;
            z-index: 2;
        }

        .input-wrapper.textarea-wrapper i {
            top: 18px;
            transform: none;
        }

        input[type="text"], input[type="date"], textarea, select { 
            width: 100%; 
            padding: 12px 12px 12px 40px; 
            border: 1px solid var(--border-color); 
            border-radius: 6px; 
            box-sizing: border-box; 
            font-family: inherit;
            font-size: 0.95rem;
            color: #1e293b;
            background-color: rgba(255, 255, 255, 0.95); 
            transition: all 0.2s ease;
            box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        }

        input:focus, textarea:focus, select:focus {
            background-color: #ffffff;
            border-color: #3b82f6;
            outline: none;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.4);
        }

        input[type="file"] {
            padding: 12px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px dashed rgba(0, 0, 0, 0.3);
            width: 100%;
            border-radius: 6px;
            box-sizing: border-box;
            cursor: pointer;
            color: #1e293b;
        }

        .btn { 
            background-color: var(--primary-color); 
            color: white; 
            padding: 14px 20px; 
            border: none; 
            border-radius: 6px; 
            cursor: pointer; 
            width: 100%; 
            font-weight: 600; 
            font-size: 1rem;
            margin-top: 15px;
            transition: all 0.2s;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(37, 99, 235, 0.4);
        }

        .btn:hover { 
            background-color: var(--primary-hover); 
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

    <div class="form-container">
        <!-- Nota Pentadbiran -->
        <div class="sticky-note">
            <i class="fa-solid fa-thumbtack"></i>
            <div>
                <strong>Nota Pentadbiran:</strong> Log masuk sebagai <u><?= htmlspecialchars($admin_name) ?></u>. Semua surat didaftarkan di bawah ID anda.
            </div>
        </div>

        <!-- Butang Tindakan Pantas Ke Google Docs -->
        <div class="action-box">
            <div class="action-text">
                <strong>Belum ada fail surat fizikal?</strong>
                Bina surat rasmi baru secara terus di Google Docs.
            </div>
            <a href="https://docs.google.com/document/u/0/?ftv=1&tgif=d" target="_blank" class="btn-docs">
                <i class="fa-solid fa-plus"></i> Buka Google Docs
            </a>
        </div>

        <div class="header-title">
            <i class="fa-solid fa-envelope-open-text"></i>
            <h2>Daftar Surat Masuk</h2>
        </div>
        <p class="sub-title">Sila lengkapkan maklumat surat masuk dengan teliti di bawah.</p>
        
        <form action="proses_daftar.php" method="POST" enctype="multipart/form-data">
            
            <input type="hidden" name="didaftarkan_oleh" value="<?= htmlspecialchars($admin_name) ?>">

            <div class="form-group">
                <label>No Rujukan Surat:</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-hashtag"></i>
                    <input type="text" name="no_rujukan" placeholder="Contoh: KKKB/100-1/2/3(4)" required>
                </div>
            </div>

            <div class="form-group">
                <label>Nama Kolej:</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-school"></i>
                    <select name="kolej" required>
                        <option value="Kolej Komuniti Kepala Batas">Kolej Komuniti Kepala Batas</option>
                        <option value="Kolej Komuniti Lain">Kolej Komuniti Lain</option>
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label>Daripada:</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-user-tie"></i>
                    <input type="text" name="daripada" placeholder="Nama agensi, syarikat atau individu" required>
                </div>
            </div>

            <div class="form-group">
                <label>Hantar Kepada:</label>
                <select name="target_role" required>
                    <option value="pengarah">Pengarah</option>
                    <option value="tpp">Timbalan Pengarah Pengurusan (TPP)</option>
                    <option value="tpa">Timbalan Pengarah Akademik (TPA)</option>
                </select>
            </div>

            <div class="form-group">
                <label>Tarikh Terima:</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-calendar-days"></i>
                    <input type="date" name="tarikh_terima" required>
                </div>
            </div>

            <div class="form-group">
                <label>Tarikh Surat:</label>
                <div class="input-wrapper">
                    <i class="fa-solid fa-calendar-check"></i>
                    <input type="date" name="tarikh_surat" required>
                </div>
            </div>

            <div class="form-group">
                <label> Perkara Surat:</label>
                <div class="input-wrapper textarea-wrapper">
                    <i class="fa-solid fa-heading"></i>
                    <textarea name="perkara" rows="2" placeholder="Ringkasan tajuk surat" required></textarea>
                </div>
            </div>

            <div class="form-group">
                <label> Maklumat Lanjut (optional):</label>
                <div class="input-wrapper textarea-wrapper">
                    <i class="fa-solid fa-align-left"></i>
                    <textarea name="perkara_surat" rows="3" placeholder="Butiran lanjut kandungan surat" required></textarea>
                </div>
            </div>

            <div class="form-group">
                <label>Muat Naik PDF:</label>
                <input type="file" name="fail_surat" accept="application/pdf" required>
            </div>

            <button type="submit" name="btn_simpan" class="btn">
                <i class="fa-solid fa-paper-plane"></i> Daftar & Hantar Surat
            </button>
        </form>
    </div>

</body>
</html>
