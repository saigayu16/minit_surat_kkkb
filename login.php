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
    <title>Log Masuk - Sistem Minit Digital</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <style>
        body { 
            font-family: 'Inter', sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            position: relative;
            overflow: hidden;
        }

        /* Lapisan khas untuk imej latar belakang dengan kesan blur */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(rgba(15, 23, 42, 0.7), rgba(15, 23, 42, 0.7)), url('backgroundkkkb.jpg') no-repeat center center fixed;
            background-size: cover;
            filter: blur(8px); /* Ubah nilai 8px ini jika mahu lebih atau kurang kabur */
            transform: scale(1.1); /* Mengelakkan kesan putih di tepi akibat blur */
            z-index: -1; /* Memastikan latar belakang berada di lapisan paling bawah */
        }

        .login-card { 
            background: rgba(255, 255, 255, 0.95); 
            padding: 40px; 
            border-radius: 16px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.3); 
            width: 380px; 
            text-align: center; 
        }
        
        /* Gaya Logo */
        .logo-container { margin-bottom: 20px; }
        .logo-kolej { height: 100px; width: auto; object-fit: contain; }

        .input-group { position: relative; margin-bottom: 20px; text-align: left; }
        .input-group i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #64748b; }
        input, select { width: 100%; padding: 12px 12px 12px 40px; border: 1px solid #cbd5e1; border-radius: 8px; box-sizing: border-box; appearance: none; }
        
        button { width: 100%; padding: 12px; background: #2563eb; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; margin-top: 10px; }
        button:hover { background: #1d4ed8; }
        
        /* Gaya Butang Register */
        .btn-register {
            display: block;
            width: 100%;
            padding: 12px;
            background: #10b981;
            color: white;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            margin-top: 10px;
            font-weight: bold;
            box-sizing: border-box;
        }
        .btn-register:hover { background: #059669; }
        
        h2 { color: #0f172a; margin-bottom: 25px; }
</style>
</head>
<body>
    <div class="login-card">
        <div class="logo-container">
            <img src="logokkkb.png" alt="Logo Kolej" class="logo-kolej">
        </div>

        <h2>Log Masuk</h2>
        <form action="auth.php" method="POST">
            <div class="input-group">
                <input type="text" name="username" placeholder="Nama Pengguna" required>
                <i class="fa-solid fa-user"></i>
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Kata Laluan" required>
                <i class="fa-solid fa-lock"></i>
            </div>
            <div class="input-group">
                <select name="role" required>
                    <option value="" disabled selected>Pilih Peranan</option>
                    <option value="admin">Admin</option>
                    <option value="pengarah">Pengarah</option>
                    <option value="tpp">Timbalan Pengarah Pengurusan</option>
                    <option value="tpa">Timbalan Pengarah Akademik</option>
                </select>
                <i class="fa-solid fa-user-shield"></i>
            </div>
            
            <button type="submit">Masuk Ke Sistem</button>
            
            <a href="register.php" class="btn-register">Daftar Akaun Baru</a>
        </form>
    </div>
</body>
</html>
