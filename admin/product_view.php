<?php
require '../_base.php';
require_login();
require_admin();

$id = get('id');
if (!$id || !is_numeric($id)) redirect('product_list.php');

$stmt = $_db->prepare("
    SELECT p.*, c.category_name, c.category_code 
    FROM product p 
    JOIN category c ON p.category_id = c.category_id 
    WHERE p.product_id = ?
");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    temp('error', 'Product not found');
    redirect('product_list.php');
}

$images = $_db->prepare("SELECT * FROM product_image WHERE product_id = ? ORDER BY is_main DESC, sort_order");
$images->execute([$id]);

$_title = 'View Product - Admin';
include '../_head.php';
?>

<div class="container">
    <div class="product-detail">
        <h2><?= encode($product->product_name) ?></h2>
        <p><strong>Code:</strong> <?= encode($product->product_code) ?></p>
        <p><strong>Category:</strong> <?= encode($product->category_name) ?></p>
        <p><strong>Price:</strong> RM <?= number_format($product->price, 2) ?></p>
        <p><strong>Stock:</strong> <?= $product->stock_quantity ?> 
            <?= $product->stock_quantity <= 5 ? '<span class="text-danger">(Low Stock!)</span>' : '' ?>
        </p>
        <p><strong>Description:</strong><br><?= nl2br(encode($product->description ?: 'No description')) ?></p>

        <h3>Product Images</h3>
        <div class="image-gallery">
            <?php foreach ($images as $img): ?>
                <img src="../<?= encode($img->image_path) ?>" alt="Product image" class="gallery-img">
            <?php endforeach; ?>
            <?php if ($images->rowCount() == 0): ?>
                <p>No images uploaded yet.</p>
            <?php endif; ?>
        </div>

        <div class="form-actions mt-20">
            <a href="product_edit.php?id=<?= $id ?>" class="btn btn-primary">Edit Product</a>
            <a href="product_list.php" class="btn btn-secondary">Back to List</a>
        </div>
    </div>
</div>

<style>
.image-gallery img { max-width: 300px; margin: 10px; border: 2px solid #ddd; border-radius: 8px; }
</style>

<?php include '../_foot.php'; ?>