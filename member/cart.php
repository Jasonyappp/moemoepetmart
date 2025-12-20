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

// Fetch stock data for all products in cart
$stock_data = [];
if (!empty($cart)) {
    $product_ids = array_keys($cart);
    $placeholders = implode(',', array_fill(0, count($product_ids), '?'));
    $stm = $_db->prepare("SELECT product_id, stock_quantity FROM product WHERE product_id IN ($placeholders)");
    $stm->execute($product_ids);
    while ($row = $stm->fetch()) {
        $stock_data[$row->product_id] = $row->stock_quantity;
    }
}

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

// Auto-remove voucher if subtotal no longer meets min spend
if ($applied_voucher) {
    $code = $applied_voucher['code'];
    $stm = $_db->prepare("SELECT min_spend FROM vouchers WHERE code = ?");
    $stm->execute([$code]);
    $v = $stm->fetch();
    if ($v && $subtotal < $v->min_spend) {
        unset($_SESSION['applied_voucher']);
        temp('info', 'Voucher removed as min spend condition no longer met ♡');
        $discount = 0;
        $grand_total = $subtotal + $shipping_fee;  // Update grand total without discount
    }
}

$_title = 'Your Cart ♡ Moe Moe Pet Mart';
include '../_head.php';
?>

<div class="container">

    <!-- Pink Flash Message Container -->
    <div id="moe-flash" class="moe-flash"></div>

    <h2>Your Shopping Cart ♡</h2>

