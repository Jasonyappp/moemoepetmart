<?php
require '../_base.php';


if (user_role() === 'admin') {
    temp('error', 'Admins cannot shop here! Use member account ♡');
    redirect('/admin.php');
}

$id = get('id');
if (!$id || !is_numeric($id)) {
    temp('error', 'Invalid product!');
    redirect('products.php');
}

$stm = $_db->prepare("SELECT p.*, c.category_name 
                     FROM product p 
                     JOIN category c ON p.category_id = c.category_id 
                     WHERE p.product_id = ? AND p.is_active = 1");
$stm->execute([$id]);
$product = $stm->fetch();

if (!$product) {
    temp('error', 'Product not found~');
    redirect('products.php');
}

$_title = encode($product->product_name) . ' ♡ Moe Moe Pet Mart';
include '../_head.php';
?>

<div class="container">
    <div class="product-detail-shopee">
        <div class="product-image">
            <?php if ($product->photo_name): ?>
                <img src="/admin/uploads/products/<?= encode($product->photo_name) ?>" 
                     alt="<?= encode($product->product_name) ?>">
            <?php else: ?>
                <div class="no-image">No Image ♡</div>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <h1><?= encode($product->product_name) ?></h1>
            
            <div class="price-stock">
                <div class="price">RM <?= number_format($product->price, 2) ?></div>
                <div class="stock">Stock: <strong><?= $product->stock_quantity ?></strong></div>
            </div>

            <?php if ($product->description): ?>
                <div class="description">
                    <?= nl2br(encode($product->description)) ?>
                </div>
            <?php endif; ?>

            <!-- NEW PREMIUM QUANTITY SELECTOR -->
            <?php if ($product->stock_quantity > 0): ?>
                <div class="quantity-section">
                    <label class="quantity-label">Quantity ♡</label>
                    <div class="quantity-controls">
                        <button type="button" class="qty-btn qty-minus">-</button>
                        <input type="number" class="qty-input" value="1" min="1" max="<?= $product->stock_quantity ?>">
                        <button type="button" class="qty-btn qty-plus">+</button>
                    </div>
                    <span class="stock-info">Available: <?= $product->stock_quantity ?> in stock</span>
                </div>

                <button class="btn-add-to-cart-premium" 
                        data-id="<?= $product->product_id ?>"
                        data-name="<?= encode($product->product_name) ?>"
                        data-price="<?= $product->price ?>"
                        data-max="<?= $product->stock_quantity ?>">
                    🛍️ Add to Cart ♡
                </button>
            <?php else: ?>
                <div class="out-of-stock-premium">Out of Stock 😿</div>
            <?php endif; ?>

            <div class="back-link">
                <a href="products.php">← Back to Products</a>
            </div>
        </div>
    </div>
</div>

<style>
.product-detail-shopee {
    display: flex;
    flex-wrap: flex;
    gap: 2rem;
    background: white;
    padding: 1.5rem;
    border-radius: 20px;
    box-shadow: 0 10px 30px rgba(255,105,180,0.1);
    margin: 2rem 0;
}

.product-image img {
    width: 100%;
    max-width: 450px;
    border-radius: 15px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
}

.product-info h1 {
    font-size: 2rem;
    color: #ff69b4;
    margin-bottom: 1rem;
}

.price-stock {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin: 1rem 0;
    font-size: 1.3rem;
}

.price {
    font-size: 2rem;
    font-weight: bold;
    color: #ff1493;
}

.description {
    margin: 1.5rem 0;
    line-height: 1.8;
    color: #555;
}

.add-to-cart-section {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin: 2rem 0;
    flex-wrap: wrap;
}

.quantity-box {
    display: flex;
    border: 2px solid #ff69b4;
    border-radius: 10px;
    overflow: hidden;
    width: fit-content;
    background: white;
}

.qty-btn {
    width: 50px;
    height: 50px;
    background: #fff0f5;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    transition: all 0.3s;
}

.qty-btn:hover:not(:disabled) {
    background: #ff69b4;
    color: white;
}

.qty-btn:disabled {
    background: #eee;
    color: #ccc;
    cursor: not-allowed;
}

.qty-input {
    width: 70px;
    text-align: center;
    border: none;
    font-size: 1.3rem;
    font-weight: bold;
    padding: 0.5rem;
    background: white;
}

.btn-add-to-cart {
    background: linear-gradient(135deg, #ff69b4, #ff1493);
    color: white;
    border: none;
    padding: 1rem 3rem;
    font-size: 1.3rem;
    font-weight: bold;
    border-radius: 50px;
    cursor: pointer;
    box-shadow: 0 8px 20px rgba(255,20,147,0.4);
    transition: all 0.3s;
    display: flex;
    align-items: center;
    gap: 10px;
}

.btn-add-to-cart:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 25px rgba(255,20,147,0.5);
}

.btn-add-to-cart:active {
    transform: translateY(0);
}

.out-of-stock {
    background: #ffebee;
    color: #c62828;
    padding: 1rem 2rem;
    border-radius: 10px;
    font-weight: bold;
    font-size: 1.2rem;
}

.no-image {
    width: 450px;
    height: 450px;
    background: #fff0f5;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #ff69b4;
    border-radius: 15px;
    border: 3px dashed #ff69b4;
}

@media (max-width: 768px) {
    .product-detail-shopee {
        flex-direction: column;
    }
    .add-to-cart-section {
        justify-content: center;
    }
}
</style>



<?php include '../_foot.php'; ?>