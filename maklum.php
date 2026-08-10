<?php 
include('db.php'); 
$id = $_GET['id'] ?? ''; 

// Proses tambah staf baharu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_staf_baru'])) {
    $nama_baru = trim($_POST['nama_baru'] ?? '');
    $email_baru = trim($_POST['email_baru'] ?? '');

    if (!empty($nama_baru) && !empty($email_baru)) {
        try {
            $stmt_check = $pdo->prepare("SELECT id FROM staff WHERE email = ?");
            $stmt_check->execute([$email_baru]);
            
            if ($stmt_check->rowCount() > 0) {
                echo "<script>alert('Ralat: E-mel staf ini sudah wujud dalam pangkalan data!'); window.location.href='?id=" . htmlspecialchars($id) . "';</script>";
                exit;
            } else {
                $stmt_insert = $pdo->prepare("INSERT INTO staff (nama, email) VALUES (?, ?)");
                $stmt_insert->execute([$nama_baru, $email_baru]);
                
                echo "<script>alert('Staf baru berjaya disimpan dalam database!'); window.location.href='?id=" . htmlspecialchars($id) . "';</script>";
                exit;
            }
        } catch (PDOException $e) {
            echo "<script>alert('Ralat Database: " . addslashes($e->getMessage()) . "');</script>";
        }
    }
}

