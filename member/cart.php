<?php
require '../_base.php';
require_login();

// Handle remove voucher
if (get('remove_voucher') === '1') {
    unset($_SESSION['applied_voucher']);
    redirect('cart.php');
}

// Handle apply voucher
$applied_voucher = $_SESSION['applied_voucher'] ?? null;
$discount = 0;

if (is_post() && post('action') === 'apply_voucher') {
    $code = strtoupper(trim(post('voucher_code')));
    if ($code) {
        $stm = $_db->prepare("
            SELECT * FROM vouchers 
            WHERE code = ? 
              AND (expiry_date IS NULL OR expiry_date >= CURDATE())
              AND (usage_limit IS NULL OR used_count < usage_limit)
        ");
        $stm->execute([$code]);
        $v = $stm->fetch();

        $cart = $_SESSION['cart'] ?? [];
        $total = array_sum(array_map(function($item) { return $item['price'] * $item['qty']; }, $cart));

        if ($v && $total >= $v->min_spend) {
            if ($v->type === 'fixed') {
                $discount = $v->value;
            } else {
                $discount = ($total * $v->value) / 100;
            }
            $discount = min($discount, $total);  // No negative total
            $_SESSION['applied_voucher'] = ['code' => $code, 'discount' => $discount];
            temp('info', "Voucher '$code' applied! Saved RM " . number_format($discount, 2) . ' ♡');
        } else {
            temp('error', 'Invalid, expired, or ineligible voucher ♡');
        }
    } else {
        temp('error', 'Select a voucher ♡');
    }
    redirect('cart.php');
}

$cart = $_SESSION['cart'] ?? [];
$total = 0;

// Calculate discount if applied
if ($applied_voucher) {
    $discount = $applied_voucher['discount'];
}

// Fetch available vouchers
$stm_vouchers = $_db->prepare("
    SELECT * FROM vouchers 
    WHERE (expiry_date IS NULL OR expiry_date >= CURDATE())
      AND (usage_limit IS NULL OR used_count < usage_limit)
    ORDER BY value DESC
");
$stm_vouchers->execute();
$available_vouchers = $stm_vouchers->fetchAll();

// Save shipping region
if (is_post() && isset($_POST['shipping_region'])) {
    $_SESSION['shipping_region'] = $_POST['shipping_region'];
    redirect('cart.php');
}

$region = $_SESSION['shipping_region'] ?? 'west';

// Calculate subtotal correctly
$subtotal = 0;
foreach ($cart as $item) {
    $subtotal += $item['price'] * $item['qty'];
}

// Shipping fee: West RM 5, East RM 10, FREE if subtotal >= RM 150
$shipping_fee = ($subtotal >= 150) ? 0 : ($region === 'east' ? 10.00 : 5.00);
$grand_total = $subtotal + $shipping_fee - $discount;

$_title = 'Your Cart ♡ Moe Moe Pet Mart';
include '../_head.php';
?>

<div class="container">
    <h2>Your Shopping Cart ♡</h2>

<?php if (empty($cart)): ?>
        <p>Your cart is empty~ <a href="products.php">Shop now! ♡</a></p>
    <?php else: ?>
        <!-- Shipping Region in Cart -->
        <div style="margin: 2rem 0; background: #fff0f5; padding: 20px; border-radius: 15px; border: 1px solid #ffb6c1;">
            <h3 style="color:#ff5722; margin-bottom: 15px;">🚚 Estimate Shipping</h3>
            <form method="post" id="shipping-form">
                <select name="shipping_region" onchange="this.form.submit()" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ff69b4; font-size: 1rem;">
                    <option value="west" <?= $region === 'west' ? 'selected' : '' ?>>West Malaysia - RM 5.00</option>
                    <option value="east" <?= $region === 'east' ? 'selected' : '' ?>>East Malaysia - RM 10.00</option>
                </select>
            </form>
            <p style="color: #ff69b4; margin-top: 10px; font-size: 0.9em;">
                <small>Free shipping for orders RM 150 and above ♡</small>
            </p>
        </div>
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
                <td colspan="3"><strong>Subtotal</strong></td>
                <td colspan="2"><strong>RM <?= number_format($total, 2) ?></strong></td>
            </tr>
            <?php if ($discount > 0): ?>
            <tr>
                <td colspan="3"><strong>Discount (<?= encode($applied_voucher['code']) ?>)</strong></td>
                <td colspan="2" style="color:#f44336;"><strong>- RM <?= number_format($discount, 2) ?></strong></td>
            </tr>
            <?php endif; ?>
            <tr>
                <td colspan="3"><strong>Shipping Fee</strong><br><small><?= $region === 'east' ? 'East Malaysia' : 'West Malaysia' ?> <?= $subtotal >= 150 ? '(Free!)' : '' ?></small></td>
                <td colspan="2"><?= $shipping_fee == 0 ? 'FREE ♡' : 'RM ' . number_format($shipping_fee, 2) ?></td>
            </tr>
            <tr>
                <td colspan="3"><strong>Total</strong></td>
                <td colspan="2"><strong>RM <?= number_format($total - $discount + $shipping_fee, 2) ?></strong></td>
            </tr>
        </table>

        <!-- Voucher Section (Selection Only) -->
        <div style="margin: 2rem 0; background: #fff0f5; padding: 20px; border-radius: 15px; border: 1px solid #ffb6c1;">
            <h3 style="margin-bottom: 1rem; color:#ff1493;">Select Voucher ♡</h3>
            
            <?php if ($applied_voucher): ?>
                <p style="margin: 15px 0; padding: 12px; background:#e8f5e9; border-radius:10px; text-align:center; color:#2e7d32; font-weight:bold;">
                    ✓ Applied: <?= encode($applied_voucher['code']) ?> 
                    (Saved RM <?= number_format($discount, 2) ?>) ♡ 
                    <a href="cart.php?remove_voucher=1" style="color:#d32f2f; text-decoration:underline; margin-left:15px;">Remove</a>
                </p>
            <?php endif; ?>

            <?php if (!empty($available_vouchers)): ?>
                <div style="display: grid; gap: 20px; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-top: 20px;">
                    <?php foreach ($available_vouchers as $voucher): ?>
                        <div style="background: #fff; padding: 20px; border-radius: 15px; border: 2px solid #ff69b4; box-shadow: 0 6px 20px rgba(255,105,180,0.15); text-align: center; display: flex; flex-direction: column; justify-content: space-between; height: 100%;">
                            <div>
                                <div style="font-size:1.4rem; font-weight:bold; color:#ff1493; margin-bottom:10px;">
                                    <?= encode($voucher->code) ?>
                                </div>
                                <div style="font-size:1.1rem; margin:12px 0; color:#444;">
                                    <?php if ($voucher->type === 'fixed'): ?>
                                        <strong>RM <?= number_format($voucher->value, 2) ?> OFF</strong>
                                    <?php else: ?>
                                        <strong><?= $voucher->value ?>% OFF</strong>
                                    <?php endif; ?>
                                </div>
                                <div style="font-size:0.95rem; color:#666; line-height:1.5;">
                                    Min spend: RM <?= number_format($voucher->min_spend, 2) ?>
                                    <?php if ($voucher->expiry_date): ?>
                                        <br>Expires: <?= date('d M Y', strtotime($voucher->expiry_date)) ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Button container - perfectly centered -->
                            <div style="margin-top: 25px; display: flex; justify-content: center;">
                                <form method="post">
                                    <input type="hidden" name="action" value="apply_voucher">
                                    <input type="hidden" name="voucher_code" value="<?= encode($voucher->code) ?>">
                                    <button type="submit" 
                                            class="btn btn-primary" 
                                            style="padding:12px 50px; font-size:1rem; border-radius: 50px; min-width: 200px;">
                                        Apply This ♡
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p style="text-align:center; color:#888; margin-top:20px;">No available vouchers right now~ Check back later! ♡</p>
            <?php endif; ?>
        </div>

        <p style="text-align:center; margin-top:40px;">
            <a href="products.php">Continue Shopping ♡</a> |
            <a href="checkout.php">Checkout ♡</a>
        </p>
    <?php endif; ?>
</div>

<?php include '../_foot.php'; ?>