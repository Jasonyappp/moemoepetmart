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

// Only show receipt if payment is done (not Pending Payment)
if ($o->order_status === 'Pending Payment') {
    temp('error', 'Payment not completed yet.');
    redirect('/member/my_purchase.php');
}

// Fetch items
$stm_items = $_db->prepare('
    SELECT oi.*, p.product_name, p.photo_name 
    FROM order_items oi 
    JOIN product p ON oi.product_id = p.product_id 
    WHERE oi.order_id = ?
');
$stm_items->execute([$id]);
$items = $stm_items->fetchAll();

$_title = 'Receipt - Order #' . $id . ' ♡ Moe Moe Pet Mart';
include '../_head.php';
?>

<div class="container" style="max-width:800px; margin:60px auto; text-align:center;">
    <div style="background:white; padding:40px; border-radius:20px; box-shadow:0 10px 30px rgba(255,105,180,0.15);">
        
        <h1 style="color:#ff69b4; font-size:2.5rem; margin-bottom:20px;">Payment Successful! ♡</h1>
        <p style="font-size:1.4rem; color:#ff1493;">Thank you for your purchase!</p>
        
        <div style="margin:40px 0; padding:30px; background:#fff0f5; border-radius:15px;">
            <h2 style="color:#ff69b4;">Official Receipt</h2>
            <p><strong>Receipt No:</strong> #<?= sprintf('%06d', $id) ?></p>
            <p><strong>Date:</strong> <?= date('d M Y H:i', strtotime($o->order_date)) ?></p>
            <p><strong>Payment Method:</strong> 
                <?= $o->payment_method === 'Credit/Debit Card' ? 'Card ending ****' . ($o->card_last4 ?? '') : encode($o->payment_method) ?>
            </p>
            <p><strong>Status:</strong> 
                <span style="padding:8px 16px; background:#4caf50; color:white; border-radius:20px;">
                    <?= $o->order_status ?>
                </span>
            </p>
        </div>

        <h3 style="margin:30px 0 15px; color:#ff69b4;">Purchased Items</h3>
        <table style="width:100%; border-collapse:collapse; margin:20px 0;">
            <tr style="background:#fff0f5;">
                <th style="padding:12px; text-align:left;">Item</th>
                <th style="padding:12px;">Qty</th>
                <th style="padding:12px;">Price</th>
                <th style="padding:12px;">Subtotal</th>
            </tr>
            <?php foreach ($items as $i): 
                $subtotal = $i->unit_price * $i->quantity;
            ?>
                <tr style="border-bottom:1px solid #ffd4e4;">
                    <td style="padding:12px;"><?= encode($i->product_name) ?></td>
                    <td style="padding:12px; text-align:center;"><?= $i->quantity ?></td>
                    <td style="padding:12px;">RM <?= number_format($i->unit_price, 2) ?></td>
                    <td style="padding:12px;">RM <?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="background:#fff0f5; font-size:1.4rem;">
                <td colspan="3" style="padding:15px; text-align:right;"><strong>Total Paid</strong></td>
                <td style="padding:15px;"><strong>RM <?= number_format($o->total_amount, 2) ?></strong></td>
            </tr>
        </table>

        <p style="margin:30px 0; color:#888; font-style:italic;">
            Your order is being prepared with love~ ♡ We'll notify you when it's shipped!
        </p>

        <div style="margin-top:40px;">
            <a href="order_detail.php?id=<?= $id ?>" 
               style="padding:15px 40px; background:#ff69b4; color:white; text-decoration:none; border-radius:15px; font-size:1.2rem; margin:0 10px;">
                View Full Order Details ♡
            </a>
            <br><br>
            <a href="my_purchase.php" 
               style="color:#ff69b4; text-decoration:underline;">
                ← Back to My Purchases
            </a>
        </div>
    </div>
</div>

<?php include '../_foot.php'; ?>