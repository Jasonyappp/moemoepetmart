<?php
require '../_base.php';
require_login();

$id = req('id');

$stm = $_db->prepare('SELECT * FROM orders WHERE order_id = ? AND user_id = ? AND order_status = "Pending Payment"');
$stm->execute([$id, current_user()->id]);
$o = $stm->fetch();

if (!$o) {
    temp('error', 'Order not found or cannot be cancelled.');
    redirect('my_purchase.php');
}

// Direct cancel on GET or POST
$_db->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE order_id = ?")->execute([$id]);

temp('info', 'Order cancelled successfully ♡');
redirect('my_purchase.php');
?>