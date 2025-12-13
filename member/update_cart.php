<?php
require '../_base.php';
require_login();

if (is_post()) {
    $id = (int)post('product_id', 0);
    $qty = (int)post('qty', 0);

    if ($id <= 0 || $qty < 1) {
        temp('error', 'Invalid quantity!');
        redirect('cart.php');
    }

    // Check stock
    $stm = $_db->prepare("SELECT stock_quantity FROM product WHERE product_id = ? AND is_active = 1");
    $stm->execute([$id]);
    $stock = $stm->fetchColumn();

    if ($stock === false || $qty > $stock) {
        temp('error', 'Quantity exceeds stock!');
        redirect('cart.php');
    }

    $key = (string)$id;
    if (isset($_SESSION['cart'][$key])) {
        $_SESSION['cart'][$key]['qty'] = $qty;
        temp('info', 'Cart updated ♡');
    }
}

save_cart_to_db();  // NEW: Sync to DB

redirect('cart.php');
