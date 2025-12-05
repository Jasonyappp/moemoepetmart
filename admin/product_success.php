<?php
require '../_base.php';
require_login();
require_admin();

$product_id = $_SESSION['last_product_id'] ?? 0;
if (!$product_id) redirect('product_list.php');

$stmt = $_db->prepare("SELECT product_name, product_code FROM product WHERE product_id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

$_title = 'Product Created Successfully!';
include '../admin/_head.php';
?>

<main class="admin-main">
    <div style="text-align:center; padding:4rem 2rem;">
        <h1 style="font-size:3.5rem; color:#ff69b4; margin-bottom:1rem;">
            Product Created Successfully!
        </h1>
        <div style="background:white; padding:2rem; border-radius:20px; display:inline-block; box-shadow:0 10px 30px rgba(255,105,180,0.2);">
            <p style="font-size:1.5rem; margin:1rem 0;">
                <strong><?= encode($product->product_name) ?></strong>
            </p>
            <p style="color:#ff69b4; font-weight:bold; font-size:1.3rem;">
                Code: <?= encode($product->product_code) ?>
            </p>
        </div>

        <div style="margin-top:3rem;">
            <a href="product_add.php" class="btn btn-primary" style="padding:1rem 3rem; font-size:1.4rem; margin:0 1rem;">
                Add Another Product
            </a>
            <a href="product_list.php" class="btn btn-secondary" style="padding:1rem 3rem; font-size:1.4rem; margin:0 1rem;">
                Back to Product List
            </a>
        </div>
    </div>
</main>

<?php 
unset($_SESSION['last_product_id']);
include '../admin/_foot.php'; 
?>