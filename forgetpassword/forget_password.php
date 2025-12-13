<?php
require '../_base.php';

$_title = 'Forgot Password ♡ Moe Moe Pet Mart';
include '../_head.php';

// ------------------- Process Forgot Password Request -------------------
if (is_post()) {
    $email = trim(post('email'));

    // Validate email
    if ($email === '') {
        $_err['email'] = 'Email is required~';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_err['email'] = 'Please enter a valid email address ♡';
    } else {
        // Check if email exists
        $stm = $_db->prepare("SELECT id, username FROM users WHERE email = ?");
        $stm->execute([$email]);
        $user = $stm->fetch();

        if ($user) {
            // Generate unique reset token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store token in database
            $stm = $_db->prepare("
                UPDATE users 
                SET reset_token = ?, reset_token_expiry = ? 
                WHERE id = ?
            ");
            $stm->execute([$token, $expiry, $user->id]);

            // Send reset email
            // Correct reset link - points to the subfolder
            $reset_link = "http://{$_SERVER['HTTP_HOST']}/forgetpassword/reset_password.php?token=$token";
            
            require './_mailer.php';
            $mail_sent = send_reset_email($email, $user->username, $reset_link);

            if ($mail_sent) {
                temp('info', 'Password reset link sent to your email! Please check your inbox ♡');
                redirect('/login.php');
            } else {
                $_err['email'] = 'Failed to send email. Please try again later.';
            }
        } else {
            // For security, don't reveal if email exists or not
            temp('info', 'If that email exists, a reset link has been sent ♡');
            redirect('/login.php');
        }
    }

    $email = encode($email ?? '');
}
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-paw">
            <img src="/images/pet-shop.png" alt="Moe Moe Pet Mart">
        </div>
        
        <h2>Forgot Password? ♡</h2>
        <p>Don't worry! Enter your email and we'll send you a reset link~</p>

        <form method="post" class="login-form">
            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= $email ?? '' ?>" 
                       required placeholder="Enter your registered email">
                <?php err('email'); ?>
            </div>

            <button type="submit" class="btn-login-full">
                Send Reset Link ♡
            </button>
        </form>

        <div class="login-footer">
            <a href="/login.php">← Back to Login</a>
        </div>
    </div>
</div>

<?php include '../_foot.php'; ?>