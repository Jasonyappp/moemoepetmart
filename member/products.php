<?php
require '../_base.php';

if (user_role() === 'admin') {
    temp('error', 'Admins cannot shop here! Use member account ♡');
    redirect('/admin.php');
}

$_title = 'Pet Supplies ♡ Moe Moe Pet Mart';

$search = trim(get('search', ''));
$category = get('category', ''); // New: Get category filter
$price_min = get('price_min', '');
$price_max = get('price_max', '');

// === Pagination ===
$page = max(1, (int)get('page', 1));
$per_page = 9;
$offset = ($page - 1) * $per_page;

// === Sorting ===
$sort = get('sort', 'relevance');
$order_by = 'p.product_name ASC';
$sales_join = '';

if ($sort === 'top_sales') {
    $sales_join = "
        LEFT JOIN (
            SELECT oi.product_id, SUM(oi.quantity) as sales 
            FROM order_items oi 
            JOIN orders o ON oi.order_id = o.order_id 
            WHERE o.order_status = 'Completed' 
            GROUP BY oi.product_id
        ) s ON p.product_id = s.product_id
    ";
    $order_by = 'COALESCE(s.sales, 0) DESC, p.product_name ASC';
} elseif ($sort === 'latest') {
    $order_by = 'p.created_at DESC';
} elseif ($sort === 'price_low') {
    $order_by = 'p.price ASC';
} elseif ($sort === 'price_high') {
    $order_by = 'p.price DESC';
}

// === Build WHERE clause ===
$where_conditions = ['p.is_active = 1'];
$params = [];

if ($search !== '') {
    $where_conditions[] = '(p.product_name LIKE :search OR p.description LIKE :search)';
    $params[':search'] = "%$search%";
}

if ($category !== '') {
    $where_conditions[] = 'c.category_id = :category';
    $params[':category'] = $category;
}

if ($price_min !== '' && is_numeric($price_min)) {
    $where_conditions[] = 'p.price >= :price_min';
    $params[':price_min'] = $price_min;
}

if ($price_max !== '' && is_numeric($price_max)) {
    $where_conditions[] = 'p.price <= :price_max';
    $params[':price_max'] = $price_max;
}

$where_clause = 'WHERE ' . implode(' AND ', $where_conditions);

// === Count total products ===
$sql_count = "
    SELECT COUNT(*) 
    FROM product p 
    JOIN category c ON p.category_id = c.category_id 
    $sales_join
    $where_clause
";
$stm_count = $_db->prepare($sql_count);
foreach ($params as $key => $value) {
    $stm_count->bindValue($key, $value);
}
$stm_count->execute();
$total = $stm_count->fetchColumn();
$total_pages = max(1, ceil($total / $per_page));

// === Fetch products for current page ===
$sql = "
    SELECT p.*, c.category_name 
    FROM product p 
    JOIN category c ON p.category_id = c.category_id 
    $sales_join
    $where_clause
    ORDER BY $order_by 
    LIMIT $offset, $per_page
";

$stm = $_db->prepare($sql);
foreach ($params as $key => $value) {
    $stm->bindValue($key, $value);
}
$stm->execute();
$products = $stm->fetchAll();

// === Fetch all categories for filter ===
$stm_cat = $_db->query("SELECT category_id, category_name FROM category ORDER BY category_name");
$categories = $stm_cat->fetchAll();

include '../_head.php';
?>

