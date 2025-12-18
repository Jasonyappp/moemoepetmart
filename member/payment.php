<?php
require '../_base.php';
require_login();

$id = req('id');
$stm = $_db->prepare('SELECT * FROM orders WHERE order_id = ? AND user_id = ?');
$stm->execute([$id, current_user()->id]);
$o = $stm->fetch();

if (!$o) {
    temp('error', 'Invalid order.');
    redirect('/member/my_purchase.php');
}

$tng_qr_path = '/images_tng/tng_qr.jpeg';

// Confirm payment (TNG only)
if (get('confirm') === '1') {
    if ($o->payment_method === 'Touch \'n Go' && $o->order_status === 'Pending Payment') {
        $_db->prepare("UPDATE orders SET order_status = 'To Ship' WHERE order_id = ?")->execute([$id]);
        clear_cart();
        temp('info', 'Payment confirmed ♡');
        redirect("receipt.php?id=$id");
    }
}

// Show QR only if TNG and Pending Payment
if ($o->payment_method === 'Touch \'n Go' && $o->order_status === 'Pending Payment') {
    $_title = 'Pay with Touch \'n Go ♡';
    include '../_head.php';
    ?>
    <div class="container" style="text-align:center; padding:50px;">
        <h2>Scan to Pay ♡</h2>
        <img src="<?= $tng_qr_path ?>" alt="TNG QR Code" style="width:320px; margin:30px auto; border:5px solid #ff5722; border-radius:20px;">
        <p style="font-size:1.4rem; margin:20px 0;">Complete payment in your TNG app</p>
        <a href="payment.php?id=<?= $id ?>&confirm=1" class="btn btn-primary" style="padding:18px 50px; font-size:1.6rem;">
            I Have Paid ♡ Confirm
        </a>
    </div>
    <?php include '../_foot.php'; exit;
}

// If not TNG pending, redirect to receipt
redirect("receipt.php?id=$id");
?>