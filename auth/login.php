<?php
session_start();
include '../config.php';
include '../includes/header.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, name, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($user_id, $name, $hashed, $role);
        $stmt->fetch();

        if ($stmt->num_rows > 0 && password_verify($password, $hashed)) {
            $_SESSION['user_id'] = $user_id;
            $_SESSION['name'] = $name;
            $_SESSION['role'] = $role;

            
            if ($role == 'admin' || $role == 'superadmin') {
                header("Location: ../admin/dashboard.php");
            } elseif ($role == 'seller') {
                header("Location: ../seller/dashboard.php");
            } else {
                header("Location: ../buyer/browse.php");
            }
            exit();
        } else {
            $error = "Incorrect email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HandmadeHub – Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
    <style>
        .auth-section { display: flex; justify-content: center; align-items: center; min-height: 80vh; padding: 40px 20px; }
        .auth-card { background: white; padding: 40px; border-radius: 20px; width: 100%; max-width: 420px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .auth-card h2 { text-align: center; font-size: 1.8rem; margin-bottom: 8px; }
        .auth-card p.subtitle { text-align: center; color: #777; margin-bottom: 28px; font-size: 0.9rem; }
        .form-group { margin-bottom: 16px; }
        .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 6px; color: #333; }
        .form-group input { width: 100%; padding: 12px 14px; border: 1px solid #ddd; border-radius: 10px; font-family: 'Poppins', sans-serif; font-size: 0.95rem; box-sizing: border-box; }
        .form-group input:focus { outline: none; border-color: #8ed1c6; }
        .btn-full { width: 100%; padding: 13px; background: #8ed1c6; color: white; border: none; border-radius: 25px; font-family: 'Poppins', sans-serif; font-size: 1rem; font-weight: 600; cursor: pointer; margin-top: 8px; }
        .btn-full:hover { background: #72bfb4; }
        .auth-footer { text-align: center; margin-top: 20px; font-size: 0.875rem; color: #777; }
        .auth-footer a { color: #8ed1c6; text-decoration: none; font-weight: 600; }
        .alert-error { background: #fdeef3; color: #c0395a; padding: 12px 16px; border-radius: 10px; margin-bottom: 16px; font-size: 0.875rem; }
    </style>
</head>
<body>


<section class="auth-section">
    <div class="auth-card">
        <h2>Welcome Back</h2>
        <p class="subtitle">Sign in to your HandmadeHub