<?php

// New file: member/add_to_favorite.php (similar to add_to_cart.php)

require '../_base.php';
require_login();
header('Content-Type: application/json');

if (is_post()) {
    $id = (int)post('product_id', 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product!']);
        exit;
    }

    // Use the new function
    $result = add_to_favorites($id);
    echo json_encode($result);
}
?>
