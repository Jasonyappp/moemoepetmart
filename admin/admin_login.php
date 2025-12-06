<?php
require '../_base.php';

if (is_post()) {
    $username = trim(post('username'));
    $password = post('password');

    $stm = $_db->prepare("SELECT * FROM users WHERE username = ? AND role = 'admin'");
    $stm->execute([$username]);
    $admin = $stm->fetch();

    if ($admin && password_verify($password, $admin->password)) {
        $_SESSION['user']        = $admin->username;
        $_SESSION['role']        = 'admin';
        $_SESSION['user_id']     = $admin->id;
        $_SESSION['show_welcome']= true;

        temp('info', "Welcome back, Master {$admin->username}! ♡");
        redirect('/admin.php');
    } else {
        $_err['login'] = 'Invalid admin credentials~';
    }
}

$_title = 'Admin Secret Door ♡ Moe Moe Pet Mart';
include '../_head.php';
?>

<div class="login-container">
    <div class="login-card admin-secret-card">

        <div class="login-paw">
            <img src="/images/pet-shop.png" alt="Moe Moe Pet Mart">
        </div>

        <h2 class="text-gradient">Secret Admin Door</h2>
        <p class="text-muted text-center mb-30">Only the real master can enter~</p>

        <?php if (isset($_err['login'])): ?>
            <div class="error-box"><?= $_err['login'] ?></div>
        <?php endif; ?>

        <form method="post" class="login-form">

            <div class="input-group">
                <label class="label-pink">Admin Username</label>
                <input type="text" name="username" class="input-pink" required placeholder="Secret key...">
            </div>

            <div class="input-group">
                <label class="label-pink">Admin Password</label>
                <input type="password" name="password" class="input-pink" required placeholder="Shhh...">
            </div>

            <button type="submit" class="btn-login-full btn-admin-enter">
                Enter Admin Kingdom
            </button>
        </form>

        <div class="login-footer">
            <a href="/">Back to Cute Shop</a>
        </div>
    </div>
</div>

    <?php include '../_foot.php';