<?php if (empty($cart)): ?>
        <!-- Beautiful Empty Cart Design with CSS Animations -->
        <div class="empty-cart-container">
            <!-- Floating Cart Icon -->
            <div class="empty-cart-icon">🛒</div>
            
            <!-- Animated Message -->
            <h3 class="empty-cart-title">Your cart is empty~</h3>
            <p class="empty-cart-subtitle">Time to fill it with cute pet supplies! ♡</p>
            
            <!-- Gorgeous Animated Button -->
            <a href="products.php" class="empty-cart-button">
                <span class="button-icon">🛍️</span>
                <span class="button-text">Shop Now!</span>
                <span class="button-heart">♡</span>
            </a>
            
            <!-- Decorative Floating Hearts -->
            <div class="floating-hearts">
                <span class="heart heart-1">♡</span>
                <span class="heart heart-2">♡</span>
                <span class="heart heart-3">♡</span>
            </div>
        </div>

        <style>
            /* Empty Cart Container */
            .empty-cart-container {
                text-align: center;
                padding: 80px 30px;
                background: linear-gradient(135deg, #ffffff 0%, #fff5f9 100%);
                border-radius: 30px;
                box-shadow: 0 15px 50px rgba(255,105,180,0.2);
                max-width: 650px;
                margin: 60px auto;
                position: relative;
                overflow: hidden;
                border: 3px solid #ffd4e4;
            }

            /* Floating Cart Icon */
            .empty-cart-icon {
                font-size: 10rem;
                margin-bottom: 30px;
                animation: float 3s ease-in-out infinite;
                opacity: 0.4;
                filter: grayscale(50%);
            }

            @keyframes float {
                0%, 100% { transform: translateY(0px); }
                50% { transform: translateY(-20px); }
            }

            /* Title with fade-in animation */
            .empty-cart-title {
                color: #ff69b4;
                font-size: 2.2rem;
                margin-bottom: 15px;
                font-family: 'Kalam', cursive;
                animation: fadeInDown 0.8s ease;
                font-weight: bold;
            }

            @keyframes fadeInDown {
                from {
                    opacity: 0;
                    transform: translateY(-30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Subtitle */
            .empty-cart-subtitle {
                color: #888;
                font-size: 1.2rem;
                margin-bottom: 50px;
                animation: fadeIn 1s ease 0.3s both;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            /* BEAUTIFUL GRADIENT BUTTON */
            .empty-cart-button {
                display: inline-flex;
                align-items: center;
                gap: 12px;
                padding: 22px 70px;
                background: linear-gradient(135deg, #ff69b4, #ff1493, #ff69b4);
                background-size: 200% 200%;
                color: white;
                text-decoration: none;
                border-radius: 60px;
                font-size: 1.6rem;
                font-weight: bold;
                box-shadow: 0 12px 35px rgba(255,20,147,0.5);
                transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                position: relative;
                overflow: hidden;
                animation: fadeInUp 1s ease 0.5s both;
            }

            @keyframes fadeInUp {
                from {
                    opacity: 0;
                    transform: translateY(30px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }

            /* Gradient animation */
            .empty-cart-button {
                animation: fadeInUp 1s ease 0.5s both, gradient-shift 3s ease infinite;
            }

            @keyframes gradient-shift {
                0%, 100% { background-position: 0% 50%; }
                50% { background-position: 100% 50%; }
            }

            /* Button hover effect */
            .empty-cart-button:hover {
                transform: translateY(-8px) scale(1.05);
                box-shadow: 0 20px 50px rgba(255,20,147,0.7);
            }

            .empty-cart-button:active {
                transform: translateY(-4px) scale(1.02);
            }

            /* Button elements */
            .button-icon {
                font-size: 1.8rem;
                animation: bounce 2s ease infinite;
            }

            @keyframes bounce {
                0%, 20%, 50%, 80%, 100% { transform: translateY(0); }
                40% { transform: translateY(-10px); }
                60% { transform: translateY(-5px); }
            }

            .button-text {
                font-family: 'Poppins', sans-serif;
                letter-spacing: 0.5px;
            }

            .button-heart {
                font-size: 1.8rem;
                animation: pulse 1.5s ease infinite;
            }

            @keyframes pulse {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.3); }
            }

            /* Floating decorative hearts */
            .floating-hearts {
                margin-top: 50px;
                font-size: 2.5rem;
                display: flex;
                justify-content: center;
                gap: 30px;
            }

            .heart {
                color: #ffb6c1;
                opacity: 0.6;
                animation: float-heart 3s ease-in-out infinite;
            }

            .heart-1 { animation-delay: 0s; }
            .heart-2 { animation-delay: 1s; }
            .heart-3 { animation-delay: 2s; }

            @keyframes float-heart {
                0%, 100% { 
                    transform: translateY(0) rotate(0deg); 
                    opacity: 0.6;
                }
                50% { 
                    transform: translateY(-15px) rotate(10deg); 
                    opacity: 1;
                }
            }

            /* Background sparkles */
            .empty-cart-container::before {
                content: '✨';
                position: absolute;
                top: 30px;
                left: 40px;
                font-size: 2rem;
                animation: sparkle 4s ease-in-out infinite;
            }

            .empty-cart-container::after {
                content: '✨';
                position: absolute;
                bottom: 40px;
                right: 50px;
                font-size: 2rem;
                animation: sparkle 4s ease-in-out 2s infinite;
            }

            @keyframes sparkle {
                0%, 100% { 
                    opacity: 0.3;
                    transform: scale(1) rotate(0deg);
                }
                50% { 
                    opacity: 1;
                    transform: scale(1.3) rotate(180deg);
                }
            }

            /* Responsive design */
            @media (max-width: 768px) {
                .empty-cart-container {
                    padding: 60px 20px;
                    margin: 30px 15px;
                }
                
                .empty-cart-icon {
                    font-size: 7rem;
                }
                
                .empty-cart-title {
                    font-size: 1.8rem;
                }
                
                .empty-cart-button {
                    padding: 18px 50px;
                    font-size: 1.3rem;
                }

                .moe-flash {
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    min-width: 300px;
                    background: linear-gradient(135deg, #ff69b4, #ff1493);
                    color: white;
                    border-radius: 15px;
                    /* ...more styles... */
                }
            }
        </style>
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
                        <form action="update_cart.php" method="post" class="update-cart-form" 
                              data-product-id="<?= $item['product_id'] ?>" 
                              data-stock="<?= $stock_data[$item['product_id']] ?? 999 ?>" 
                              data-product-name="<?= encode($item['name']) ?>">
                            <input type="hidden" name="product_id" value="<?= $item['product_id'] ?>">
                            <input type="number" name="qty" class="cart-qty-input" value="<?= $item['qty'] ?>" 
                                   min="1" max="<?= $stock_data[$item['product_id']] ?? 999 ?>">
                            <button type="submit">Update</button>
                        </form>
                    </td>
                    <td>RM <?= number_format($subtotal, 2) ?></td>
                    <td><a href="remove_from_cart.php?id=<?= $key ?>" class="remove-cart-item" data-id="<?= $key ?>" data-name="<?= encode($item['name']) ?>">Remove</a></td>
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

        <!-- Beautiful Action Buttons -->
        <div style="text-align: center; margin-top: 60px; margin-bottom: 40px;">
            <a href="products.php" class="cart-action-btn continue-shopping-btn">
                <span class="btn-icon">🛍️</span>
                <span class="btn-text">Continue Shopping</span>
                <span class="btn-heart">♡</span>
            </a>
            
            <a href="checkout.php" class="cart-action-btn checkout-btn">
                <span class="btn-icon">✨</span>
                <span class="btn-text">Checkout</span>
                <span class="btn-heart">♡</span>
            </a>
        </div>

        <style>
            /* Cart Action Buttons Container */
            .cart-action-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
                padding: 18px 45px;
                margin: 0 10px;
                border-radius: 50px;
                font-size: 1.3rem;
                font-weight: bold;
                text-decoration: none;
                transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
                position: relative;
                overflow: hidden;
                box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            }

            /* Continue Shopping Button - Pink Outline */
            .continue-shopping-btn {
                background: white;
                color: #ff69b4;
                border: 3px solid #ff69b4;
            }

            .continue-shopping-btn:hover {
                background: linear-gradient(135deg, #fff0f5, #ffe4e9);
                transform: translateY(-5px) scale(1.05);
                box-shadow: 0 12px 35px rgba(255,105,180,0.4);
                border-color: #ff1493;
            }

            .continue-shopping-btn:active {
                transform: translateY(-2px) scale(1.02);
            }

            /* Checkout Button - Gradient Fill */
            .checkout-btn {
                background: linear-gradient(135deg, #ff69b4, #ff1493);
                color: white;
                border: 3px solid transparent;
                box-shadow: 0 8px 25px rgba(255,20,147,0.4);
            }

            .checkout-btn:hover {
                background: linear-gradient(135deg, #ff1493, #ff69b4);
                transform: translateY(-5px) scale(1.05);
                box-shadow: 0 12px 35px rgba(255,20,147,0.6);
            }

            .checkout-btn:active {
                transform: translateY(-2px) scale(1.02);
            }

            /* Button Icon Animation */
            .cart-action-btn .btn-icon {
                font-size: 1.5rem;
                transition: transform 0.3s ease;
            }

            .cart-action-btn:hover .btn-icon {
                transform: scale(1.2) rotate(10deg);
                animation: bounce-icon 0.6s ease infinite;
            }

            @keyframes bounce-icon {
                0%, 100% { transform: scale(1.2) rotate(10deg) translateY(0); }
                50% { transform: scale(1.2) rotate(10deg) translateY(-5px); }
            }

            /* Button Text */
            .cart-action-btn .btn-text {
                font-family: 'Poppins', sans-serif;
                letter-spacing: 0.5px;
            }

            /* Button Heart Animation */
            .cart-action-btn .btn-heart {
                font-size: 1.4rem;
                transition: all 0.3s ease;
            }

            .cart-action-btn:hover .btn-heart {
                animation: pulse-heart 0.8s ease infinite;
                color: #ff1493;
            }

            @keyframes pulse-heart {
                0%, 100% { transform: scale(1); }
                50% { transform: scale(1.4); }
            }

            /* Shine Effect on Hover */
            .cart-action-btn::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
                transition: left 0.5s ease;
            }

            .cart-action-btn:hover::before {
                left: 100%;
            }

            /* Responsive Design */
            @media (max-width: 768px) {
                .cart-action-btn {
                    display: flex;
                    width: 90%;
                    max-width: 300px;
                    margin: 10px auto;
                    padding: 16px 35px;
                    font-size: 1.2rem;
                }
            }

            @media (max-width: 480px) {
                .cart-action-btn {
                    padding: 14px 30px;
                    font-size: 1.1rem;
                }
                
                .cart-action-btn .btn-icon {
                    font-size: 1.3rem;
                }
            }
        </style>
    <?php endif; ?>
</div>

<!-- Custom Pink Confirmation Modal -->
<div id="custom-confirm-modal" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: white; padding: 40px; border-radius: 25px; box-shadow: 0 20px 60px rgba(255,105,180,0.4); max-width: 450px; width: 90%; border: 3px solid #ff69b4; text-align: center; animation: modalSlideIn 0.3s ease;">
        <div style="font-size: 3rem; margin-bottom: 20px;">🗑️</div>
        <h3 style="color: #ff1493; font-size: 1.8rem; margin-bottom: 15px; font-family: 'Kalam', cursive;">Remove item? ♡</h3>
        <p id="confirm-item-name" style="color: #666; font-size: 1.1rem; margin-bottom: 30px;">Are you sure you want to remove this item?</p>
        <div style="display: flex; gap: 15px; justify-content: center;">
            <button id="confirm-cancel" style="padding: 15px 40px; background: white; color: #ff69b4; border: 3px solid #ff69b4; border-radius: 50px; font-size: 1.2rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease;">
                Cancel
            </button>
            <button id="confirm-ok" style="padding: 15px 40px; background: linear-gradient(135deg, #ff69b4, #ff1493); color: white; border: none; border-radius: 50px; font-size: 1.2rem; font-weight: bold; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 8px 20px rgba(255,20,147,0.4);">
                OK
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-50px) scale(0.8);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    #custom-confirm-modal {
        display: none;
    }

    #custom-confirm-modal.show {
        display: flex !important;
    }

    #confirm-cancel:hover {
        background: #fff0f5;
        transform: translateY(-2px);
        box-shadow: 0 6px 15px rgba(255,105,180,0.3);
    }

    #confirm-ok:hover {
        background: linear-gradient(135deg, #ff1493, #ff69b4);
        transform: translateY(-2px);
        box-shadow: 0 12px 25px rgba(255,20,147,0.6);
    }

    #confirm-cancel:active, #confirm-ok:active {
        transform: translateY(0);
    }
</style>

<script>
// ========== PINK FLASH MESSAGE FUNCTION ==========
function showFlashMessage(message) {
    const flash = document.getElementById('moe-flash');
    flash.innerHTML = `
        ${message}
        <span class="close-btn" onclick="this.parentElement.classList.remove('show')">&times;</span>
    `;
    flash.classList.add('show');

    // Auto hide after 5 seconds
    setTimeout(() => {
        flash.classList.remove('show');
        setTimeout(() => flash.innerHTML = '', 700);
    }, 5000);
}

// Custom confirmation dialog for removing cart items
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('custom-confirm-modal');
    const confirmOk = document.getElementById('confirm-ok');
    const confirmCancel = document.getElementById('confirm-cancel');
    const itemNameDisplay = document.getElementById('confirm-item-name');
    let pendingUrl = null;

    // Handle all remove cart item clicks
    document.querySelectorAll('.remove-cart-item').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const itemName = this.dataset.name || 'this item';
            pendingUrl = this.href;
            
            itemNameDisplay.textContent = `Remove "${itemName}" from cart?`;
            modal.classList.add('show');
        });
    });

    // OK button - proceed with removal
    confirmOk.addEventListener('click', function() {
        if (pendingUrl) {
            window.location.href = pendingUrl;
        }
    });

    // Cancel button - close modal
    confirmCancel.addEventListener('click', function() {
        modal.classList.remove('show');
        pendingUrl = null;
    });

    // Close modal when clicking outside
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.classList.remove('show');
            pendingUrl = null;
        }
    });

    // Close modal with Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.classList.contains('show')) {
            modal.classList.remove('show');
            pendingUrl = null;
        }
    });

    // ========== UPDATE CART QUANTITY VALIDATION ==========
    // Handle update cart form submissions with stock validation
    document.querySelectorAll('.update-cart-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const maxStock = parseInt(this.dataset.stock);
            const qtyInput = this.querySelector('.cart-qty-input');
            const qty = parseInt(qtyInput.value);
            const submitBtn = this.querySelector('button[type="submit"]');

            // Validation
            if (isNaN(qty) || qty < 1) {
                showFlashMessage('❌ Invalid quantity! Please enter at least 1. ♡');
                qtyInput.focus();
                return;
            }

            if (qty > maxStock) {
                showFlashMessage(`❌ Not enough stock! Only ${maxStock} available for "${productName}" ♡`);
                qtyInput.value = maxStock;
                qtyInput.focus();
                return;
            }

            // If validation passes, submit the form
            submitBtn.disabled = true;
            submitBtn.textContent = 'Updating...';
            this.submit();
        });
    });
    
});
</script>

<?php include '../_foot.php'; ?>