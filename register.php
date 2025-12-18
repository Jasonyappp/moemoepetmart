<?php
require '_base.php';

$_title = 'Register ♡ Moe Moe Pet Mart';
include '_head.php';

// ------------------- Process Registration -------------------
if (is_post()) {
    $username = trim(post('username'));
    $email = trim(post('email')); 
    $phone = trim(post('phone'));
    $password = post('password');
    $confirm  = post('confirm');

    $hasError = false;

    // Validation
    if ($username === '') {
        $_err['username'] = 'Username is required~';
        $hasError = true;
    } elseif (strlen($username) < 3) {
        $_err['username'] = 'Username too short ♡';
        $hasError = true;
    } elseif (!is_unique($username, 'users', 'username')) {
        $_err['username'] = 'Username already taken!';
        $hasError = true;
    }

    if ($email === '') {
        $_err['email'] = 'Email is required~';
        $hasError = true;
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_err['email'] = 'Please enter a valid email address ♡';
        $hasError = true;
    } elseif (!is_unique($email, 'users', 'email')) {
        $_err['email'] = 'Email already registered!';
        $hasError = true;
    }

    if ($phone === '') {
        $_err['phone'] = 'Phone number is required~';
        $hasError = true;
    } elseif (!preg_match('/^[0-9+\-\s()]{10,20}$/', $phone)) {
        $_err['phone'] = 'Please enter a valid phone number ♡';
        $hasError = true;
    }

    if ($password === '') {
        $_err['password'] = 'Password is required~';
        $hasError = true;
    } elseif (strlen($password) < 8) {  // Increased min length to 8 for better security
        $_err['password'] = 'Password must be at least 8 characters ♡';
        $hasError = true;
    } elseif (strlen($password) > 128) {  // Added max length to prevent abuse
        $_err['password'] = 'Password is too long! Maximum 128 characters ♡';
        $hasError = true;
    } elseif (!preg_match('/[A-Z]/', $password)) {
        $_err['password'] = 'Password must include at least one uppercase letter ♡';
        $hasError = true;
    } elseif (!preg_match('/[a-z]/', $password)) {
        $_err['password'] = 'Password must include at least one lowercase letter ♡';
        $hasError = true;
    } elseif (!preg_match('/\d/', $password)) {
        $_err['password'] = 'Password must include at least one number ♡';
        $hasError = true;
    } elseif (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
        $_err['password'] = 'Password must include at least one special character ♡';
        $hasError = true;
    } elseif ($password !== $confirm) {
        $_err['confirm'] = 'Passwords do not match!';
        $hasError = true;
    }

    if (!$hasError) {
        // Hash password securely
        $hashed = password_hash($password, PASSWORD_DEFAULT);

        // Insert into database
       $stm = $_db->prepare("INSERT INTO users (username, email, phone, password) VALUES (?, ?, ?, ?)");
       $stm->execute([$username, $email, $phone, $hashed]);
        
        temp('info', 'Welcome to the family, ' . encode($username) . '! You can now login ♡');
        redirect('/login.php');
    }

    // Keep values on error
    $username = encode($username);
    $email = encode($email);
    $phone = encode($phone);
}
?>

<div class="login-container">
    <div class="login-card">
        <div class="login-paw">
            <img src="/images/pet-shop.png" alt="Moe Moe Pet Mart">
        </div>
        
        <h2>Join Moe Moe Pet Mart ♡</h2>
        <p>Create your cute member account~</p>

        <form method="post" class="login-form">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" value="<?= $username ?? '' ?>" required placeholder="Choose a cute username" maxlength="50">
                <?php err('username'); ?>
            </div>

            <div class="input-group">
                <label>Email Address</label>
                <input type="email" name="email" value="<?= $email ?? '' ?>" required placeholder="Your email address" maxlength="100">
                <?php err('email'); ?>
            </div>

             <div class="input-group">
                <label>Phone Number</label>
                <input type="tel" name="phone" value="<?= $phone ?? '' ?>" required placeholder="Your phone number" maxlength="20">
                <?php err('phone'); ?>
            </div>

            <div class="input-group">
                <label>Password</label>
                <input type="password" name="password" required placeholder="Enter your password">
                <?php err('password'); ?>
            </div>

            <div class="input-group">
                <label>Confirm Password</label>
                <input type="password" name="confirm" required placeholder="Type password again">
                <?php err('confirm'); ?>
            </div>

            <button type="submit" class="btn-login-full">
                Register ♡
            </button>
        </form>

        <!-- Security Tips -->
        <div class="security-tips" style="margin-top: 20px; padding: 10px; background: #fff0f5; border-radius: 8px; font-size: 0.9rem;">
            <h4>Password Tips for Security:</h4>
            <ul>
                <li>At least 8 characters long</li>
                <li>Include uppercase and lowercase letters</li>
                <li>Include at least one number</li>
                <li>Include at least one special character (e.g., !@#$%)</li>
                <li>Avoid using personal information or common words</li>
                <li>Don't reuse passwords from other sites</li>
            </ul>
        </div>

        <div class="login-footer">
            <p>Already have an account?</p>
            <a href="/login.php">← Login Here</a>
        </div>
    </div>
</div>

<?php include '_foot.php'; ?>