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
</head>
<body>
    <!-- Flash message -->
    <div id="info"><?= temp('info') ?></div>

    <header>
        <h1><a href="/">MoeMoepet Mart</a></h1>
    </header>
<nav>
        <div class="nav-left">
           <a href="/">Home</a>
            <a href="/products.php">Products</a>
        </div>
       <div class="nav-right">
        <?php if (isset($_SESSION['user']) && $_SESSION['user'] === 'admin'): ?>
            <!-- ADMIN LOGGED IN → Show "Admin123" button that goes to dashboard -->
            <a href="/admin.php" class="btn-login active-admin">
                Admin123
            </a>
        <?php else: ?>
            <!-- Not admin → normal Login / Member button -->
            <a href="/login.php" class="btn-login">
                <?= isset($_SESSION['user']) ? 'Member' : 'Login' ?>
            </a>
        <?php endif; ?>
    </div>
    </nav>
    <main>
        <h1><?= $_title ?? 'Untitled' ?></h1>