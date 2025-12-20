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
                        style="padding: 15px 50px; background: linear-gradient(135deg, #ff69b4, #ff1493); color: white; border: none; border-radius: 50px; font-size: 1.3rem; font-weight: bold; cursor: pointer; box-shadow: 0 8px 20px rgba(255,20,147,0.4); margin: 0 10px;">
                    Update Review ♡
                </button>
                <a href="../member/product_detail.php?id=<?= $review->product_id ?>" 
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