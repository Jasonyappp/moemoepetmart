<?php
    require_once '_base.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?? 'Untitled' ?></title>
    <link rel="shortcut icon" href="/images/favicon.png">
    <link rel="stylesheet" href="/css/app.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="/js/app.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css">
    <script src="https://unpkg.com/dropzone@5/dist/min/dropzone.min.js"></script>
</head>

<body data-flash="<?= encode(temp('info')) ?>">
    <div id="moe-flash"></div>

    <header>
        <h1><a href="/">MoeMoepet Mart</a></h1>
    </header>

    <nav>
        <div class="nav-left">
            <a href="/">Home</a>
            <a href="/products.php">Products</a>
        </div>

        
       <div class="nav-right">
            <?php 
                $isLoggedIn = isset($_SESSION['user']);
                $userRole   = $_SESSION['role'] ?? null;   
                $username   = $_SESSION['user'] ?? null;
            ?>

            <?php if ($isLoggedIn && $userRole === 'admin'): ?>
                <!-- ADMIN LOGGED IN -->
                <a href="/admin.php" class="btn-login active-admin">
                    Admin123
                </a>

            <?php elseif ($isLoggedIn && $userRole === 'member'): ?>
                <!-- MEMBER LOGGED IN -->
                <a href="/profile.php" class="btn-login" style="background: #ff99cc;">
                    My Profile
                </a>
                <a href="#" class="btn-login member-active">
                    Member: <?= encode($username) ?> ♡
                </a>
                <a href="/logout.php" class="btn-logout">Logout</a>

            <?php else: ?>
                <!-- NOT LOGGED IN -->
                <a href="/login.php" class="btn-login">
                    Login
                </a>
            <?php endif; ?>
        </div>
    </nav>

    <main>
        <h1><?= $_title ?? 'Untitled' ?></h1>