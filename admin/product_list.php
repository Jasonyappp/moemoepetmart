<?php
require '../_base.php';
require_login();
require_admin();

$_title = 'Product Management - Admin';
include '../_head.php';

// Pagination & Search
$page = get('page', 1);
$limit = 10;
$offset = ($page - 1) * $limit;

$search = trim(get('search', ''));
$category_filter = get('category', '');

$where = "WHERE p.is_active = 1";
$params = [];

if ($search !== '') {
    $where .= " AND (p.product_name LIKE ? OR p.product_code LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category_filter !== '') {
    $where .= " AND p.category_id = ?";
    $params[] = $category_filter;
}

$count_stmt = $_db->prepare("SELECT COUNT(*) FROM product p $where");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = ceil($total / $limit);

$sql = "
    SELECT p.*, c.category_name, c.category_code,
           (SELECT image_path FROM product_image WHERE product_id = p.product_id AND is_main = 1 LIMIT 1) as main_image
    FROM product p
    JOIN category c ON p.category_id = c.category_id
    $where
    ORDER BY p.created_at DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $_db->prepare($sql);
$stmt->execute($params);  
$products = $stmt->fetchAll();

// Get categories for filter
$cats = $_db->query("SELECT * FROM category ORDER BY category_name")->fetchAll();
?>

<div class="container">
    <h2>Product Management</h2>
    
    <div class="admin-actions mb-20">
        <a href="product_add.php" class="btn btn-primary">Add New Product</a>
        <a href="category_list.php" class="btn btn-secondary">Manage Categories</a>
    </div>

    <form method="get" class="search-form mb-20">
        <input type="text" name="search" value="<?= encode($search) ?>" placeholder="Search by name or code...">
        <select name="category">
            <option value="">All Categories</option>
            <?php foreach ($cats as $c): ?>
                <option value="<?= $c->category_id ?>" <?= $category_filter == $c->category_id ? 'selected' : '' ?>>
                    <?= encode($c->category_name) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Search</button>
    </form>

    <?php if ($total == 0): ?>
        <p>No products found.</p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Image</th>
                    <th>Code</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <?php if ($p->main_image): ?>
                                <img src="../<?= encode($p->main_image) ?>" width="50" height="50" style="object-fit: cover;">
                            <?php else: ?>
                                <div style="width:50px;height:50px;background:#eee;border:1px dashed #ccc;"></div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= encode($p->product_code) ?></strong></td>
                        <td><?= encode($p->product_name) ?></td>
                        <td><?= encode($p->category_name) ?></td>
                        <td>RM <?= number_format($p->price, 2) ?></td>
                        <td class="<?= $p->stock_quantity <= 5 ? 'text-danger' : '' ?>">
                            <?= $p->stock_quantity ?> <?= $p->stock_quantity <= 5 ? '(Low Stock!)' : '' ?>
                        </td>
                        <td>
                            <a href="product_view.php?id=<?= $p->product_id ?>" class="btn-small">View</a>
                            <a href="product_edit.php?id=<?= $p->product_id ?>" class="btn-small btn-edit">Edit</a>
                            <a href="product_process.php?delete=<?= $p->product_id ?>" 
                               onclick="return confirm('Delete <?= encode($p->product_name) ?>?')" 
                               class="btn-small btn-delete">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>&category=<?= $category_filter ?>" 
                       class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<?php include '../_foot.php'; ?>