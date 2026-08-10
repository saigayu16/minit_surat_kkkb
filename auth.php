<?php
// 1. Start output buffering to prevent header errors
ob_start(); 

session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? ''; 
    $role     = $_POST['role'] ?? '';

    if (!empty($username) && !empty($password) && !empty($role)) {
        try {
            // Gunakan \"role\" kerana ia kata kunci rizab dalam PostgreSQL
            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND \"role\" = ?");
            $stmt->execute([$username, $role]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            // Semak kata laluan dalam bentuk teks biasa (plain text)
            if ($user && $password === $user['password']) {
                session_regenerate_id(true);

                // Set session variables
                $_SESSION['user_logged_in'] = true;
                $_SESSION['user_name']      = $user['username'];
                $_SESSION['user_role']      = $user['role'];
                $_SESSION['user_email']     = $user['email'] ?? '';

                // Redirect based on role
                switch ($role) {
                    case 'admin':
                        header("Location: homeadmin.php");
                        break;
                    case 'pengarah':
                        header("Location: homedirector.php");
                        break;
                    case 'tpp':
                        header("Location: hometpp.php");
                        break;
                    case 'tpa':
                        header("Location: hometpa.php");
                        break;
                    default:
                        header("Location: login.php");
                        break;
                }
                exit();
            } else {
                // Gagal log masuk
                header("Location: login.php?error=1");
                exit();
            }
        } catch (PDOException $e) {
            die("Ralat Log Masuk: " . $e->getMessage());
        }
    } else {
        header("Location: login.php?error=empty");
        exit();
    }
} else {
    // Access denied if not POST
    header("Location: login.php");
    exit();
}

// End output buffering
ob_end_flush(); 
?>
