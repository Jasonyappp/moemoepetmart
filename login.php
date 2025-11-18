<?php
// login.php - MoeMoePetMart Login
$headerTitle = "Welcome Back!";
$pageTitle = "Login • MoeMoePetMart";
ob_start();
?>

<div class="login-container">
  <div class="paw-icon">🐾</div>
  <h1>MoeMoePetMart</h1>
  <p class="tagline">Your favorite pet shop is waiting for you!</p>

  <form action="dashboard.php" method="POST">
    <input type="text" name="username" placeholder="Username or Email" required />
    <input type="password" name="password" placeholder="Password" required />
    
    <button type="submit" class="btn-login">
      Login Now
    </button>
  </form>

  <div class="extra-links">
    <a href="#">Forgot Password?</a> • <a href="register.php">Create Account</a>
  </div>

<?php
$content = ob_get_clean();
include 'includes/Base.php';
?>