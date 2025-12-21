<?php
require '../_base.php';
require_login();
require_admin();

$_title = 'Order Details - Admin';

$order_id = (int)($_GET['id'] ?? 0);
if ($order_id <= 0) {
    temp('info', 'Invalid order ID');
    redirect('order_list.php');
}

// Fetch order main info
$stmt = $_db->prepare("
    SELECT o.*, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.order_id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    temp('info', 'Order not found');
    redirect('order_list.php');
}

// Handle status update
if (is_post()) {
    $new_status = post('order_status');
    $allowed = ['Pending Payment', 'To Ship', 'Shipped', 'Completed', 'Cancelled', 'Return Requested'];

    if (!in_array($new_status, $allowed)) {
        temp('error', 'Invalid status selected.');
        redirect("order_view.php?id=$order_id");
    }

    // Get current status
    $current_status = $order->order_status;

    if ($current_status !== $new_status) {
        // If changing TO Cancelled → restore stock
        if ($new_status === 'Cancelled') {
            $items_stmt = $_db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
            $items_stmt->execute([$order_id]);
            $items = $items_stmt->fetchAll();

            foreach ($items as $item) {
                $_db->prepare("UPDATE product SET stock_quantity = stock_quantity + ? WHERE product_id = ?")
                    ->execute([$item->quantity, $item->product_id]);
            }

            temp('info', "Order #$order_id cancelled → stock restored automatically ♡");
        } else {
            temp('info', "Order #$order_id status updated to: $new_status ♡");
        }

        // Update status
        $_db->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?")
            ->execute([$new_status, $order_id]);

        // Refresh order data
        redirect("order_view.php?id=$order_id");
    } else {
        temp('info', 'No change in status.');
        redirect("order_view.php?id=$order_id");
    }
}

// Fetch order items
$items_stmt = $_db->prepare("
    SELECT oi.*, p.product_name, p.photo_name 
    FROM order_items oi 
    JOIN product p ON oi.product_id = p.product_id 
    WHERE oi.order_id = ?
");
$items_stmt->execute([$order_id]);
$items = $items_stmt->fetchAll();

// Calculate total quantity for display (optional, nice to have)
$total_quantity = array_sum(array_column($items, 'quantity'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $_title ?> • Moe Moe Pet Mart</title>
    <link rel="stylesheet" href="/css/app.css">
    <style>
        .order-detail-card {
            background: white;
            border-radius: 25px;
            padding: 30px;
            box-shadow: 0 8px 25px rgba(255,105,180,0.15);
            max-width: 1000px;
            margin: 30px auto;
        }
        .order-header {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            font-size: 1.2rem;
        }
        .order-status {
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 1rem;
        }
        .order-status.pending-payment { background: #fff3e0; color: #ef6c00; }
        .order-status.to-ship { background: #e3f2fd; color: #1565c0; }
        .order-status.shipped { background: #e8f5e9; color: #2e7d32; }
        .order-status.completed { background: #f1f8e9; color: #558b2f; }
        .order-status.cancelled { background: #ffebee; color: #c62828; }
        .order-status.return-refund { background: #fffde7; color: #f57f17; }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 30px 0;
        }
        .items-table th {
            background: #fff0f5;
            padding: 15px;
            text-align: left;
        }
        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #ffd4e4;
        }
        .items-table img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
        .total-row {
            background: #fff0f5 !important;
            font-size: 1.3rem;
            font-weight: bold;
        }
        .status-update {
            background: #fff5f9;
            padding: 25px;
            border-radius: 15px;
            margin-top: 40px;
            text-align: center;
        }
        .back-btn {
            display: inline-block;
            margin-top: 30px;
            padding: 12px 30px;
            background: #ccc;
            color: white;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
        }
        .back-btn:hover {
            background: #aaa;
        }
    </style>
</head>
<body>

<div class="admin-layout">

    <!-- Sidebar - copied from admin.php for consistency -->
    <aside class="admin-sidebar">
        <div class="logo">
            <h2>MoeMoePet</h2>
        </div>
        <ul>
            <li><a href="../admin.php"><i class="fas fa-tachometer-alt"></i> <span>Dashboard</span></a></li>
            <li><a href="product_list.php"><i class="fas fa-box"></i> <span>Products</span></a></li>
            <li><a href="member_list.php"><i class="fas fa-users"></i> <span>Members</span></a></li>
            <li><a href="order_list.php" class="active"><i class="fas fa-shopping-cart"></i> <span>Orders</span></a></li>
            <li><a href="/logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <header class="admin-header">
            <h1>Order #<?= sprintf('%06d', $order->order_id) ?> Details ♡</h1>
        </header>

        <div class="order-detail-card">
            <div class="order-header">
                <div>
                    <strong>Customer:</strong> <?= encode($order->username) ?><br>
                    <strong>Date:</strong> <?= date('d M Y H:i', strtotime($order->order_date)) ?><br>
                    <strong>Total Items:</strong> 
                    <span style="color:#ff69b4;font-weight:bold;">
                        <?= $total_quantity ?> <?= $total_quantity == 1 ? 'item' : 'items' ?>
                    </span>
                </div>
                <span class="order-status <?= strtolower(str_replace([' ', '/'], '-', $order->order_status)) ?>">
                    <?= $order->order_status ?>
                </span>
            </div>

            <?php if ($order->payment_method): ?>
                <p><strong>Payment Method:</strong> 
                    <?= $order->payment_method === 'Credit/Debit Card' 
                        ? 'Card ending ****' . ($order->card_last4 ?? '') 
                        : encode($order->payment_method) ?>
                </p>
            <?php endif; ?>

            <h3 style="color:#ff69b4;margin:40px 0 15px;">Purchased Items</h3>
            <?php if (empty($items)): ?>
                <p>No items found.</p>
            <?php else: ?>
                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Photo</th>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $i): 
                            $subtotal = $i->unit_price * $i->quantity;
                        ?>
                            <tr>
                                <td>
                                    <?php if ($i->photo_name): ?>
                                        <img src="/admin/uploads/products/<?= encode($i->photo_name) ?>" alt="<?= encode($i->product_name) ?>">
                                    <?php else: ?>
                                        <div style="width:80px;height:80px;background:#f0f0f0;border-radius:10px;"></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= encode($i->product_name) ?></td>
                                <td>RM <?= number_format($i->unit_price, 2) ?></td>
                                <td style="text-align:center;"><?= $i->quantity ?></td>
                                <td>RM <?= number_format($subtotal, 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr class="total-row">
                            <td colspan="4"><strong>Total Amount</strong></td>
                            <td><strong>RM <?= number_format($order->total_amount, 2) ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- Status Update Form -->
            <div class="status-update">
                <form method="post">
                    <strong style="font-size:1.3rem;display:block;margin-bottom:20px;">Update Order Status</strong>
                    <select name="order_status" style="padding:12px 25px;border-radius:15px;border:2px solid #ff69b4;font-size:1.1rem;">
                        <?php 
                        $statuses = ['Pending Payment', 'To Ship', 'Shipped', 'Completed', 'Cancelled', 'Return/Refund'];
                        foreach ($statuses as $s): ?>
                            <option value="<?= $s ?>" <?= $order->order_status === $s ? 'selected' : '' ?>><?= $s ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" style="padding:12px 35px;background:#ff69b4;color:white;border:none;border-radius:25px;margin-left:20px;font-weight:bold;cursor:pointer;">
                        Update Status ♡
                    </button>
                </form>
            </div>

            <a href="order_list.php" class="back-btn">← Back to Order List</a>
        </div>
    </main>
</div>

</body>
</html>