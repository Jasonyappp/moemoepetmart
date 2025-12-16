<?php
require '../_base.php';
require_login();
require_admin();

$user = current_user();

if (!$user) {
    temp('error', 'User not found!');
    redirect('/admin.php');
}

// Handle username update
if (is_post() && isset($_POST['update_info'])) {
    $username = trim(post('username'));

    $errors = [];

    if ($username === '') {
        $errors[] = 'Username is required~';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters ♡';
    } elseif ($username !== $user->username) {
        $stm = $_db->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stm->execute([$username, $user->id]);
        if ($stm->fetchColumn() > 0) {
            $errors[] = 'Username already taken!';
        }
    }

    if (empty($errors)) {
        $_db->prepare("UPDATE users SET username = ? WHERE id = ?")
            ->execute([$username, $user->id]);

        $_SESSION['user'] = $username; // Update session
        temp('info', 'Username updated successfully! ♡');
        redirect('profile.php');
    } else {
        foreach ($errors as $error) temp('error', $error);
    }
}

// Handle profile picture upload
if (is_post() && isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
    $uploadDir = 'uploads/profile_pics/';
    if (!is_dir('../' . $uploadDir)) {
        mkdir('../' . $uploadDir, 0777, true);
    }

    $file = $_FILES['profile_pic'];
    $fileName = $user->id . '_' . time() . '_' . basename($file['name']);
    $targetPath = '../' . $uploadDir . $fileName;
    $dbPath = $uploadDir . $fileName;

    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if ($file['size'] > 5*1024*1024) {
        temp('error', 'Image must be under 5MB ♡');
    } elseif (!in_array($mime, $allowed)) {
        temp('error', 'Only image files allowed!');
    } elseif (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Delete old picture if exists
        if ($user->profile_pic && file_exists('../' . $user->profile_pic)) {
            unlink('../' . $user->profile_pic);
        }

        $_db->prepare("UPDATE users SET profile_pic = ? WHERE id = ?")
            ->execute([$dbPath, $user->id]);

        temp('info', 'Profile picture updated! ♡');
        redirect('edit_profile.php');
    } else {
        temp('error', 'Upload failed~');
    }
}

// Handle delete profile picture
if (is_post() && isset($_POST['delete_pic'])) {
    if ($user->profile_pic && file_exists('../' . $user->profile_pic)) {
        unlink('../' . $user->profile_pic);
    }
    $_db->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?")
        ->execute([$user->id]);
    temp('info', 'Profile picture removed ♡');
    redirect('edit_profile.php');
}

$_title = 'Edit Admin Profile ♛';
include '../_head.php';
?>

<div class="profile-container">
    <div class="profile-card" style="max-width: 800px;">

        <h2>Edit Admin Profile ♛</h2>

        <!-- Profile Picture Upload -->
        <div class="profile-edit-section" style="text-align: center; margin-bottom: 3rem;">
            <div class="profile-pic-container" style="margin: 0 auto 1.5rem;">
                <?php if ($user->profile_pic && file_exists('../' . $user->profile_pic)): ?>
                    <img src="/<?= encode($user->profile_pic) ?>?t=<?= time() ?>"
                         class="profile-pic"
                         style="width: 160px; height: 160px; object-fit: cover; border: 5px solid #ff69b4; border-radius: 50%;">
                <?php else: ?>
                    <div style="width: 160px; height: 160px; background: linear-gradient(135deg, #ff8fab, #ff69b4); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 4rem; color: white;">
                        ♛
                    </div>
                <?php endif; ?>
            </div>

            <form method="post" enctype="multipart/form-data" style="margin: 1rem 0;">
                <label style="font-size: 1.2rem; color: #ff69b4;">Change Avatar ♡</label><br>
                <input type="file" name="profile_pic" accept="image/*" required style="margin: 1rem 0;">
                <button type="submit" class="btn btn-primary">Upload New Picture</button>
            </form>

            <?php if ($user->profile_pic): ?>
            <form method="post" onsubmit="return confirm('Delete your admin avatar forever? 🥺');">
                <button type="submit" name="delete_pic" value="1" class="btn-delete-pic" style="margin-top: 1rem;">
                    Delete Avatar 🗑️
                </button>
            </form>
            <?php endif; ?>
        </div>

        <!-- Username Update -->
        <div class="profile-edit-section">
            <h3>Change Username</h3>
            <form method="post">
                <div class="input-group">
                    <label>New Username</label>
                    <input type="text" name="username" value="<?= encode($user->username) ?>" required minlength="3">
                    <small>At least 3 characters ♡</small>
                </div>
                <button type="submit" name="update_info" class="btn btn-primary" style="width: 100%;">Update Username ♡</button>
            </form>
        </div>

        <div class="profile-actions" style="margin-top: 3rem;">
            <a href="profile.php" class="btn btn-secondary">← Back to Profile</a>
            <a href="change_password.php" class="btn btn-secondary">Change Password</a>
            <a href="/admin.php" class="btn btn-secondary">Dashboard</a>
        </div>
    </div>
</div>

<?php include '../_foot.php'; ?>