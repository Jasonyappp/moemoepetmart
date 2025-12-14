<?php
require '../_base.php';
require_login();
header('Content-Type: application/json');

if (is_post()) {
    $id = (int)post('product_id', 0);
    $raw_qty = post('qty', '1');  // Default to string '1' for safety
    $qty = is_numeric($raw_qty) ? (int)$raw_qty : 0;

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product!']);
        exit;
    }

    if ($qty < 1 || !is_numeric($raw_qty)) {
        echo json_encode(['success' => false, 'message' => 'Invalid quantity! Please select at least 1. ♡']);
        exit;
    }

    // Optional: Cap max qty to prevent abuse
    $qty = min($qty, 999);

    $user_id = current_user()->id;


    // Check stock
    $stm = $_db->prepare("SELECT stock_quantity FROM product WHERE product_id = ? AND is_active = 1");
    $stm->execute([$id]);
    $stock = $stm->fetchColumn();
    if ($stock === false) {
        echo json_encode(['success' => false, 'message' => 'Product not available!']);
        exit;
    }

    // NEW: Get current qty in cart for this product (from DB, since cart is synced)
    $stm_current = $_db->prepare("SELECT quantity FROM cart_item WHERE user_id = ? AND product_id = ?");
    $stm_current->execute([$user_id, $id]);
    $current_qty = (int)$stm_current->fetchColumn() ?: 0;

    // NEW: Check if new total (current + adding) exceeds stock
    $new_total_qty = $current_qty + $qty;
    if ($stock < $new_total_qty) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock! (Max available: ' . ($stock - $current_qty) . ')']);
        exit;
    }

    // UPSERT using UNIQUE KEY
    $stm = $_db->prepare("INSERT INTO cart_item (user_id, product_id, quantity) 
                          VALUES (?, ?, ?) 
                          ON DUPLICATE KEY UPDATE quantity = quantity + ?");
    $stm->execute([$user_id, $id, $qty, $qty]);

    load_cart_from_db();  // Refresh session

    $total_items = array_sum(array_column($_SESSION['cart'], 'qty'));
    echo json_encode(['success' => true, 'total_items' => $total_items]);
}