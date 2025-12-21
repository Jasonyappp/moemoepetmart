<?php
require '../_base.php';
require_login();
require_admin();

$_title = 'Review Management - Admin';

// Handle admin reply submission
if (is_post() && post('action') === 'reply') {
    $review_id = (int)post('review_id');
    $admin_reply = trim(post('admin_reply'));

    if ($review_id && $admin_reply) {
        $stmt = $_db->prepare("
            UPDATE product_reviews 
            SET admin_reply = ?, admin_reply_date = NOW() 
            WHERE review_id = ?
        ");
        $stmt->execute([$admin_reply, $review_id]);
        temp('success', 'Reply posted successfully ♡');
        redirect('review_list.php');
    }
}

// Handle delete review
if (is_post() && post('action') === 'delete') {
    $review_id = (int)post('review_id');
    
    if ($review_id) {
        try {
            $_db->beginTransaction();
            
            // Get product_id before deleting
            $stmt = $_db->prepare("SELECT product_id FROM product_reviews WHERE review_id = ?");
            $stmt->execute([$review_id]);
            $product_id = $stmt->fetchColumn();
            
            // Delete review
            $_db->prepare("DELETE FROM product_reviews WHERE review_id = ?")->execute([$review_id]);
            
            // Update product average rating
            $stmt_avg = $_db->prepare("
                UPDATE product 
                SET average_rating = COALESCE((SELECT AVG(rating) FROM product_reviews WHERE product_id = ?), 0),
                    review_count = (SELECT COUNT(*) FROM product_reviews WHERE product_id = ?)
                WHERE product_id = ?
            ");
            $stmt_avg->execute([$product_id, $product_id, $product_id]);
            
            $_db->commit();
            temp('success', 'Review deleted successfully ♡');
        } catch (Exception $e) {
            $_db->rollBack();
            temp('error', 'Failed to delete review');
        }
        redirect('review_list.php');
    }
}

// Fetch all reviews with filters
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = 10;
$offset = ($page - 1) * $limit;

$filter_rating = $_GET['rating'] ?? 'all';
$filter_replied = $_GET['replied'] ?? 'all';
$search = trim($_GET['search'] ?? '');

$where = 'WHERE 1=1';
$params = [];

if ($filter_rating !== 'all') {
    $where .= ' AND r.rating = ?';
    $params[] = (int)$filter_rating;
}

if ($filter_replied === 'yes') {
    $where .= ' AND r.admin_reply IS NOT NULL';
} elseif ($filter_replied === 'no') {
    $where .= ' AND r.admin_reply IS NULL';
}

if ($search !== '') {
    $where .= ' AND (p.product_name LIKE ? OR u.username LIKE ?)';
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Count total
$count_stmt = $_db->prepare("
    SELECT COUNT(*) 
    FROM product_reviews r
    JOIN product p ON r.product_id = p.product_id
    JOIN users u ON r.user_id = u.id
    $where
");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = max(1, ceil($total / $limit));

// Fetch reviews
$sql = "
    SELECT r.*, p.product_name, p.photo_name, u.username
    FROM product_reviews r
    JOIN product p ON r.product_id = p.product_id
    JOIN users u ON r.user_id = u.id
    $where
    ORDER BY r.review_date DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $_db->prepare($sql);
$stmt->execute($params);
$reviews = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?> • Moe Moe Pet Mart</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        .review-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(255,105,180,0.1);
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #ffd4e4;
        }
        .stars {
            color: #ffd700;
            font-size: 1.3rem;
        }
        .reply-form {
            margin-top: 20px;
            padding: 20px;
            background: #fff8fb;
            border-radius: 15px;
            border: 2px solid #ffd4e4;
        }
        .reply-form textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #ff69b4;
            border-radius: 12px;
            font-size: 1rem;
            resize: vertical;
            min-height: 100px;
        }
        .admin-reply-box {
            margin-top: 15px;
            padding: 20px;
            background: #e8f5e9;
            border-left: 4px solid #4caf50;
            border-radius: 10px;
        }
    </style>
</head>
<body>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="logo"><h2>MoeMoePet</h2></div>
        <ul>
            <li><a href="/admin.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
            <li><a href="/admin/product_list.php"><i class="fas fa-box"></i> Products</a></li>
            <li><a href="/admin/member_list.php"><i class="fas fa-users"></i> Members</a></li>
            <li><a href="/admin/order_list.php"><i class="fas fa-shopping-cart"></i> Orders</a></li>
            <li><a href="/admin/review_list.php" class="active"><i class="fas fa-star"></i> Reviews</a></li>
            <li><a href="/admin/chat_list.php"><i class="fas fa-comments"></i> Chats</a></li>
            <li><a href="/admin/report.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
            <li><a href="/admin/profile.php"><i class="fas fa-user-cog"></i> My Profile ♛</a></li>
            <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <h1>Review Management ♡</h1>
            <div class="admin-user">
                <i class="fas fa-user-circle"></i>
                <span><?= encode($_SESSION['user']) ?></span>
            </div>
        </header>

        <!-- Filters -->
        <div class="toolbar">
            <input type="text" name="search" placeholder="Search product or username..." value="<?= encode($search) ?>">
            
            <select name="rating">
                <option value="all" <?= $filter_rating === 'all' ? 'selected' : '' ?>>All Ratings</option>
                <option value="5" <?= $filter_rating === '5' ? 'selected' : '' ?>>5 Stars</option>
                <option value="4" <?= $filter_rating === '4' ? 'selected' : '' ?>>4 Stars</option>
                <option value="3" <?= $filter_rating === '3' ? 'selected' : '' ?>>3 Stars</option>
                <option value="2" <?= $filter_rating === '2' ? 'selected' : '' ?>>2 Stars</option>
                <option value="1" <?= $filter_rating === '1' ? 'selected' : '' ?>>1 Star</option>
            </select>

            <select name="replied">
                <option value="all" <?= $filter_replied === 'all' ? 'selected' : '' ?>>All Reviews</option>
                <option value="no" <?= $filter_replied === 'no' ? 'selected' : '' ?>>Not Replied</option>
                <option value="yes" <?= $filter_replied === 'yes' ? 'selected' : '' ?>>Replied</option>
            </select>

            <button onclick="applyFilters()">Apply Filters</button>
        </div>

        <!-- Reviews List -->
        <div style="margin-top: 30px;">
            <?php if (empty($reviews)): ?>
                <div style="text-align: center; padding: 80px; color: #999;">
                    <div style="font-size: 5rem; margin-bottom: 20px;">💭</div>
                    <p style="font-size: 1.3rem;">No reviews found~</p>
                </div>
            <?php else: ?>
                <?php foreach ($reviews as $review): ?>
                <div class="review-card">
                    <div class="review-header">
                        <div>
                            <h3 style="color: #ff1493; margin: 0 0 8px 0;"><?= encode($review->product_name) ?></h3>
                            <p style="margin: 5px 0; color: #666;">
                                By <strong><?= encode($review->username) ?></strong> • 
                                <?= date('M d, Y h:i A', strtotime($review->review_date)) ?>
                            </p>
                        </div>
                        <div style="text-align: right;">
                            <div class="stars">
                                <?php for ($i = 0; $i < $review->rating; $i++) echo '★'; ?>
                                <?php for ($i = $review->rating; $i < 5; $i++) echo '☆'; ?>
                            </div>
                            <span style="color: #888; font-size: 0.9rem;">
                                <?= $review->rating ?> / 5
                            </span>
                        </div>
                    </div>

                    <!-- Product Image -->
                    <?php if ($review->photo_name): ?>
                        <img src="/admin/uploads/products/<?= encode($review->photo_name) ?>" 
                             style="width: 80px; height: 80px; object-fit: cover; border-radius: 10px; margin-bottom: 15px;">
                    <?php endif; ?>

                    <!-- Review Text -->
                    <?php if ($review->review_text): ?>
                        <div style="background: #fff0f5; padding: 20px; border-radius: 12px; margin: 15px 0; line-height: 1.6;">
                            <?= nl2br(encode($review->review_text)) ?>
                        </div>
                    <?php else: ?>
                        <p style="color: #999; font-style: italic;">No review text provided</p>
                    <?php endif; ?>

                    <!-- Existing Admin Reply -->
                    <?php if ($review->admin_reply): ?>
                        <div class="admin-reply-box">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                <strong style="color: #2e7d32;">👑 Your Reply:</strong>
                                <span style="color: #666; font-size: 0.9rem;">
                                    <?= date('M d, Y h:i A', strtotime($review->admin_reply_date)) ?>
                                </span>
                            </div>
                            <p style="margin: 0; color: #555; line-height: 1.5;">
                                <?= nl2br(encode($review->admin_reply)) ?>
                            </p>
                        </div>
                    <?php endif; ?>

                    <!-- Reply Form -->
                    <div class="reply-form">
                        <!-- ✅ FIXED CODE - Reply and Delete forms are now SEPARATE -->

                        <!-- Reply Form -->
                        <form method="post">
                            <input type="hidden" name="action" value="reply">
                            <input type="hidden" name="review_id" value="<?= $review->review_id ?>">
                            <label style="display: block; font-weight: bold; color: #ff69b4; margin-bottom: 10px;">
                                <?= $review->admin_reply ? 'Update' : 'Add' ?> Admin Reply:
                            </label>
                            <textarea name="admin_reply" placeholder="Write your reply to this review... ♡"><?= encode($review->admin_reply ?? '') ?></textarea>
                            <div style="margin-top: 15px;">
                                <button type="submit" 
                                    style="padding: 12px 30px; background: linear-gradient(135deg, #ff69b4, #ff1493); color: white; border: none; border-radius: 25px; font-weight: bold; cursor: pointer;">
                                <?= $review->admin_reply ? 'Update Reply' : 'Post Reply' ?> ♡
                                </button>
                            </div>
                        </form>

                        <!-- Delete Review Form (SEPARATE - NOT nested!) -->
                        <form method="post" style="margin-top: 15px;" 
                                onsubmit="return confirm('Delete this review permanently? This cannot be undone!');">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="review_id" value="<?= $review->review_id ?>">
                            <button type="submit" 
                                    style="padding: 12px 30px; background: #d32f2f; color: white; border: none; border-radius: 25px; font-weight: bold; cursor: pointer;">
                                Delete Review
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>

                <!-- Pagination -->
                <?php if ($total > $limit): ?>
                <div class="pagination" style="margin-top: 30px; text-align: center;">
                    <span>Page <?= $page ?> of <?= $total_pages ?></span>
                    <?php if ($page > 1): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>">Next</a>
                    <?php endif; ?>
                    <span style="margin-left: 20px;">
                        <?= $offset + 1 ?>–<?= min($offset + $limit, $total) ?> of <?= $total ?> reviews
                    </span>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
function applyFilters() {
    const search = document.querySelector('[name="search"]').value.trim();
    const rating = document.querySelector('[name="rating"]').value;
    const replied = document.querySelector('[name="replied"]').value;

    const url = new URL(location);
    url.searchParams.set('search', search);
    url.searchParams.set('rating', rating);
    url.searchParams.set('replied', replied);
    url.searchParams.set('page', 1);
    location = url.toString();
}
</script>

</body>
</html>