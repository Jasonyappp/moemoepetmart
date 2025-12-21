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
$is_favorited = false;
if (is_login() && user_role() === 'member') {
    global $_db;
    $user_id = current_user()->id;
    $stm = $_db->prepare("SELECT favorite_id FROM favorites WHERE user_id = ? AND product_id = ?");
    $stm->execute([$user_id, $id]);
    $is_favorited = (bool)$stm->fetch();
}

// Fetch reviews for this product
$stmt_reviews = $_db->prepare("
    SELECT r.*, u.username, u.profile_pic
    FROM product_reviews r
    JOIN users u ON r.user_id = u.id
    WHERE r.product_id = ?
    ORDER BY r.review_date DESC
");
$stmt_reviews->execute([$id]);
$reviews = $stmt_reviews->fetchAll();

// Calculate rating distribution
$rating_dist = [5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0];
foreach ($reviews as $r) {
    $rating_dist[$r->rating]++;
}

$total_reviews = count($reviews);
$avg_rating = $product->average_rating ?? 0;

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
            
            <!-- Rating Summary (new) -->
            <?php if ($total_reviews > 0): ?>
            <div style="display: flex; align-items: center; gap: 15px; margin: 15px 0; padding: 15px; background: #fff0f5; border-radius: 12px;">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span style="color: #ffd700; font-size: 1.8rem;">★</span>
                    <span style="font-size: 1.5rem; font-weight: bold; color: #ff1493;">
                        <?= number_format($avg_rating, 1) ?>
                    </span>
                </div>
                <div style="color: #666;">
                    <span style="font-weight: bold;"><?= $total_reviews ?></span> 
                    <?= $total_reviews == 1 ? 'review' : 'reviews' ?>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="price-stock">
                <div class="price">RM <?= number_format($product->price, 2) ?></div>
                <div class="stock">Stock: <strong><?= $product->stock_quantity ?></strong></div>
            </div>

            <?php if ($product->description): ?>
                <div class="description">
                    <?= nl2br(encode($product->description)) ?>
                </div>
            <?php endif; ?>

        <?php if ($product->stock_quantity > 0): ?>
    <div class="quantity-section">
        <label class="quantity-label">Quantity ♡</label>
        <div class="quantity-and-favorite">
            <!-- Favorite button on the LEFT -->
            <button class="btn-favorite-small <?= $is_favorited ? 'favorited' : '' ?>" 
                    data-id="<?= $product->product_id ?>"
                    title="<?= $is_favorited ? 'Already in Favorites ♡' : 'Add to Favorites ♡' ?>">
                <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" 
                     fill="<?= $is_favorited ? '#ff69b4' : 'none' ?>" 
                     stroke="#ff69b4" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                </svg>
            </button>

            <!-- Quantity controls next to it -->
            <div class="quantity-controls">
                <button type="button" class="qty-btn qty-minus">-</button>
                <input type="number" class="qty-input" value="1" min="1" max="<?= $product->stock_quantity ?>">
                <button type="button" class="qty-btn qty-plus">+</button>
            </div>
        </div>
        <span class="stock-info">Available: <?= $product->stock_quantity ?> in stock</span>
    </div>

    <button class="btn-add-to-cart-premium" 
            data-id="<?= $product->product_id ?>"
            data-name="<?= encode($product->product_name) ?>"
            data-price="<?= $product->price ?>"
            data-max="<?= $product->stock_quantity ?>">
        🛒 | Add to Cart ♡
    </button>
<?php else: ?>
    <div class="out-of-stock-premium">Out of Stock 😿</div>
<?php endif; ?>

            <div class="back-link">
                <a href="products.php" class="btn-back-products">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M19 12H5M12 19l-7-7 7-7"/>
                    </svg>
                    Back to Products
                </a>
            </div>
        </div>
    </div>

    <!-- ========== PRODUCT REVIEWS SECTION ========== -->
    <div style="background: white; border-radius: 25px; padding: 40px; margin-top: 40px; box-shadow: 0 10px 30px rgba(255,105,180,0.1);">
        <h2 style="color: #ff69b4; margin-bottom: 30px; font-size: 2rem;">Customer Reviews ♡</h2>

        <?php if ($total_reviews > 0): ?>
            <!-- Rating Overview -->
            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 40px; margin-bottom: 40px; padding-bottom: 30px; border-bottom: 2px solid #ffd4e4;">
                
                <!-- Left: Average Rating -->
                <div style="text-align: center; padding: 30px; background: linear-gradient(135deg, #fff0f5, #ffe4e9); border-radius: 20px;">
                    <div style="font-size: 4rem; font-weight: bold; color: #ff1493; margin-bottom: 10px;">
                        <?= number_format($avg_rating, 1) ?>
                    </div>
                    <div style="color: #ffd700; font-size: 2rem; margin-bottom: 10px;">
                        <?php 
                        $full_stars = floor($avg_rating);
                        $half_star = ($avg_rating - $full_stars) >= 0.5;
                        for ($i = 0; $i < $full_stars; $i++) echo '★';
                        if ($half_star) echo '☆';
                        for ($i = 0; $i < (5 - $full_stars - ($half_star ? 1 : 0)); $i++) echo '☆';
                        ?>
                    </div>
                    <div style="color: #666; font-size: 1.1rem;">
                        Based on <?= $total_reviews ?> <?= $total_reviews == 1 ? 'review' : 'reviews' ?>
                    </div>
                </div>

                <!-- Right: Rating Distribution -->
                <div>
                    <?php for ($i = 5; $i >= 1; $i--): 
                        $count = $rating_dist[$i];
                        $percentage = $total_reviews > 0 ? ($count / $total_reviews) * 100 : 0;
                    ?>
                    <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 12px;">
                        <span style="width: 80px; color: #666;"><?= $i ?> star<?= $i > 1 ? 's' : '' ?></span>
                        <div style="flex: 1; height: 20px; background: #f0f0f0; border-radius: 10px; overflow: hidden;">
                            <div style="width: <?= $percentage ?>%; height: 100%; background: linear-gradient(90deg, #ffd700, #ffed4e); transition: width 0.3s;"></div>
                        </div>
                        <span style="width: 60px; text-align: right; color: #666; font-weight: bold;">
                            <?= $count ?>
                        </span>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- Individual Reviews -->
            <div style="margin-top: 40px;">
                <h3 style="color: #ff69b4; margin-bottom: 25px; font-size: 1.5rem;">All Reviews</h3>
                
                <?php foreach ($reviews as $review): ?>
                <div style="border-bottom: 1px solid #ffd4e4; padding: 25px 0;">
                    <div style="display: flex; gap: 20px; align-items: flex-start;">
                        <!-- User Avatar -->
                        <div>
                            <?php if ($review->profile_pic && file_exists('../' . $review->profile_pic)): ?>
                                <img src="/<?= encode($review->profile_pic) ?>" 
                                     style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover; border: 3px solid #ff69b4;">
                            <?php else: ?>
                                <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #ff69b4, #ff8fab); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; font-weight: bold;">
                                    <?= strtoupper(substr($review->username, 0, 1)) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Review Content -->
                        <div style="flex: 1;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <div>
                                    <strong style="color: #ff1493; font-size: 1.1rem;">
                                        <?= encode($review->username) ?>
                                    </strong>
                                    <?php if ($review->is_verified_purchase): ?>
                                        <span style="display: inline-block; background: #e8f5e9; color: #2e7d32; padding: 4px 12px; border-radius: 12px; font-size: 0.85rem; margin-left: 10px;">
                                            ✓ Verified Purchase
                                        </span>
                                    <?php endif; ?>
                                </div>
                                <span style="color: #888; font-size: 0.9rem;">
                                    <?= date('M d, Y h:i A', strtotime($review->review_date)) ?>
                                </span>
                            </div>

                            <!-- Star Rating -->
                            <div style="color: #ffd700; font-size: 1.3rem; margin-bottom: 12px;">
                                <?php for ($i = 0; $i < $review->rating; $i++) echo '★'; ?>
                                <?php for ($i = $review->rating; $i < 5; $i++) echo '☆'; ?>
                            </div>

                            <!-- Review Text -->
                            <?php if ($review->review_text): ?>
                                <p style="color: #333; line-height: 1.6; margin: 15px 0;">
                                    <?= nl2br(encode($review->review_text)) ?>
                                </p>
                            <?php endif; ?>

                            <?php if ($review->updated_at && $review->updated_at != '0000-00-00 00:00:00'): ?>
                                <p style="color: #888; font-size: 0.85rem; font-style: italic; margin-top: 10px;">
                                    Edited on <?= date('M d, Y h:i A', strtotime($review->updated_at)) ?>
                                </p>
                            <?php endif; ?>

                            <!-- Admin Reply -->
                            <?php if ($review->admin_reply): ?>
                                <div style="background: #fff8fb; border-left: 4px solid #ff69b4; padding: 15px; margin-top: 15px; border-radius: 8px;">
                                    <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 10px;">
                                        <span style="background: #ff69b4; color: white; padding: 4px 12px; border-radius: 15px; font-size: 0.9rem; font-weight: bold;">
                                            👑 Admin Reply
                                        </span>
                                        <span style="color: #888; font-size: 0.85rem;">
                                            <?= date('M d, Y h:i A', strtotime($review->admin_reply_date)) ?>
                                        </span>
                                    </div>
                                    <p style="color: #555; line-height: 1.5;">
                                        <?= nl2br(encode($review->admin_reply)) ?>
                                    </p>
                                </div>
                            <?php endif; ?>

                            <!-- Edit button for own review -->
                            <?php if (is_login() && $review->user_id == current_user()->id): ?>
                                <div style="margin-top: 15px;">
                                    <a href="edit_review.php?review_id=<?= $review->review_id ?>" 
                                       style="color: #ff69b4; text-decoration: underline; font-size: 0.95rem;">
                                        Edit your review
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

        <?php else: ?>
            <!-- No Reviews Yet -->
            <div style="text-align: center; padding: 60px 20px; color: #999;">
                <div style="font-size: 5rem; margin-bottom: 20px;">💭</div>
                <p style="font-size: 1.3rem; color: #ff69b4;">No reviews yet~</p>
                <p style="font-size: 1.1rem; margin-top: 10px;">Be the first to share your experience! ♡</p>
            </div>
        <?php endif; ?>
    </div>
    <!-- ========== END REVIEWS SECTION ========== -->
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
.quantity-and-favorite {
    display: flex;
    align-items: center;
    gap: 15px; /* space between heart and quantity box */
    margin: 12px 0;
}

.btn-favorite-small {
    background: none;
    border: none;
    cursor: pointer;
    padding: 8px;
    border-radius: 50%;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 48px;
    height: 48px;
}

.btn-favorite-small:hover {
    background: #fff0f5;
    transform: scale(1.15);
}



.btn-favorite-small svg {
    transition: all 0.3s ease;
    filter: drop-shadow(0 2px 6px rgba(255,105,180,0.3));
}

/* ========== BACK TO PRODUCTS BUTTON - COMFORTABLE DESIGN ========== */
.back-link {
    margin-top: 2.5rem;
    padding-top: 2rem;
    border-top: 3px dashed #ffeef8;
}

.btn-back-products {
    display: inline-flex;
    align-items: center;
    gap: 12px;
    background: linear-gradient(135deg, #fff5f9, #ffeef8);
    color: #ff69b4;
    padding: 16px 32px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    font-size: 1.1rem;
    border: 3px solid #ff69b4;
    box-shadow: 0 8px 20px rgba(255, 105, 180, 0.2);
    transition: all 0.4s ease;
}

.btn-back-products:hover {
    background: linear-gradient(135deg, #ff69b4, #ff1493);
    color: white;
    transform: translateX(-8px) translateY(-3px);
    box-shadow: 0 12px 30px rgba(255, 20, 147, 0.4);
    border-color: #fff0f5;
}

.btn-back-products svg {
    transition: transform 0.3s ease;
}

.btn-back-products:hover svg {
    transform: translateX(-5px);
}

/* Mobile responsive */
@media (max-width: 768px) {
    .btn-back-products {
        width: 100%;
        justify-content: center;
        padding: 14px 24px;
        font-size: 1rem;
    }
}
</style>

<script>
// Unified Favorite Toggle – uses showFlashMessage() like Add to Cart ♡
document.querySelectorAll('.btn-favorite-small').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        const btn = this;
        const svg = btn.querySelector('svg');
        const productId = btn.dataset.id;
        const isFavorited = btn.classList.contains('favorited');

        // Visual feedback: pulse
        btn.style.transform = 'scale(1.3)';
        setTimeout(() => btn.style.transform = '', 200);

        const url = isFavorited ? 'remove_from_favorite.php' : 'add_to_favorite.php';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ product_id: productId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (isFavorited) {
                    // Removed
                    btn.classList.remove('favorited');
                    svg.setAttribute('fill', 'none');
                    svg.setAttribute('stroke', '#ff69b4');
                    btn.title = 'Add to Favorites ♡';
                    showFlashMessage('Removed from favorites~ ♡');
                } else {
                    // Added
                    btn.classList.add('favorited');
                    svg.setAttribute('fill', '#ff69b4');
                    svg.setAttribute('stroke', '#ff1493');
                    btn.title = 'Remove from Favorites ♡';
                    showFlashMessage('Added to favorites! ♡');
                }
            } else {
                showFlashMessage(data.message || 'Oops! Something went wrong ♡');
            }
        })
        .catch(err => {
            console.error(err);
            showFlashMessage('Connection error~ Please try again ♡');
        });
    });
});
</script>

<?php include '../_foot.php'; ?>