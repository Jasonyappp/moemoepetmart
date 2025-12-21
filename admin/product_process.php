<?php
// product_process.php
require '../_base.php';
require_login();
require_admin();

$action = post('action');

try {
    switch ($action) {

        case 'edit':
            $product_id = post('product_id');
            $product_name = trim(post('product_name'));
            $category_id = post('category_id');
            $price = post('price');
            $stock_quantity = post('stock_quantity') ?: 0;
            $description = post('description') ?: '';

            $stmt = $_db->prepare("UPDATE product SET 
                product_name = ?, category_id = ?, price = ?, stock_quantity = ?, description = ? 
                WHERE product_id = ?");
            $stmt->execute([$product_name, $category_id, $price, $stock_quantity, $description, $product_id]);

            temp('success', 'Product updated successfully~');
            redirect('product_edit.php?id=' . $product_id);
            break;

        case 'set_main':
            $image_id = get('image_id');
            $product_id = get('product_id');

       
            $_db->prepare("UPDATE product_image SET is_main = 0 WHERE product_id = ?")->execute([$product_id]);
         
            $_db->prepare("UPDATE product_image SET is_main = 1 WHERE image_id = ?")->execute([$image_id]);

            temp('info', 'Main image updated');
            redirect('product_edit.php?id=' . $product_id);
            break;

        case 'delete_image':
            $image_id = get('image_id');
            $product_id = get('product_id');

          //  $img = $_db->prepare("SELECT image_path FROM product_image WHERE image_id = ?")->execute([$image_id])->fetch();
            if ($img && is_file('../' . $img->image_path)) {
                unlink('../' . $img->image_path);
            }
            $_db->prepare("DELETE FROM product_image WHERE image_id = ?")->execute([$image_id]);

            temp('info', 'Image deleted');
            redirect('product_edit.php?id=' . $product_id);
            break;

        case 'upload_temp':
        
            break;

        default:
            temp('error', 'Invalid action');
            redirect('product_list.php');
    }
} catch (Exception $e) {
    temp('error', 'Operation failed: ' . $e->getMessage());
    redirect($_SERVER['HTTP_REFERER'] ?? 'product_list.php');
}