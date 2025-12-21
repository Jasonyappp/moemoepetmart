<?php
require '../_base.php';
require_login();
require_admin();

$_title = 'Add New Product - Admin';


if (is_post()) {
    $action = post('action');

   
    if ($action === '') {
      
    }


    elseif ($action === 'add') {
        $category_id     = req('category_id');
        $product_code    = trim(req('product_code'));
        $product_name    = trim(req('product_name'));
        $price           = (float)req('price');
        $stock_quantity  = (int)post('stock_quantity', 0);
        $description     = post('description', '');

     
        if (!$category_id || !$product_code || !$product_name || $price <= 0) {
            temp('error', 'Please fill in all required fields!');
        }
        elseif (empty($_FILES['productImage']['name'])) {
            temp('error', 'Please upload the main image of your product!');
        }
        else {
            try {
               
                $stm = $_db->prepare("INSERT INTO product 
                    (product_code, product_name, category_id, price, stock_quantity, description, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");

                $stm->execute([$product_code, $product_name, $category_id, $price, $stock_quantity, $description]);
                $product_id = $_db->lastInsertId();

               
                $uploadDir = '../admin/uploads/products';           
                $webDir    = 'admin/uploads/products';              

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $file = $_FILES['productImage'];

      
                if ($file['error'] !== UPLOAD_ERR_OK) {
                    throw new Exception('Upload failed, error code:' . $file['error']);
                }
                if ($file['size'] > 10 * 1024 * 1024) {
                    throw new Exception('Images cannot exceed. 10MB');
                }

                $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);

                if (!in_array($mime, $allowed)) {
                    throw new Exception('Only images are allowed.');
                }

              

                $newName = uniqid() . '.jpg';
                $targetFile = $uploadDir . '/' . $newName;
                move_uploaded_file($file['tmp_name'], $targetFile);

              
                $_db->prepare("UPDATE product SET photo_name = ? WHERE product_id = ?")
                    ->execute([$newName, $product_id]);

                temp('success', "product「{$product_name}」Added successfully！");
                redirect('product_list.php'); 

            } catch (Exception $e) {
                temp('error', 'Addition failed:' . $e->getMessage());
            }
        }
    }
}


$category_id = post('category_id') ?: get('category_id');
$next_code = '';
if ($category_id) {
    $stm = $_db->prepare("SELECT category_code FROM category WHERE category_id = ?");
    $stm->execute([$category_id]);
    if ($cat = $stm->fetch()) {
        $stm2 = $_db->prepare("SELECT product_code FROM product WHERE product_code LIKE ? ORDER BY product_code DESC LIMIT 1");
        $stm2->execute([$cat->category_code . '%']);
        $last = $stm2->fetchColumn();
        $next_code = $last
            ? $cat->category_code . str_pad((int)substr($last, strlen($cat->category_code)) + 1, 4, '0', STR_PAD_LEFT)
            : $cat->category_code . '0001';
    }
}

$cats = $_db->query("SELECT * FROM category ORDER BY category_name")->fetchAll();

include '../_head.php';
?>

<div class="container">
    <h2>Add New Product</h2>

    <form class="form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="">

        <div class="form-grid">
            <div>
                <label>Category <span class="req">*</span></label>
                <select name="category_id" required onchange="this.form.submit()">
                    <option value="">-- Select Category --</option>
                    <?php foreach ($cats as $c): ?>
                        <option value="<?= $c->category_id ?>" <?= $category_id == $c->category_id ? 'selected' : '' ?>>
                            <?= encode($c->category_name) ?> (<?= $c->category_code ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label>Product Code <span class="req">*</span></label>
                <input type="text" name="product_code" value="<?= $next_code ?>" required
                       <?= $next_code ? '' : 'readonly placeholder="Please select a category first."' ?>>
                <?php if ($next_code): ?>
                    <small>Recommended use: <strong><?= $next_code ?></strong></small>
                <?php endif; ?>
            </div>

            <div>
                <label>Product Name <span class="req">*</span></label>
                <input type="text" name="product_name" value="<?= post('product_name') ?>" required>
            </div>

            <div>
                <label>Price (RM) <span class="req">*</span></label>
                <input type="number" step="0.01" name="price" value="<?= post('price') ?>" required min="0.01">
            </div>

            <div>
                <label>Stock Quantity</label>
                <input type="number" name="stock_quantity" value="<?= post('stock_quantity', 0) ?>" min="0">
            </div>
        </div>

        <label>Description</label>
        <textarea name="description" rows="5"><?= encode(post('description')) ?></textarea>

            <!-- Product Image Upload with Live Preview -->
    <label class="mt-20">Product Image <span class="req">*</span></label>
    <button type="submit" class="btn btn-primary" style="padding:16px 40px; font-size:1.4rem; border-radius:50px;"
                onclick="document.querySelector('[name=action]').value='add';">
            Create Product ♡
        </button>

    <div id="dropzone-upload" class="dropzone-pink">
        <input type="file" name="productImage" accept="image/*" id="file-input" style="display:none;">

        <!-- Default placeholder when no image selected -->
        <div id="placeholder" class="preview-placeholder">
            <img src="/public/images/photo.jpg" alt="Upload placeholder" style="max-height:300px; border-radius:20px;">
            <p style="margin-top:20px; font-size:1.2rem; color:#ff69b4;">
                Click here or drag a cute photo ♡<br>
                (Max 10MB, will be converted to JPG)
            </p>
        </div>

        <!-- Live preview when image is selected -->
        <div id="preview-area" class="hidden" style="text-align:center;">
            <img id="preview-img" src="" alt="Preview" style="max-height:380px; border-radius:25px; border:6px solid #ff69b4; box-shadow:0 15px 35px rgba(255,105,180,0.3);">
            <div style="margin-top:20px;">
                <button type="button" id="cancel-preview" class="btn btn-secondary" style="padding:10px 25px;">
                    Cancel
                </button>
            </div>
        </div>
    </div>

<!-- Simple required note -->
<small style="color:#ff69b4; display:block; margin-top:8px;">* Required – this will be the main product photo</small>

<?php include '../_foot.php'; ?>