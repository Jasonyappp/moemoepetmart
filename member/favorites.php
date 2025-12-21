<?php
// Create new file: member/favorites.php

require '../_base.php';
require_login();

if (user_role() !== 'member') {
    temp('error', 'Only members can view favorites ♡');
    redirect('products.php');
}

$user_id = current_user()->id;

// Fetch favorite products
$stm = $_db->prepare("
    SELECT p.*, f.added_at 
    FROM favorites f 
    JOIN product p ON f.product_id = p.product_id 
    WHERE f.user_id = ? AND p.is_active = 1
    ORDER BY f.added_at DESC
");
$stm->execute([$user_id]);
$favorites = $stm->fetchAll();

$_title = 'My Favorites ♡ Moe Moe Pet Mart';
include '../_head.php';
?>

<div class="container">
    <h2>My Favorites ♡</h2>

    <?php if (empty($favorites)): ?>
        <div class="empty-purchases">
            <p>No favorite products yet~ Start adding some! ♡</p>
            <a href="products.php" class="btn btn-primary">Browse Products ♡</a>
        </div>
    <?php else: ?>
        <div class="product-grid">
            <?php foreach ($favorites as $p): ?>
                <div class="product-card">
                    <?php if ($p->photo_name): ?>
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
                    
                    <!-- Optional: Remove from favorites button -->
                    <button class="remove-favorite" data-id="<?= $p->product_id ?>" style="margin-top:10px; background:#ff69b4; color:white; border:none; padding:10px; border-radius:10px; cursor:pointer;">
                        Remove from Favorites ♡
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
/* Reuse the same styles from products.php for consistency */
.product-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 2rem; }
.product-card { background: white; border-radius: 15px; padding: 1.5rem; text-align: center; box-shadow: 0 5px 15px rgba(255,105,180,0.1); }
.product-card img { max-width: 100%; height: 200px; object-fit: cover; border-radius: 10px; }
</style>

<script>
// Direct Remove from Favorites – no confirm dialog, instant cute feedback ♡
document.querySelectorAll('.remove-favorite').forEach(btn => {
    btn.addEventListener('click', function(e) {
        e.preventDefault();

        const productId = this.dataset.id;
        const card = this.closest('.product-card');

        // Optional: small hover feedback before action
        this.style.background = '#ff1493';
        this.textContent = 'Removing... ♡';

        fetch('remove_from_favorite.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showFlashMessage('Removed from favorites ♡');

                // Smooth fade-out + slide down animation
                card.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                card.style.opacity = '0';
                card.style.transform = 'translateY(30px) scale(0.95)';

                setTimeout(() => {
                    card.remove();

                    // If no more favorites left, show empty state
                    if (!document.querySelector('.product-card')) {
                        document.querySelector('.product-grid').innerHTML = `
                            <div class="empty-purchases" style="grid-column: 1 / -1; text-align: center; padding: 80px;">
                                <p style="font-size: 1.4rem; color: #ff69b4;">No favorite products yet~ Start adding some! ♡</p>
                                <a href="products.php" class="btn btn-primary" style="margin-top: 20px; padding: 14px 30px; font-size: 1.1rem;">Browse Products ♡</a>
                            </div>
                        `;
                    }
                }, 500);
            } else {
                showFlashMessage(data.message || 'Failed to remove~ Try again ♡');
                this.style.background = '#ff69b4';
                this.textContent = 'Remove from Favorites ♡';
            }
        })
        .catch(() => {
            showFlashMessage('Connection error~ Please try again ♡');
            this.style.background = '#ff69b4';
            this.textContent = 'Remove from Favorites ♡';
        });
    });
});
</script>
<?php include '../_foot.php'; ?>