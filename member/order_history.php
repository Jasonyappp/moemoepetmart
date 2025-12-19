<?php
// member/order_history.php
require '../_base.php';
require_login();

$_title = 'Order History ♡ Moe Moe Pet Mart';
include '../_head.php';

$user = current_user();

// Fetch all orders (latest first)
$sql = "SELECT o.*, GROUP_CONCAT(CONCAT(oi.quantity, ' x ', p.product_name) SEPARATOR ', ') AS items_summary
        FROM orders o
        LEFT JOIN order_items oi ON o.order_id = oi.order_id
        LEFT JOIN product p ON oi.product_id = p.product_id
        WHERE o.user_id = ?
        GROUP BY o.order_id
        ORDER BY o.order_date DESC";

$stm = $_db->prepare($sql);
$stm->execute([$user->id]);
$orders = $stm->fetchAll();
?>

<div class="container">
    <h2>Order History ♡</h2>

    <?php if (empty($orders)): ?>
        <div class="empty-purchases">
            <img src="/images/empty-cart.png" alt="No history" style="width: 200px; opacity: 0.8;">
            <p>No order history yet~ Start shopping! ♡</p>
            <a href="products.php" class="btn btn-primary">Shop Now ♡</a>
        </div>
    <?php else: ?>
        <div class="purchase-list">
            <?php foreach ($orders as $order): ?>
                <div class="purchase-card">
                    <div class="order-header">
                        <span class="order-id">Order #<?= $order->order_id ?></span>
                        <span class="order-date"><?= date('M d, Y H:i', strtotime($order->order_date)) ?></span>
                        <span class="order-status <?= strtolower(str_replace(' ', '-', $order->order_status)) ?>">
                            <?= $order->order_status ?>
                        </span>
                    </div>
                    
                    <div class="order-items">
                        <p>Items Summary: <?= encode($order->items_summary ?? 'No items') ?></p>
                    </div>
                    
                    <div class="order-total">
                        Total: RM <?= number_format($order->total_amount, 2) ?>
                    </div>

                    <!-- Expandable Full Details -->
                    <details class="order-details">
                        <summary>View Full Details ♡</summary>
                        <div class="details-content">
                            <?php if ($order->payment_method): ?>
                                <p><strong>Paid with:</strong> 
                                    <?= $order->payment_method === 'Credit/Debit Card' ? 'Card ending ****' . ($order->card_last4 ?? '') : encode($order->payment_method) ?>
                                </p>
                            <?php endif; ?>

                            <h4>Items Purchased</h4>
                            <table class="items-table">
                                <tr>
                                    <th>Product</th>
                                    <th>Photo</th>
                                    <th>Price</th>
                                    <th>Qty</th>
                                    <th>Subtotal</th>
                                </tr>
                                <?php 
                                // Fetch items for this order
                                $stm_items = $_db->prepare('
                                    SELECT oi.*, p.product_name, p.photo_name 
                                    FROM order_items oi 
                                    JOIN product p ON oi.product_id = p.product_id 
                                    WHERE oi.order_id = ?
                                ');
                                $stm_items->execute([$order->order_id]);
                                $items = $stm_items->fetchAll();
                                foreach ($items as $i): 
                                    $subtotal = $i->unit_price * $i->quantity;
                                ?>
                                    <tr>
                                        <td><?= encode($i->product_name) ?></td>
                                        <td><img src="/admin/uploads/products/<?= encode($i->photo_name) ?>" style="width:50px; border-radius:5px;"></td>
                                        <td>RM <?= number_format($i->unit_price, 2) ?></td>
                                        <td><?= $i->quantity ?></td>
                                        <td>RM <?= number_format($subtotal, 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                <tr class="total-row">
                                    <td colspan="4"><strong>Total</strong></td>
                                    <td><strong>RM <?= number_format($order->total_amount, 2) ?></strong></td>
                                </tr>
                            </table>
                        </div>
                    </details>
                    
                    <div class="order-actions">
                        <?php if ($order->order_status === 'Shipped'): ?>
                            <a href="confirm_receive.php?id=<?= $order->order_id ?>" class="btn btn-receive">Confirm Received ♡</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include '../_foot.php'; ?>
