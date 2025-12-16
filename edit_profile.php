<?php
require '_base.php';
require_login();

$user = current_user();

if (!$user) {
    temp('error', 'User not found!');
    redirect('/login.php');
}

// ========== FIX TABLE STRUCTURE FIRST ==========
try {
    // Check if table exists and fix it if needed
    $tableCheck = $_db->query("SHOW TABLES LIKE 'user_addresses'");
    if ($tableCheck->rowCount() == 0) {
        // Create fresh table with proper structure
        $_db->exec("CREATE TABLE user_addresses (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            address_name VARCHAR(100) DEFAULT 'Home',
            full_address TEXT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    } else {
        // Check current structure
        $desc = $_db->query("DESCRIBE user_addresses");
        $columns = $desc->fetchAll(PDO::FETCH_ASSOC);
        
        $hasAutoIncrement = false;
        foreach ($columns as $col) {
            if ($col['Field'] == 'id' && strpos($col['Extra'], 'auto_increment') !== false) {
                $hasAutoIncrement = true;
                break;
            }
        }
        
        // If no auto_increment, repair the table
        if (!$hasAutoIncrement) {
            // Create temporary table with correct structure
            $_db->exec("CREATE TABLE user_addresses_temp LIKE user_addresses");
            
            // Copy data to temp table
            $_db->exec("INSERT INTO user_addresses_temp SELECT * FROM user_addresses");
            
            // Drop original table
            $_db->exec("DROP TABLE user_addresses");
            
            // Create new table with auto_increment
            $_db->exec("CREATE TABLE user_addresses (
                id INT AUTO_INCREMENT PRIMARY KEY,
                user_id INT NOT NULL,
                address_name VARCHAR(100) DEFAULT 'Home',
                full_address TEXT NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
            
            // Copy data back, letting MySQL generate new auto-increment IDs
            $_db->exec("INSERT INTO user_addresses (user_id, address_name, full_address, created_at) 
                       SELECT user_id, address_name, full_address, created_at 
                       FROM user_addresses_temp");
            
            // Drop temp table
            $_db->exec("DROP TABLE user_addresses_temp");
            
            error_log("Fixed user_addresses table structure");
        }
    }
} catch (Exception $e) {
    error_log("Table repair error: " . $e->getMessage());
}

// Get user's addresses
$stm = $_db->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY created_at DESC");
$stm->execute([$user->id]);
$addresses = $stm->fetchAll();

// ========== HANDLE PROFILE PICTURE DELETE ==========
if (is_post() && isset($_POST['delete_profile_pic'])) {
    if ($user->profile_pic && file_exists($user->profile_pic)) {
        unlink($user->profile_pic);
    }
   
    $stm = $_db->prepare("UPDATE users SET profile_pic = NULL WHERE id = ?");
    if ($stm->execute([$user->id])) {
        temp('info', 'Profile picture deleted successfully! ♡');
        redirect('/edit_profile.php');
    }
}

// ========== HANDLE PROFILE PICTURE UPLOAD ==========
if (is_post() && isset($_FILES['profile_pic']) && !empty($_FILES['profile_pic']['name'])) {
    $uploadDir = 'uploads/profile_pics/';
   
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
   
    $file = $_FILES['profile_pic'];
   
    if ($file['error'] === UPLOAD_ERR_OK) {
        $fileName = $user->id . '_' . time() . '_' . basename($file['name']);
        $targetPath = $uploadDir . $fileName;
       
        $imageFileType = strtolower(pathinfo($targetPath, PATHINFO_EXTENSION));
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
       
        if (in_array($imageFileType, $allowedTypes)) {
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                if ($user->profile_pic && file_exists($user->profile_pic) && $user->profile_pic !== $targetPath) {
                    unlink($user->profile_pic);
                }
               
                $stm = $_db->prepare("UPDATE users SET profile_pic = ? WHERE id = ?");
                if ($stm->execute([$targetPath, $user->id])) {
                    temp('info', 'Profile picture updated successfully! ♡');
                    redirect('/edit_profile.php');
                }
            }
        }
    }
}

// ========== HANDLE ADDING NEW ADDRESS ==========
if (is_post() && isset($_POST['add_address'])) {
    $address_name = trim(post('address_name')) ?: 'Home';
    $full_address = trim(post('full_address'));
    
    if (empty($full_address)) {
        temp('error', 'Address cannot be empty~');
        redirect('/edit_profile.php');
    } else {
        try {
            // NOW THIS WILL WORK - Table is fixed with auto_increment
            $stm = $_db->prepare("INSERT INTO user_addresses (user_id, address_name, full_address) VALUES (?, ?, ?)");
            
            if ($stm->execute([$user->id, $address_name, $full_address])) {
                temp('info', '✅ Address saved successfully!');
                redirect('/edit_profile.php');
            } else {
                $errorInfo = $stm->errorInfo();
                error_log("Address insert failed: " . print_r($errorInfo, true));
                temp('error', 'Failed to save address. Error: ' . $errorInfo[2]);
                redirect('/edit_profile.php');
            }
            
        } catch (PDOException $e) {
            error_log("PDO Error: " . $e->getMessage());
            
            // Fallback: Use manual ID if auto_increment still not working
            if ($e->getCode() == 23000) {
                try {
                    // Get max ID and add 1
                    $maxIdStmt = $_db->query("SELECT COALESCE(MAX(id), 0) as max_id FROM user_addresses");
                    $maxId = $maxIdStmt->fetch()->max_id;
                    $nextId = $maxId + 1;
                    
                    $stm = $_db->prepare("INSERT INTO user_addresses (id, user_id, address_name, full_address) VALUES (?, ?, ?, ?)");
                    $stm->execute([$nextId, $user->id, $address_name, $full_address]);
                    
                    temp('info', '✅ Address saved! (Fallback method)');
                    redirect('/edit_profile.php');
                    
                } catch (Exception $ex) {
                    temp('error', 'Error: ' . $ex->getMessage());
                    redirect('/edit_profile.php');
                }
            } else {
                temp('error', 'Database error: ' . $e->getMessage());
                redirect('/edit_profile.php');
            }
        }
    }
}

// ========== HANDLE DELETING ADDRESS ==========
if (is_post() && isset($_POST['delete_address'])) {
    $address_id = post('address_id');
    
    // Verify address belongs to user before deleting
    $checkStm = $_db->prepare("SELECT id FROM user_addresses WHERE id = ? AND user_id = ?");
    $checkStm->execute([$address_id, $user->id]);
    
    if ($checkStm->fetch()) {
        $stm = $_db->prepare("DELETE FROM user_addresses WHERE id = ? AND user_id = ?");
        if ($stm->execute([$address_id, $user->id])) {
            temp('info', '🗑️ Address deleted successfully!');
            redirect('/edit_profile.php');
        } else {
            temp('error', 'Failed to delete address.');
            redirect('/edit_profile.php');
        }
    } else {
        temp('error', 'Address not found or you do not have permission to delete it.');
        redirect('/edit_profile.php');
    }
}

// ========== HANDLE PROFILE INFO UPDATE ==========
if (is_post() && isset($_POST['update_info'])) {
    $username = trim(post('username'));
    $email = trim(post('email'));
    $phone = trim(post('phone'));
   
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

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address ♡';
    } elseif ($email !== $user->email) {
        $stm = $_db->prepare("SELECT COUNT(*) FROM users WHERE email = ? AND id != ?");
        $stm->execute([$email, $user->id]);
        if ($stm->fetchColumn() > 0) {
            $errors[] = 'Email already registered by another user!';
        }
    }

    if (!preg_match('/^[0-9+\-\s()]{10,20}$/', $phone)) {
        $errors[] = 'Please enter a valid phone number ♡';
    }
   
    if (empty($errors)) {
        $stm = $_db->prepare("UPDATE users SET username = ?, email = ?, phone = ? WHERE id = ?");
        $stm->execute([$username, $email, $phone, $user->id]);
        
        if ($username !== $user->username) {
            $_SESSION['user'] = $username;
        }
        
        temp('info', 'Profile updated! ♡');
        redirect('/profile.php');
    } else {
        foreach ($errors as $error) {
            temp('error', $error);
        }
    }
}

$_title = 'Edit Profile ♡ Moe Moe Pet Mart';
include '_head.php';
?>

<div class="profile-container">
    <div class="profile-card">
        <h2>Edit Profile ♡</h2>
        
        <!-- ========== PROFILE PICTURE SECTION ========== -->
        <div class="profile-picture-section">
            <div class="profile-pic-container">
                <?php if ($user->profile_pic && file_exists($user->profile_pic)): ?>
                    <img src="/<?= encode($user->profile_pic) ?>?t=<?= time() ?>" 
                         alt="Profile Picture" class="profile-pic">
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
                    <input type="file" name="profile_pic" accept="image/*">
                </div>
                <button type="submit" class="btn btn-primary">Upload Picture ♡</button>
            </form>

            <!-- DELETE BUTTON FOR PROFILE PICTURE -->
            <?php if ($user->profile_pic && file_exists($user->profile_pic)): ?>
                <form method="post" class="delete-profile-pic-form" style="margin-top: 1rem;">
                    <button type="submit" name="delete_profile_pic" class="btn-delete"
                            onclick="return confirm('Delete your profile picture? 🥺')">
                        🗑️ Delete Current Picture
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <!-- ========== END PROFILE PICTURE ========== -->

        <!-- ========== SIMPLE ADDRESS BOX ========== -->
        <div class="simple-address-box">
            <h3>🏠 Add Address</h3>
            
            <form method="post">
                <div class="input-group">
                    <input type="text" name="address_name" placeholder="Name (Optional): Home, Work, etc." 
                           style="width: 100%; padding: 10px; margin-bottom: 10px;">
                </div>
                
                <div class="input-group">
                    <textarea name="full_address" rows="3" placeholder="Type your address here..." 
                              style="width: 100%; padding: 10px;" required></textarea>
                </div>
                
                <button type="submit" name="add_address" class="btn btn-primary" style="width: 100%;">
                    Save Address ♡
                </button>
            </form>
            
            <!-- Show saved addresses -->
            <?php if (!empty($addresses)): ?>
            <div class="saved-addresses">
                <h4>Your Saved Addresses:</h4>
                <?php foreach ($addresses as $addr): ?>
                <div class="address-line">
                    <span class="address-text">
                        <?php if (!empty($addr->address_name)): ?>
                            <strong><?= encode($addr->address_name) ?>:</strong> 
                        <?php endif; ?>
                        <?= encode($addr->full_address) ?>
                    </span>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="address_id" value="<?= $addr->id ?>">
                        <button type="submit" name="delete_address" class="btn-small">🗑️</button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <!-- ========== END ADDRESS BOX ========== -->

        <!-- ========== EDIT PROFILE INFO ========== -->
        <div class="profile-edit-section">
            <h3>Edit Information ♡</h3>
            
            <form method="post">
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" value="<?= encode($user->username) ?>" required>
                </div>
               
                <div class="input-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= encode($user->email) ?>" required>
                </div>
               
                <div class="input-group">
                    <label>Phone</label>
                    <input type="tel" name="phone" value="<?= encode($user->phone) ?>" required>
                </div>
               
                <button type="submit" name="update_info" class="btn btn-primary" style="width: 100%;">
                    Update Profile ♡
                </button>
            </form>
        </div>
        <!-- ========== END EDIT PROFILE INFO ========== -->

        <!-- ========== ACTION BUTTONS ========== -->
        <div class="profile-actions">
            <a href="/profile.php" class="btn btn-secondary">← Back to Profile</a>
            <a href="/" class="btn btn-secondary">← Back to Home</a>
        </div>
    </div>
</div>

<?php include '_foot.php'; ?>