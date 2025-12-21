<?php
/**
 * product_delete.php
 
 */

require '../_base.php';
require_login();
require_admin();


if (!is_post()) {
    temp('error', 'Invalid request method');
    redirect('product_list.php');
}

$product_id = post('product_id');

if (!$product_id || !is_numeric($product_id)) {
    temp('error', 'Invalid Product ID');
    redirect('product_list.php');
}

try {
    $_db->beginTransaction();

    // 1. 先获取产品信息（用于提示 + 验证存在）
    $stmt = $_db->prepare("SELECT product_name FROM product WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product_name = $stmt->fetchColumn();

    if ($product_name === false) {
        throw new Exception('The product does not exist or has been removed.');
    }

    
    delete_old_product_photo($product_id);

  
    $_db->prepare("DELETE FROM product WHERE product_id = ?")
         ->execute([$product_id]);

    $_db->commit();

    temp('success', "product「{$product_name}」Completely deleted (including the main image).");

} catch (Exception $e) {
    $_db->rollBack();
    error_log('Product delete failed (ID: '.$product_id.'): '.$e->getMessage());
    temp('error', 'Deletion failed:' . $e->getMessage());
}

redirect('product_list.php');