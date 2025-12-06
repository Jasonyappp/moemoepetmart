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
        <?php if (is_login()): 
            $user = current_user();
            $displayName = encode(username());
            $roleText    = user_role() === 'admin' ? 'Admin ♛' : 'Member ♡';
            $avatar = !empty($user->profile_pic) ? $user->profile_pic : '/images/default-avatar.png';
        ?>

            <div class="user-menu">
                <div class="user-info" tabindex="0">
                    <img src="<?= $avatar ?>" alt="<?= $displayName ?>" class="user-avatar">
                    <div class="user-details">
                        <div class="user-name"><?= $displayName ?></div>
                        <div class="user-role"><?= $roleText ?></div>
                    </div>
                    <span class="dropdown-arrow">▼</span>
                </div>

                <div class="user-dropdown">
                    <?php if (user_role() === 'member'): ?>
                        <a href="/profile.php">Profile</a>
                    <?php elseif (user_role() === 'admin'): ?>
                        <a href="/admin.php">Admin Dashboard</a>
                    <?php endif; ?>
                    <hr>
                    <a href="/logout.php" class="logout-link">Logout</a>
                </div>
            </div>

        <?php else: ?>
            <a href="/login.php" class="btn-login">Login</a>
        <?php endif; ?>
    </div>
</nav>
    <main>
        <h1><?= $_title ?? 'Untitled' ?></h1>