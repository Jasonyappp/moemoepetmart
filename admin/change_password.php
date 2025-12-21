<?php
require '../_base.php';
require_login();
require_admin();

$user = current_user();
$_err = []; // Initialize error array

if (is_post()) {
    $current_password = trim(post('current_password'));
    $new_password     = trim(post('new_password'));
    $confirm_password = trim(post('confirm_password'));

    // === Validation ===
    if (empty($current_password)) {
        $_err['current'] = 'Current password is required~';
    } elseif (!password_verify($current_password, $user->password)) {
        $_err['current'] = 'Current password is incorrect! Please try again ♡';
    }

    // New password validation (same strength as registration)
    if (empty($new_password)) {
        $_err['new'] = 'New password is required~';
    } elseif (strlen($new_password) < 8) {
        $_err['new'] = 'Password must be at least 8 characters ♡';
    } elseif (strlen($new_password) > 128) {
        $_err['new'] = 'Password is too long! Maximum 128 characters ♡';
    } elseif (!preg_match('/[A-Z]/', $new_password)) {
        $_err['new'] = 'Must include at least one uppercase letter (A-Z) ♡';
    } elseif (!preg_match('/[a-z]/', $new_password)) {
        $_err['new'] = 'Must include at least one lowercase letter (a-z) ♡';
    } elseif (!preg_match('/\d/', $new_password)) {
        $_err['new'] = 'Must include at least one number (0-9) ♡';
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $new_password)) {
        $_err['new'] = 'Must include at least one special character (!@#$ etc.) ♡';
    }

    if (empty($confirm_password)) {
        $_err['confirm'] = 'Please confirm your new password~';
    } elseif ($new_password !== $confirm_password) {
        $_err['confirm'] = 'Passwords do not match! Please type carefully ♡';
    }

    // Optional: Prevent reusing old password
    if (empty($_err) && password_verify($new_password, $user->password)) {
        $_err['new'] = 'New password cannot be the same as your current one ♡';
    }

    // === If no errors → update password ===
    if (empty($_err)) {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $_db->prepare("UPDATE users SET password = ? WHERE id = ?")
            ->execute([$hashed, $user->id]);

        temp('info', 'Password changed successfully! Your admin account is now super secure ♛');
        redirect('profile.php');
    }
    // If errors → fall through to form (with $_err populated)
}

$_title = 'Change Admin Password ♛';
include '../_head.php';
?>

<div class="profile-container">
    <div class="profile-card">
        <h2>Change Admin Password ♛</h2>
        <p>Keep your royal account protected with a strong password~</p>

        <!-- Password Requirements Box -->
        <div style="background:#fff0f5; padding:16px; border-radius:12px; margin:20px 0; font-size:0.95rem; color:#ff69b4; border:2px dashed #ff69b4;">
            <strong>Password Rules ♡</strong>
            <ul style="margin:10px 0; padding-left:22px;">
                <li>At least 8 characters</li>
                <li>One uppercase & one lowercase letter</li>
                <li>One number</li>
                <li>One special character (!@#$%^&* etc.)</li>
            </ul>
        </div>

        <form method="post" class="profile-edit-form">
            <div class="input-group">
                <label>Current Password *</label>
                <input type="password" 
                       name="current_password" 
                       required 
                       value="<?= encode(post('current_password') ?? '') ?>"
                       autocomplete="current-password">
                <?php if (!empty($_err['current'])): ?>
                    <small style="color:#c62828; display:block; margin-top:6px; font-weight:bold;">
                        <?= encode($_err['current']) ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="input-group">
                <label>New Password *</label>
                <input type="password" 
                       name="new_password" 
                       required 
                       value="<?= encode(post('new_password') ?? '') ?>"
                       autocomplete="new-password">
                <?php if (!empty($_err['new'])): ?>
                    <small style="color:#c62828; display:block; margin-top:6px; font-weight:bold;">
                        <?= encode($_err['new']) ?>
                    </small>
                <?php endif; ?>
            </div>

            <div class="input-group">
                <label>Confirm New Password *</label>
                <input type="password" 
                       name="confirm_password" 
                       required 
                       autocomplete="new-password">
                <?php if (!empty($_err['confirm'])): ?>
                    <small style="color:#c62828; display:block; margin-top:6px; font-weight:bold;">
                        <?= encode($_err['confirm']) ?>
                    </small>
                <?php endif; ?>
            </div>

            <button type="submit" class="btn btn-primary" style="width:100%; padding:1.3rem; margin-top:1rem; font-size:1.1rem;">
                Update Password ♛
            </button>
        </form>

        <div class="profile-actions" style="margin-top:2rem; text-align:center;">
            <a href="profile.php" class="btn btn-secondary">← Back to Profile</a>
        </div>
    </div>
</div>

<?php include '../_foot.php'; ?>