<?php
require '_base.php';

$_title = 'Admin Dashboard';

$showWelcome = false;
if (isset($_SESSION['show_welcome']) && $_SESSION['show_welcome'] === true) {
    $showWelcome = true;
    unset($_SESSION['show_welcome']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?></title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>

<!-- welcome Toast -->
<?php if ($showWelcome): ?>
<div class="welcome-toast show" id="welcomeToast">
    <i class="fas fa-heart"></i>
    <span>Welcome back, Master <?= encode($_SESSION['user']) ?>!</span>
</div>

<script>
// 2秒后自动消失 + 淡出动画
setTimeout(() => {
    const toast = document.getElementById('welcomeToast');
    toast.classList.remove('show');
    setTimeout(() => toast.remove(), 600);
}, 2000);
</script>
<?php endif; ?>

<div class="admin-layout">

    <!-- 左侧侧边栏 -->
    <aside class="admin-sidebar">
        <div class="logo">
            <h2>MoeMoePet</h2>
        </div>
        <ul>
            <li><a href="admin.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="admin/product_list.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="admin/product_add.php"><i class="fas fa-plus-circle"></i> Add Product</a></li>
            <li><a href="category_list.php"><i class="fas fa-tags"></i> Categories</a></li>
            <li><a href="#"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="#"><i class="fas fa-users"></i> Users</a></li>
            <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <!-- 主内容区 -->
    <main class="admin-main">
        <div class="admin-welcome-header">
            <h1>Admin Dashboard • Moe Moe Pet Mart</h1>
        </div>

        <div class="dashboard-cards">
            <!-- Product Management -->
            <a href="admin/product_list.php" class="dashboard-card">
                <i class="fas fa-cube"></i>
                <h3>Product</h3>
                <p>Management</p>
                <span class="btn">Manage Products</span>
            </a>

            <!-- Orders -->
            <div class="dashboard-card coming-soon">
                <i class="fas fa-clipboard-list"></i>
                <h3>Order</h3>
                <p>Management</p>
                <span class="btn">Coming Soon</span>
            </div>

            <!-- Users -->
            <div class="dashboard-card coming-soon">
                <i class="fas fa-user-friends"></i>
                <h3>User</h3>
                <p>Management</p>
                <span class="btn">Coming Soon</span>
            </div>
        </div>
    </main>
</div>

</body>
</html>