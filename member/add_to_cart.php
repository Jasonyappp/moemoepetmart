<?php
require '../_base.php';
require_login();
header('Content-Type: application/json');

if (is_post()) {
    $id = (int)post('product_id', 0);
    $qty = max(1, (int)post('qty', 1));
    $user_id = current_user()->id;

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product!']);
        exit;
    }

    // Check stock
    $stm = $_db->prepare("SELECT stock_quantity FROM product WHERE product_id = ? AND is_active = 1");
    $stm->execute([$id]);
    $stock = $stm->fetchColumn();
    if ($stock === false || $stock < $qty) {
        echo json_encode(['success' => false, 'message' => 'Not enough stock!']);
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
?>