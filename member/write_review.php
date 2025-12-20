<?php
require '../_base.php';
require_login();

if (user_role() !== 'member') {
    temp('error', 'Only members can write reviews ♡');
    redirect('/');
}

$order_id = (int)get('order_id');
$product_id = (int)get('product_id');

if (!$order_id || !$product_id) {
    temp('error', 'Invalid order or product!');
    redirect('my_purchase.php');
}

// Verify user owns this order AND order is completed
$stmt = $_db->prepare("
    SELECT o.*, p.product_name, p.photo_name
    FROM orders o
    JOIN order_items oi ON o.order_id = oi.order_id
    JOIN product p ON oi.product_id = p.product_id
    WHERE o.order_id = ? 
      AND o.user_id = ? 
      AND oi.product_id = ?
      AND o.order_status = 'Completed'
");
$stmt->execute([$order_id, current_user()->id, $product_id]);
$order = $stmt->fetch();

if (!$order) {
    temp('error', 'Order not found or not eligible for review!');
    redirect('my_purchase.php');
}

// Check if already reviewed
$stmt_check = $_db->prepare("SELECT review_id FROM product_reviews WHERE user_id = ? AND product_id = ? AND order_id = ?");
$stmt_check->execute([current_user()->id, $product_id, $order_id]);
$existing_review = $stmt_check->fetch();

if ($existing_review) {
    temp('info', 'You have already reviewed this product ♡');
    redirect("edit_review.php?review_id={$existing_review->review_id}");
}

// Handle form submission
if (is_post()) {
    $rating = (int)post('rating');
    $review_text = trim(post('review_text', ''));

    $errors = [];

    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Please select a rating from 1 to 5 stars ♡';
    }

    if (strlen($review_text) > 1000) {
        $errors[] = 'Review text is too long! Maximum 1000 characters ♡';
    }

    if (empty($errors)) {
        try {
            $_db->beginTransaction();

            // Insert review
            $stmt_insert = $_db->prepare("
                INSERT INTO product_reviews (product_id, user_id, order_id, rating, review_text, is_verified_purchase)
                VALUES (?, ?, ?, ?, ?, 1)
            ");
            $stmt_insert->execute([$product_id, current_user()->id, $order_id, $rating, $review_text]);

            // Update product average rating and review count
            $stmt_avg = $_db->prepare("
                UPDATE product 
                SET average_rating = (SELECT AVG(rating) FROM product_reviews WHERE product_id = ?),
                    review_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = ?)
                WHERE product_id = ?
            ");
            $stmt_avg->execute([$product_id, $product_id, $product_id]);

            $_db->commit();

            temp('success', 'Thank you for your review! ♡');
            redirect("../member/product_detail.php?id=$product_id");

        } catch (Exception $e) {
            $_db->rollBack();
            $errors[] = 'Failed to submit review: ' . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $err) temp('error', $err);
    }
}

$_title = 'Write Review ♡ ' . encode($order->product_name);
include '../_head.php';
?>

<div class="container" style="max-width: 800px; margin: 40px auto;">
    <div style="background: white; padding: 40px; border-radius: 25px; box-shadow: 0 10px 30px rgba(255,105,180,0.15);">
        
        <h2 style="text-align: center; color: #ff69b4; margin-bottom: 30px;">Write Your Review ♡</h2>

        <!-- Product Info -->
        <div style="display: flex; gap: 20px; align-items: center; background: #fff0f5; padding: 20px; border-radius: 15px; margin-bottom: 30px;">
            <?php if ($order->photo_name): ?>
                <img src="/admin/uploads/products/<?= encode($order->photo_name) ?>" 
                     alt="<?= encode($order->product_name) ?>"
                     style="width: 100px; height: 100px; object-fit: cover; border-radius: 12px; border: 3px solid #ff69b4;">
            <?php endif; ?>
            <div>
                <h3 style="color: #ff1493; margin: 0 0 10px 0;"><?= encode($order->product_name) ?></h3>
                <p style="color: #888; margin: 0;">Order #<?= $order_id ?></p>
            </div>
        </div>

        <form method="post">
            
            <!-- Star Rating -->
            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 1.2rem; font-weight: bold; color: #ff69b4; margin-bottom: 15px;">
                    Your Rating <span style="color: #ff1493;">*</span>
                </label>
                <div class="star-rating" style="display: flex; gap: 10px; font-size: 3rem; justify-content: center;">
                    <input type="radio" name="rating" value="1" id="star1" required style="display: none;">
                    <label for="star1" class="star" data-rating="1">☆</label>
                    
                    <input type="radio" name="rating" value="2" id="star2" required style="display: none;">
                    <label for="star2" class="star" data-rating="2">☆</label>
                    
                    <input type="radio" name="rating" value="3" id="star3" required style="display: none;">
                    <label for="star3" class="star" data-rating="3">☆</label>
                    
                    <input type="radio" name="rating" value="4" id="star4" required style="display: none;">
                    <label for="star4" class="star" data-rating="4">☆</label>
                    
                    <input type="radio" name="rating" value="5" id="star5" required style="display: none;">
                    <label for="star5" class="star" data-rating="5">☆</label>
                </div>
                <p style="text-align: center; color: #ff69b4; margin-top: 10px;" id="rating-text">Click to rate ♡</p>
            </div>

            <!-- Review Text -->
            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 1.2rem; font-weight: bold; color: #ff69b4; margin-bottom: 10px;">
                    Your Review (Optional)
                </label>
                <textarea name="review_text" 
                          rows="6" 
                          maxlength="1000"
                          placeholder="Share your experience with this product... What did you like? How was the quality? ♡"
                          style="width: 100%; padding: 15px; border: 3px solid #ffd4e4; border-radius: 15px; font-size: 1rem; resize: vertical;"><?= encode(post('review_text', '')) ?></textarea>
                <small style="color: #888;">Maximum 1000 characters</small>
            </div>

            <!-- Submit Buttons -->
            <div style="text-align: center; margin-top: 40px;">
                <button type="submit" 
                        style="padding: 15px 50px; background: linear-gradient(135deg, #ff69b4, #ff1493); color: white; border: none; border-radius: 50px; font-size: 1.3rem; font-weight: bold; cursor: pointer; box-shadow: 0 8px 20px rgba(255,20,147,0.4); margin: 0 10px;">
                    Submit Review ♡
                </button>
                <a href="my_purchase.php" 
                   style="display: inline-block; padding: 15px 50px; background: #ccc; color: white; text-decoration: none; border-radius: 50px; font-size: 1.3rem; font-weight: bold; margin: 0 10px;">
                    Cancel
                </a>
            </div>
        </form>

    </div>
</div>

<style>
.star {
    cursor: pointer;
    transition: all 0.2s;
    color: #ddd;
}
.star:hover,
.star.active {
    color: #ffd700;
    transform: scale(1.1);
}
.star.selected {
    color: #ffd700;
}
</style>

<script>
// Star rating interactive
const stars = document.querySelectorAll('.star');
const ratingText = document.getElementById('rating-text');
const ratingLabels = ['', 'Poor 😢', 'Fair 😐', 'Good 😊', 'Very Good 😄', 'Excellent! 🤩'];

stars.forEach(star => {
    // Hover effect
    star.addEventListener('mouseenter', function() {
        const rating = this.dataset.rating;
        highlightStars(rating);
        ratingText.textContent = ratingLabels[rating] + ' ♡';
    });

    // Click to select
    star.addEventListener('click', function() {
        const rating = this.dataset.rating;
        document.getElementById('star' + rating).checked = true;
        stars.forEach(s => {
            if (s.dataset.rating <= rating) {
                s.classList.add('selected');
            } else {
                s.classList.remove('selected');
            }
        });
        ratingText.textContent = ratingLabels[rating] + ' ♡';
    });
});

// Reset on mouse leave
document.querySelector('.star-rating').addEventListener('mouseleave', function() {
    const checkedRating = document.querySelector('input[name="rating"]:checked');
    if (checkedRating) {
        const rating = checkedRating.value;
        highlightStars(rating);
        ratingText.textContent = ratingLabels[rating] + ' ♡';
    } else {
        stars.forEach(s => s.classList.remove('active'));
        ratingText.textContent = 'Click to rate ♡';
    }
});

function highlightStars(rating) {
    stars.forEach(star => {
        if (star.dataset.rating <= rating) {
            star.classList.add('active');
        } else {
            star.classList.remove('active');
        }
    });
}

// Initialize selected stars on page load
document.addEventListener('DOMContentLoaded', function() {
    const checkedRating = document.querySelector('input[name="rating"]:checked');
    if (checkedRating) {
        highlightStars(checkedRating.value);
    }
});
</script>

<?php include '../_foot.php'; ?>