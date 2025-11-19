<?php
require '_base.php';

$_title = 'Login ♡ Moe Moe Pet Mart';
include '_head.php';

// ------------------- Process Login -------------------
if (is_post()) {
    $username = post('username');
    $password = post('password');

    // Simple demo login (you can change or secure this later)
    if ($username === 'admin' && $password === '1234') {
        $_SESSION['user'] = 'admin';
        temp('info', 'Welcome back, master! ♡');
        redirect('/');
    } else {
        $_err['login'] = 'Wrong username or password~';
    }
    $username = $username;
}
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-paw">
            <img src="/images/pet-shop.png" alt="Moe Moe Pet Mart">
        </div>
        
        <h2>Welcome Back ♡</h2>
        <p>Login to manage Moe Moe Pet Mart</p>

        <?php if (isset($_err['login'])): ?>
            <div class="error-box"><?= $_err['login'] ?></div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= encode($username ?? '') ?>" required placeholder="Enter your username">
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter your password">
            </div>

            <button type="submit" class="btn-login-full">
                Login ♡
            </button>
        </form>

        <!-- New Register Link -->
        <div class="register-link">
            <p>Don't have an account?</p>
            <a href="/register.php" class="btn-register">
                Register As Member Right Now! 
            </a>
        </div>

        <div class="login-footer">
            <a href="/">← Back to Home</a>
        </div>
    </div>
</div>

<?php include '_foot.php'; ?>