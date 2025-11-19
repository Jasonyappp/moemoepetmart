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
            <a href="/login.php" class="btn-login">
                Login
            </a>
        </div>
    </nav>
    <main>
        <h1><?= $_title ?? 'Untitled' ?></h1>