<?php
require '../_base.php';
require_login();
require_admin();

$_title = 'Add New Product - Admin';
include '../_head.php';

// Get next product code suggestion
$category_id = post('category_id', get('category_id', ''));
$next_code = '';
if ($category_id) {
    $stmt = $_db->prepare("SELECT category_code FROM category WHERE category_id = ?");
    $stmt->execute([$category_id]);
    $cat = $stmt->fetch();
    if ($cat) {
        $stmt2 = $_db->prepare("SELECT product_code FROM product WHERE product_code LIKE ? ORDER BY product_code DESC LIMIT 1");
        $stmt2->execute(["{$cat->category_code}%"]);
        $last = $stmt2->fetchColumn();
        if ($last) {
            $num = (int)substr($last, 3) + 1;
            $next_code = $cat->category_code . str_pad($num, 4, '0', STR_PAD_LEFT);
        } else {
            $next_code = $cat->category_code . '0001';
        }
    }
}

$cats = $_db->query("SELECT * FROM category ORDER BY category_name")->fetchAll();
?>

<div class="container">
    <h2>Add New Product</h2>
    <form action="product_process.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        
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
                       <?= $next_code ? '' : 'readonly placeholder="Select category first"' ?>>
                <?php if ($next_code): ?>
                    <small>Suggested: <strong><?= $next_code ?></strong></small>
                <?php endif; ?>
            </div>

            <div>
                <label>Product Name <span class="req">*</span></label>
                <input type="text" name="product_name" required maxlength="200">
            </div>

            <div>
                <label>Price (RM) <span class="req">*</span></label>
                <input type="number" name="price" step="0.01" min="0" required>
            </div>

            <div>
                <label>Stock Quantity</label>
                <input type="number" name="stock_quantity" value="0" min="0">
            </div>
        </div>

        <label>Description</label>
        <textarea name="description" rows="4"></textarea>

        <label>Product Images (Drag & drop or click)</label>
        <div id="dropzone-upload" class="dropzone"></div>

        <div class="form-actions mt-20">
            <button type="submit" class="btn btn-primary">Create Product</button>
            <a href="product_list.php" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>

<script>
Dropzone.autoDiscover = false;
new Dropzone("#dropzone-upload", {
    url: "product_process.php",
    paramName: "product_images",
    maxFilesize: 5,
    acceptedFiles: "image/*",
    addRemoveLinks: true,
    init: function() {
        this.on("sending", function(file, xhr, formData) {
            formData.append("action", "upload_temp");
        });
    }
});
</script>

<?php include '../_foot.php'; ?>