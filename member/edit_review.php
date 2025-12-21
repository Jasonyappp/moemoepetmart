<?php
require '../_base.php';
require_login();

if (user_role() !== 'member') {
    temp('error', 'Only members can edit reviews ♡');
    redirect('/');
}

$review_id = (int)get('review_id');

if (!$review_id) {
    temp('error', 'Invalid review!');
    redirect('my_purchase.php');
}

// Fetch review (must belong to current user)
$stmt = $_db->prepare("
    SELECT r.*, p.product_name, p.photo_name, o.order_id
    FROM product_reviews r
    JOIN product p ON r.product_id = p.product_id
    JOIN orders o ON r.order_id = o.order_id
    WHERE r.review_id = ? AND r.user_id = ?
");
$stmt->execute([$review_id, current_user()->id]);
$review = $stmt->fetch();

if (!$review) {
    temp('error', 'Review not found or you don\'t have permission to edit it!');
    redirect('my_purchase.php');
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

            // Update review
            $stmt_update = $_db->prepare("
                UPDATE product_reviews 
                SET rating = ?, review_text = ?, updated_at = NOW()
                WHERE review_id = ? AND user_id = ?
            ");
            $stmt_update->execute([$rating, $review_text, $review_id, current_user()->id]);

            // Update product average rating
            $stmt_avg = $_db->prepare("
                UPDATE product 
                SET average_rating = (SELECT AVG(rating) FROM product_reviews WHERE product_id = ?),
                    review_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = ?)
                WHERE product_id = ?
            ");
            $stmt_avg->execute([$review->product_id, $review->product_id, $review->product_id]);

            $_db->commit();

            temp('success', 'Review updated successfully! ♡');
            redirect("../member/product_detail.php?id={$review->product_id}");

        } catch (Exception $e) {
            $_db->rollBack();
            $errors[] = 'Failed to update review: ' . $e->getMessage();
        }
    }

    if (!empty($errors)) {
        foreach ($errors as $err) temp('error', $err);
    }
}

$_title = 'Edit Review ♡ ' . encode($review->product_name);
include '../_head.php';
?>

