<?php
require '../_base.php';
require_login();
require_admin();

$_title = 'Product Management - Admin';

// === 参数处理（你原来的全部保留）===
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = (int)($_GET['limit'] ?? 5);
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? '';
$stock_status = $_GET['stock_status'] ?? 'all'; // all, low, out

$where = "WHERE p.is_active = 1";
$params = [];

if ($search !== '') {
    $where .= " AND (p.product_name LIKE ? OR p.product_code LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category !== '') {
    $where .= " AND p.category_id = ?";
    $params[] = $category;
}
if ($stock_status === 'low') {
    $where .= " AND p.stock_quantity <= 10 AND p.stock_quantity > 0";
} elseif ($stock_status === 'out') {
    $where .= " AND p.stock_quantity = 0";
}

// 总数
$count_stmt = $_db->prepare("SELECT COUNT(*) FROM product p $where");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = max(1, ceil($total / $limit));

// 数据
$sql = "SELECT p.*, c.category_name, c.category_code
        FROM product p
        JOIN category c ON p.category_id = c.category_id
        $where
        ORDER BY p.created_at DESC
        LIMIT $limit OFFSET $offset";

$stmt = $_db->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$cats = $_db->query("SELECT * FROM category ORDER BY category_name")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?> • Moe Moe Pet Mart</title>
    <link rel="stylesheet" href="/css/app.css">
</head>
<body>

<div class="admin-layout">

    <!-- 左侧侧边栏 -->
    <aside class="admin-sidebar">
        <div class="logo">
            <h2>MoeMoePet</h2>
        </div>
        <ul>
            <li><a href="../admin.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
            <li><a href="product_list.php" class="active"><i class="fas fa-box"></i> <span>Products</span></a></li>
            <li><a href="#"><i class="fas fa-shopping-cart"></i> <span>Orders (Soon)</span></a></li>
            <li><a href="#"><i class="fas fa-users"></i> <span>Members (Soon)</span></a></li>
            <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- 主内容区 -->
    <main class="admin-main">
        <!-- 顶部栏 -->
        <header class="admin-header">
            <h1>Product Management</h1>
            <div class="admin-user">
                <i class="fas fa-user-circle"></i>
                <span><?= encode($_SESSION['user']) ?></span>
            </div>
        </header>

        <!-- 工具栏 -->
        <div class="toolbar">
            <input type="text" name="search" placeholder="Search products..." value="<?= encode($search) ?>">

            <select name="category">
                <option value="">All Categories</option>
                <?php foreach ($cats as $c): ?>
                    <option value="<?= $c->category_id ?>" <?= $category == $c->category_id ? 'selected' : '' ?>>
                        <?= encode($c->category_name) ?>
                    </option>
                <?php endforeach; ?>

            </select>

            <select name="stock_status">
                <option value="all" <?= $stock_status === 'all' ? 'selected' : '' ?>>All Stock</option>
                <option value="low" <?= $stock_status === 'low' ? 'selected' : '' ?>>Low Stock</option>
                <option value="out" <?= $stock_status === 'out' ? 'selected' : '' ?>>Out of Stock</option>
            </select>

            <button onclick="applyFilters()">Apply</button>
            <a href="product_add.php" class="btn-add">Add New</a>
            
        </div>

        <!-- 表格卡片 -->
        <div class="table-card">
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Product Code</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>On Hand</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $p): ?>
                    <tr>
                        <td>
                            <?php if ($p->photo_name): ?>
                                <img src="../admin/uploads/products/<?= encode($p->photo_name) ?>" class="photo_name" alt="photo_name" style="width:50px;height:50px;object-fit:cover;border-radius:10px;">
                            <?php else: ?>
                                <div style="width:50px;height:50px;background:#f0f0f0;border-radius:10px;"></div>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= encode($p->product_code) ?></strong></td>
                        <td><?= encode($p->product_name) ?></td>
                        <td><?= encode($p->category_name) ?></td>
                        <td>RM <?= number_format($p->price, 2) ?></td>
                        <td class="<?= $p->stock_quantity == 0 ? 'out-stock' : ($p->stock_quantity <= 10 ? 'low-stock' : '') ?>">
                            <?= $p->stock_quantity ?>
                            <?php if ($p->stock_quantity <= 10): ?>
                                (<?= $p->stock_quantity == 0 ? 'Out' : 'Low' ?>)
                            <?php endif; ?>
                        <td class="action-links">
                            <a href="product_view.php?id=<?= $p->product_id ?>"
                            class="text-pink-500 hover:text-pink-700 hover:underline font-medium transition">
                            View
                        </a>
                        <span class="text-pink-300 mx-3">·</span>

                        <a href="product_edit.php?id=<?= $p->product_id ?>"
                            class="text-pink-500 hover:text-pink-700 hover:underline font-medium transition">
                            Edit
                        </a>
                        <span class="text-pink-300 mx-3">·</span>

                        <!-- Delete：用 button 但伪装成纯文字，完美融入你的风格 -->
                        <form method="post" action="product_delete.php" style="display:inline;"
                            onsubmit="return confirm('Are you sure you want to delete it completely?「<?= encode($p->product_name) ?>」？\n\nThis operation cannot be undone!');">
                            <input type="hidden" name="product_id" value="<?= $p->product_id ?>">
                            <button type="submit" class="delete-link">
                                Delete
                            </button>
                        </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- 分页 -->
        <div class="pagination">
            <span>Page <?= $page ?> of <?= $total_pages ?></span>
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&stock_status=<?= $stock_status ?>">Previous</a>
            <?php endif; ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page+1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>&category=<?= $category ?>&stock_status=<?= $stock_status ?>">Next</a>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
// 让 Apply 按钮真的能提交筛选
function applyFilters() {
    const search = document.querySelector('input[name="search"]').value;
    const category = document.querySelector('select[name="category"]').value;
    const stock = document.querySelector('select[name="stock_status"]').value;
    const url = new URL(location);
    url.searchParams.set('search', search);
    url.searchParams.set('category', category);
    url.searchParams.set('stock_status', stock);
    url.searchParams.set('page', 1);
    location = url;
}
</script>

</body>
</html>