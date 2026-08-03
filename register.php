<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    include('db.php');
    
    $username = trim($_POST['username']);
    $password = $_POST['password']; // Simpan terus tanpa hash (ikut keperluan anda)
    $role     = $_POST['role'];

    // Simpan ke DB secara terus
    $stmt = $conn->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $password, $role);

    if ($stmt->execute()) {
        echo "<script>alert('Pendaftaran berjaya!'); window.location='login.php';</script>";
        exit;
    } else {
        echo "<script>alert('Ralat: " . addslashes($conn->error) . "');</script>";
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Pengguna - Sistem Minit Surat (Neon Mode)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --neon-bg: #05050a;
            --neon-card: rgba(13, 13, 25, 0.85);
            --neon-primary: #00f2fe; /* Cyan Terang */
            --neon-secondary: #4facfe; /* Biru Neon */
            --neon-accent: #a855f7; /* Ungu Neon */
            --neon-glow: 0 0 15px rgba(0, 242, 254, 0.4), 0 0 30px rgba(0, 242, 254, 0.2);
            --neon-glow-purple: 0 0 15px rgba(168, 85, 247, 0.4);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(0, 242, 254, 0.3);
        }

        body { 
            font-family: 'Inter', sans-serif; 
            background-image: linear-gradient(rgba(5, 5, 10, 0.85), rgba(5, 5, 10, 0.85)), url('backgroundkkkb.jpg'); 
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            background-position: center center;
            background-color: var(--neon-bg);
            margin: 0; 
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        /* Kad Gaya Neon Glassmorphism */
        .register-card { 
            background: var(--neon-card); 
            backdrop-filter: blur(16px); 
            padding: 40px 35px; 
            border-radius: 20px; 
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.8), inset 0 0 15px rgba(0, 242, 254, 0.05); 
            width: 380px; 
            box-sizing: border-box;
            border: 1px solid var(--border-color); 
            text-align: center;
            position: relative;
        }

        /* Kesan Garisan Cahaya Atas Kad */
        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 20%;
            right: 20%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--neon-primary), transparent);
            box-shadow: var(--neon-glow);
        }

        /* Gaya Logo */
        .logo-container {
            margin-bottom: 20px;
        }
        
        .logo-kolej {
            height: 90px;
            width: auto;
            object-fit: contain;
            filter: drop-shadow(0 0 8px rgba(0, 242, 254, 0.4));
        }

        h2 { 
            color: var(--text-main); 
            margin: 0 0 5px 0; 
            font-weight: 700; 
            font-size: 1.6rem;
            letter-spacing: -0.5px;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }

        .subtitle {
            color: var(--text-muted);
            font-size: 0.88rem;
            margin-bottom: 30px;
        }

        /* Input Sematan Ikon */
        .input-group {
            position: relative;
            margin-bottom: 18px;
            text-align: left;
        }

        .input-group i {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--neon-primary);
            font-size: 1rem;
            z-index: 2;
            transition: color 0.3s;
        }

        input, select { 
            width: 100%; 
            padding: 12px 12px 12px 42px; 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 10px; 
            background: rgba(15, 23, 42, 0.6); 
            box-sizing: border-box; 
            font-size: 0.95rem;
            color: var(--text-main);
            transition: all 0.3s ease;
            font-family: inherit;
            position: relative;
        }

        /* Pilihan dropdown select */
        select {
            cursor: pointer;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2300f2fe'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px;
        }

        select option {
            background-color: #0f172a;
            color: var(--text-main);
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--neon-primary);
            background: rgba(15, 23, 42, 0.9);
            box-shadow: 0 0 12px rgba(0, 242, 254, 0.3);
        }

        input:focus + i, select:focus ~ i {
            color: var(--neon-secondary);
            text-shadow: 0 0 8px var(--neon-primary);
        }

        /* Butang Neon */
        button { 
            width: 100%; 
            padding: 13px; 
            margin-top: 10px; 
            background: linear-gradient(135deg, var(--neon-secondary), var(--neon-primary)); 
            color: #05050a; 
            border: none; 
            border-radius: 10px; 
            cursor: pointer; 
            font-size: 0.95rem;
            font-weight: 700; 
            transition: all 0.3s ease; 
            box-shadow: 0 4px 15px rgba(0, 242, 254, 0.3);
            letter-spacing: 0.5px;
        }

        button:hover { 
            background: linear-gradient(135deg, var(--neon-primary), var(--neon-secondary)); 
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(0, 242, 254, 0.6), 0 0 40px rgba(0, 242, 254, 0.2);
        }

        button:active {
            transform: translateY(0);
        }

        /* Pautan Kembali */
        .back-link { 
            margin-top: 25px; 
            display: inline-flex; 
            align-items: center;
            gap: 6px;
            font-size: 0.85rem; 
            color: var(--text-muted); 
            text-decoration: none; 
            transition: all 0.3s;
            font-weight: 500;
        }

        .back-link:hover { 
            color: var(--neon-primary); 
            text-shadow: 0 0 8px rgba(0, 242, 254, 0.4);
        }
    </style>
</head>
<body>

<div class="register-card">
    <div class="logo-container">
        <img src="logokkkb.png" alt="Logo Kolej" class="logo-kolej">
    </div>

    <h2>Daftar Pengguna</h2>
    <p class="subtitle">Sila lengkapkan maklumat akaun baharu</p>
    
    <form method="POST">
        <div class="input-group">
            <i class="fa-solid fa-user"></i>
            <input type="text" name="username" placeholder="Nama Pengguna" required>
        </div>
        
        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" name="password" placeholder="Kata Laluan" required>
        </div>
        
        <div class="input-group">
            <i class="fa-solid fa-user-shield"></i>
            <select name="role" required>
                <option value="" disabled selected hidden>Pilih Peranan...</option>
                <option value="admin">Admin</option>
                <option value="pengarah">Pengarah Kolej</option>
                <option value="tpp">Timbalan Pengarah Pengurusan</option>
                <option value="tpa">Timbalan Pengarah Akademik</option>
            </select>
        </div>
        
        <button type="submit">Daftar Sekarang</button>
    </form>
    
    <a href="login.php" class="back-link">
        <i class="fa-solid fa-arrow-left"></i> Kembali ke Log Masuk
    </a>
</div>

</body>
</html>
