<?php
require '_base.php';
$_title = 'Welcome to Moe Moe Pet Mart ♡';
include '_head.php';
?>

<div class="hero">
    <div class="hero-content">
        <h1 class="hero-title">Moe Moe Pet Mart</h1>
        <p class="hero-subtitle">Your one-stop shop for the cutest & premium pet supplies ♡</p>
        <div class="hero-buttons">
            <a href="/member/products.php" class="btn btn-primary">Shop Now ♡</a>
        </div>
    </div>
    <div class="hero-paw">🐾</div>
</div>


<?php
// Fetch Top 3 Selling Products (by QUANTITY, not revenue)
$stmt_top_home = $_db->query("
    SELECT p.product_id, p.product_name, p.price, p.photo_name, 
           SUM(oi.quantity) AS total_sold
    FROM order_items oi
    JOIN product p ON oi.product_id = p.product_id
    JOIN orders o ON oi.order_id = o.order_id
    WHERE o.order_status = 'Completed' AND p.is_active = 1
    GROUP BY p.product_id
    ORDER BY total_sold DESC
    LIMIT 3
");
$top_selling_products = $stmt_top_home->fetchAll(PDO::FETCH_ASSOC);

if (empty($top_selling_products)): ?>
    <section class="top-products">
        <div class="container">
            <h2 class="section-title">🔥 Top Selling Products ♡</h2>
            <p style="text-align:center; color:#ff69b4;">No sales data yet ~ Check back soon!</p>
        </div>
    </section>
<?php else: ?>
    <section class="top-products">
        <div class="container">
            <h2 class="section-title">🔥 Top Selling Products ♡</h2>
            <div class="products-grid">
                <?php foreach ($top_selling_products as $index => $product): 
                    $photo_path = !empty($product['photo_name']) 
                        ? '/admin/uploads/products/' . $product['photo_name'] 
                        : '/images/default-product.png';  // fallback image if none
                ?>
                    <div class="product-card">
                        <div class="product-rank">#<?= $index + 1 ?></div>
                        <img src="<?= $photo_path ?>" alt="<?= encode($product['product_name']) ?>">
                        <h4><?= encode($product['product_name']) ?></h4>
                        <p class="price">RM <?= number_format($product['price'], 2) ?></p>
                        <a href="/member/product_detail.php?id=<?= $product['product_id'] ?>" class="btn btn-primary">View Product ♡</a>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="text-align:center; margin-top:30px;">
                <a href="/member/products.php" class="btn btn-secondary">See All Products →</a>
            </div>
        </div>
    </section>
<?php endif; ?>
<section id="products" class="about">
    <div class="container">
        <h2 class="section-title">Why Choose Moe Moe Pet Mart? ♡</h2>
        <div class="about-grid">
            <div class="about-text">
                <p>We hand-pick only the highest quality pet supplies to spoil your furry friends with the very best. From adorable kawaii-style accessories to premium nutrition and comfy essentials — everything is chosen with love for your pet's happiness and health.</p>
                <p>Make every day extra special for your pet! 💕</p>
                <a href="/member/products.php" class="btn btn-primary mt-20">Start Shopping Now ♡</a>
            </div>
        </div>
    </div>
</section>

<?php include '_foot.php'; ?>