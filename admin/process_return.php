<?php
require '../_base.php';
require_admin(); // your admin check function

$id = get('id');
$action = get('action'); // 'restock' or 'no_restock'

$stm = $_db->prepare('SELECT * FROM orders WHERE order_id = ? AND order_status = "Return Requested"');
$stm->execute([$id]);
$order = $stm->fetch();

if (!$order) {
    temp('error', 'Invalid or already processed return request.');
    redirect('order_list.php');
}

// Only allow if still in Return Requested state
if ($action === 'restock') {
    // Get all items in this order
    $stm_items = $_db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $stm_items->execute([$id]);
    $items = $stm_items->fetchAll();

    // Restore stock for each item
    foreach ($items as $item) {
        $_db->prepare("UPDATE product SET stock_quantity = stock_quantity + ? WHERE product_id = ?")
            ->execute([$item->quantity, $item->product_id]);
    }

    // Update order status to fully processed
    $_db->prepare("UPDATE orders SET order_status = 'Returned & Restocked' WHERE order_id = ?")
        ->execute([$id]);

    temp('info', 'Return processed and stock restored successfully ♡');

} elseif ($action === 'no_restock') {
    // Just mark as refunded (e.g., damaged item, not restockable)
    $_db->prepare("UPDATE orders SET order_status = 'Refunded (No Restock)' WHERE order_id = ?")
        ->execute([$id]);

    temp('info', 'Return processed (no restock) ♡');
}

redirect('order_list.php');
?>