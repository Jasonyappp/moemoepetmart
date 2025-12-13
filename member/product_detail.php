<?php
require '../_base.php';
require_login();

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

            <!-- SHOPEE-STYLE QUANTITY + ADD TO CART -->
            <?php if ($product->stock_quantity > 0): ?>
                <div class="add-to-cart-section">
                    <div class="quantity-box">
                        <button type="button" class="qty-btn minus" <?= $product->stock_quantity <= 1 ? 'disabled' : '' ?>>−</button>
                        <input type="number" class="qty-input" value="1" min="1" max="<?= $product->stock_quantity ?>" readonly>
                        <button type="button" class="qty-btn plus" <?= $product->stock_quantity <= 1 ? 'disabled' : '' ?>>+</button>
                    </div>

                    <button class="btn-add-to-cart" 
                            data-id="<?= $product->product_id ?>"
                            data-name="<?= encode($product->product_name) ?>"
                            data-price="<?= $product->price ?>"
                            data-max="<?= $product->stock_quantity ?>">
                        <span class="cart-icon">Add to Cart</span>
                    </button>
                </div>
            <?php else: ?>
                <div class="out-of-stock">Out of Stock~</div>
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
    flex-wrap: wrap;
    gap: 2rem;
    background: white;
    padding: 2rem;
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

<script>
// Shopee-style Quantity Selector + Add to Cart
$(document).ready(function() {
    const $minus = $('.qty-btn.minus');
    const $plus = $('.qty-btn.plus');
    const $input = $('.qty-input');
    const $btn = $('.btn-add-to-cart');
    const maxStock = parseInt($btn.data('max'));

    $minus.on('click', function() {
        let val = parseInt($input.val());
        if (val > 1) {
            $input.val(--val);
        }
    });

    $plus.on('click', function() {
        let val = parseInt($input.val());
        if (val < maxStock) {
            $input.val(++val);
        }
    });

    $input.on('change', function() {
        let val = parseInt(this.value);
        if (isNaN(val) || val < 1) this.value = 1;
        if (val > maxStock) this.value = maxStock;
    });

    $btn.on('click', function() {
        const qty = parseInt($input.val());
        const id = $btn.data('id');
        const name = $btn.data('name');
        const price = $btn.data('price');

        $btn.prop('disabled', true).html('Adding... ♡');

        $.post('add_to_cart.php', {
            product_id: id,
            product_name: name,
            price: price,
            qty: qty
        }, function(res) {
            if (res.success) {
                $('.cart-count, .cart-badge').text(res.total_items).fadeIn(200);
                alert(`Added ${qty} × ${name} to cart! ♡`);
            } else {
                alert(res.message || 'Failed to add to cart~');
            }
        }, 'json')
        .always(function() {
            $btn.prop('disabled', false).html('<span class="cart-icon">Add to Cart</span>');
        });
    });
});
</script>

<?php include '../_foot.php'; ?>