<?php
require '_base.php';
require_login();

$user = current_user();

if (!$user) {
    temp('error', 'User not found!');
    redirect('/login.php');
}

// Get user's addresses
$stm = $_db->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY created_at DESC");
$stm->execute([$user->id]);
$addresses = $stm->fetchAll();

$_title = 'My Profile ♡ Moe Moe Pet Mart';
include '_head.php';
?>

<div class="profile-container">
    <div class="profile-card">
        <h2>My Profile ♡</h2>
        <p>Welcome back, <?= encode($user->username) ?>!</p>

        <!-- Profile Picture -->
        <div class="profile-picture-section">
            <div class="profile-pic-container">
                <?php if ($user->profile_pic && file_exists($user->profile_pic)): ?>
                    <img src="/<?= encode($user->profile_pic) ?>?t=<?= time() ?>" class="profile-pic">
                <?php else: ?>
                    <div class="default-avatar">🐾</div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Profile Info -->
        <div class="profile-info">
            <div class="profile-field">
                <div class="field-label">Username</div>
                <div class="field-value"><?= encode($user->username) ?></div>
            </div>

            <div class="profile-field">
                <div class="field-label">Email</div>
                <div class="field-value"><?= encode($user->email) ?></div>
            </div>

            <div class="profile-field">
                <div class="field-label">Phone</div>
                <div class="field-value"><?= encode($user->phone) ?></div>
            </div>

            <div class="profile-field">
                <div class="field-label">Member Since</div>
                <div class="field-value"><?= date('F j, Y', strtotime($user->created_at)) ?></div>
            </div>
        </div>

        <!-- ========== SIMPLE ADDRESS DISPLAY ========== -->
        <div class="address-display-simple">
            <h3>🏠 Your Addresses</h3>
            
            <?php if (!empty($addresses)): ?>
                <div class="address-list-simple">
                    <?php foreach ($addresses as $addr): ?>
                        <div class="address-card-simple" style="background: #fff0f5; padding: 15px; border-radius: 10px; margin-bottom: 15px; border-left: 4px solid #ff69b4;">
                            <?php if (!empty($addr->address_name)): ?>
                                <div class="address-name" style="font-weight: bold; color: #ff1493; margin-bottom: 5px;">
                                    <?= encode($addr->address_name) ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($addr->recipient_name)): ?>
                                <div class="recipient-info" style="margin-bottom: 8px;">
                                    <span style="color: #555;">
                                        📦 <strong>To:</strong> <?= encode($addr->recipient_name) ?>
                                    </span>
                                    <br>
                                    <span style="color: #555;">
                                        📞 <strong>Phone:</strong> <?= encode($addr->recipient_phone) ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="address-text" style="color: #333; line-height: 1.4;">
                                <?= nl2br(encode($addr->full_address)) ?>
                            </div>
                            
                            <div class="address-date" style="margin-top: 8px; font-size: 0.85em; color: #888;">
                                Added: <?= date('M j, Y', strtotime($addr->created_at)) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-address">
                    <p style="color: #ff69b4; text-align: center;">
                        No addresses saved yet~
                    </p>
                    <p style="text-align: center; margin-top: 10px;">
                        <a href="edit_profile.php" style="color: #ff1493; font-weight: 600;">
                            ➕ Add your first address
                        </a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
        <!-- ========== END ADDRESS DISPLAY ========== -->

        <!-- Actions -->
        <div class="profile-actions">
            <a href="edit_profile.php" class="btn btn-primary">Edit Profile ♡</a>
            <a href="change_password.php" class="btn btn-secondary">Change Password</a>
            <a href="/" class="btn btn-secondary">← Back to Home</a>
        </div>
    </div>
</div>

<?php include '_foot.php'; ?>