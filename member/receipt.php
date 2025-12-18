<?php
require '../_base.php';
require_login();

$id = req('id');
$stm = $_db->prepare('SELECT * FROM orders WHERE order_id = ? AND user_id = ?');
$stm->execute([$id, current_user()->id]);
$o = $stm->fetch();

if (!$o) {
    temp('error', 'Order not found.');
    redirect('/member/my_purchase.php');
}

// Allow receipt for Pending Payment if COD
if ($o->order_status === 'Pending Payment' && $o->payment_method !== 'Cash on Delivery') {
    temp('error', 'Payment not completed yet.');
    redirect('/member/my_purchase.php');
}

clear_cart();

$_title = 'Receipt - Order #' . $id . ' ♡';
include '../_head.php';
?>

<div class="container" style="padding:40px; max-width:900px; margin:0 auto;">
    <h2 style="color:#ff1493; text-align:center;">Thank You for Your Order! ♡</h2>
    <p style="text-align:center; font-size:1.4rem; margin:20px 0;">
        Order ID: <strong>#<?= $id ?></strong>
    </p>

    <div style="background:#fff0f5; padding:20px; border-radius:15px; text-align:center; margin:20px 0;">
        <p style="font-size:1.2rem;">
            <strong>Payment Method:</strong>
            <?php if ($o->payment_method === 'Credit/Debit Card' && $o->card_last4): ?>
                Credit/Debit Card ending ****<?= $o->card_last4 ?>
            <?php else: ?>
                <?= encode($o->payment_method) ?>
            <?php endif; ?>
        </p>
        <p style="font-size:1.2rem; margin-top:10px;">
            <strong>Status:</strong> <?= encode($o->order_status) ?>
        </p>
    </div>

    <!-- Centered Table -->
    <table style="width:100%; border-collapse:collapse; margin:30px 0;">
        <tr style="background:#fff0f5;">
            <th style="padding:15px; text-align:left;">Product</th>
            <th style="padding:15px; text-align:center;">Price</th>
            <th style="padding:15px; text-align:center;">Qty</th>
            <th style="padding:15px; text-align:center;">Subtotal</th>
        </tr>
        <?php 
        $stm_items = $_db->prepare('
            SELECT oi.*, p.product_name 
            FROM order_items oi 
            JOIN product p ON oi.product_id = p.product_id 
            WHERE oi.order_id = ?
        ');
        $stm_items->execute([$id]);
        $items = $stm_items->fetchAll();
        foreach ($items as $i): 
            $subtotal = $i->unit_price * $i->quantity;
        ?>
            <tr style="border-bottom:1px solid #ffd4e4;">
                <td style="padding:15px; text-align:left;"><?= encode($i->product_name) ?></td>
                <td style="padding:15px; text-align:center;">RM <?= number_format($i->unit_price, 2) ?></td>
                <td style="padding:15px; text-align:center;"><?= $i->quantity ?></td>
                <td style="padding:15px; text-align:center;">RM <?= number_format($subtotal, 2) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr style="background:#fff0f5; font-size:1.4rem;">
            <td colspan="3" style="padding:20px; text-align:right;"><strong>Total Paid</strong></td>
            <td style="padding:20px; text-align:center;"><strong>RM <?= number_format($o->total_amount, 2) ?></strong></td>
        </tr>
    </table>

    <p style="text-align:center; margin:30px 0; color:#888; font-style:italic;">
        Your order is being prepared with love~ ♡ We'll notify you when it's shipped!
    </p>

    <div style="text-align:center;">
        <a href="order_detail.php?id=<?= $id ?>" style="padding:15px 40px; background:#ff69b4; color:white; text-decoration:none; border-radius:15px;">
            View Full Order Details ♡
        </a>
        <br><br>
        <a href="my_purchase.php" style="color:#ff69b4; text-decoration:underline;">← Back to My Purchases</a>
    </div>
</div>

<?php include '../_foot.php'; ?>