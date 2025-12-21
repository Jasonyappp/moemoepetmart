<?php
// member/my_purchase.php
require '../_base.php';
require_login();

$_title = 'My Purchases ♡ Moe Moe Pet Mart';
include '../_head.php';

$user = current_user();
$tab = get('tab', 'all');

$statuses = [
    'all' => 'All',
    'to_pay' => 'To Pay',
    'to_ship' => 'To Ship',
    'to_receive' => 'To Receive',
    'completed' => 'Completed',
    'cancelled' => 'Cancelled',
    'return_refund' => 'Return/Refund'
];

$current_status = $statuses[$tab] ?? 'All';

$where = 'WHERE o.user_id = ?';
$params = [$user->id];

if ($tab !== 'all') {
    switch ($tab) {
        case 'to_pay':
            $status = 'Pending Payment';
            break;
        case 'to_ship':
            $status = 'To Ship';
            break;
        case 'to_receive':
            $status = 'Shipped';
            break;
        case 'completed':
            $status = 'Completed';
            break;
        case 'cancelled':
            $status = 'Cancelled';
            break;
        case 'return_refund':
            $status = 'Return/Refund';
            break;
        default:
            $status = null;
    }
    if ($status) {
        $where .= ' AND o.order_status = ?';
        $params[] = $status;
    }
}

$sql = "SELECT o.*, GROUP_CONCAT(CONCAT(oi.quantity, ' x ', p.product_name) SEPARATOR ', ') AS items_summary
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        LEFT JOIN product p ON oi.product_id = p.product_id
        $where
        GROUP BY o.order_id
        ORDER BY o.order_date DESC";

$stm = $_db->prepare($sql);
$stm->execute($params);
$orders = $stm->fetchAll();
?>

<div class="container">
    <div style="display: flex; justify-content: center; align-items: center; position: relative; margin-bottom: 2rem; height: 60px;">

        <h2>My Purchases ♡</h2>
        <a href="?tab=completed" style="color:#666; text-decoration:none; font-size:1rem; font-weight:normal; white-space: nowrap; margin-left: 380px;">
                View Order History >
        </a>
    </div>

    <!-- Tabs like Shopee -->
    <div class="purchase-tabs">
        <?php foreach ($statuses as $key => $label): ?>
            <a href="?tab=<?= $key ?>" class="<?= $tab === $key ? 'active' : '' ?>">
                <?= $label ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if (empty($orders)): ?>
        <div class="empty-purchases">       
            <div style="font-size: 5rem; margin-bottom: 20px;">🛒</div>
            <p>No purchases in this category yet~ ♡</p>
            <a href="products.php" class="btn btn-primary">Shop Now ♡</a>
        </div>
    <?php else: ?>
        <div class="purchase-list">
            <?php foreach ($orders as $order): ?>
                <div class="purchase-card">
                    <div class="order-header">
                        <span class="order-id">Order #<?= $order->order_id ?></span>
                        <span class="order-date"><?= date('M d, Y', strtotime($order->order_date)) ?></span>
                        <span class="order-status <?= strtolower(str_replace(' ', '-', $order->status)) ?>">
                            <?= $order->order_status ?>
                        </span>
                    </div>
                    
                    <div class="order-items">
                        <p>Items: <?= encode($order->items_summary ?? 'No items') ?></p>
                    </div>
                    
                    <div class="order-total">
                        Total: RM <?= number_format($order->total_amount, 2) ?>
                    </div>
                    
                    <div class="order-actions">
                        <?php if ($order->order_status === 'Pending Payment'): ?>
                            <a href="cancel_order.php?id=<?= $order->order_id ?>" class="btn btn-cancel">Cancel Order ♡</a>
                        <?php elseif ($order->order_status === 'Shipped'): ?>
                            <a href="confirm_receive.php?id=<?= $order->order_id ?>" class="btn btn-receive">Confirm Received ♡</a>
                        <?php elseif ($order->order_status === 'Completed'): ?>
                            <!-- Show products in this order with review buttons -->
                            <?php
                            $stmt_items = $_db->prepare("
                                SELECT oi.product_id, p.product_name, 
                                       pr.review_id, 
                                       CASE WHEN pr.review_id IS NOT NULL THEN 1 ELSE 0 END AS has_review
                                FROM order_items oi
                                JOIN product p ON oi.product_id = p.product_id
                                LEFT JOIN product_reviews pr ON pr.product_id = oi.product_id 
                                    AND pr.user_id = ? 
                                    AND pr.order_id = ?
                                WHERE oi.order_id = ?
                            ");
                            $stmt_items->execute([$user->id, $order->order_id, $order->order_id]);
                            $order_items = $stmt_items->fetchAll();
                            ?>
        
                            <?php if (!empty($order_items)): ?>
                                <div class="review-section">
                                    <div class="review-section-title">Rate your purchase</div>
                                    <?php foreach ($order_items as $item): ?>
                                        <div class="review-item">
                                            <span class="review-product-name"><?= encode($item->product_name) ?></span>
                                            <?php if ($item->has_review): ?>
                                                <div style="display: flex; gap: 10px; align-items: center;">
                                                    <span class="review-status-reviewed">Reviewed</span>
                                                    <a href="edit_review.php?review_id=<?= $item->review_id ?>" 
                                                       class="btn-edit-review">
                                                        Edit your review
                                                    </a>
                                                </div>
                                            <?php else: ?>
                                                <a href="write_review.php?order_id=<?= $order->order_id ?>&product_id=<?= $item->product_id ?>" 
                                                   class="btn-write-review">
                                                    Write Review ♡
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
        
                            <!-- Request Return and View Details on same line -->
                            <div class="action-buttons-row">
                                <a href="request_return.php?id=<?= $order->order_id ?>" class="btn btn-return">Request Return/Refund ♡</a>
                                <a href="order_detail.php?id=<?= $order->order_id ?>" class="btn btn-detail">View Details</a>
                            </div>
                        <?php else: ?>
                            <a href="order_detail.php?id=<?= $order->order_id ?>" class="btn btn-detail">View Details</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>



<style>
.purchase-tabs {
    display: flex;
    gap: 0rem;
    margin: 2rem 0;
    border-bottom: 1px solid #ff69b4;
    border-left: 1px solid #ffbae0;
    border-top: 1px solid #ffbae0;
    background: #fff0f5;
}

.purchase-tabs a {
    padding: 1rem 2rem;
    text-decoration: none;
    color: #555;
    font-weight: bold;
    position: relative;
    border-right: 1px solid #ffbae0;
}

.purchase-tabs a.active {
    color: #ff1493;
}

.purchase-tabs a.active::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 100%;
    height: 5px;
    background: #ff1493;
}

