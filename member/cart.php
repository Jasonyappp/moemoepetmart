<?php
require '../_base.php';
require_login();

$cart = $_SESSION['cart'] ?? [];
$total = 0;

$_title = 'Your Cart ♡ Moe Moe Pet Mart';
include '../_head.php';
?>

<div class="container">
    <h2>Your Shopping Cart ♡</h2>

    <?php if (empty($cart)): ?>
        <p>Your cart is empty~ <a href="products.php">Shop now! ♡</a></p>
    <?php else: ?>
        <table class="cart-table">
            <tr>
                <th>Product</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Subtotal</th>
                <th>Action</th>
            </tr>
            <?php foreach ($cart as $key => $item): 
                $subtotal = $item['price'] * $item['qty'];
                $total += $subtotal;
            ?>
                <tr>
                    <td><?= encode($item['name']) ?></td>
                    <td>RM <?= number_format($item['price'], 2) ?></td>
                    <td>
                        <form action="update_cart.php" method="post">
                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                            <input type="number" name="qty" value="<?= $item['qty'] ?>" min="1" max="999">
                            <button type="submit">Update</button>
                        </form>
                    </td>
                    <td>RM <?= number_format($subtotal, 2) ?></td>
                    <td><a href="remove_from_cart.php?id=<?= $key ?>" onclick="return confirm('Remove item? ♡')">Remove</a></td>
                </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td colspan="2"><strong>RM <?= number_format($total, 2) ?></strong></td>
            </tr>
        </table>

        <p>
            <a href="products.php">Continue Shopping ♡</a> |
            <a href="checkout.php">Checkout ♡</a>
        </p>
    <?php endif; ?>
</div>

<?php include '../_foot.php'; ?>