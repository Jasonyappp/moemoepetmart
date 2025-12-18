<?php
require '../_base.php';
require_login();

$id = req('id');

// Fetch order - must belong to user and be Shipped
$stm = $_db->prepare('SELECT * FROM orders WHERE order_id = ? AND user_id = ? AND order_status = "Shipped"');
$stm->execute([$id, current_user()->id]);
$order = $stm->fetch();

if (!$order) {
    temp('error', 'Order not found or not eligible for confirmation.');
    redirect('my_purchase.php');
}

// If form submitted (POST)
if (is_post()) {
    // Update status to Completed
    $_db->prepare("UPDATE orders SET order_status = 'Completed' WHERE order_id = ?")
        ->execute([$id]);

    temp('info', 'Order confirmed received! Thank you for shopping ♡');
    redirect('my_purchase.php');
}

$_title = 'Confirm Receipt - Order #' . $id;
include '../_head.php';
?>

<div class="container" style="max-width:700px; margin:60px auto;">
    <div style="background:white; padding:40px; border-radius:20px; box-shadow:0 10px 30px rgba(255,105,180,0.15); text-align:center;">
        <h2 style="color:#ff69b4; margin-bottom:20px;">Confirm Order Received ♡</h2>
        
        <div style="background:#f0f8ff; padding:25px; border-radius:15px; margin:20px 0;">
            <p style="font-size:1.3rem; margin:10px 0;"><strong>Order ID:</strong> #<?= $id ?></p>
            <p style="font-size:1.1rem; color:#555; margin:15px 0;">
                Have you received your order in good condition?
            </p>
        </div>

        <form method="post">
            <button type="submit" style="
                padding:18px 50px; 
                background:#4caf50; 
                color:white; 
                border:none; 
                border-radius:20px; 
                font-size:1.4rem; 
                font-weight:bold; 
                cursor:pointer; 
                box-shadow:0 8px 20px rgba(76,175,80,0.3);
                margin:15px;
            ">
                Yes, Confirm Received ♡
            </button>
        </form>

        <p style="margin-top:30px;">
            <a href="my_purchase.php" style="color:#ff69b4; text-decoration:underline; font-size:1.1rem;">
                ← No, go back
            </a>
        </p>

        <p style="margin-top:30px; color:#888; font-style:italic; font-size:1rem;">
            After confirming, you can request return/refund if needed ♡
        </p>
    </div>
</div>

<?php include '../_foot.php'; ?>