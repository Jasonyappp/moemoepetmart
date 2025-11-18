<?php

$currentPage = basename($_SERVER['PHP_SELF']);
?>
<header>
    <div class="header-content">
        <h1><?php echo $headerTitle ?? 'MoeMoePetMart'; ?></h1>
        
        <?php if ($currentPage !== 'login.php'): ?>
            <a href="login.php" class="login-btn">
                Login
            </a>
        <?php endif; ?>
    </div>
</header>