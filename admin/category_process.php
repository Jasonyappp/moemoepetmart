<?php
require '../_base.php';
require_login();
require_admin();

$action = get('action');

if ($action === 'add_form' || $action === 'edit_form') {
    $id = get('id');
    $cat = null;
    if ($id) {
        $stmt = $_db->prepare("SELECT * FROM category WHERE category_id = ?");
        $stmt->execute([$id]);
        $cat = $stmt->fetch();
    }
    $_title = $action === 'add_form' ? 'Add Category' : 'Edit Category';
    include '../_head.php';
    ?>
    <div class="container">
        <h2><?= $action === 'add_form' ? 'Add New Category' : 'Edit Category' ?></h2>
        <form action="category_process.php" method="post">
            <input type="hidden" name="action" value="<?= $action === 'add_form' ? 'add' : 'edit' ?>">
            <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
            
            <label>Category Code (e.g. TOY, FOD) <span class="req">*</span></label>
            <input type="text" name="code" value="<?= $cat->category_code ?? '' ?>" maxlength="10" required 
                   <?= $cat ? 'readonly' : '' ?>>
            
            <label>Category Name <span class="req">*</span></label>
            <input type="text" name="name" value="<?= encode($cat->category_name ?? '') ?>" required>
            
            <label>Description</label>
            <textarea name="description"><?= encode($cat->description ?? '') ?></textarea>
            
            <div class="form-actions mt-20">
                <button type="submit" class="btn btn-primary">Save Category</button>
                <a href="category_list.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
    <?php include '../_foot.php'; 
    exit;
}

if ($action === 'add') {
    $_db->prepare("INSERT INTO category (category_code, category_name, description) VALUES (?, ?, ?)")
        ->execute([post('code'), post('name'), post('description')]);
    temp('info', 'Category added');
}
elseif ($action === 'edit') {
    $_db->prepare("UPDATE category SET category_name = ?, description = ? WHERE category_id = ?")
        ->execute([post('name'), post('description'), post('id')]);
    temp('info', 'Category updated');
}
elseif ($action === 'delete') {
    $id = get('id');
    $_db->prepare("DELETE FROM category WHERE category_id = ?")->execute([$id]);
    temp('info', 'Category deleted');
}

redirect('category_list.php');