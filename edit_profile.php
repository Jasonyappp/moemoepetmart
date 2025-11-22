<?php
require '_base.php';
require_login();

$user = current_user();

if (!$user) {
    temp('error', 'User not found!');
    redirect('/login.php');
}

// Handle profile information update
if (is_post() && isset($_POST['update_info'])) {
    $username = trim(post('username'));
    $email = trim(post('email'));
    $phone = trim(post('phone'));
   
    $errors = [];
   
    // Username validation
    if ($username === '') {
        $errors[] = 'Username is required~';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters ♡';
    } elseif ($username !== $user->username) {
        // Check if new username is unique
        $stm = $_db->prepare("SELECT COUNT(*) FROM users WHERE username = ? AND id != ?");
        $stm->execute([$username, $user->id]);
        if ($stm->fetchColumn() > 0) {
            $errors[] = 'Username already taken!';
        }
    }

    // Email validation
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address ♡';
    } elseif ($email !== $user->email) {
        // Check if new email is unique
        $stm = $_db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stm->execute([$email, $user->id]);
        if ($stm->fetchColumn() > 0) {
            $errors[] = 'Email already registered by another user!';
        }
    }

    // Phone validation
    if (!preg_match('/^[0-9+\-\s()]{10,20}$/', $phone)) {
        $errors[] = 'Please enter a valid phone number ♡';
    }
   
    // Update if no errors
    if (empty($errors)) {
        $stm = $_db->prepare("UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?");
        $stm->execute([$username, $email, $phone, $user->id]);
        
        // Update session if username changed
        if ($username !== $user->username) {
            $_SESSION['user'] = $username;
        }
        
        temp('info', 'Profile information updated successfully! ♡');
        redirect('/profile.php');
    } else {
        foreach ($errors as $error) {
            temp('error', $error);
        }
    }
}

// Handle profile picture upload
if (is_post() && isset($_FILES['profile_pic']) && !empty($_FILES['profile_pic']['name'])) {
    $uploadDir = 'uploads/profile_pics/';
   
    // Create directory if it doesn't exist
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
   
    $file = $_FILES['profile_pic'];
   
    // Check if file was uploaded without errors
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileName = $user->id . '_' . time() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
       
        $imageFileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
       
        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                // Delete old profile picture if exists
                if ($user->profile_pic && file_exists($user->profile_pic) && $user->profile_pic !== $targetPath) {
                    unlink($user->profile_pic);
                }
               
                // Update database with the relative path
                $stm = $_db->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                $stm->execute([$targetPath, $user->id]);
               
                temp('info', 'Profile picture updated successfully! ♡');
                redirect('/profile.php');
            } else {
                temp('error', 'Sorry, there was an error uploading your file.');
            }
        } else {
            temp('error', 'Only JPG, JPEG, PNG & GIF files are allowed.');
        }
    } else {
        temp('error', 'File upload error: ' . $file['error']);
    }
}

// Handle profile picture deletion
if (is_post() && isset($_POST['delete_pic'])) {
    if ($user->profile_pic && file_exists($user->profile_pic)) {
        unlink($user->profile_pic);
    }
   
    $stm = $_db->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?");
    $stm->execute([$user->id]);
    temp('info', 'Profile picture deleted successfully! ♡');
    redirect('/profile.php');
}

$_title = 'Edit Profile ♡ Moe Moe Pet Mart';
include '_head.php';
?>

<div class="profile-container">
    <div class="profile-card">
        <h2>Edit Profile ♡</h2>
        
        <!-- Current Profile Picture -->
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
           
            <!-- Upload Form -->
            <form method="post" enctype="multipart/form-data" class="profile-pic-form">
                <div class="input-group">
                    <label>Update Profile Picture</label>
                    <input type="file" name="profile_pic" accept="image/*" class="file-input">
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Upload Picture ♡</button>
            </form>

            <!-- Delete Button -->
            <?php if ($user->profile_pic && file_exists($user->profile_pic)): ?>
            <form method="post" class="delete-pic-form">
                <button type="submit" name="delete_pic" value="1"
                        class="btn-delete-pic"
                        onclick="return confirm('Are you sure you want to delete your profile picture? 🥺')">
                    Delete Current Picture 🗑️
                </button>
            </form>
            <?php endif; ?>
        </div>

        <!-- Edit Profile Information -->
        <div class="profile-edit-section">
            <h3>Edit Information ♡</h3>
            
            <?php if (isset($_err) && !empty($_err)): ?>
                <div class="error-box">
                    <?php foreach ($_err as $error): ?>
                        <div><?= $error ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" class="profile-edit-form">
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= encode($user->username) ?>" required 
                           placeholder="Choose a cute username">
                    <small>Must be at least 3 characters</small>
                </div>
               
                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" value="<?= encode($user->email) ?>" required
                           placeholder="your.email@example.com">
                </div>
               
                <div class="input-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" value="<?= encode($user->phone) ?>" required
                           placeholder="012-3456789">
                    <small>Format: 012-3456789</small>
                </div>
               
                <button type="submit" name="update_info" class="btn btn-primary" style="width: 100%;">Update Information ♡</button>
            </form>
        </div>

        <!-- Profile Actions -->
        <div class="profile-actions">
            <a href="/profile.php" class="btn btn-secondary">← Back to Profile</a>
            <a href="/" class="btn btn-secondary">← Back to Home</a>
        </div>
    </div>
</div>

<?php include '_foot.php'; ?>