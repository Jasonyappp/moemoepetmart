<?php
require '../_base.php';
require_login();
require_admin();

$_title = 'Order Management - Admin';

// === Parameters ===
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = (int)($_GET['limit'] ?? 10);
$offset = ($page - 1) * $limit;

$search_id = trim($_GET['search_id'] ?? '');
$date_from = $_GET['date_from'] ?? '';
$date_to = $_GET['date_to'] ?? '';
$status_filter = $_GET['status'] ?? 'all';  // 'all' or specific status

$where = '';
$params = [];

if ($search_id !== '') {
    $where .= " AND o.order_id LIKE ?";
    $params[] = "%$search_id%";
}

if ($date_from !== '') {
    $where .= " AND DATE(o.order_date) >= ?";
    $params[] = $date_from;
}

if ($date_to !== '') {
    $where .= " AND DATE(o.order_date) <= ?";
    $params[] = $date_to;
}

if ($status_filter !== 'all') {
    $where .= " AND o.order_status = ?";
    $params[] = $status_filter;
}

// Handle status update from dropdown
if (is_post()) {
    $order_id = post('order_id');
    $new_status = post('order_status');

    $allowed = ['Pending Payment', 'To Ship', 'Shipped', 'Completed', 'Cancelled', 'Return/Refund'];
    if (in_array($new_status, $allowed)) {
        $_db->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?")
            ->execute([$new_status, $order_id]);
        temp('info', "Order #$order_id status updated to: $new_status ♡");
        redirect('order_list.php?' . http_build_query($_GET));
    }
}

// Count total orders
$count_stmt = $_db->prepare("SELECT COUNT(*) FROM orders o WHERE 1 $where");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = max(1, ceil($total / $limit));

// Fetch orders (summary)
$sql = "
    SELECT o.*, u.username,
           GROUP_CONCAT(CONCAT(oi.quantity, ' x ', p.product_name) SEPARATOR ', ') AS items_summary
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.order_id = oi.order_id
    LEFT JOIN product p ON oi.product_id = p.product_id
    WHERE 1 $where
    GROUP BY o.order_id
    ORDER BY o.order_date DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $_db->prepare($sql);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Status options for filter dropdown
