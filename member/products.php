<?php
require '../_base.php';


if (user_role() === 'admin') {
    temp('error', 'Admins cannot shop here! Use member account ♡');
    redirect('/admin.php');
}

$_title = 'Pet Supplies ♡ Moe Moe Pet Mart';

$search = trim(get('search', ''));

$sql = "SELECT p.*, c.category_name 
        FROM product p 
        JOIN category c ON p.category_id = c.category_id 
        WHERE p.is_active = 1";

if ($search !== '') {
    $sql .= " AND (p.product_name LIKE :search OR p.description LIKE :search)";
}
$sql .= " ORDER BY p.product_name";

$stm = $_db->prepare($sql);
$params = $search !== '' ? [':search' => "%$search%"] : [];
$stm->execute($params);
$products = $stm->fetchAll();

include '../_head.php';
?>

<div class="container">
    <h2>Our Adorable Pet Supplies ♡</h2>

    <form method="get" class="search-form">
        <input type="text" name="search" placeholder="Search products..." value="<?= encode($search) ?>">
        <button type="submit">Search ♡</button>
    </form>

    <div class="product-grid">
        <?php if (empty($products)): ?>
            <p>No products found~ Try another search! ♡</p>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <?php if ($p->photo_name): ?>
                    <!----add this for when click product_image can jump to product_detail--->
                        <a href="product_detail.php?id=<?= $p->product_id ?>">
                            <img src="/admin/uploads/products/<?= encode($p->photo_name) ?>" alt="<?= encode($p->product_name) ?>">
                        </a>
                    <?php endif; ?>
                    <a href="product_detail.php?id=<?= $p->product_id ?>">
                        <h3><?= encode($p->product_name) ?></h3>
                        <p class="price">RM <?= number_format($p->price, 2) ?></p>
                        <p>Stock: <?= $p->stock_quantity ?></p>
                    </a>
                    <?php if ($p->stock_quantity > 0): ?>
                        <button class="add-to-cart" data-id="<?= $p->product_id ?>" data-name="<?= encode($p->product_name) ?>" data-price="<?= $p->price ?>">
                            Add to Cart ♡
                        </button>
                    <?php else: ?>
                        <p>Out of Stock~</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php 
    $cart_items = 0;
        if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        $cart_items = array_sum(array_column($_SESSION['cart'], 'qty'));
    }
    $cart_text = $cart_items > 0 ? "View Cart ($cart_items items)" : "View Cart";
    ?>

    <div class="cart-floating-btn">
        <a href="cart.php" class="moe-cart-btn">
            <span class="cart-icon"> </span>
            <span class="cart-text"><?= $cart_text ?></span>
            <?php if ($cart_items > 0): ?>
                <span class="cart-badge"><?= $cart_items ?></span>
            <?php endif; ?>
        </a>
    </div>

</div>


<?php include '../_foot.php'; ?>