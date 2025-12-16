<?php
require '../_base.php';
require_login();
require_admin();

$user = current_user();

if (!$user) {
    temp('error', 'User not found!');
    redirect('/admin.php');
}

$_title = 'Admin Profile ♛ Moe Moe Pet Mart';
include '../_head.php';
?>

<div class="profile-container">
    <div class="profile-card" style="max-width: 800px; margin: 2rem auto;">
        <h2>Admin Profile ♛</h2>
        <p class="text-center" style="font-size: 1.2rem; color: #ff69b4;">Master <?= encode($user->username) ?> controls everything~</p>

        <!-- Profile Picture -->
        <div class="profile-picture-section" style="text-align: center; margin: 2rem 0;">
            <div class="profile-pic-container" style="display: inline-block;">
                <?php if ($user->profile_pic && file_exists('../' . $user->profile_pic)): ?>
                    <img src="/<?= encode($user->profile_pic) ?>?t=<?= time() ?>"
                         alt="Admin Avatar"
                         class="profile-pic"
                         style="width: 180px; height: 180px; object-fit: cover; border: 6px solid #ff69b4; border-radius: 50%; box-shadow: 0 10px 30px rgba(255,105,180,0.3);">
                <?php else: ?>
                    <div class="default-avatar" style="width: 180px; height: 180px; background: linear-gradient(135deg, #ff69b4, #ff1493); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 5rem; color: white; box-shadow: 0 10px 30px rgba(255,105,180,0.3);">
                        ♛
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Info -->
        <div class="profile-info" style="background: white; padding: 2rem; border-radius: 20px; box-shadow: 0 8px 25px rgba(255,105,180,0.15);">
            <div class="profile-field">
                <div class="field-label">Username</div>
                <div class="field-value" style="font-size: 1.4rem; font-weight: bold; color: #ff1493;"><?= encode($user->username) ?></div>
            </div>

            <div class="profile-field">
                <div class="field-label">Role</div>
                <div class="field-value" style="color: #ff69b4; font-weight: bold;">Administrator ♛</div>
            </div>

            <div class="profile-field">
                <div class="field-label">Joined</div>
                <div class="field-value"><?= date('F j, Y', strtotime($user->created_at)) ?></div>
            </div>
        </div>

        <!-- Actions -->
        <div class="profile-actions" style="margin-top: 3rem; text-align: center;">
            <a href="edit_profile.php" class="btn btn-primary" style="padding: 1rem 2.5rem; font-size: 1.3rem;">Edit Profile ♡</a>
            <a href="change_password.php" class="btn btn-secondary" style="padding: 1rem 2.5rem; font-size: 1.3rem; margin: 0 1rem;">Change Password</a>
            <a href="/admin.php" class="btn btn-secondary" style="padding: 1rem 2.5rem; font-size: 1.3rem;">← Back to Dashboard</a>
        </div>
    </div>
</div>

<?php include '../_foot.php'; ?>