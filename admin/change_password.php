<?php
require '../_base.php';
require_login();
require_admin();

$user = current_user();

if (is_post()) {
    $current = post('current_password');
    $new     = post('new_password');
    $confirm = post('confirm_password');

    $errors = [];

    if (!password_verify($current, $user->password)) {
        $errors[] = 'Current password is incorrect!';
    }
    if (strlen($new) < 4) {
        $errors[] = 'New password must be at least 4 characters ♡';
    }
    if ($new !== $confirm) {
        $errors[] = 'New passwords do not match!';
    }

    if (empty($errors)) {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $_db->prepare("UPDATE users SET password = ? WHERE id = ?")
            ->execute([$hashed, $user->id]);

        temp('info', 'Password changed successfully! Your kingdom is secure ♛');
        redirect('profile.php');
    } else {
        foreach ($errors as $e) temp('error', $e);
    }
}

$_title = 'Change Admin Password ♛';
include '../_head.php';
?>

<div class="profile-container">
    <div class="profile-card">
        <h2>Change Admin Password ♛</h2>
        <p>Keep your master account safe~</p>

        <form method="post" class="profile-edit-form">
            <div class="input-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required placeholder="Your current secret...">
            </div>

            <div class="input-group">
                <label>New Password</label>
                <input type="password" name="new_password" required placeholder="New strong password (min 4 chars)">
            </div>

            <div class="input-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required placeholder="Type again ♡">
            </div>

            <button type="submit" class="btn btn-primary" style="width: 100%; padding: 1.2rem;">
                Update Password ♛
            </button>
        </form>

        <div class="profile-actions" style="margin-top: 2rem;">
            <a href="profile.php" class="btn btn-secondary">← Back to Profile</a>
        </div>
    </div>
</div>

<?php include '../_foot.php'; ?>