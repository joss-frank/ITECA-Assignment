<?php if (!isset($_SESSION)) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HandmadeHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap" rel="stylesheet">
    <?php
    $is_live = $_SERVER['HTTP_HOST'] !== 'localhost';
    $base_url = $is_live ? '' : '/handmadehub';
    ?>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/css/styles.css">
   <style>
    .hamburger {
        display: none;
        flex-direction: column;
        cursor: pointer;
        gap: 5px;
        background: none;
        border: none;
        padding: 5px;
    }
    .hamburger span {
        display: block;
        width: 25px;
        height: 3px;
        background: #333;
        border-radius: 3px;
        transition: 0.3s;
    }
    .nav-menu {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .nav-menu a {
        margin: 0 10px;
        text-decoration: none;
        color: black;
        font-weight: 500;
        font-size: 0.95rem;
    }
    .nav-menu a:hover { color: #8ed1c6; }
    .navbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 16px 40px;
        background: white;
        position: sticky;
        top: 0;
        z-index: 100;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }

    @media (max-width: 768px) {
        .hamburger { display: flex; }

        .nav-menu {
            display: none;
            flex-direction: column;
            align-items: flex-start;
            position: absolute;
            top: 65px;
            left: 0;
            right: 0;
            background: white;
            padding: 20px 30px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            z-index: 999;
            gap: 0;
        }

        .nav-menu.open { display: flex; }

        .nav-menu a {
            padding: 12px 0;
            width: 100%;
            border-bottom: 1px solid #f5efe6;
            margin: 0;
            font-size: 1rem;
        }

        .nav-menu a:last-child { border-bottom: none; }

        .nav-menu a.btn {
            margin-top: 10px;
            text-align: center;
            border-radius: 25px;
            padding: 10px 20px;
            width: auto;
            border-bottom: none;
        }

        .navbar { padding: 16px 20px; }
    }
</style>
</head>
<body>
<header class="navbar">
    <div class="logo">HandmadeHub</div>

    
    <button class="hamburger" id="hamburger" onclick="toggleMenu()">
        <span></span>
        <span></span>
        <span></span>
    </button>

    
    <nav class="nav-menu" id="navMenu">
        <a href="<?php echo $root ?? '../'; ?>index.php">Home</a>
        <a href="<?php echo $root ?? '../'; ?>buyer/browse.php">Shop</a>

        <?php if (isset($_SESSION['user_id'])): ?>

            <?php if ($_SESSION['role'] == 'seller'): ?>
                <a href="<?php echo $root ?? '../'; ?>seller/dashboard.php">My Shop</a>
            <?php endif; ?>

            <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'superadmin'): ?>
                <a href="<?php echo $root ?? '../'; ?>admin/dashboard.php">Admin</a>
            <?php endif; ?>

            <a href="<?php echo $root ?? '../'; ?>buyer/cart.php">🛒 Cart
                <span class="cart-count"><?php echo isset($_SESSION['cart']) ? array_sum(array_column($_SESSION['cart'], 'quantity')) : 0; ?></span>
            </a>

            <a href="<?php echo $root ?? '../'; ?>profile.php">👤 <?php echo htmlspecialchars($_SESSION['name']); ?></a>

            <a href="<?php echo $root ?? '../'; ?>auth/logout.php">Logout</a>

        <?php else: ?>

            <a href="<?php echo $root ?? '../'; ?>auth/login.php">Login</a>
            <a href="<?php echo $root ?? '../'; ?>auth/register.php" class="btn">Register</a>

        <?php endif; ?>
    </nav>
</header>

<script>
function toggleMenu() {
    const menu = document.getElementById('navMenu');
    const hamburger = document.getElementById('hamburger');
    menu.classList.toggle('open');
    hamburger.classList.toggle('active');
}


document.addEventListener('click', function(e) {
    const menu = document.getElementById('navMenu');
    const hamburger = document.getElementById('hamburger');
    if (!menu.contains(e.target) && !hamburger.contains(e.target)) {
        menu.classList.remove('open');
        hamburger.classList.remove('active');
    }
});


document.querySelectorAll('.nav-menu a').forEach(function(link) {
    link.addEventListener('click', function() {
        document.getElementById('navMenu').classList.remove('open');
    });
});