<div class="container" style="max-width: 800px; margin: 40px auto;">
    <div style="background: white; padding: 40px; border-radius: 25px; box-shadow: 0 10px 30px rgba(255,105,180,0.15);">
        
        <h2 style="text-align: center; color: #ff69b4; margin-bottom: 30px;">Edit Your Review ♡</h2>

        <!-- Product Info -->
        <div style="display: flex; gap: 20px; align-items: center; background: #fff0f5; padding: 20px; border-radius: 15px; margin-bottom: 30px;">
            <?php if ($review->photo_name): ?>
                <img src="/admin/uploads/products/<?= encode($review->photo_name) ?>" 
                     alt="<?= encode($review->product_name) ?>"
                     style="width: 100px; height: 100px; object-fit: cover; border-radius: 12px; border: 3px solid #ff69b4;">
            <?php endif; ?>
            <div>
                <h3 style="color: #ff1493; margin: 0 0 10px 0;"><?= encode($review->product_name) ?></h3>
                <p style="color: #888; margin: 0;">Order #<?= $review->order_id ?></p>
                <p style="color: #888; margin: 5px 0 0 0; font-size: 0.9rem;">
                    Original review: <?= date('M d, Y', strtotime($review->review_date)) ?>
                </p>
            </div>
        </div>

        <form method="post">
            
            <!-- Star Rating -->
            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 1.2rem; font-weight: bold; color: #ff69b4; margin-bottom: 15px;">
                    Your Rating <span style="color: #ff1493;">*</span>
                </label>
                <div class="star-rating" style="display: flex; gap: 10px; font-size: 3rem; justify-content: center;">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <input type="radio" name="rating" value="<?= $i ?>" id="star<?= $i ?>" 
                               <?= $review->rating == $i ? 'checked' : '' ?> required style="display: none;">
                        <label for="star<?= $i ?>" class="star <?= $i <= $review->rating ? 'selected' : '' ?>" data-rating="<?= $i ?>">☆</label>
                    <?php endfor; ?>
                </div>
                <p style="text-align: center; color: #ff69b4; margin-top: 10px;" id="rating-text">
                    <?php 
                    $labels = ['', 'Poor 😢', 'Fair 😐', 'Good 😊', 'Very Good 😄', 'Excellent! 🤩'];
                    echo $labels[$review->rating] . ' ♡';
                    ?>
                </p>
            </div>

            <!-- Review Text -->
            <div style="margin-bottom: 30px;">
                <label style="display: block; font-size: 1.2rem; font-weight: bold; color: #ff69b4; margin-bottom: 10px;">
                    Your Review (Optional)
                </label>
                <textarea name="review_text" 
                          rows="6" 
                          maxlength="1000"
                          placeholder="Share your experience with this product... ♡"
                          style="width: 100%; padding: 15px; border: 3px solid #ffd4e4; border-radius: 15px; font-size: 1rem; resize: vertical;"><?= encode($review->review_text) ?></textarea>
                <small style="color: #888;">Maximum 1000 characters</small>
            </div>

            <!-- Submit Buttons -->
            <div style="text-align: center; margin-top: 40px;">
                <button type="submit" 
                        class="btn-update-review"
                        style="padding: 15px 50px; background: linear-gradient(135deg, #ff69b4, #ff1493); color: white; border: none; border-radius: 50px; font-size: 1.3rem; font-weight: bold; cursor: pointer; box-shadow: 0 8px 20px rgba(255,20,147,0.4); margin: 0 10px; position: relative; overflow: hidden;">
                    <span class="btn-text">Update Review ♡</span>
                </button>
                <a href="../member/product_detail.php?id=<?= $review->product_id ?>" 
                   style="display: inline-block; padding: 15px 50px; background: white; color: #666; text-decoration: none; border-radius: 50px; font-size: 1.3rem; font-weight: bold; margin: 0 10px; border: 2px solid #ddd; transition: all 0.3s;"
                   onmouseover="this.style.background='#f5f5f5'; this.style.borderColor='#999';"
                   onmouseout="this.style.background='white'; this.style.borderColor='#ddd';">
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

/* Update Review Button Animation */
.btn-update-review {
    transition: all 0.3s ease;
}

.btn-update-review:hover {
    transform: translateY(-3px);
    box-shadow: 0 12px 30px rgba(255,20,147,0.6) !important;
}

.btn-update-review:active {
    transform: translateY(-1px);
    box-shadow: 0 5px 15px rgba(255,20,147,0.4) !important;
}

/* Ripple effect */
.btn-update-review::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.5);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-update-review:hover::before {
    width: 300px;
    height: 300px;
}

.btn-text {
    position: relative;
    z-index: 1;
}

/* Shine effect */
@keyframes shine {
    0% {
        left: -100%;
    }
    100% {
        left: 100%;
    }
}

.btn-update-review::after {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 50%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.4),
        transparent
    );
    transition: left 0.5s;
}

.btn-update-review:hover::after {
    animation: shine 1.5s infinite;
}

/* Pulse animation on page load */
@keyframes pulse {
    0%, 100% {
        transform: scale(1);
        box-shadow: 0 8px 20px rgba(255,20,147,0.4);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 12px 30px rgba(255,20,147,0.6);
    }
}

.btn-update-review {
    animation: pulse 2s ease-in-out infinite;
}

.btn-update-review:hover {
    animation: none;
}

</style>

<script>
// Star rating interactive (same as write_review.php)
const stars = document.querySelectorAll('.star');
const ratingText = document.getElementById('rating-text');
const ratingLabels = ['', 'Poor 😢', 'Fair 😐', 'Good 😊', 'Very Good 😄', 'Excellent! 🤩'];

stars.forEach(star => {
    star.addEventListener('mouseenter', function() {
        const rating = this.dataset.rating;
        highlightStars(rating);
        ratingText.textContent = ratingLabels[rating] + ' ♡';
    });

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
</script>

<?php include '../_foot.php'; ?>