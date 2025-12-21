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

// === RESTORE STOCK AUTOMATICALLY ===
$items_stmt = $_db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
$items_stmt->execute([$id]);
$items = $items_stmt->fetchAll();

foreach ($items as $item) {
    $_db->prepare("UPDATE product SET stock_quantity = stock_quantity + ? WHERE product_id = ?")
        ->execute([$item->quantity, $item->product_id]);
}
// === END RESTORE ===

// Update status to Cancelled
$_db->prepare("UPDATE orders SET order_status = 'Cancelled' WHERE order_id = ?")
    ->execute([$id]);

temp('info', 'Order cancelled successfully and items returned to stock ♡');
redirect('my_purchase.php');
?>