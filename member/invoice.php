<?php
require '../_base.php';
require_login();

$id = req('id');

// Fetch order
$stm = $_db->prepare('SELECT * FROM orders WHERE order_id = ? AND user_id = ?');
$stm->execute([$id, current_user()->id]);
$o = $stm->fetch();

if (!$o || $o->order_status !== 'Pending Payment') {
    temp('error', 'Invalid order or already paid.');
    redirect('my_purchase.php');
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

$_title = 'Order Invoice #' . $id . ' ♡ Moe Moe Pet Mart';
include '../_head.php';
?>

<div class="container" style="max-width:900px; margin:60px auto;">
    <div style="background:white; padding:40px; border-radius:20px; box-shadow:0 10px 30px rgba(255,105,180,0.15); text-align:center;">

        <h1 style="color:#ff69b4; font-size:2.8rem; margin-bottom:10px;">Order Placed Successfully! ♡</h1>
        <p style="font-size:1.4rem; color:#ff1493; margin-bottom:40px;">
            Your order has been received. Please complete payment to confirm.
        </p>

        <div style="background:#fff0f5; padding:25px; border-radius:15px; margin:30px 0;">
            <h2 style="color:#ff69b4;">Order Invoice</h2>
            <p><strong>Order ID:</strong> #<?= sprintf('%06d', $id) ?></p>
            <p><strong>Order Date:</strong> <?= date('d M Y H:i', strtotime($o->order_date)) ?></p>
            <p><strong>Status:</strong> 
                <span style="padding:8px 16px; background:#ff9800; color:white; border-radius:20px;">
                    Pending Payment
                </span>
            </p>
            <p><strong>Total Amount:</strong> 
                <span style="font-size:1.6rem; color:#ff1493; font-weight:bold;">
                    RM <?= number_format($o->total_amount, 2) ?>
                </span>
            </p>
        </div>

        <h3 style="color:#ff69b4; margin:30px 0 15px; text-align:left;">Order Items</h3>
        <table style="width:100%; border-collapse:collapse; margin:20px 0;">
            <tr style="background:#fff0f5;">
                <th style="padding:15px; text-align:left;">Product</th>
                <th style="padding:15px; text-align:center;">Photo</th>
                <th style="padding:15px;">Price</th>
                <th style="padding:15px; text-align:center;">Qty</th>
                <th style="padding:15px;">Subtotal</th>
            </tr>
            <?php foreach ($items as $i): 
                $subtotal = $i->unit_price * $i->quantity;
            ?>
                <tr style="border-bottom:1px solid #ffd4e4;">
                    <td style="padding:15px;"><?= encode($i->product_name) ?></td>
                    <td style="padding:15px; text-align:center;">
                        <img src="/admin/uploads/products/<?= $i->photo_name ?>" style="width:80px; border-radius:10px;">
                    </td>
                    <td style="padding:15px;">RM <?= number_format($i->unit_price, 2) ?></td>
                    <td style="padding:15px; text-align:center;"><?= $i->quantity ?></td>
                    <td style="padding:15px;">RM <?= number_format($subtotal, 2) ?></td>
                </tr>
            <?php endforeach; ?>
            <tr style="background:#fff0f5; font-size:1.4rem;">
                <td colspan="4" style="padding:20px; text-align:right;"><strong>Total</strong></td>
                <td style="padding:20px;"><strong>RM <?= number_format($o->total_amount, 2) ?></strong></td>
            </tr>
        </table>

        <div style="margin-top:50px;">
            <a href="payment.php?id=<?= $id ?>" 
               style="display:inline-block; padding:18px 50px; background:#ff69b4; color:white; text-decoration:none; border-radius:15px; font-size:1.4rem; font-weight:bold;">
                Proceed to Payment ♡
            </a>
            <br><br>
            <a href="my_purchase.php" style="color:#ff69b4; text-decoration:underline;">
                ← Back to My Purchases
            </a>
        </div>

        <p style="margin-top:40px; color:#888; font-style:italic;">
            Please complete payment within 24 hours or the order may be cancelled.
        </p>
    </div>
</div>

<?php include '../_foot.php'; ?>