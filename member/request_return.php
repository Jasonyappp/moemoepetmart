<?php
require '../_base.php';
require_login();

$id = req('id');
$reason = trim(post('reason', ''));

$stm = $_db->prepare('SELECT * FROM orders WHERE order_id = ? AND user_id = ? AND order_status = "Completed"');
$stm->execute([$id, current_user()->id]);
$o = $stm->fetch();

if (!$o) {
    temp('error', 'Order not eligible for return/refund.');
    redirect('my_purchase.php');
}

if (is_post()) {
    if (empty($reason)) {
        $_err['reason'] = 'Please provide a reason for return/refund.';
    }

    if (empty($_err)) {
        $_db->prepare("UPDATE orders SET order_status = 'Return/Refund', return_reason = ? WHERE order_id = ?")
            ->execute([$reason, $id]);

        temp('info', 'Return/Refund request submitted successfully ♡');
        redirect('my_purchase.php');
    }
}

$_title = 'Request Return/Refund - Order #' . $id;
include '../_head.php';
?>

<div class="container" style="max-width:600px; margin:60px auto;">
    <div style="background:white; padding:40px; border-radius:20px; box-shadow:0 10px 30px rgba(255,105,180,0.15);">
        <h2 style="text-align:center; color:#ff69b4;">Request Return/Refund ♡</h2>
        <p style="text-align:center; margin:20px 0;">Order #<?= $id ?></p>

        <form method="post">
            <label style="display:block; margin:15px 0; font-weight:bold;">Reason for Return/Refund *</label>
            <select name="reason" style="width:100%; padding:12px; border:1px solid #ff69b4; border-radius:12px; font-size:1rem;">
                <option value="">-- Select Reason --</option>
                <option value="Wrong item received">Wrong item received</option>
                <option value="Defective/Damaged">Defective or damaged</option>
                <option value="Does not match description">Does not match description</option>
                <option value="Changed mind">Changed my mind</option>
                <option value="Other">Other</option>
            </select>
            <?= err('reason') ?>

            <label style="display:block; margin:20px 0 10px; font-weight:bold;">Additional Notes (Optional)</label>
            <textarea name="notes" placeholder="Any extra details..." style="width:100%; height:100px; padding:15px; border:1px solid #ff69b4; border-radius:12px; resize:vertical;"></textarea>

            <div style="text-align:center; margin-top:30px;">
                <button type="submit" style="padding:15px 40px; background:#ff5722; color:white; border:none; border-radius:15px; font-size:1.2rem; cursor:pointer;">
                    Submit Request ♡
                </button>
                <br><br>
                <a href="my_purchase.php" style="color:#ff69b4; text-decoration:underline;">← Cancel</a>
            </div>
        </form>
    </div>
</div>

<?php include '../_foot.php'; ?>