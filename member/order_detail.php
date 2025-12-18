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

// Refresh latest data
$stm_refresh = $_db->prepare('SELECT order_status, payment_method, card_last4 FROM orders WHERE order_id = ?');
$stm_refresh->execute([$id]);
$refresh = $stm_refresh->fetch();
$o->order_status = $refresh->order_status;
$o->payment_method = $refresh->payment_method;
$o->card_last4 = $refresh->card_last4;

// Get items
$stm_items = $_db->prepare('
    SELECT oi.*, p.product_name, p.photo_name 
    FROM order_items oi 
    JOIN product p ON oi.product_id = p.product_id 
    WHERE oi.order_id = ?
');
$stm_items->execute([$id]);
$items = $stm_items->fetchAll();

$_title = 'Order Details #' . $id . ' ♡';
include '../_head.php';
?>

<div class="container" style="max-width:900px; margin:40px auto;">
    <h2 style="text-align:center; color:#ff69b4;">Order Details #<?= $id ?> ♡</h2>

    <div style="background:white; padding:30px; border-radius:20px; box-shadow:0 10px 30px rgba(255,105,180,0.1);">
        <p><strong>Order Date:</strong> <?= date('d M Y H:i', strtotime($o->order_date)) ?></p>
        <p><strong>Status:</strong> <?= $o->order_status ?></p>

        <?php if ($o->payment_method): ?>
            <p style="font-size:1.1rem;">
                <strong>Paid with:</strong>
                <?php if ($o->payment_method === 'Credit/Debit Card' && $o->card_last4): ?>
                    Credit/Debit Card ending ****<?= $o->card_last4 ?>
                <?php else: ?>
                    <?= encode($o->payment_method) ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <h3 style="margin:30px 0 15px; color:#ff69b4;">Items Purchased</h3>
        <!-- Perfectly Centered Table -->
        <table style="width:100%; border-collapse:collapse;">
            <tr style="background:#fff0f5;">
                <th style="padding:15px; text-align:left;">Product</th>
                <th style="padding:15px; text-align:center;">Photo</th>
                <th style="padding:15px; text-align:center;">Price</th>
                <th style="padding:15px; text-align:center;">Qty</th>
                <th style="padding:15px; text-align:center;">Subtotal</th>
            </tr>
            <?php foreach ($items as $i): 
                $subtotal = $i->unit_price * $i->quantity;
            ?>
                <tr style="border-bottom:1px solid #ffd4e4;">
                    <td style="padding:15px; text-align:left;"><?= encode($i->product_name) ?></td>
                    <td style="padding:15px; text-align:center;"><img src="/admin/uploads/products/<?= $i->photo_name ?>" style="width:80px; border-radius:8px;"></td>
                    <td style="padding:15px; text-align:center;">RM <?= number_format($i->unit_price, 2) ?></td>
                    <td style="padding:15px; text-align:center;"><?= $i->quantity ?></td>
                    <td style="padding:15px; text-align:center;">RM <?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="background:#fff0f5; font-size:1.4rem;">
                <td colspan="4" style="padding:20px; text-align:right;"><strong>Total</strong></td>
                <td style="padding:20px; text-align:center;"><strong>RM <?= number_format($o->total_amount, 2) ?></strong></td>
            </tr>
        </table>

        <div style="text-align:center; margin-top:40px;">
            <a href="/member/my_purchase.php" style="padding:15px 40px; background:#ff69b4; color:white; text-decoration:none; border-radius:15px;">
                ← Back to My Purchases
            </a>
        </div>
    </div>
</div>

<?php include '../_foot.php'; ?>