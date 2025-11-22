<?php
require '_base.php';

$_title = 'Login ♡ Moe Moe Pet Mart';
include '_head.php';

// ------------------- Process Login -------------------
if (is_post()) {
    $username = trim(post('username'));
    $password = post('password');
    
    // === ADMIN LOGIN (hardcoded - still works) ===
    if ($username === 'admin123' && $password === 'yap123') {
        $_SESSION['user'] = 'admin';
        $_SESSION['role'] = 'admin';
        temp('info', 'Welcome back, Master admin123! ♡');
        redirect('/admin.php');
    }

// === MEMBER LOGIN FROM DATABASE ===
else {
    $stm = $_db->prepare("SELECT * FROM users WHERE username = ?");
    $stm->execute([$username]);
    $user = $stm->fetch();

    if ($user && password_verify($password, $user->password)) {
        
        $_SESSION['user'] = $user->username;
        $_SESSION['role']  = $user->role;   

        temp('info', 'Welcome to the family, ' . encode($user->username) . '! Enjoy the cuteness~');
        redirect('/');  
    } 
    else {
        $_err['login'] = 'Wrong username or password~';
    }
}
    $username = encode($username); // keep input on error
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
                <input type="text" name="username" value="<?= $username ?? '' ?>" required placeholder="Enter your username">
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter your password">
            </div>

            <button type="submit" class="btn-login-full">
                Login ♡
            </button>
        </form>

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