<?php
require '../_base.php';
require_login();
require_admin();

$id = get('id');
if (!$id || !is_numeric($id)) redirect('product_list.php');

$stmt = $_db->prepare("SELECT p.*, c.category_code FROM product p JOIN category c ON p.category_id = c.category_id WHERE p.product_id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();
if (!$product) {
    temp('error', 'Product not found');
    redirect('product_list.php');
}

// Load existing images
$images = $_db->prepare("SELECT * FROM product_image WHERE product_id = ? ORDER BY is_main DESC, sort_order");
$images->execute([$id]);

$cats = $_db->query("SELECT * FROM category ORDER BY category_name")->fetchAll();

$_title = 'Edit Product - Admin';
include '../_head.php';
?>

<div class="container">
    <h2>Edit Product</h2>
    <form action="product_process.php" method="post" enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="product_id" value="<?= $product->product_id ?>">

        <div class="form-grid">
            <div>
                <label>Category</label>
                <select name="category_id" required>
                    <?php foreach ($cats as $c): ?>
                        <option value="<?= $c->category_id ?>" <?= $c->category_id == $product->category_id ? 'selected' : '' ?>>
                            <?= encode($c->category_name) ?> (<?= $c->category_code ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label>Product Code</label>
                <input type="text" name="product_code" value="<?= encode($product->product_code) ?>" readonly>
                <small>This code cannot be changed</small>
            </div>

            <div>
                <label>Product Name <span class="req">*</span></label>
                <input type="text" name="product_name" value="<?= encode($product->product_name) ?>" required>
            </div>

            <div>
                <label>Price (RM) <span class="req">*</span></label>
                <input type="number" name="price" step="0.01" value="<?= $product->price ?>" required>
            </div>

            <div>
                <label>Stock Quantity</label>
                <input type="number" name="stock_quantity" value="<?= $product->stock_quantity ?>" min="0">
            </div>
        </div>

        <label>Description</label>
        <textarea name="description" rows="4"><?= encode($product->description) ?></textarea>

        <label>Current Images</label>
        <div class="existing-images">
            <?php foreach ($images as $img): ?>
                <div class="image-item">
                    <img src="../<?= encode($img->image_path) ?>" width="120">
                    <div>
                        <?php if ($img->is_main): ?><strong>Main Image</strong><?php else: ?>
                            <a href="product_process.php?action=set_main&image_id=<?= $img->image_id ?>&product_id=<?= $id ?>" class="btn-small">Set as Main</a>
                        <?php endif; ?>
                        <a href="product_process.php?action=delete_image&image_id=<?= $img->image_id ?>&product_id=<?= $id ?>" 
                           onclick="return confirm('Delete this image?')" class="btn-small btn-delete">Delete</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <label>Add More Images</label>
        <div id="dropzone-upload" class="dropzone"></div>

        <div class="form-actions mt-20">
            <button type="submit" class="btn btn-primary">Update Product</button>
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
            formData.append("product_id", "<?= $id ?>");
        });
    }
});
</script>

<?php include '../_foot.php'; ?>