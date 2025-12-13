<?php
require '../_base.php';
require_login();

$id = get('id', '');

if ($id !== '' && isset($_SESSION['cart'][$id])) {
    unset($_SESSION['cart'][$id]);
    if (empty($_SESSION['cart'])) {
        unset($_SESSION['cart']);
    }
    temp('info', 'Item removed ♡');
}

save_cart_to_db();  // NEW: Sync to DB

redirect('cart.php');