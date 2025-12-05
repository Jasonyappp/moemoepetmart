<?php
require '../_base.php';
require_login();
require_admin();

$id = get('id');
if (!$id || !is_numeric($id)) redirect('product_list.php');

// ====================== 删除主图 ======================
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

// ====================== 表单提交 ======================
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

            // 处理新上传的主图
            if (!empty($_FILES['productImage']['name']) && $_FILES['productImage']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['productImage'];
                if ($file['size'] > 10*1024*1024) throw new Exception('Images cannot exceed 10MB.');
                $allowed = ['image/jpeg','image/jpg','image/png','image/gif','image/webp'];
                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                $mime = finfo_file($finfo, $file['tmp_name']);
                finfo_close($finfo);
                if (!in_array($mime, $allowed)) throw new Exception('Only images are allowed.');

                // 删除旧图
                $stmt = $_db->prepare("SELECT photo_name FROM product WHERE product_id = ?");
                $stmt->execute([$id]);
                $old = $stmt->fetchColumn();   // fetchColumn() 直接返回第一列的值（或 false）
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

// ====================== 加载数据 ======================
$stmt = $_db->prepare("SELECT p.*, c.category_name, c.category_code 
                       FROM product p 
                       LEFT JOIN category c ON p.category_id = c.category_id 
                       WHERE p.product_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch(PDO::FETCH_OBJ);
if (!$product) { temp('error', 'Product does not exist.'); redirect('product_list.php'); }

$_title = 'Edit • ' . ($product->product_name);
include '../_head.php'; // 你的粉色后台头部
?>

<div class="product-edit-container">

    <!-- 替换你原来的 <div class="product-edit-container"> 开始到结束 -->
<div class="container">
    <h2>Edit Product ♡</h2>

    <form method="post" enctype="multipart/form-data">

        <div class="form-grid">

            <!-- 左侧：基本信息 -->
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

            <!-- 右侧：图片管理 -->
            <div>
                <label>Current Main Image</label>
                <div class="existing-images" style="margin:20px 0;">
                    <?php if ($product->photo_name): ?>
                        <div class="image-item">
                            <img src="../admin/uploads/products/<?= $product->photo_name ?>" alt="Main image">
                            <div>
                                <a href="?id=<?= $id ?>&delete_photo=1" 
                                   onclick="return confirm('Are you sure you want to delete this main image?')"
                                   style="color:white; background:rgba(255,71,87,0.9); padding:6px 12px; border-radius:50px; font-size:0.9rem;">
                                   Delete
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <p style="text-align:center;color:#ff69b4;padding:40px 0;font-size:1.3rem;">
                            There is no main image yet.
                        </p>
                    <?php endif; ?>
                </div>

                <label>Replace Main Image</label>
                
                <!-- 关键！改成你 CSS 里写好的 id -->
                <div id="dropzone-upload" class="dz-message">
                    Click or drag a new cute photo to replace ♡<br>
                    <input 
                        type="file" 
                        name="productImage" 
                        accept="image/*" 
                        id="file-input" 
                        style="display:none !important;"
                    >
                    <strong style="font-size:3rem;margin:15px 0;display:block;">Drop here~</strong>
                </div>

                <!-- 预览区域（你的 JS 已经完美支持）-->
                <div id="preview-area" class="hidden" style="margin-top:25px;text-align:center;">
                    <p style="color:#ff1493;font-weight:bold;">Soon to be replaced with:</p>
                    <img id="preview-img" src="" style="max-height:340px;border-radius:25px;border:6px solid #ff69b4;box-shadow:0 15px 35px rgba(255,105,180,0.4);">
                    <button type="button" id="cancel-preview" class="moe-btn moe-btn-secondary" style="margin-top:15px;padding:12px 30px;font-size:1rem;">
                        Cancel Replacement
                    </button>
                </div>

                <!-- 隐藏的 file input（必须保留）-->
                <!-- <input type="file" name="productImage" accept="image/*" id="file-input" class="hidden"> -->
            </div>

        </div>

        <!-- 描述（跨列）-->
        <div style="margin-top:35px;">
            <label>Description</label>
            <textarea name="description" rows="7" style="width:100%;padding:18px 22px;border:4px solid #ffeef8;border-radius:20px;background:#fff8fb;font-size:1.1rem;"><?= ($product->description) ?></textarea>
        </div>

        <!-- 按钮区 -->
        <div class="form-actions">
            <button type="submit" class="btn-primary">Save Changes ♡</button>
            <a href="product_list.php" class="btn-secondary">Cancel</a>
        </div>

    </form>
</div>

<!-- 保留你原来的完整拖拽 + 预览 JS -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('file-input');
    const previewArea = document.getElementById('preview-area');
    const previewImg = document.getElementById('preview-img');
    const cancelBtn = document.getElementById('cancel-preview');
    const hasImageDiv = document.getElementById('has-image');
    const noImageDiv = document.getElementById('no-image');
    const dropzone = document.getElementById('dropzone-upload');

    // 当前是否有主图（用于取消时恢复对应提示）
    const hasMainImage = <?= $product->photo_name ? 'true' : 'false' ?>;

    function showPreview(file) {
        if (!file) return;
        if (file.size > 10*1024*1024) { alert('图片不能超过10MB哦~'); input.value=''; return; }
        const reader = new FileReader();
        reader.onload = e => {
            hasImageDiv.classList.add('hidden');
            noImageDiv.classList.add('hidden');
            previewArea.classList.remove('hidden');
            previewImg.src = e.target.result;
        };
        reader.readAsDataURL(file);
    }
    

    input.addEventListener('change', () => input.files[0] && showPreview(input.files[0]));

    cancelBtn.addEventListener('click', () => {
        input.value = '';
        previewArea.classList.add('hidden');
        if (<?= $product->photo_name ? 'true' : 'false' ?>) {
            hasImageDiv.classList.remove('hidden');
        } else {
            noImageDiv.classList.remove('hidden');
        }
    });

    // 拖拽效果
    ['dragover','dragenter'].forEach(e => dropzone.addEventListener(e, ev => {
        ev.preventDefault();
        dropzone.style.borderColor = '#ff1493';
        dropzone.style.background = '#fff0f5';
    }));
    ['dragleave','dragend','drop'].forEach(e => dropzone.addEventListener(e, ev => {
        ev.preventDefault();
        dropzone.style.borderColor = '#ff69b4';
        dropzone.style.background = '';
    }));
    dropzone.addEventListener('drop', e => {
        if (e.dataTransfer.files[0]) {
            input.files = e.dataTransfer.files;
            showPreview(e.dataTransfer.files[0]);
        }
    });

    dropzone.addEventListener('click', () => input.click());
});
</script>

