<?php
// New file: member/remove_from_favorite.php

require '../_base.php';
require_login();
header('Content-Type: application/json');

if (is_post()) {
    $id = (int)post('product_id', 0);
    
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid product!']);
        exit;
    }

    $user_id = current_user()->id;

    $stm = $_db->prepare("DELETE FROM favorites WHERE user_id = ? AND product_id = ?");
    $stm->execute([$user_id, $id]);

    echo json_encode(['success' => true, 'message' => 'Removed from favorites ♡']);
}
?>