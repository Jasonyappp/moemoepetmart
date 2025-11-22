<?php
require '_base.php';
require_login();

$user = current_user();

if (!$user) {
    temp('error', 'User not found!');
    redirect('/login.php');
}

// Clear previous errors
$_err = [];

// Handle password change
if (is_post()) {
    $current_password = post('current_password');
    $new_password = post('new_password');
    $confirm_password = post('confirm_password');
   
    // Validation
    if (empty($current_password)) {
        $_err['current'] = 'Current password is required~';
    } elseif (!password_verify($current_password, $user->password)) {
        $_err['current'] = 'Current password is incorrect! Please try again ♡';
    }
    
    if (empty($new_password)) {
        $_err['new'] = 'New password is required~';
    } elseif (strlen($new_password) < 4) {
        $_err['new'] = 'New password must be at least 4 characters ♡';
    } elseif (strlen($new_password) > 50) {
        $_err['new'] = 'New password is too long! Maximum 50 characters ♡';
    }
    
    if (empty($confirm_password)) {
        $_err['confirm'] = 'Please confirm your new password~';
    } elseif ($new_password !== $confirm_password) {
        $_err['confirm'] = 'New passwords do not match! Please type carefully ♡';
    }
   
    // Update password if no errors
    if (empty($_err)) {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stm = $_db->prepare("UPDATE users SET password = ? WHERE id = ?");
        
        if ($stm->execute([$hashed_password, $user->id])) {
            temp('info', 'Password changed successfully! Your account is now more secure ♡');
            redirect('/profile.php');
        } else {
            temp('error', 'Something went wrong. Please try again~');
        }
    }
}

$_title = 'Change Password ♡ Moe Moe Pet Mart';
include '_head.php';
?>

<div class="profile-container">
    <div class="profile-card">
        <h2>Change Password ♡</h2>
        <p class="change-password-intro">Update your password to keep your account secure and cute~</p>

        <?php if (isset($_err) && !empty($_err)): ?>
            <div class="error-box">
                <strong>Oops! Please check the following:</strong>
                <?php foreach ($_err as $error): ?>
                    <div>• <?= $error ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form method="post" class="profile-edit-form">
            <div class="input-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required 
                       placeholder="Enter your current password"
                       class="<?= isset($_err['current']) ? 'error-field' : '' ?>">
                <?php if (isset($_err['current'])): ?>
                    <div class="field-error"><?= $_err['current'] ?></div>
                <?php endif; ?>
            </div>
           
            <div class="input-group">
                <label>New Password</label>
                <input type="password" name="new_password" required 
                       placeholder="Enter your new password (min. 4 characters)"
                       class="<?= isset($_err['new']) ? 'error-field' : '' ?>">
                <?php if (isset($_err['new'])): ?>
                    <div class="field-error"><?= $_err['new'] ?></div>
                <?php else: ?>
                    <small>Must be at least 4 characters long</small>
                <?php endif; ?>
            </div>
           
            <div class="input-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required 
                       placeholder="Confirm your new password"
                       class="<?= isset($_err['confirm']) ? 'error-field' : '' ?>">
                <?php if (isset($_err['confirm'])): ?>
                    <div class="field-error"><?= $_err['confirm'] ?></div>
                <?php endif; ?>
            </div>
           
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                Change Password ♡
            </button>
        </form>

        <!-- Security Tips -->
        <div class="security-tips">
            <h4>Password Tips:</h4>
            <ul>
                <li>Use at least 4 characters</li>
                <li>Combine letters and numbers for better security</li>
                <li>Avoid using personal information</li>
                <li>Don't reuse passwords from other sites</li>
            </ul>
        </div>

        <!-- Profile Actions -->
        <div class="profile-actions">
            <a href="/profile.php" class="btn btn-secondary">← Back to Profile</a>
            <a href="/" class="btn btn-secondary">← Back to Home</a>
        </div>
    </div>
</div>

<?php include '_foot.php'; ?>