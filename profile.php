<?php
require '_base.php';
require_login();

$user = current_user();

if (!$user) {
    temp('error', 'User not found!');
    redirect('/login.php');
}

$_title = 'My Profile ♡ Moe Moe Pet Mart';
include '_head.php';
?>

<div class="profile-container">
    <div class="profile-card">
        <h2>My Profile ♡</h2>
        <p>Welcome back, <?= encode($user->username) ?>!</p>

        <!-- Profile Picture Section -->
        <div class="profile-picture-section">
            <div class="profile-pic-container">
                <?php if ($user->profile_pic && file_exists($user->profile_pic)): ?>
                    <img src="/<?= encode($user->profile_pic) ?>?t=<?= time() ?>"
                         alt="Profile Picture"
                         class="profile-pic">
                <?php else: ?>
                    <div class="default-avatar">
                        <span>🐾</span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profile Information Section -->
        <div class="profile-info">
            <div class="profile-field">
                <div class="field-label">Username</div>
                <div class="field-value"><?= encode($user->username) ?></div>
            </div>

            <div class="profile-field">
                <div class="field-label">Email Address</div>
                <div class="field-value"><?= encode($user->email) ?></div>
            </div>

            <div class="profile-field">
                <div class="field-label">Phone Number</div>
                <div class="field-value"><?= encode($user->phone) ?></div>
            </div>

            <div class="profile-field">
                <div class="field-label">Member Since</div>
                <div class="field-value"><?= date('F j, Y', strtotime($user->created_at)) ?></div>
            </div>
        </div>

        <!-- Profile Actions -->
        <div class="profile-actions">
            <a href="edit_profile.php" class="btn btn-primary">Edit Profile ♡</a>
            <a href="change_password.php" class="btn btn-secondary">Change Password</a>
            <a href="/" class="btn btn-secondary">← Back to Home</a>
        </div>
    </div>
</div>

<?php include '_foot.php'; ?>