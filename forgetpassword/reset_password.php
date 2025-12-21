<?php
require '../_base.php';

$_title = 'Reset Password ♡ Moe Moe Pet Mart';
include '../_head.php';

// Get token from URL
$token = get('token');

// Verify token
$stm = $_db->prepare("
    SELECT id, username, email, reset_token_expiry 
    FROM users 
    WHERE reset_token = ?
");
$stm->execute([$token]);
$user = $stm->fetch();

// Check if token is valid and not expired
if (!$user) {
    temp('error', 'Invalid or expired reset link. Please request a new one.');
    redirect('/forget_password.php');
}

if (strtotime($user->reset_token_expiry) < time()) {
    temp('error', 'This reset link has expired. Please request a new one.');
    redirect('/forget_password.php');
}

// ------------------- Process Password Reset -------------------
if (is_post()) {
    $new_password = post('new_password');
    $confirm_password = post('confirm_password');

    $hasError = false;

    // Validate new password (match register.php rules)
    if ($new_password === '') {
        $_err['new_password'] = 'New password is required~';
        $hasError = true;
    } elseif (strlen($new_password) < 8) {
        $_err['new_password'] = 'Password must be at least 8 characters ♡';
        $hasError = true;
    } elseif (strlen($new_password) > 128) {
        $_err['new_password'] = 'Password is too long! Maximum 128 characters ♡';
        $hasError = true;
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $_err['new_password'] = 'Password must include at least one uppercase letter ♡';
        $hasError = true;
    } elseif (!preg_match('/[a-z]/', $new_password)) {
        $_err['new_password'] = 'Password must include at least one lowercase letter ♡';
        $hasError = true;
    } elseif (!preg_match('/\d/', $new_password)) {
        $_err['new_password'] = 'Password must include at least one number ♡';
        $hasError = true;
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
        $_err['new_password'] = 'Password must include at least one special character ♡';
        $hasError = true;
    }

    // Validate confirm password
    if ($confirm_password === '') {
        $_err['confirm_password'] = 'Please confirm your password~';
        $hasError = true;
    } elseif ($new_password !== $confirm_password) {
        $_err['confirm_password'] = 'Passwords do not match!';
        $hasError = true;
    }

    if (!$hasError) {
        // Hash the new password
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);

        // Update password and clear reset token
        $stm = $_db->prepare("
            UPDATE users 
            SET password = ?, reset_token = NULL, reset_token_expiry = NULL 
            WHERE id = ?
        ");
        $stm->execute([$hashed, $user->id]);

        temp('info', 'Password reset successful! You can now login with your new password ♡');
        redirect('/login.php');
    }
}
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-paw">
            <img src="/images/pet-shop.png" alt="Moe Moe Pet Mart">
        </div>
        
        <h2>Reset Your Password ♡</h2>
        <p>Hello, <?= encode($user->username) ?>! Enter your new password below~</p>

        <form method="post" class="login-form">
            <div class="input-group">
                <label>New Password</label>
                <input type="password" name="new_password" required 
                       placeholder="Enter your new password (min. 8 characters)">
                <?php err('new_password'); ?>
            </div>

            <div class="input-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required 
                       placeholder="Type password again">
                <?php err('confirm_password'); ?>
            </div>

            <div class="security-tips">
                <h4>🔐 Password Tips:</h4>
                <ul>
                    <li>Use at least 8 characters</li>
                    <li>Mix uppercase and lowercase letters</li>
                    <li>Include numbers and special characters</li>
                    <li>Avoid common words</li>
                </ul>
            </div>

            <button type="submit" class="btn-login-full">
                Reset Password ♡
            </button>
        </form>

        <div class="login-footer">
            <a href="/login.php">← Back to Login</a>
        </div>
    </div>
</div>

<?php include '../_foot.php'; ?>