<?php
require '../_base.php';
require_login();
require_admin();

$_title = 'View Product - Admin';

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

$images = $_db->prepare("SELECT * FROM product_image WHERE product_id = ? ORDER BY is_main DESC, sort_order ASC");
$images->execute([$id]);
$images = $images->fetchAll();

include '../_head.php';
?>

<div class="product-view-container">

    <div class="product-view-header">
        <h1><?= encode($product->product_name) ?></h1>
        <div class="product-code">#<?= encode($product->product_code) ?></div>
        <div class="category-badge"><?= encode($product->category_name) ?></div>
    </div>

    <div class="product-body">

        <div class="product-gallery">
    <?php if ($product->photo_name): ?>
   >
        <img src="../admin/uploads/products/<?=  encode($product->photo_name) ?>"
             alt="<?=  encode($product->photo_name) ?>"
             class="main-image" 
             style="width:100%; max-width:500px; border-radius:12px; box-shadow:0 4px 12px rgba(0,0,0,0.1);">
       
        <?php if (count($images) > 1): ?>
        <div class="thumbnail-list">
            <?php foreach ($images as $img): ?>
                <img src="../admin/uploads/products/ <?encode($product->photo_name) ?>"
                     alt="Thumbnail"
                     class="thumbnail <?= $product->photo_name ? 'active' : '' ?>"
                     onclick="document.getElementById('mainImage').src = this.src;
                              document.querySelectorAll('.thumbnail').forEach(t => t.classList.remove('active'));
                              this.classList.add('active');"
                     style="cursor:pointer; width:80px; height:80px; object-fit:cover; margin:5px; border: <?= $product->photo_name ? '3px solid #ff69b4' : '2px solid #ddd' ?>; border-radius:8px;">
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="no-image" style="text-align:center; padding:60px; background:#f9f9f9; border-radius:12px; color:#999;">
            <div style="font-size:4rem;">No photos yet</div>
            <div style="font-size:1.3rem; margin-top:10px;">Waiting for cute pictures!</div>
        </div>
    <?php endif; ?>
</div>

       
        <div class="product-info">
            <div class="price-box">
                RM <?= number_format($product->price, 2) ?>
                <small>Super affordable price!</small>
            </div>

            <div class="stock-status <?= $product->stock_quantity > 10 ? 'stock-in' : ($product->stock_quantity > 0 ? 'stock-low' : 'stock-out') ?>">
                <?php if ($product->stock_quantity > 10): ?>
                    In Stock
                <?php elseif ($product->stock_quantity > 0): ?>
                    Only <?= $product->stock_quantity ?> left! Hurry~
                <?php else: ?>
                    Sold Out
                <?php endif; ?>
            </div>

            <div class="info-grid">
                <strong>Product Code</strong>   <span>#<?= encode($product->product_code) ?></span>
                <strong>Category</strong>       <span><?= encode($product->category_name) ?></span>
                <strong>Price</strong>          <span>RM <?= number_format($product->price, 2) ?></span>
                <strong>Stock</strong>          <span><?= $product->stock_quantity ?> units</span>
                <strong>Added on</strong>       <span><?= date('j M Y', strtotime($product->created_at)) ?></span>
            </div>

            <?php if ($product->description): ?>
            <div class="description-box">
                <strong style="color:#ff69b4; font-size:1.3rem;">Product Description</strong><br><br>
                <?= nl2br(encode($product->description)) ?>
            </div>
            <?php endif; ?>

            <div class="action-buttons" style="text-align:center; margin-top:40px;">
                <a href="product_edit.php?id=<?= $id ?>" class="moe-btn moe-btn-primary">Edit Product</a>
                <a href="product_list.php" class="moe-btn moe-btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</div>

