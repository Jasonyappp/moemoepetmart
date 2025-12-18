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

$_err = [];

if (is_post()) {
    $payment_method = post('payment_method', 'cod');

    if (!in_array($payment_method, ['cod', 'card', 'tng'])) {
        temp('error', 'Invalid payment method!');
        redirect('checkout.php');
    }

    if ($payment_method === 'card') {
        $card_number = preg_replace('/\D/', '', post('card_number'));
        $expiry = trim(post('expiry')); // MM/YY
        $cvv = trim(post('cvv'));

        if (strlen($card_number) !== 16) {
            $_err['card_number'] = 'Card number must be 16 digits';
        }

        // Expiry validation: format + not expired
        if (!preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/', $expiry, $matches)) {
            $_err['expiry'] = 'Expiry must be in MM/YY format';
        } else {
            $exp_month = (int)$matches[1];
            $exp_year = 2000 + (int)$matches[2]; // YY → 20YY

            // Current date: December 17, 2025
            $current_year = 2025;
            $current_month = 12;

            // Card is expired if year < current year OR (year == current year AND month < current month)
            if ($exp_year < $current_year || ($exp_year == $current_year && $exp_month < $current_month)) {
                $_err['expiry'] = 'Card has expired. Please use a valid card.';
            }
        }

        // CVV: exactly 3 digits only
        if (!preg_match('/^\d{3}$/', $cvv)) {
            $_err['cvv'] = 'CVV must be exactly 3 digits';
        }

        $card_last4 = substr($card_number, -4);
    }

    if (empty($_err)) {
        try {
            $_db->beginTransaction();

            $status = ($payment_method === 'cod' || $payment_method === 'tng') ? 'Pending Payment' : 'To Ship';

            $payment_display = [
                'cod' => 'Cash on Delivery',
                'card' => 'Credit/Debit Card',
                'tng' => 'Touch \'n Go'
            ][$payment_method];

            // Insert order with card_last4
            $stm = $_db->prepare("INSERT INTO orders (user_id, total_amount, order_date, order_status, payment_method, card_last4) VALUES (?, ?, NOW(), ?, ?, ?)");
            $stm->execute([$user->id, $total, $status, $payment_display, ($payment_method === 'card' ? $card_last4 : null)]);
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
            clear_cart(); // Clear cart

            if ($payment_method === 'tng') {
                temp('info', 'Please scan the QR code to complete payment ♡');
                redirect("payment.php?id=$order_id");
            } else {
                temp('info', "Order placed successfully! Order #$order_id ♡");
                redirect("receipt.php?id=$order_id"); // Both COD and Card go to receipt
            }
        } catch (Exception $e) {
            $_db->rollBack();
            temp('error', 'Checkout failed: ' . $e->getMessage());
        }
    }
}

$_title = 'Checkout ♡ Moe Moe Pet Mart';
include '../_head.php';
?>

<div class="container">
    <h2>Checkout ♡</h2>

    <table class="cart-summary">
        <tr><th>Product</th><th>Price</th><th>Qty</th><th>Subtotal</th></tr>
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
        <h3 style="margin:2rem 0 1rem; color:#ff5722;">Payment Method ♡</h3>
        <table class="payment-table">
            <tr>
                <td><input type="radio" name="payment_method" value="cod" id="cod" checked></td>
                <td><label for="cod"><strong>Cash on Delivery (COD)</strong><br><small>Pay when you receive your order</small></label></td>
            </tr>
            <tr>
                <td><input type="radio" name="payment_method" value="card" id="card"></td>
                <td><label for="card"><strong>Credit / Debit Card</strong><br><small>Secure online payment</small></label></td>
            </tr>
            <tr id="card-fields" style="display:none;">
                <td colspan="2">
                    <div style="background:#fff0f5; padding:20px; border-radius:15px; margin:15px 0;">
                        <input type="text" name="card_number" placeholder="Card Number (1234 5678 9012 3456)" maxlength="19" style="width:100%; padding:12px; margin:8px 0; border-radius:8px; border:1px solid #ff69b4;">
                        <?= err('card_number') ?>

                        <div style="display:flex; gap:15px;">
                            <input type="text" name="expiry" placeholder="MM/YY" maxlength="5" style="flex:1; padding:12px; margin:8px 0; border-radius:8px; border:1px solid #ff69b4;">
                            <input type="text" name="cvv" placeholder="CVV (3 digits)" maxlength="3" inputmode="numeric" style="flex:1; padding:12px; margin:8px 0; border-radius:8px; border:1px solid #ff69b4;">
                        </div>
                        <?= err('expiry') ?>
                        <?= err('cvv') ?>
                    </div>
                </td>
            </tr>
            <tr>
                <td><input type="radio" name="payment_method" value="tng" id="tng"></td>
                <td><label for="tng"><strong>Touch 'n Go eWallet</strong><br><small>Scan QR code to pay</small></label></td>
            </tr>
        </table>

        <div style="margin-top:30px; text-align:center;">
            <button type="submit" class="btn btn-primary">Place Order ♡</button>
            <a href="cart.php" class="btn btn-secondary">← Edit Cart</a>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    function toggleCardFields() {
        if ($('input[name="payment_method"]:checked').val() === 'card') {
            $('#card-fields').show();
        } else {
            $('#card-fields').hide();
        }
    }

    toggleCardFields();
    $('input[name="payment_method"]').on('change', toggleCardFields);

    $('input[name="card_number"]').on('input', function() {
        let v = this.value.replace(/\D/g, '').match(/(\d{0,4})(\d{0,4})(\d{0,4})(\d{0,4})/);
        this.value = v.slice(1).filter(Boolean).join(' ');
    });

    $('input[name="expiry"]').on('input', function() {
        let v = this.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,2})/);
        this.value = v[1] + (v[2] ? '/' + v[2] : '');
    });

    $('input[name="cvv"]').on('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 3);
    });
});
</script>

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