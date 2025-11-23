<?php
require '../_base.php';
require_login();
require_admin();

$action = post('action') ?: get('action');

if ($action === 'add' || $action === 'edit') {
    $product_id = post('product_id');
    $product_code = trim(post('product_code'));
    $product_name = trim(post('product_name'));
    $price = post('price');
    $stock = post('stock_quantity', 0);
    $desc = post('description');
    $category_id = post('category_id');

    if ($action === 'add') {
        $stmt = $_db->prepare("INSERT INTO product (product_code, product_name, description, price, stock_quantity, category_id) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$product_code, $product_name, $desc, $price, $stock, $category_id]);
        $product_id = $_db->lastInsertId();
        temp('info', "Product $product_code created successfully!");
    } else {
        $stmt = $_db->prepare("UPDATE product SET product_name = ?, description = ?, price = ?, stock_quantity = ?, category_id = ? 
                               WHERE product_id = ?");
        $stmt->execute([$product_name, $desc, $price, $stock, $category_id, $product_id]);
        temp('info', "Product updated successfully!");
    }

    // Handle uploaded images (from temp session)
    if (!empty($_SESSION['temp_images'])) {
        $sort = 0;
        foreach ($_SESSION['temp_images'] as $path) {
            $is_main = ($sort === 0) ? 1 : 0;
            $_db->prepare("INSERT INTO product_image (product_id, image_path, is_main, sort_order) VALUES (?, ?, ?, ?)")
                ->execute([$product_id, $path, $is_main, $sort++]);
        }
        unset($_SESSION['temp_images']);
    }

    redirect("product_view.php?id=$product_id");
}

elseif ($action === 'delete') {
    $id = get('delete');
    $_db->prepare("UPDATE product SET is_active = 0 WHERE product_id = ?")->execute([$id]);
    temp('info', 'Product deleted');
    redirect('product_list.php');
}

elseif ($action === 'set_main') {
    $image_id = get('image_id');
    $product_id = get('product_id');
    $_db->prepare("UPDATE product_image SET is_main = 0 WHERE product_id = ?")->execute([$product_id]);
    $_db->prepare("UPDATE product_image SET is_main = 1 WHERE image_id = ?")->execute([$image_id]);
    temp('info', 'Main image updated');
    redirect("product_edit.php?id=$product_id");
}

elseif ($action === 'delete_image') {
    $image_id = get('image_id');
    $product_id = get('product_id');
    $stmt = $_db->prepare("SELECT image_path FROM product_image WHERE image_id = ?");
    $stmt->execute([$image_id]);
    $path = $stmt->fetchColumn();
    if ($path && file_exists("../$path")) unlink("../$path");
    $_db->prepare("DELETE FROM product_image WHERE image_id = ?")->execute([$image_id]);
    temp('info', 'Image deleted');
    redirect("product_edit.php?id=$product_id");
}

elseif ($action === 'upload_temp' && !empty($_FILES['product_images'])) {
    $upload_dir = "uploads/products/";
    if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

    foreach ($_FILES['product_images']['tmp_name'] as $key => $tmp) {
        if ($_FILES['product_images']['error'][$key] == 0) {
            $ext = pathinfo($_FILES['product_images']['name'][$key], PATHINFO_EXTENSION);
            $filename = "product_" . time() . "_$key.$ext";
            $path = $upload_dir . $filename;
            move_uploaded_file($tmp, "../$path");
            $_SESSION['temp_images'][] = $path;
        }
    }
    exit;
}