<?php
require '../_base.php';
require_login();
require_admin();

$_title = 'Member Management - Admin';

// === Parameter handling ===
$page = max(1, (int)($_GET['page'] ?? 1));
$limit = (int)($_GET['limit'] ?? 10);
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');
$user_id = (int)($_GET['user_id'] ?? 0); // For viewing specific member's orders

$where = "WHERE u.role = 'member'";
$params = [];

if ($search !== '') {
    $where .= " AND (u.username LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

// Total members count
$count_stmt = $_db->prepare("SELECT COUNT(*) FROM users u $where");
$count_stmt->execute($params);
$total = $count_stmt->fetchColumn();
$total_pages = max(1, ceil($total / $limit));

// Fetch members
$sql = "
    SELECT u.id, u.username, u.email, u.phone, u.created_at,
           COUNT(o.order_id) AS total_orders
    FROM users u
    LEFT JOIN orders o ON u.id = o.user_id
    $where
    GROUP BY u.id
    ORDER BY u.created_at DESC
    LIMIT $limit OFFSET $offset
";

$stmt = $_db->prepare($sql);
$stmt->execute($params);
$members = $stmt->fetchAll();

// If viewing a specific member's orders
$selected_member = null;
$member_orders = [];
if ($user_id > 0) {
    $stmt = $_db->prepare("SELECT id, username FROM users WHERE id = ? AND role = 'member'");
    $stmt->execute([$user_id]);
    $selected_member = $stmt->fetch();

    if ($selected_member) {
        // Handle status update
        if (is_post() && post('order_id')) {
            $order_id = post('order_id');
            $new_status = post('order_status');

            $allowed = ['Pending Payment', 'To Ship', 'Shipped', 'Completed', 'Cancelled', 'Return/Refund'];
            if (in_array($new_status, $allowed)) {
                $_db->prepare("UPDATE orders SET order_status = ? WHERE order_id = ? AND user_id = ?")
                    ->execute([$new_status, $order_id, $user_id]);
                temp('info', "Order #$order_id status updated to: $new_status ♡");
                redirect("member_list.php?user_id=$user_id");
            }
        }

        // Fetch member's orders
        $sql_orders = "
            SELECT o.*, GROUP_CONCAT(CONCAT(oi.quantity, ' x ', p.product_name) SEPARATOR ', ') AS items_summary
            FROM orders o
            LEFT JOIN order_items oi ON o.order_id = oi.order_id
            LEFT JOIN product p ON oi.product_id = p.product_id
            WHERE o.user_id = ?
            GROUP BY o.order_id
            ORDER BY o.order_date DESC
        ";
        $stmt_orders = $_db->prepare($sql_orders);
        $stmt_orders->execute([$user_id]);
        $member_orders = $stmt_orders->fetchAll();
    }
}
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
    <aside class="admin-sidebar">
        <div class="logo"><h2>MoeMoePet</h2></div>
        <ul>
            <li><a href="../admin.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
            <li><a href="product_list.php"><i class="fas fa-box"></i> <span>Products</span></a></li>
            <li><a href="member_list.php" class="active"><i class="fas fa-users"></i> <span>Members</span></a></li>
            <li><a href="order_list.php"><i class="fas fa-clipboard-list"></i> Orders</a></li>
            <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <header class="admin-header">
            <h1>Member Management ♡</h1>
            <div class="admin-user">
                <i class="fas fa-user-circle"></i>
                <span><?= encode($_SESSION['user']) ?></span>
            </div>
        </header>

        <div class="toolbar">
            <input type="text" name="search" placeholder="Search by username, email or phone..." value="<?= encode($search) ?>">
            <button onclick="applyFilters()">Apply</button>
            <?php if ($user_id > 0): ?>
                <a href="member_list.php" class="btn-add">← Back to Members List</a>
            <?php endif; ?>
        </div>

        <div class="table-card">
            <?php if ($user_id > 0 && $selected_member): ?>
                <!-- === VIEWING SPECIFIC MEMBER'S ORDERS === -->
                <h2 style="text-align:center; color:#ff69b4; margin-bottom:20px;">
                    Orders for <?= encode($selected_member->username) ?> ♡
                </h2>

                <?php if (empty($member_orders)): ?>
                    <div class="empty-full" style="text-align:center;padding:60px;color:#999;">
                        This member has no orders yet~ ♡
                    </div>
                <?php else: ?>
                    <div class="purchase-list">
                        <?php foreach ($member_orders as $order): ?>
                            <div class="purchase-card">
                                <div class="order-header">
                                    <span class="order-id">Order #<?= $order->order_id ?></span>
                                    <span class="order-date"><?= date('M d, Y H:i', strtotime($order->order_date)) ?></span>
                                    <span class="order-status <?= strtolower(str_replace(' ', '-', $order->order_status)) ?>">
                                        <?= $order->order_status ?>
                                    </span>
                                </div>

                                <div class="order-items">
                                    <p>Items: <?= encode($order->items_summary ?? 'No items') ?></p>
                                </div>

                                <div class="order-total">
                                    Total: RM <?= number_format($order->total_amount, 2) ?>
                                </div>

                                <!-- Admin: Change Status -->
                                <div style="margin-top:15px; padding:15px; background:#fff5f9; border-radius:12px;">
                                    <form method="post" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                                        <input type="hidden" name="order_id" value="<?= $order->order_id ?>">
                                        <strong>Update Status:</strong>
                                        <select name="order_status" style="padding:8px; border-radius:8px; border:2px solid #ff69b4;">
                                            <option value="Pending Payment" <?= $order->order_status === 'Pending Payment' ? 'selected' : '' ?>>Pending Payment</option>
                                            <option value="To Ship" <?= $order->order_status === 'To Ship' ? 'selected' : '' ?>>To Ship</option>
                                            <option value="Shipped" <?= $order->order_status === 'Shipped' ? 'selected' : '' ?>>Shipped</option>
                                            <option value="Completed" <?= $order->order_status === 'Completed' ? 'selected' : '' ?>>Completed</option>
                                            <option value="Cancelled" <?= $order->order_status === 'Cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                            <option value="Return/Refund" <?= $order->order_status === 'Return/Refund' ? 'selected' : '' ?>>Return/Refund</option>
                                        </select>
                                        <button type="submit" style="padding:8px 20px; background:#ff69b4; color:white; border:none; border-radius:8px;">
                                            Update ♡
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

            <?php else: ?>
                <!-- === MAIN MEMBERS LIST === -->
                <?php if (empty($members)): ?>
                    <div class="empty-full" style="text-align:center;padding:60px;color:#999;">
                        No members registered yet~<br>Waiting for our first cute customer! ♡
                    </div>
                <?php else: ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Username</th>
                                <th>Email</th>
                                <th>Phone Number</th>
                                <th>Total Orders</th>
                                <th>Join Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $m): ?>
                            <tr>
                                <td><strong><?= encode($m->username) ?></strong></td>
                                <td><?= encode($m->email ?? '—') ?></td>
                                <td><?= encode($m->phone ?? '—') ?></td>
                                <td><span class="total-orders-badge"><?= $m->total_orders ?></span></td>
                                <td><?= date('d M Y', strtotime($m->created_at)) ?></td>
                                <td class="action-links">
                                    <a href="member_list.php?user_id=<?= $m->id ?>"
                                       class="text-pink-500 hover:text-pink-700 hover:underline font-medium transition">
                                        👀 View Orders ♡
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <!-- Pagination -->
                <?php if ($total > $limit): ?>
                <div class="pagination">
                    <span>Page <?= $page ?> of <?= $total_pages ?></span>
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page-1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">Previous</a>
                    <?php endif; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page+1 ?>&limit=<?= $limit ?>&search=<?= urlencode($search) ?>">Next</a>
                    <?php endif; ?>
                    <span><?= $offset + 1 ?>–<?= min($offset + $limit, $total) ?> of <?= $total ?> members</span>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </main>
</div>

<script>
function applyFilters() {
    const search = document.querySelector('input[name="search"]').value.trim();
    const url = new URL(location);
    url.searchParams.set('search', search);
    url.searchParams.set('page', 1);
    <?php if ($user_id > 0): ?>
        url.searchParams.set('user_id', '<?= $user_id ?>');
    <?php endif; ?>
    location = url.toString();
}
</script>

</body>
</html>