<div class="container">
    <div class="products-layout">
        <!-- Sidebar Filter -->
        <aside class="filter-sidebar">
            <h3>Filters ♡</h3>
            
            <!-- Price Range Filter -->
            <div class="filter-section">
                <h4>Price Range</h4>
                <form method="get" class="price-filter-form">
                    <!-- Hidden fields to preserve other filters -->
                    <input type="hidden" name="search" value="<?= encode($search) ?>">
                    <input type="hidden" name="category" value="<?= encode($category) ?>">
                    <input type="hidden" name="sort" value="<?= encode($sort) ?>">
                    
                    <div class="price-inputs">
                        <input type="number" 
                               name="price_min" 
                               placeholder="RM MIN" 
                               value="<?= encode($price_min) ?>"
                               min="0"
                               step="0.01"
                               class="price-input">
                        <span class="price-separator">—</span>
                        <input type="number" 
                               name="price_max" 
                               placeholder="RM MAX" 
                               value="<?= encode($price_max) ?>"
                               min="0"
                               step="0.01"
                               class="price-input">
                    </div>
                    <button type="submit" class="btn-apply-price">APPLY</button>
                    
                    <?php if ($price_min !== '' || $price_max !== ''): ?>
                        <a href="?search=<?= urlencode($search) ?>&category=<?= $category ?>&sort=<?= $sort ?>&page=1" 
                           class="btn-clear-price">Clear Price Filter</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Category Filter (moved to sidebar) -->
            <div class="filter-section">
                <h4>Categories</h4>
                <div class="category-list">
                    <a href="?sort=<?= $sort ?>&page=1&search=<?= urlencode($search) ?>&price_min=<?= $price_min ?>&price_max=<?= $price_max ?>" 
                       class="category-item <?= $category === '' ? 'active' : '' ?>">
                        All Categories
                    </a>
                    <?php foreach ($categories as $cat): ?>
                        <a href="?category=<?= $cat->category_id ?>&sort=<?= $sort ?>&page=1&search=<?= urlencode($search) ?>&price_min=<?= $price_min ?>&price_max=<?= $price_max ?>" 
                           class="category-item <?= $category == $cat->category_id ? 'active' : '' ?>">
                            <?= encode($cat->category_name) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="products-main">
            <h2>Our Adorable Pet Supplies ♡</h2>

            <form method="get" class="search-form">
                <!-- Preserve filters in search -->
                <input type="hidden" name="category" value="<?= encode($category) ?>">
                <input type="hidden" name="price_min" value="<?= encode($price_min) ?>">
                <input type="hidden" name="price_max" value="<?= encode($price_max) ?>">
                <input type="text" name="search" placeholder="Search products..." value="<?= encode($search) ?>">
                <button type="submit">Search ♡</button>
            </form>

    <!-- Sorting and Pagination Bar -->
    <div class="sort-bar">
        <span>Sort by</span>
        <a href="?category=<?= $category ?>&sort=relevance&page=1&search=<?= urlencode($search) ?>&price_min=<?= $price_min ?>&price_max=<?= $price_max ?>" class="<?= $sort === 'relevance' || $sort === '' ? 'active' : '' ?>">Relevance</a>
        <a href="?category=<?= $category ?>&sort=latest&page=1&search=<?= urlencode($search) ?>&price_min=<?= $price_min ?>&price_max=<?= $price_max ?>" class="<?= $sort === 'latest' ? 'active' : '' ?>">Latest</a>
        <a href="?category=<?= $category ?>&sort=top_sales&page=1&search=<?= urlencode($search) ?>&price_min=<?= $price_min ?>&price_max=<?= $price_max ?>" class="<?= $sort === 'top_sales' ? 'active' : '' ?>">Top Sales</a>
        
        <div class="price-sort <?= in_array($sort, ['price_low', 'price_high']) ? 'active' : '' ?>">
            <?php 
            if ($sort === 'price_low') echo 'Price: Low to High';
            elseif ($sort === 'price_high') echo 'Price: High to Low';
            else echo 'Price';
            ?> 
            <span class="dropdown-arrow">▼</span>
            <div class="price-options">
                <a href="?category=<?= $category ?>&sort=price_low&page=1&search=<?= urlencode($search) ?>&price_min=<?= $price_min ?>&price_max=<?= $price_max ?>">Low to High</a>
                <a href="?category=<?= $category ?>&sort=price_high&page=1&search=<?= urlencode($search) ?>&price_min=<?= $price_min ?>&price_max=<?= $price_max ?>">High to Low</a>
            </div>
        </div>

        <div class="page-info">
            <?php if ($page > 1): ?>
                <a href="?page=<?= $page-1 ?>&category=<?= $category ?>&sort=<?= $sort ?>&search=<?= urlencode($search) ?>&price_min=<?= $price_min ?>&price_max=<?= $price_max ?>" class="page-arrow">&lt;</a>
            <?php else: ?>
                <span class="page-arrow disabled">&lt;</span>
            <?php endif; ?>
            <?= $page ?>/<?= $total_pages ?>
            <?php if ($page < $total_pages): ?>
                <a href="?page=<?= $page+1 ?>&category=<?= $category ?>&sort=<?= $sort ?>&search=<?= urlencode($search) ?>&price_min=<?= $price_min ?>&price_max=<?= $price_max ?>" class="page-arrow">&gt;</a>
            <?php else: ?>
                <span class="page-arrow disabled">&gt;</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="product-grid">
        <?php if (empty($products)): ?>
            <p>No products found~ Try another search! ♡</p>
        <?php else: ?>
            <?php foreach ($products as $p): ?>
                <div class="product-card">
                    <?php if ($p->photo_name): ?>
                        <a href="product_detail.php?id=<?= $p->product_id ?>">
                            <img src="/admin/uploads/products/<?= encode($p->photo_name) ?>" alt="<?= encode($p->product_name) ?>">
                        </a>
                    <?php endif; ?>
                    <a href="product_detail.php?id=<?= $p->product_id ?>">
                        <h3><?= encode($p->product_name) ?></h3>
                        <p class="price">RM <?= number_format($p->price, 2) ?></p>
                        <p>Stock: <?= $p->stock_quantity ?></p>
                    </a>
                    <?php if ($p->stock_quantity > 0): ?>
                        <button class="add-to-cart" data-id="<?= $p->product_id ?>" data-name="<?= encode($p->product_name) ?>" data-price="<?= $p->price ?>">
                            Add to Cart ♡
                        </button>
                    <?php else: ?>
                        <p>Out of Stock~</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <?php 
    $cart_items = 0;
    if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {
        $cart_items = array_sum(array_column($_SESSION['cart'], 'qty'));
    }
    $cart_text = $cart_items > 0 ? "View Cart ($cart_items items)" : "View Cart";
    ?>

    <div class="cart-floating-btn">
        <a href="cart.php" class="moe-cart-btn">
            <span class="cart-icon">🛒</span>
            <span class="cart-text"><?= $cart_text ?></span>
            <?php if ($cart_items > 0): ?>
                <span class="cart-badge"><?= $cart_items ?></span>
            <?php endif; ?>
        </a>
    </div>
