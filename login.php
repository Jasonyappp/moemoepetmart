<?php
require '_base.php';

$_title = 'Login ♡ Moe Moe Pet Mart';
include '_head.php';

// ------------------- Process NORMAL MEMBER Login ONLY -------------------
if (is_post()) {
    $username = trim(post('username'));
    $password = post('password');

    // Find user in database
    $stm = $_db->prepare("SELECT * FROM users WHERE username = ?");
    $stm->execute([$username]);
    $user = $stm->fetch();

    // BLOCK ADMIN FROM LOGGING IN HERE — NO EXCEPTIONS!
    if ($user && $user->role === 'admin') {
        $_err['login'] = 'Admins are not allowed to login here!<br>Use the <strong>Secret Admin Door</strong> instead ♡';
        $username = '';
    }
    // Normal member login (only if NOT admin)
    else if ($user && password_verify($password, $user->password)) {
        $_SESSION['user']        = $user->username;
        $_SESSION['role']        = $user->role;
        $_SESSION['user_id']     = $user->id;
        $_SESSION['show_welcome']= true;

        load_cart_from_db();  // NEW: Load saved cart

        temp('info', "Welcome back, cutie " . encode($user->username) . "! ♡");
        redirect('/');
    } 
    else {
        $_err['login'] = 'Wrong username or password~ Please try again!';
    }

    $username = encode($username ?? '');
}
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-paw">
            <img src="/images/pet-shop.png" alt="Moe Moe Pet Mart">
        </div>
        
        <h2>Welcome Back ♡</h2>
        <p>Login as a cute member~</p>

        <?php if (isset($_err['login'])): ?>
            <div class="error-box"><?= $_err['login'] ?></div>
        <?php endif; ?>

        <form method="post" class="login-form">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= $username ?? '' ?>" 
                       required placeholder="Your cute username">
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required 
                       placeholder="Your secret password">
            </div>

            <div style="text-align: right; margin: -10px 0 15px 0;">
                <a href="/forgetpassword/forget_password.php" style="color: #ff69b4; font-size: 0.9rem; text-decoration: none; font-weight: 500;">
                    Forgot Password? 🔑
                </a>
            </div>

            <button type="submit" class="btn-login-full">
                Login as Member ♡
            </button>
        </form>

        <div class="register-link">
            <p>New here?</p>
            <a href="/register.php" class="btn-register">
                Register Now! ♡
            </a>
        </div>

        <div class="login-footer">
            <a href="/">← Back to Home</a>
            <div style="margin-top:20px; font-size:0.8rem; color:#ff69b4;">
                Admins: use the secret pink door
            </div>
        </div>
    </div>
</div>

<?php include '_foot.php'; ?>