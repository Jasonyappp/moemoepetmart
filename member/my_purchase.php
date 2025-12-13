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
        $where .= ' AND o.status = ?';
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
    <h2>My Purchases ♡</h2>

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
            <img src="/images/empty-cart.png" alt="No purchases" style="width: 200px; opacity: 0.8;">
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
                            <?= $order->status ?>
                        </span>
                    </div>
                    
                    <div class="order-items">
                        <p>Items: <?= encode($order->items_summary ?? 'No items') ?></p>
                    </div>
                    
                    <div class="order-total">
                        Total: RM <?= number_format($order->total_amount, 2) ?>
                    </div>
                    
                    <div class="order-actions">
                        <?php if ($order->status === 'Pending Payment'): ?>
                            <a href="pay_order.php?id=<?= $order->order_id ?>" class="btn btn-pay">Pay Now ♡</a>
                        <?php elseif ($order->status === 'Shipped'): ?>
                            <a href="confirm_receive.php?id=<?= $order->order_id ?>" class="btn btn-receive">Confirm Received ♡</a>
                        <?php endif; ?>
                        <a href="order_detail.php?id=<?= $order->order_id ?>" class="btn btn-detail">View Details</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.purchase-tabs {
    display: flex;
    gap: 1rem;
    margin: 2rem 0;
    border-bottom: 2px solid #ff69b4;
    overflow-x: auto;
}

.purchase-tabs a {
    padding: 1rem 2rem;
    text-decoration: none;
    color: #555;
    font-weight: bold;
    position: relative;
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
    height: 2px;
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
</style>

<?php include '../_foot.php'; ?>