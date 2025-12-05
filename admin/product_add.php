<?php
require '../_base.php';
require_login();
require_admin();

$_title = 'Add New Product - Admin';

// ============================ 提交处理 ============================
if (is_post()) {
    $action = post('action');

    // 只是选分类 → 刷新生成编号
    if ($action === '') {
        // do nothing
    }

    // 真正添加
    elseif ($action === 'add') {
        $category_id     = req('category_id');
        $product_code    = trim(req('product_code'));
        $product_name    = trim(req('product_name'));
        $price           = (float)req('price');
        $stock_quantity  = (int)post('stock_quantity', 0);
        $description     = post('description', '');

        // 基础验证
        if (!$category_id || !$product_code || !$product_name || $price <= 0) {
            temp('error', 'Please fill in all required fields!');
        }
        elseif (empty($_FILES['productImage']['name'])) {
            temp('error', 'Please upload the main image of your product!');
        }
        else {
            try {
                // 1. 先插入产品（photo_name 留空）
                $stm = $_db->prepare("INSERT INTO product 
                    (product_code, product_name, category_id, price, stock_quantity, description, created_at, updated_at)
                    VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");

                $stm->execute([$product_code, $product_name, $category_id, $price, $stock_quantity, $description]);
                $product_id = $_db->lastInsertId();

                // 2. 处理图片上传（存到 ../admin/uploads/products）
                $uploadDir = '../admin/uploads/products';           // 真实路径
                $webDir    = 'admin/uploads/products';              // 给数据库和前端用的路径

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }

                $file = $_FILES['productImage'];

                // 简单安全检查
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

                // 生成安全文件名，强制 .jpg

                $newName = uniqid() . '.jpg';
                $targetFile = $uploadDir . '/' . $newName;
                move_uploaded_file($file['tmp_name'], $targetFile);

                // 3. 更新数据库 photo_name
                $_db->prepare("UPDATE product SET photo_name = ? WHERE product_id = ?")
                    ->execute([$newName, $product_id]);

                temp('success', "product「{$product_name}」Added successfully！");
                redirect('product_list.php'); // 跳转到列表页立刻看到

            } catch (Exception $e) {
                temp('error', 'Addition failed:' . $e->getMessage());
            }
        }
    }
}

// ============================ 自动生成编号 ============================
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

        <label class="mt-20">Product Image <span class="req"></span></label>
        <label class="upload">
            <input type="file" name="productImage" accept="image/*">
            <img src="/public/images/photo.jpg" alt="Upload">
            <span class="upload-text">Click to upload (it will automatically convert to JPG).</span>
        </label>

        <div class="form-actions mt-30">
            <button type="submit" class="btn btn-primary"
                    onclick="document.querySelector('[name=action]').value='add';">
                Create Product
            </button>
            <a href="product_list.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<?php include '../_foot.php'; ?>