.purchase-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    margin: 1rem 0;
    box-shadow: 0 5px 15px rgba(255,105,180,0.1);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1rem;
    font-size: 1.1rem;
}

.order-id {
    font-weight: bold;
    color: #ff69b4;
}

.order-status {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.9rem;
    text-transform: uppercase;
}

.order-status.pending-payment { background: #fff3e0; color: #ef6c00; }
.order-status.to-ship { background: #e3f2fd; color: #1565c0; }
.order-status.shipped { background: #e8f5e9; color: #2e7d32; }
.order-status.completed { background: #f1f8e9; color: #558b2f; }
.order-status.cancelled { background: #ffebee; color: #c62828; }
.order-status.return-refund { background: #fffde7; color: #f57f17; }

.order-items {
    margin: 1rem 0;
    color: #555;
}

.order-total {
    font-size: 1.3rem;
    font-weight: bold;
    color: #ff1493;
    text-align: right;
    margin: 1rem 0;
}

.order-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    flex-wrap: wrap;
}

.btn-pay {
    background: #ff5722;
    color: white;
}

.btn-receive {
    background: #4caf50;
    color: white;
}

.btn-detail {
    background: #2196f3;
    color: white;
}

.empty-purchases {
    text-align: center;
    padding: 4rem 0;
    color: #888;
}

.empty-purchases p {
    font-size: 1.2rem;
    margin: 1rem 0;
}

.review-section {
    background: #fff0f5;
    border: 2px solid #ff69b4;
    border-radius: 15px;
    padding: 20px;
    margin: 15px 0;
}

.review-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.1rem;
    font-weight: bold;
    color: #ff1493;
    margin-bottom: 20px;
    font-style: italic;
}

.review-section-title::before {
    content: '📝';
    font-size: 1.3rem;
}

.review-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px 20px;
    background: white;
    border-radius: 12px;
    margin-bottom: 12px;
    box-shadow: 0 2px 8px rgba(255, 105, 180, 0.1);
    gap: 20px; /* Added gap between elements */
}

.review-item:last-child {
    margin-bottom: 0;
}

.review-product-name {
    flex: 1;
    font-size: 1rem;
    color: #333;
    padding-right: 15px; /* Added padding to create space */
    min-width: 0; /* Allow text to wrap if needed */
}

.review-status-reviewed {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #e8f5e9;
    color: #2e7d32;
    padding: 8px 16px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: bold;
    white-space: nowrap; /* Prevent wrapping */
    flex-shrink: 0; /* Don't shrink */
}

.review-status-reviewed::before {
    content: '✓';
    font-weight: bold;
}

.btn-write-review {
    background: linear-gradient(135deg, #ff69b4, #ff1493);
    color: white;
    padding: 10px 24px;
    border-radius: 25px;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: bold;
    transition: all 0.3s;
    white-space: nowrap; /* Prevent text wrapping */
    flex-shrink: 0; /* Don't shrink the button */
    box-shadow: 0 4px 12px rgba(255, 20, 147, 0.3);
}

.btn-write-review:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(255, 20, 147, 0.4);
}

.btn-edit-review {
    background: white;
    color: #ff69b4;
    padding: 8px 20px;
    border-radius: 20px;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: bold;
    border: 2px solid #ff69b4;
    transition: all 0.3s;
    white-space: nowrap;
    flex-shrink: 0;
}

.btn-edit-review:hover {
    background: #ff69b4;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(255, 105, 180, 0.3);
}

/* Responsive design for mobile */
@media (max-width: 768px) {
    .review-item {
        flex-direction: column;
        align-items: flex-start;
        gap: 12px;
    }
    
    .review-product-name {
        padding-right: 0;
        width: 100%;
    }
    
    .btn-write-review,
    .review-status-reviewed {
        align-self: flex-end;
    }
}
</style>

<?php include '../_foot.php'; ?>