</div>

<?php include '../_foot.php'; ?>

<style>
.products-layout {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.filter-sidebar {
    width: 250px;
    background: white;
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(255,105,180,0.1);
    height: fit-content;
    position: sticky;
    top: 20px;
}

.filter-sidebar h3 {
    color: #ff69b4;
    font-size: 1.5rem;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #fff0f5;
}

.filter-section {
    margin-bottom: 25px;
}

.filter-section h4 {
    color: #333;
    font-size: 1rem;
    margin-bottom: 12px;
    font-weight: 600;
}

.price-filter-form {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.price-inputs {
    display: flex;
    align-items: center;
    gap: 8px;
}

.price-input {
    flex: 1;
    padding: 10px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 0.9rem;
    transition: all 0.3s;
}

.price-input:focus {
    outline: none;
    border-color: #ff69b4;
}

.price-separator {
    color: #999;
    font-weight: bold;
}

.btn-apply-price {
    background: linear-gradient(135deg, #ff69b4, #ff1493);
    color: white;
    border: none;
    padding: 12px;
    border-radius: 8px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s;
    font-size: 1rem;
}

.btn-apply-price:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(255,20,147,0.3);
}

.btn-clear-price {
    text-align: center;
    color: #ff69b4;
    text-decoration: none;
    font-size: 0.9rem;
    transition: color 0.3s;
}

.btn-clear-price:hover {
    color: #ff1493;
    text-decoration: underline;
}

.category-list {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.category-item {
    padding: 10px 12px;
    background: #f8f8f8;
    border-radius: 8px;
    text-decoration: none;
    color: #333;
    transition: all 0.3s;
    font-size: 0.95rem;
}

.category-item:hover {
    background: #fff0f5;
    color: #ff69b4;
    transform: translateX(5px);
}

.category-item.active {
    background: linear-gradient(135deg, #ff69b4, #ff1493);
    color: white;
    font-weight: 600;
}

.products-main {
    flex: 1;
}

.sort-bar {
    background: #f8f8f8;
    padding: 12px 20px;
    margin: 20px 0;
    border-radius: 8px;
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    font-size: 1rem;
}

.sort-bar > span {
    font-weight: bold;
    margin-right: 10px;
    color: #333;
}

.separator {
    color: #ccc;
    font-weight: normal !important;
    margin: 0 5px !important;
}

.sort-bar a {
    padding: 8px 16px;
    border-radius: 20px;
    text-decoration: none;
    color: #333;
    background: white;
    transition: all 0.3s;
}

.sort-bar a.active {
    background: #ff5722;
    color: white;
}

.category-filter,
.price-sort {
    position: relative;
    padding: 8px 16px;
    border-radius: 20px;
    background: white;
    cursor: pointer;
    user-select: none;
}

.category-filter.active,
.price-sort.active {
    background: #ff69b4;
    color: white;
}

.category-options,
.price-options {
    display: none;
    position: absolute;
    top: 100%;
    left: 0;
    background: white;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    z-index: 100;
    min-width: 200px;
    margin-top: 5px;
}

.category-filter:hover .category-options,
.price-sort:hover .price-options {
    display: block;
}

.category-options a,
.price-options a {
    display: block;
    padding: 10px 16px;
    color: #333;
    border-radius: 0;
}

.category-options a:first-child {
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}

.category-options a:last-child {
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}

.category-options a:hover,
.price-options a:hover {
    background: #fff0f5;
    color: #ff69b4;
}

.dropdown-arrow {
    font-size: 0.8em;
    margin-left: 5px;
}

.page-info {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: 15px;
    font-weight: bold;
}

.page-arrow {
    font-size: 1.4rem;
    color: #ff5722;
    text-decoration: none;
}

.page-arrow.disabled {
    color: #ccc;
    pointer-events: none;
}


@media (max-width: 992px) {
    .products-layout {
        flex-direction: column;
    }
    
    .filter-sidebar {
        width: 100%;
        position: static;
    }
}
</style>