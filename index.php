<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Minit Digital - Menu Utama</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0f172a; 
            --accent-color: #2563eb; 
            --bg-color: #f8fafc;
            --card-bg: #cad0e4;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
        }

        body { 
            font-family: 'Inter', sans-serif; 
            margin: 0; 
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
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
            background-image: linear-gradient(rgba(15, 23, 42, 0.75), rgba(15, 23, 42, 0.75)), url('backgroundkkkb.jpg'); 
            background-repeat: no-repeat;
            background-size: cover;
            background-attachment: fixed;
            background-position: center center;
            filter: blur(8px);
            transform: scale(1.1);
            z-index: -1;
        }

        .navbar { 
            background: var(--primary-color); 
            color: white; 
            padding: 1rem 2rem; 
            display: flex; 
            justify-content: space-between; 
            align-items: center; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.1); 
        }
        
        .navbar h2 {
            margin: 0;
            font-size: 1.2rem;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .main-welcome {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 20px;
            text-align: center;
        }

        .logo-container-center {
            margin-bottom: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo-kolej-besar {
            height: 100px;     
        }

        .welcome-title {
            color: orange;
            margin: 0 0 10px 0;
            font-size: 2.4rem;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0,0,0,0.4);
        }

        .welcome-subtitle {
            color: #cbd5e1;
            margin-bottom: 40px;
            font-size: 1.1rem;
            max-width: 550px;
            text-shadow: 0 1px 3px rgba(0,0,0,0.4);
            line-height: 1.6;
        }

        .options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            width: 100%;
            max-width: 750px;
        }

        .option-card {
            background: var(--card-bg);
            padding: 40px 30px;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            text-decoration: none;
            color: var(--text-main);
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            align-items: center;
            cursor: pointer;
        }

        .option-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 30px -10px rgba(0,0,0,0.5);
            border-color: var(--accent-color);
        }

        .option-icon {
            font-size: 3rem;
            margin-bottom: 20px;
            width: 90px;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50px;
            transition: all 0.3s;
        }

        .icon-surat { background: #eff6ff; color: #2563eb; }
        .icon-borang { background: #fdf2f8; color: #db2777; }

        .option-card:hover .icon-surat { background: #2563eb; color: white; }
        .option-card:hover .icon-borang { background: #db2777; color: white; }

        .option-card h3 {
            margin: 0 0 10px 0;
            font-size: 1.3rem;
            font-weight: 700;
            color: #0f172a;
        }

        .option-card p {
            margin: 0;
            font-size: 0.95rem;
            color: var(--text-muted);
            line-height: 1.5;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <h2>Sistem Minit Digital</h2>
</nav>

<div class="main-welcome">
    <div class="logo-container-center">
        <img src="logokkkb.png" alt="Logo Kolej Komuniti" class="logo-kolej-besar">
    </div>

    <h1 class="welcome-title">Selamat Datang ke Laman Minit Surat!</h1>
    <p class="welcome-subtitle">Sila klik mana-mana modul tugasan di bawah untuk ke halaman log masuk.</p>

    <div class="options-grid">
        <a href="homeadmin.php" class="option-card">
            <div class="option-icon icon-surat">
                <i class="fa-solid fa-envelope-open-text"></i>
            </div>
            <h3>Masukkan Surat</h3>
            <p>Daftar maklumat surat masuk dan rujukan rasmi ke dalam sistem digital.</p>
        </a>

        <a href="homeadmin.php" class="option-card">
            <div class="option-icon icon-borang">
                <i class="fa-solid fa-file-signature"></i>
            </div>
            <h3>Sahkan Borang</h3>
            <p>Semak senarai dokumen tertangguh dan turunkan tandatangan kelulusan digital.</p>
        </a>
    </div>
</div>

</body>
</html>
