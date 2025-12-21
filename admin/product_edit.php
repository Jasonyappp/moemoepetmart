<?php
require '../_base.php';
require_login();
require_admin();

$id = get('id');
if (!$id || !is_numeric($id)) redirect('product_list.php');


if (get('delete_photo') === '1') {
    $stmt = $_db->prepare("SELECT photo_name FROM product WHERE product_id = ?");
    $stmt->execute([$id]);
    $old = $stmt->fetchColumn();
    if ($old) {
        $path = "../admin/uploads/products/$old";
        if (is_file($path)) @unlink($path);
    }
    $_db->prepare("UPDATE product SET photo_name = NULL WHERE product_id = ?")->execute([$id]);
    temp('info', 'The main image has been deleted.');
    redirect("product_edit.php?id=$id");
}


if (is_post()) {
    $name = trim(req('product_name'));
    $price = (float)req('price');
    $stock = (int)req('stock_quantity');
    $desc = req('description') ?? '';

    if (!$name || $price <= 0) {
        temp('error', 'Please fill in all required fields!');
    } else {
        try {
            $_db->prepare("UPDATE product SET product_name=?, price=?, stock_quantity=?, description=?, updated_at=NOW() WHERE product_id=?")
                ->execute([$name, $price, $stock, $desc, $id]);

           
            if (!empty($_FILES['productImage']['name']) && $_FILES['productImage']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['productImage'];
                if ($file['size'] > 10*1024*1024) throw new Exception('Images cannot exceed 10MB.');
                $allowed = ['image/jpeg','image/jpg','image/png','image/gif','image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if (!in_array($mime, $allowed)) throw new Exception('Only images are allowed.');

             
                $stmt = $_db->prepare("SELECT photo_name FROM product WHERE product_id = ?");
                $stmt->execute([$id]);
                $old = $stmt->fetchColumn();   
                if ($old) @unlink("../admin/uploads/products/$old");

                $newName = uniqid('prod_').'.jpg';
                $target = "../admin/uploads/products/$newName";
                if (move_uploaded_file($file['tmp_name'], $target)) {
                    $_db->prepare("UPDATE product SET photo_name=? WHERE product_id=?")
                        ->execute([$newName, $id]);
                    temp('info', 'Main image changed successfully!');
                }
            }

            temp('success', 'Product modifications successful!');
            redirect("product_view.php?id=$id");
        } catch (Exception $e) {
            temp('error', $e->getMessage());
        }
    }
}


$stmt = $_db->prepare("SELECT p.*, c.category_name, c.category_code 
                       FROM product p 
                       LEFT JOIN category c ON p.category_id = c.category_id 
                       WHERE p.product_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_OBJ);
if (!$product) { temp('error', 'Product does not exist.'); redirect('product_list.php'); }

$_title = 'Edit • ' . ($product->product_name);
include '../_head.php'; 
?>

<div class="product-edit-container">

    
<div class="container">
    <h2>Edit Product ♡</h2>

    <form method="post" enctype="multipart/form-data">

        <div class="form-grid">

    <!-- Left: Basic info -->
    <div>
        <label>Category</label>
        <input type="text" value="<?= ($product->category_name ?? 'Uncategorized') ?> (<?= ($product->category_code) ?>)" disabled>

        <label>Product Code <small>(Cannot change)</small></label>
        <input type="text" value="<?= ($product->product_code) ?>" disabled style="background:#f0f0f0; font-weight:bold;">

        <label>Product Name <span class="req">*</span></label>
        <input type="text" name="product_name" value="<?= ($product->product_name) ?>" required>

        <label>Price (RM) <span class="req">*</span></label>
        <input type="number" step="0.01" name="price" value="<?= $product->price ?>" required>

        <label>Stock Quantity</label>
        <input type="number" name="stock_quantity" value="<?= $product->stock_quantity ?>" min="0">
    </div>

    <!-- Right: Image column – current image first, then upload box below it -->
    <div class="image-column" style="display: flex; flex-direction: column; align-items: center; gap: 40px;">

        <!-- Current Main Image -->
        <?php if ($product->photo_name): ?>
        <div class="current-image-section" style="text-align:center;">
            <p style="color:#ff69b4; font-weight:bold; font-size:1.3rem; margin-bottom:20px;">
                Current Main Image ♡
            </p>
            <img src="../admin/uploads/products/<?= encode($product->photo_name) ?>" 
                 alt="Current product image" 
                 class="product-preview-img"
                 style="max-width:100%; max-height:420px; object-fit:contain;">
            <div style="margin-top:25px;">
                <a href="?id=<?= $id ?>&delete_photo=1" 
                  class="btn-primary"
                   style="padding:12px 30px; background:#ff6b9d; font-size:1.1rem"
                   onclick="return confirm('Permanently delete this image? Cannot undo ♡');">
                    Delete Current Image
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Upload New Image Section (now below current image) -->
        <div id="dropzone-upload" class="dropzone-pink" style="max-width:500px;">
            <input type="file" name="productImage" accept="image/*" id="file-input" style="display:none;">

            <div id="placeholder" class="preview-placeholder">
                <img src="/public/images/photo.jpg" alt="Upload" class="product-preview-img">
                <p style="margin-top:20px; font-size:1.3rem; color:#ff69b4;">
                    Click or drag a new photo here ♡<br>
                    <small>(Max 10MB • Will replace current image)</small>
                </p>
            </div>

            <div id="preview-area" class="hidden" style="text-align:center;">
                <p style="color:#ff1493; font-weight:bold; font-size:1.4rem; margin-bottom:20px;">
                    New Image Preview ♡
                </p>
                <img id="preview-img" src="" alt="New preview" class="product-preview-img">
                <div style="margin-top:25px;">
                    <button type="button" id="cancel-preview" class="btn btn-secondary" style="padding:12px 30px;">
                        Cancel Replacement
                    </button>
                </div>
            </div>
        </div>

    </div>
</div>

<div style="margin-top: 20px; padding-right: 15px; text-align: right;">
            <p style="color: #ff69b4; font-size: 1.1rem;">
                Optional* – only upload if you want to change the main photo
            </p>
        </div>   

        
        <div style="margin-top:35px;">
            <label>Description</label>
            <textarea name="description" rows="7" style="width:100%;padding:18px 22px;border:4px solid #ffeef8;border-radius:20px;background:#fff8fb;font-size:1.1rem;"><?= ($product->description) ?></textarea>
        </div>

       
        <div class="form-actions">
            <button type="submit" class="btn-primary">Save Changes ♡</button>
            <a href="product_list.php" class="btn-secondary">Cancel</a>
        </div>

    </form>
</div>



