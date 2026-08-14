<?php
// 1. Start output buffering to prevent header errors
ob_start(); 

session_start();
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize input
    $username = trim($_POST['username']);
    $password = $_POST['password']; 
    $role     = $_POST['role'];

    // Use PDO prepared statements to prevent SQL injection
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->execute([$username, $role]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify user and password
    if ($user && $password === $user['password']) {
        session_regenerate_id(true);

        // Set session variables (SERAGAMKAN DENGAN DASHBOARD)
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_name']      = $user['username'];
        $_SESSION['user_role']      = $user['role']; // INI KUNCI UTAMA YANG DISERAGAMKAN
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
        exit(); // Always exit after header redirect
    } else {
        // Redirect with error instead of using echo/alert
        header("Location: login.php?error=1");
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
