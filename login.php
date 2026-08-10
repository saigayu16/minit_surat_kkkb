<?php
session_start();

// 1. If already logged in, redirect
if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    $role = $_SESSION['user_role'] ?? '';
    
    switch ($role) {
        case 'admin': $target = 'homeadmin.php'; break;
        case 'pengarah': $target = 'homedirector.php'; break;
        case 'tpa': $target = 'hometpa.php'; break;
        case 'tpp': $target = 'hometpp.php'; break;
        default: $target = 'login.php'; break;
    }
    header("Location: $target");
    exit;
}
?>

<!-- 2. Error handling snippet -->
<?php if(isset($_GET['error'])): ?>
    <script>alert('Nama pengguna, kata laluan, atau peranan salah!');</script>
<?php endif; ?>

<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Log Masuk - Sistem Minit Digital (Neon Mode)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --neon-bg: #05050a;
            --neon-card: rgba(13, 13, 25, 0.85);
            --neon-primary: #00f2fe; 
            --neon-secondary: #4facfe; 
            --neon-glow: 0 0 15px rgba(0, 242, 254, 0.4), 0 0 30px rgba(0, 242, 254, 0.2);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: rgba(0, 242, 254, 0.3);
        }

        body { 
            font-family: 'Inter', sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            position: relative;
            overflow: hidden;
            background-color: var(--neon-bg);
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(5, 5, 10, 0.85), rgba(5, 5, 10, 0.85)), url('backgroundkkkb.jpg') no-repeat center center fixed;
            background-size: cover;
            filter: blur(8px); 
            transform: scale(1.1); 
            z-index: -1; 
        }

        .login-card { 
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

        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 20%;
            right: 20%;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--neon-primary), transparent);
            box-shadow: var(--neon-glow);
        }
        
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
            margin: 0 0 25px 0; 
            font-weight: 700;
            font-size: 1.6rem;
            letter-spacing: -0.5px;
            text-shadow: 0 0 10px rgba(255, 255, 255, 0.3);
        }

        .input-group { 
            position: relative; 
            margin-bottom: 18px; 
            text-align: left; 
        }

        /* Ikon kiri di dalam input */
        .input-group > i:not(.toggle-password) { 
            position: absolute; 
            left: 14px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: var(--neon-primary); 
            font-size: 1rem;
            z-index: 2;
            transition: color 0.3s;
        }

        /* Ikon mata untuk togol kata laluan di sebelah kanan */
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            cursor: pointer;
            font-size: 1rem;
            z-index: 3;
            transition: color 0.3s;
        }

        .toggle-password:hover {
            color: var(--neon-primary);
        }

        input, select { 
            width: 100%; 
            padding: 12px 42px 12px 42px; 
            border: 1px solid rgba(255, 255, 255, 0.1); 
            border-radius: 10px; 
            background: rgba(15, 23, 42, 0.6); 
            box-sizing: border-box; 
            font-size: 0.95rem;
            color: var(--text-main);
            transition: all 0.3s ease;
            font-family: inherit;
            appearance: none; 
            -webkit-appearance: none;
            -moz-appearance: none;
        }

        /* Input biasa tanpa ikon kanan (seperti nama pengguna) saiz padding kanan berbeza */
        input[name="username"] {
            padding-right: 12px;
        }

        select {
            cursor: pointer;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%2300f2fe'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M19 9l-7 7-7-7'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 14px center;
            background-size: 16px;
            padding-right: 42px;
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

        input:focus ~ i {
            color: var(--neon-secondary);
            text-shadow: 0 0 8px var(--neon-primary);
        }
        
        button { 
            width: 100%; 
            padding: 13px; 
            background: linear-gradient(135deg, var(--neon-secondary), var(--neon-primary)); 
            color: #05050a; 
            border: none; 
            border-radius: 10px; 
            cursor: pointer; 
            font-weight: 700; 
            font-size: 0.95rem;
            margin-top: 10px; 
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 242, 254, 0.3);
            letter-spacing: 0.5px;
        }

        button:hover { 
            background: linear-gradient(135deg, var(--neon-primary), var(--neon-secondary)); 
            transform: translateY(-2px);
            box-shadow: 0 0 20px rgba(0, 242, 254, 0.6), 0 0 40px rgba(0, 242, 254, 0.2);
        }

        .btn-register {
            display: block;
            width: 100%;
            padding: 13px;
            background: transparent;
            color: var(--neon-primary);
            text-align: center;
            text-decoration: none;
            border: 1px solid var(--neon-primary);
            border-radius: 10px;
            margin-top: 12px;
            font-weight: 600;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: all 0.3s ease;
        }

        .btn-register:hover { 
            background: rgba(0, 242, 254, 0.1); 
            box-shadow: 0 0 12px rgba(0, 242, 254, 0.3);
            transform: translateY(-1px);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-container">
            <img src="logokkkb.jpg" alt="Logo Kolej" class="logo-kolej">
        </div>

        <h2>Log Masuk</h2>
        <form action="auth.php" method="POST">
            <div class="input-group">
                <i class="fa-solid fa-user"></i>
                <input type="text" name="username" placeholder="Nama Pengguna" required>
            </div>
            
            <div class="input-group">
                <i class="fa-solid fa-lock"></i>
                <input type="password" name="password" id="login-password" placeholder="Kata Laluan" required>
                <i class="fa-solid fa-eye toggle-password" id="toggleLoginPassword"></i>
            </div>

            <div class="input-group">
                <i class="fa-solid fa-user-shield"></i>
                <select name="role" required>
                    <option value="" disabled selected hidden>Pilih Peranan...</option>
                    <option value="admin">Admin</option>
                    <option value="pengarah">Pengarah</option>
                    <option value="tpp">Timbalan Pengarah Pengurusan</option>
                    <option value="tpa">Timbalan Pengarah Akademik</option>
                </select>
            </div>
            
            <button type="submit">Masuk Ke Sistem</button>
            <a href="register.php" class="btn-register">Daftar Akaun Baru</a>
        </form>
    </div>

    <!-- Skrip JavaScript untuk Kawal Eye On/Off -->
    <script>
        const togglePassword = document.querySelector('#toggleLoginPassword');
        const password = document.querySelector('#login-password');

        togglePassword.addEventListener('click', function () {
            // Tukar jenis input daripada password kepada text
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            
            // Tukar ikon mata (fa-eye <-> fa-eye-slash)
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