// Proses padam staf
if (isset($_GET['padam_id'])) {
    $padam_id = $_GET['padam_id'];
    try {
        $stmt_delete = $pdo->prepare("DELETE FROM staff WHERE id = ?");
        $stmt_delete->execute([$padam_id]);
        
        echo "<script>alert('Staf berjaya dipadam dari database!'); window.location.href='?id=" . htmlspecialchars($id) . "';</script>";
        exit;
    } catch (PDOException $e) {
        echo "<script>alert('Ralat Database: " . addslashes($e->getMessage()) . "'); window.location.href='?id=" . htmlspecialchars($id) . "';</script>";
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maklum Kepada Staf</title>
    <link href="https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { 
            font-family: 'Quicksand', sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            margin: 0; 
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
            background-image: linear-gradient(rgba(0,0,0,0.3), rgba(0,0,0,0.3)), url('daftarsurat.jpg'); 
            background-size: cover;         
            background-position: center;     
            background-attachment: fixed;    
            background-repeat: no-repeat;
            filter: blur(8px); 
            transform: scale(1.1); 
            z-index: -1; 
        }

        .box { 
            background: #fff9c4; 
            padding: 30px 40px; 
            border-radius: 2px 20px 2px 20px; 
            width: 100%; 
            max-width: 450px; 
            box-shadow: 15px 15px 30px rgba(0,0,0,0.15); 
            position: relative;
            transform: rotate(-1deg); 
            transition: transform 0.3s;
            margin: 20px 0;
        }

        .box:hover { transform: rotate(0deg) scale(1.01); }

        h3 { margin: 0 0 15px 0; color: #5d4037; text-align: center; font-weight: 700; }
        
        .form-group { margin-bottom: 12px; }
        label { display: block; margin-bottom: 4px; color: #795548; font-size: 0.85rem; font-weight: 600; }
        
        input, select { 
            width: 100%; 
            padding: 10px; 
            border: 2px dashed #fbc02d; 
            border-radius: 5px; 
            background: rgba(255,255,255,0.4);
            box-sizing: border-box; 
            font-family: inherit;
            font-size: 0.9rem;
        }

        .staff-checkbox-container {
            max-height: 140px;
            overflow-y: auto;
            border: 2px dashed #fbc02d;
            border-radius: 5px;
            background: rgba(255,255,255,0.4);
            padding: 8px;
            box-sizing: border-box;
        }

        .staff-checkbox-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 6px;
            font-size: 0.85rem;
            color: #5d4037;
        }

        .staff-left {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            flex-grow: 1;
        }

        .staff-checkbox-item input[type="checkbox"] {
            width: auto;
            cursor: pointer;
        }

        .btn-delete-staff {
            background: #e53935;
            color: white;
            border: none;
            border-radius: 3px;
            padding: 3px 6px;
            font-size: 0.75rem;
            cursor: pointer;
            text-decoration: none;
            transition: 0.2s;
        }
        .btn-delete-staff:hover { background: #c62828; }

        button { 
            width: 100%; 
            padding: 11px; 
            background: #f57c00; 
            color: white; 
            border: none; 
            border-radius: 50px; 
            font-weight: 600; 
            cursor: pointer; 
            margin-top: 10px; 
            transition: 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        button:hover { background: #e65100; transform: scale(1.02); }

        .btn-secondary {
            background: #795548;
            font-size: 0.85rem;
            padding: 8px;
            margin-top: 5px;
        }
        .btn-secondary:hover { background: #5d4037; }

        .toggle-section {
            background: rgba(255, 243, 224, 0.6);
            border: 1px dashed #ffa726;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }

        .pin {
            width: 25px;
            height: 25px;
            background: radial-gradient(circle at 30% 30%, #ef5350, #b71c1c);
            border-radius: 50%;
            position: absolute;
            top: -10px;
            left: 50%;
            margin-left: -12.5px;
            box-shadow: 2px 2px 5px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>

    <div class="box">
        <div class="pin"></div>
        <h3><i class="fa-solid fa-note-sticky"></i> Nota Makluman</h3>
        
        <!-- Bahagian Optional: Daftar Staf Baru -->
        <div class="toggle-section">
            <details>
                <summary style="cursor: pointer; color: #d84315; font-weight: 600; font-size: 0.9rem;">
                    <i class="fa-solid fa-user-plus"></i> Staf tiada dalam senarai? Klik sini untuk daftar baru
                </summary>
                <form method="POST" style="margin-top: 10px;">
                    <input type="hidden" name="tambah_staf_baru" value="1">
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label>Nama Staf Baru:</label>
                        <input type="text" name="nama_baru" placeholder="Masukkan nama penuh" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 8px;">
                        <label>E-mel Staf Baru:</label>
                        <input type="email" name="email_baru" placeholder="Masukkan e-mel" required>
                    </div>
                    <button type="submit" class="btn-secondary">Simpan Staf Baru</button>
                </form>
            </details>
        </div>

        <!-- Borang Utama Makluman -->
        <form action="proses_makluman.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="surat_id" value="<?= htmlspecialchars($id) ?>">
            
            <div class="form-group">
                <label>Pilih Nama Staf (Boleh pilih lebih daripada satu):</label>
                <div class="staff-checkbox-container">
                    <?php
                    try {
                        $stmt = $pdo->query("SELECT id, nama, email FROM staff ORDER BY nama ASC");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $staff_id = $row['id'];
                            $nama_attr = htmlspecialchars($row['nama']);
                            $email_attr = htmlspecialchars($row['email']);
                            
                            echo '<div class="staff-checkbox-item">
                                    <label class="staff-left">
                                        <input type="checkbox" name="nama_staf[]" value="' . $nama_attr . '" data-email="' . $email_attr . '" onchange="updateEmails()"> ' . $nama_attr . '
                                    </label>
                                    <a href="?id=' . htmlspecialchars($id) . '&padam_id=' . $staff_id . '" class="btn-delete-staff" onclick="return confirm(\'Adakah anda pasti mahu memadam staf ' . $nama_attr . '?\')"><i class="fa-solid fa-trash"></i></a>
                                  </div>';
                        }
                    } catch (PDOException $e) {
                        echo '<span style="color:red; font-size:0.8rem;">Ralat: ' . $e->getMessage() . '</span>';
                    }
                    ?>
                </div>
            </div>
            
            <div class="form-group">
                <label>E-mel Staf (Auto-popup):</label>
                <input type="text" name="email" id="email" required readonly style="background: rgba(255,255,255,0.2); cursor: not-allowed;" placeholder="Akan terpapar secara automatik">
            </div>
            
            <!-- Dokumen Asal dikemaskini kepada multiple -->
            <div class="form-group">
                <label>Dokumen Asal (Boleh pilih lebih daripada satu):</label>
                <input type="file" name="dokumen_asal[]" accept=".pdf,.jpg,.png" multiple required>
            </div>

            <div class="form-group">
                <label>Borang Minit Ceraian:</label>
                <input type="file" name="dokumen_minit" accept=".pdf,.jpg,.png" required>
            </div>
            
            <button type="submit">Hantar Sekarang!</button>
        </form>
    </div>

    <script>
        function updateEmails() {
            const checkboxes = document.querySelectorAll('input[name="nama_staf[]"]:checked');
            const emails = [];
            
            checkboxes.forEach((checkbox) => {
                const email = checkbox.getAttribute('data-email');
                if (email) {
                    emails.push(email);
                }
            });
            
            document.getElementById('email').value = emails.join(', ');
        }
    </script>

</body>
</html>