$status_options = [
    'all' => 'All Statuses',
    'Pending Payment' => 'Pending Payment',
    'To Ship' => 'To Ship',
    'Shipped' => 'Shipped',
    'Completed' => 'Completed',
    'Cancelled' => 'Cancelled',
    'Return/Refund' => 'Return/Refund'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?> • Moe Moe Pet Mart</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        .toolbar {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
            padding: 20px;
            background: white;
            border-radius: 25px;
            box-shadow: 0 5px 15px rgba(255,105,180,0.1);
            margin-bottom: 30px;
        }
        .toolbar input, .toolbar select, .toolbar button {
            padding: 12px 18px;
            border-radius: 25px;
            border: 1px solid #ffdee6;
            background: white;
            font-size: 1rem;
        }
        .toolbar button {
            background: #ff69b4;
            color: white;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }
        .toolbar button:hover { background: #ff1493; }

        details summary {
            cursor: pointer;
            font-weight: bold;
            color: #ff69b4;
            margin: 15px 0 10px;
        }
        .order-items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .order-items-table th {
            background: #fff0f5;
            padding: 12px;
            text-align: left;
        }
        .order-items-table td {
            padding: 12px;
            border-bottom: 1px solid #ffd4e4;
        }
        .order-items-table img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
        }
        .total-row {
            background: #fff0f5 !important;
            font-size: 1.2rem;
        }
        .purchase-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            margin-bottom: 25px;
            box-shadow: 0 8px 25px rgba(255,105,180,0.1);
        }
        .order-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 10px;
            font-size: 1.1rem;
            margin-bottom: 15px;
        }
        .order-status {
            padding: 8px 16px;
            border-radius: 20px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        /* Status colors (optional - customize as needed) */
        .order-status.pending-payment { background: #fff3e0; color: #ef6c00; }
        .order-status.to-ship { background: #e3f2fd; color: #1565c0; }
        .order-status.shipped { background: #e8f5e9; color: #2e7d32; }
        .order-status.completed { background: #f1f8e9; color: #558b2f; }
        .order-status.cancelled { background: #ffebee; color: #c62828; }
        .order-status.return-refund { background: #fffde7; color: #f57f17; }
    </style>
</head>
<body>

<div class="admin-layout">
    <aside class="admin-sidebar">
        <div class="logo"><h2>MoeMoePet</h2></div>
        <ul>
            <li><a href="../admin.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
            <li><a href="product_list.php"><i class="fas fa-box"></i> <span>Products</span></a></li>
            <li><a href="member_list.php"><i class="fas fa-users"></i> <span>Members</span></a></li>
            <li><a href="order_list.php" class="active"><i class="fas fa-shopping-cart"></i> <span>Orders</span></a></li>
            <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <h1>Order Management ♡</h1>
            <div class="admin-user">
                <i class="fas fa-user-circle"></i>
                <span><?= encode($_SESSION['user']) ?></span>
            </div>
        </header>

        <!-- Toolbar with Filters -->
        <div class="toolbar">
            <input type="text" name="search_id" placeholder="Search by Order ID..." value="<?= encode($search_id) ?>">

            <input type="date" name="date_from" value="<?= encode($date_from) ?>">
            <input type="date" name="date_to" value="<?= encode($date_to) ?>">

            <select name="status">
                <?php foreach ($status_options as $value => $label): ?>
                    <option value="<?= $value ?>" <?= $status_filter === $value ? 'selected' : '' ?>>
                        <?= $label ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button onclick="applyFilters()">Apply Filters</button>
            <button onclick="clearFilters()" style="background:#ccc;">Clear</button>
        </div>

        <div class="table-card">
            <?php if (empty($orders)): ?>
                <div class="empty-full" style="text-align:center;padding:80px;color:#999;">
                    No orders found~ ♡<br>
                    <?php if ($search_id || $date_from || $date_to || $status_filter !== 'all'): ?>
                        Try adjusting your filters!
                    <?php else: ?>
                        Waiting for the first cute order! ♡
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $o): ?>
                    <div class="purchase-card">
                        <div class="order-header">
                            <span class="order-id">#<?= sprintf('%06d', $o->order_id) ?></span>
                            <span class="order-date"><?= date('d M Y H:i', strtotime($o->order_date)) ?></span>
                            <span style="color:#ff69b4;font-weight:bold;"><?= encode($o->username) ?></span>
                            <span class="order-status <?= strtolower(str_replace(' ', '-', $o->order_status)) ?>">
                                <?= $o->order_status ?>
                            </span>
                        </div>

                        <div class="order-items">
                            <p><strong>Items:</strong> <?= encode($o->items_summary ?? '—') ?></p>
                        </div>

                        <div class="order-total" style="text-align:right;font-size:1.4rem;margin:15px 0;">
                            Total: <strong style="color:#ff1493;">RM <?= number_format($o->total_amount, 2) ?></strong>
                        </div>

                        <!-- Instant Status Update -->
                        <div style="margin:15px 0;padding:12px;background:#fff5f9;border-radius:12px;">
                            <form method="post" style="display:inline-flex;gap:10px;align-items:center;">
                                <input type="hidden" name="order_id" value="<?= $o->order_id ?>">
                                <strong>Update Status:</strong>
                                <select name="order_status" onchange="this.form.submit()" style="padding:8px 12px;border-radius:10px;border:2px solid #ff69b4;">
                                    <?php 
                                    $statuses = ['Pending Payment', 'To Ship', 'Shipped', 'Completed', 'Cancelled', 'Return/Refund'];
                                    foreach ($statuses as $s): ?>
                                        <option value="<?= $s ?>" <?= $o->order_status === $s ? 'selected' : '' ?>><?= $s ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        </div>

                        <!-- Inline Expandable Details -->
                        <details>
                            <summary>👀 View Full Order Details ♡</summary>
                            <div style="padding:20px;background:#fffafc;border-radius:15px;margin-top:10px;">

                                <?php if ($o->payment_method): ?>
                                    <p><strong>Paid with:</strong> 
                                        <?= $o->payment_method === 'Credit/Debit Card' 
                                            ? 'Card ending ****' . ($o->card_last4 ?? '') 
                                            : encode($o->payment_method) ?>
                                    </p>
                                <?php endif; ?>

                                <h4 style="color:#ff69b4;margin:20px 0 10px;">Purchased Items</h4>
                                <?php
                                $items_stmt = $_db->prepare("
                                    SELECT oi.*, p.product_name, p.photo_name 
                                    FROM order_items oi 
                                    JOIN product p ON oi.product_id = p.product_id 
                                    WHERE oi.order_id = ?
                                ");
                                $items_stmt->execute([$o->order_id]);
                                $items = $items_stmt->fetchAll();
                                ?>

                                <?php if (empty($items)): ?>
                                    <p>No items found.</p>
                                <?php else: ?>
                                    <table class="order-items-table">
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th>Photo</th>
                                                <th>Price</th>
                                                <th>Qty</th>
                                                <th>Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($items as $i): 
                                                $subtotal = $i->unit_price * $i->quantity;
                                            ?>
                                                <tr>
                                                    <td><?= encode($i->product_name) ?></td>
                                                    <td>
                                                        <?php if ($i->photo_name): ?>
                                                            <img src="/admin/uploads/products/<?= encode($i->photo_name) ?>" alt="product">
                                                        <?php else: ?>
                                                            <div style="width:60px;height:60px;background:#f0f0f0;border-radius:8px;"></div>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>RM <?= number_format($i->unit_price, 2) ?></td>
                                                    <td style="text-align:center;"><?= $i->quantity ?></td>
                                                    <td>RM <?= number_format($subtotal, 2) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                            <tr class="total-row">
                                                <td colspan="4"><strong>Total Amount</strong></td>
                                                <td><strong>RM <?= number_format($o->total_amount, 2) ?></strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                <?php endif; ?>
                            </div>
                        </details>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Pagination -->
            <?php if ($total > $limit): ?>
            <div class="pagination" style="margin-top:30px;text-align:center;">
                <span>Page <?= $page ?> of <?= $total_pages ?></span>
                <?php if ($page > 1): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page-1])) ?>">Previous</a>
                <?php endif; ?>
                <?php if ($page < $total_pages): ?>
                    <a href="?<?= http_build_query(array_merge($_GET, ['page' => $page+1])) ?>">Next</a>
                <?php endif; ?>
                <span style="margin-left:20px;">
                    <?= $offset + 1 ?>–<?= min($offset + $limit, $total) ?> of <?= $total ?> orders
                </span>
            </div>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
function applyFilters() {
    const search = document.querySelector('[name="search_id"]').value.trim();
    const from = document.querySelector('[name="date_from"]').value;
    const to = document.querySelector('[name="date_to"]').value;
    const status = document.querySelector('[name="status"]').value;

    const url = new URL(location);
    url.searchParams.set('search_id', search);
    url.searchParams.set('date_from', from);
    url.searchParams.set('date_to', to);
    url.searchParams.set('status', status);
    url.searchParams.set('page', 1);
    location = url.toString();
}

function clearFilters() {
    location = 'order_list.php';
}
</script>

</body>
</html>