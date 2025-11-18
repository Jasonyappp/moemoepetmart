<?php
// login.php - FINAL VERSION (Header + Footer + Perfect Design)
$headerTitle = "MoeMoePetMart";
$pageTitle = "Login - MoeMoePetMart";
ob_start();
?>

<div class="login-full-container">
    <div class="login-box">
        <div class="petshop-icon">
            <img src="images/pet-shop.png" alt="MoeMoePetMart" width="90" height="90">
        </div>

        <h1>MoeMoePetMart</h1>
        <p class="tagline">Your favorite pet shop is waiting for you!</p>

        <form action="dashboard.php" method="POST">
            <div class="input-group">
                <input type="text" name="username" placeholder="Username or Email" required autocomplete="username">
            </div>
            <div class="input-group">
                <input type="password" name="password" placeholder="Password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn-login">Login Now</button>
        </form>

        <div class="extra-links">
            <a href="#">Forgot Password?</a> • <a href="register.php">Create Account</a>
        </div>
    </div>
</div>

<?php
$content = ob_get_clean();
include 'includes/Base.php';
?>