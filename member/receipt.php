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
    <h2 style="color:#ff1493; text-align:center; font-family:'Kalam', cursive;">Thank You for Your Order! ♡</h2>
    <p style="text-align:center; font-size:1.4rem; margin:20px 0;">
        Order ID: <strong>#<?= $id ?></strong>
    </p>

    <div style="background:#fff0f5; padding:20px; border-radius:15px; text-align:center; margin:20px 0; border:2px dashed #ff69b4;">
        <p style="font-size:1.2rem; margin:8px 0;">
            <strong>Payment Method:</strong>
            <?php if ($o->payment_method === 'Credit/Debit Card' && $o->card_last4): ?>
                Credit/Debit Card ending ****<?= $o->card_last4 ?>
            <?php else: ?>
                <?= encode($o->payment_method) ?>
            <?php endif; ?>
        </p>
        <p style="font-size:1.2rem; margin:8px 0;">
            <strong>Status:</strong> 
            <span><?= encode($o->order_status) ?></span>
        </p>
    </div>

    <!-- Shipping Details Box -->
    <div style="background:#fff; padding:25px; border-radius:20px; margin:30px 0; border:2px solid #ffeef8; box-shadow:0 10px 30px rgba(255,105,180,0.15);">
        <h3 style="color:#ff1493; text-align:center; margin-bottom:20px; font-family:'Kalam', cursive;">Shipping Details ♡</h3>
        <div style="text-align:center; font-size:1.2rem; line-height:1.8;">
            <p style="margin:10px 0;">
                <strong>Recipient:</strong> <?= encode($o->recipient_name) ?>
            </p>
            <p style="margin:10px 0;">
                <strong>Phone:</strong> <?= encode($o->recipient_phone) ?>
            </p>
            <p style="margin:15px 0; padding:15px; background:#fff5f9; border-radius:12px; border-left:4px solid #ff69b4;">
                <strong>Address:</strong><br>
                <?= nl2br(encode($o->shipping_address)) ?>
            </p>
        </div>
    </div>

    <!-- Items Table -->
    <table style="width:100%; border-collapse:collapse; margin:30px 0; background:white; border-radius:15px; overflow:hidden; box-shadow:0 8px 25px rgba(255,105,180,0.1);">
        <tr style="background:#fff0f5;">
            <th style="padding:15px; text-align:left; color:#ff1493;">Product</th>
            <th style="padding:15px; text-align:center; color:#ff1493;">Price</th>
            <th style="padding:15px; text-align:center; color:#ff1493;">Qty</th>
            <th style="padding:15px; text-align:center; color:#ff1493;">Subtotal</th>
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

        $calculated_subtotal = 0;  // We calculate it here to match cart/checkout exactly
        foreach ($items as $i): 
            $item_subtotal = $i->unit_price * $i->quantity;
            $calculated_subtotal += $item_subtotal;
        ?>
            <tr style="border-bottom:1px dashed #ffb6c1;">
                <td style="padding:15px; text-align:left;"><?= encode($i->product_name) ?></td>
                <td style="padding:15px; text-align:center;">RM <?= number_format($i->unit_price, 2) ?></td>
                <td style="padding:15px; text-align:center;"><?= $i->quantity ?></td>
                <td style="padding:15px; text-align:center;">RM <?= number_format($item_subtotal, 2) ?></td>
            </tr>
        <?php endforeach; ?>
        
        <!-- Subtotal (calculated from items - matches cart/checkout) -->
        <tr style="background:#fff0f5;">
            <td colspan="3" style="padding:20px; text-align:right;"><strong>Subtotal</strong></td>
            <td style="padding:20px; text-align:center;"><strong>RM <?= number_format($calculated_subtotal, 2) ?></strong></td>
        </tr>
        
        <!-- Discount -->
        <?php if ($o->discount_amount > 0): ?>
        <tr style="background:#fff0f5;">
            <td colspan="3" style="padding:20px; text-align:right;"><strong>Discount (<?= encode($o->voucher_code ?? 'None') ?>)</strong></td>
            <td style="padding:20px; text-align:center; color:#f44336;"><strong>- RM <?= number_format($o->discount_amount, 2) ?></strong></td>
        </tr>
        <?php endif; ?>
        
        <!-- Shipping Fee -->
        <tr style="background:#fff0f5;">
            <td colspan="3" style="padding:20px; text-align:right;"><strong>Shipping Fee</strong></td>
            <td style="padding:20px; text-align:center;">
                <?= $o->shipping_fee == 0 ? 'FREE ♡' : 'RM ' . number_format($o->shipping_fee, 2) ?>
            </td>
        </tr>
        
        <!-- Grand Total -->
        <tr style="background:#fff0f5; font-size:1.4rem;">
            <td colspan="3" style="padding:20px; text-align:right;"><strong>Total Paid</strong></td>
            <td style="padding:20px; text-align:center; color:#ff1493;"><strong>RM <?= number_format($o->total_amount, 2) ?></strong></td>
        </tr>
    </table>

    <p style="text-align:center; margin:40px 0; color:#ff69b4; font-size:1.1rem; font-style:italic;">
        Your order is being prepared with lots of love~ ♡<br>
        We'll notify you as soon as it's on the way! 🐾
    </p>

    <div style="text-align:center; margin-top:30px;">
        <a href="/member/order_detail.php?id=<?= $id ?>" 
           style="display:inline-block; padding:15px 50px; background:#ff69b4; color:white; text-decoration:none; border-radius:50px; font-weight:bold; box-shadow:0 8px 20px rgba(255,105,180,0.3); transition:all 0.3s;"
           onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 12px 25px rgba(255,20,147,0.4)'"
           onmouseout="this.style.transform=''; this.style.boxShadow='0 8px 20px rgba(255,105,180,0.3)'">
            View Full Order Details ♡
        </a>
        <br><br>
        <a href="/member/my_purchase.php" style="color:#ff69b4; text-decoration:underline; font-size:1rem;">
            ← Back to My Purchases
        </a>
    </div>
</div>

<?php include '../_foot.php'; ?>