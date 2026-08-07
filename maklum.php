<?php 
include('db.php'); 
$id = $_GET['id'] ?? ''; 
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
            overflow: hidden;
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
            padding: 40px; 
            border-radius: 2px 20px 2px 20px; 
            width: 100%; 
            max-width: 400px; 
            box-shadow: 15px 15px 30px rgba(0,0,0,0.15); 
            position: relative;
            transform: rotate(-2deg); 
            transition: transform 0.3s;
        }

        .box:hover { transform: rotate(0deg) scale(1.02); }

        h3 { margin: 0 0 20px 0; color: #5d4037; text-align: center; font-weight: 700; }
        
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #795548; font-size: 0.9rem; font-weight: 600; }
        
        input, select { 
            width: 100%; 
            padding: 12px; 
            border: 2px dashed #fbc02d; 
            border-radius: 5px; 
            background: rgba(255,255,255,0.4);
            box-sizing: border-box; 
            font-family: inherit;
        }

        select option {
            background: #fff9c4;
            color: #5d4037;
        }

        button { 
            width: 100%; 
            padding: 12px; 
            background: #f57c00; 
            color: white; 
            border: none; 
            border-radius: 50px; 
            font-weight: 600; 
            cursor: pointer; 
            margin-top: 15px; 
            transition: 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        button:hover { background: #e65100; transform: scale(1.03); }

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
        
        <form action="proses_makluman.php" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="surat_id" value="<?= htmlspecialchars($id) ?>">
            
            <div class="form-group">
                <label>Nama Staf:</label>
                <select name="nama_staf" required>
                    <option value="">-- Pilih Nama Staf --</option>
                    <?php
                    $result = mysqli_query($conn, "SELECT * FROM staf ORDER BY nama_staf ASC");
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . htmlspecialchars($row['nama_staf']) . "'>" . htmlspecialchars($row['nama_staf']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>E-mel Staf:</label>
                <select name="email" required>
                    <option value="">-- Pilih E-mel Staf --</option>
                    <?php
                    // Reset pointer atau query semula untuk senarai e-mel
                    mysqli_data_seek($result, 0);
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<option value='" . htmlspecialchars($row['email']) . "'>" . htmlspecialchars($row['email']) . "</option>";
                    }
                    ?>
                </select>
            </div>
            
            <div class="form-group">
                <label>Dokumen Asal:</label>
                <input type="file" name="dokumen_asal" accept=".pdf,.jpg,.png" required>
            </div>

            <div class="form-group">
                <label>Borang Minit Ceraian:</label>
                <input type="file" name="dokumen_minit" accept=".pdf,.jpg,.png" required>
            </div>
            
            <button type="submit">Hantar Sekarang!</button>
        </form>
    </div>

</body>
</html>
