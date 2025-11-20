<?php
require '_base.php';

// Protect this page — only allow logged in admin
if (!isset($_SESSION['user']) || $_SESSION['user'] !== 'admin') {
    temp('info', 'Access denied! Admins only~');
    redirect('/login.php');
}

$_title = 'Admin Dashboard ♡ Moe Moe Pet Mart';
include '_head.php';
?>

<div class="admin-container">
    <div class="admin-header">
        <h1>Admin Dashboard</h1>
        <p>Welcome back, <b>Master Admin</b>! Manage your pet mart below ♡</p>
    </div>

    <div class="admin-grid">
        <!-- Product Maintenance -->
        <a href="product_list.php" class="admin-card">
            <div class="card-icon">Pets</div>
            <h3>Manage Pets / Products</h3>
            <p>Add, edit, delete, or restock pets and supplies</p>
        </a>

        <!-- Future features (you can add later) -->
        <a href="#" class="admin-card disabled">
            <div class="card-icon">Orders</div>
            <h3>View Orders</h3>
            <p>Coming soon~</p>
        </a>

        <a href="#" class="admin-card disabled">
            <div class="card-icon">Users</div>
            <h3>Manage Members</h3>
            <p>Coming soon~</p>
        </a>

        <a href="logout.php" class="admin-card logout">
            <div class="card-icon">Logout</div>
            <h3>Logout</h3>
            <p>End admin session safely</p>
        </a>
    </div>


<?php include '_foot.php'; ?>