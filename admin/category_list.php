<?php
require '../_base.php';
require_login();
require_admin();

$_title = 'Category Management - Admin';
$cats = $_db->query("SELECT * FROM category ORDER BY category_name")->fetchAll();
include '../_head.php';
?>

<div class="container">
    <h2>Category Management</h2>
    <a href="category_process.php?action=add_form" class="btn btn-primary mb-20">Add New Category</a>

    <table class="data-table">
        <thead>
            <tr>
                <th>Code</th>
                <th>Name</th>
                <th>Description</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cats as $c): ?>
                <tr>
                    <td><strong><?= encode($c->category_code) ?></strong></td>
                    <td><?= encode($c->category_name) ?></td>
                    <td><?= encode($c->description ?: '-') ?></td>
                    <td>
                        <a href="category_process.php?action=edit_form&id=<?= $c->category_id ?>" class="btn-small">Edit</a>
                        <a href="category_process.php?action=delete&id=<?= $c->category_id ?>" 
                           onclick="return confirm('Delete category <?= encode($c->category_name) ?>?')" 
                           class="btn-small btn-delete">Delete</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <a href="product_list.php" class="btn btn-secondary">Back to Products</a>
</div>

<?php include '../_foot.php'; ?>