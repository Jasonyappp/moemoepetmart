<?php
require '../_base.php';
require_login();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    temp('error', 'Cart is empty!');
    redirect('cart.php');
}

$user = current_user();
$cart = $_SESSION['cart'];
$total = array_sum(array_map(function($item) { return $item['price'] * $item['qty']; }, $cart));

if (is_post()) {
    try {
        $_db->beginTransaction();

        // Insert order
        $stm = $_db->prepare("INSERT INTO orders (user_id, total_amount, order_date, order_status) VALUES (?, ?, NOW(), 'Pending Payment')");
        $stm->execute([$user->id, $total]);
        $order_id = $_db->lastInsertId();

        // Insert items + update stock
        $stm_item = $_db->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
        $stm_stock = $_db->prepare("UPDATE product SET stock_quantity = stock_quantity - ? WHERE product_id = ? AND stock_quantity >= ?");
        foreach ($cart as $item) {
            $stm_stock->execute([$item['qty'], $item['product_id'], $item['qty']]);
            if ($stm_stock->rowCount() === 0) {
                throw new Exception('Stock insufficient for ' . encode($item['name']));
            }
            $stm_item->execute([$order_id, $item['product_id'], $item['qty'], $item['price']]);
        }

        $_db->commit();
        clear_cart();  // NEW: Clear both session and DB
        temp('info', "Order placed successfully! ID: #$order_id ♡ Please review your invoice.");
        redirect('/member/invoice.php?id=' . $order_id);

    }catch (Exception $e){
        $_db->rollBack();
        temp('error', 'Checkout failed: ' . $e->getMessage());
    }
}

$_title = 'Checkout ♡ Moe Moe Pet Mart';
include '../_head.php';
?>

<div class="container">
    <h2>Checkout ♡</h2>
    
    <!-- Display cart summary for confirmation -->
    <table class="cart-summary">
        <tr>
            <th>Product</th>
            <th>Price</th>
            <th>Qty</th>
            <th>Subtotal</th>
        </tr>
        <?php foreach ($cart as $item): 
            $subtotal = $item['price'] * $item['qty'];
        ?>
            <tr>
                <td><?= encode($item['name']) ?></td>
                <td>RM <?= number_format($item['price'], 2) ?></td>
                <td><?= $item['qty'] ?></td>
                <td>RM <?= number_format($subtotal, 2) ?></td>
            </tr>
        <?php endforeach; ?>
        <tr>
            <td colspan="3"><strong>Total</strong></td>
            <td><strong>RM <?= number_format($total, 2) ?></strong></td>
        </tr> 
    </table>

    <form method="post"> 
        <button type="submit" class="btn btn-primary" onclick="return confirm('Confirm and place order? ♡')">Place Order ♡</button>
        <a href="cart.php" class="btn btn-secondary">← Edit Cart</a>
    </form>
</div>

<style>
.cart-summary {
    width: 100%;
    border-collapse: collapse;
    margin: 2rem 0;
}
.cart-summary th, .cart-summary td {
    border: 1px solid #ff69b4;
    padding: 1rem;
    text-align: left;
}
.cart-summary th {
    background: #fff0f5;
    font-weight: bold;
}
</style>

<?php include '../_foot.php'; ?>