<?php
require '../_base.php';
require_login();

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    temp('error', 'Cart is empty!');
    redirect('cart.php');
}

$user = current_user();

// ========== GET USER'S ADDRESSES ==========
$stm = $_db->prepare("SELECT * FROM user_addresses WHERE user_id = ? ORDER BY created_at DESC");
$stm->execute([$user->id]);
$addresses = $stm->fetchAll();

$cart = $_SESSION['cart'];
$total = array_sum(array_map(function($item) { return $item['price'] * $item['qty']; }, $cart));

$_err = [];

if (is_post()) {
    // ========== RECIPIENT DETAILS ==========
    $recipient_name = trim(post('recipient_name'));
    $recipient_phone = trim(post('recipient_phone'));
    
    // Validate recipient details
    if (empty($recipient_name)) {
        $_err['recipient_name'] = 'Recipient name is required';
    }
    
    if (empty($recipient_phone)) {
        $_err['recipient_phone'] = 'Recipient phone is required';
    } elseif (!preg_match('/^01[0-9]-?[0-9]{7,8}$/', $recipient_phone)) {
        $_err['recipient_phone'] = 'Please enter a valid Malaysian phone number';
    }

    // ========== ADDRESS HANDLING ==========
    $shipping_address = '';
    
    // Check for new address first
    $new_address = trim(post('new_full_address'));
    if (!empty($new_address)) {
        $shipping_address = $new_address;
        
        // Save if requested (but continue to place order!)
        if (isset($_POST['save_new_address'])) {
            $address_name = trim(post('new_address_name')) ?: 'Home';
            $stm = $_db->prepare("INSERT INTO user_addresses (user_id, address_name, full_address, recipient_name, recipient_phone) VALUES (?, ?, ?, ?, ?)");
            $stm->execute([$user->id, $address_name, $new_address, $recipient_name, $recipient_phone]);
        }
    }
    // Otherwise check for saved address
    else {
        $selected_id = post('selected_address_id');
        if ($selected_id && $selected_id !== 'new') {
            $stm = $_db->prepare("SELECT full_address, recipient_name, recipient_phone FROM user_addresses WHERE id = ? AND user_id = ?");
            $stm->execute([$selected_id, $user->id]);
            $address = $stm->fetch();
            if ($address) {
                $shipping_address = $address->full_address;
                // Use saved recipient details if form fields are empty
                if (empty($recipient_name) && !empty($address->recipient_name)) {
                    $recipient_name = $address->recipient_name;
                }
                if (empty($recipient_phone) && !empty($address->recipient_phone)) {
                    $recipient_phone = $address->recipient_phone;
                }
            }
        }
    }
    
    if (empty($shipping_address)) {
        $_err['address'] = 'Please provide a shipping address';
    }

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

            $current_year = date('Y');
            $current_month = date('m');

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

            // Insert order with shipping address AND recipient details
            $stm = $_db->prepare("INSERT INTO orders (user_id, shipping_address, recipient_name, recipient_phone, total_amount, order_date, order_status, payment_method, card_last4) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)");
            $stm->execute([$user->id, $shipping_address, $recipient_name, $recipient_phone, $total, $status, $payment_display, ($payment_method === 'card' ? $card_last4 : null)]);
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
        <!-- ========== RECIPIENT DETAILS SECTION ========== -->
        <div style="margin: 2rem 0;">
            <h3 style="margin-bottom: 1rem; color:#ff5722;">📞 Recipient Details ♡</h3>
            
            <div style="background: #fff0f5; padding: 20px; border-radius: 15px; border: 1px solid #ffb6c1;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 15px;">
                    <div>
                        <label for="recipient_name" style="display: block; margin-bottom: 8px; color: #ff1493; font-weight: bold;">
                            Recipient Name *
                        </label>
                        <input type="text" 
                               id="recipient_name" 
                               name="recipient_name" 
                               value="<?= post('recipient_name', $user->name ?? '') ?>" 
                               placeholder="Enter recipient's full name" 
                               style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ff69b4;">
                        <?= err('recipient_name') ?>
                    </div>
                    
                    <div>
                        <label for="recipient_phone" style="display: block; margin-bottom: 8px; color: #ff1493; font-weight: bold;">
                            Phone Number *
                        </label>
                        <input type="tel" 
                               id="recipient_phone" 
                               name="recipient_phone" 
                               value="<?= post('recipient_phone') ?>" 
                               placeholder="e.g., 012-3456789" 
                               style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ff69b4;">
                        <?= err('recipient_phone') ?>
                    </div>
                </div>
                <p style="color: #666; font-size: 0.9em; margin-top: 10px;">
                    <small>* Required fields. Delivery will be made to this recipient.</small>
                </p>
            </div>
        </div>
        <!-- ========== END RECIPIENT DETAILS SECTION ========== -->

        <!-- ========== SHIPPING ADDRESS SECTION ========== -->
        <div class="address-checkout-section">
            <h3 style="margin:2rem 0 1rem; color:#ff5722;">🏠 Shipping Address ♡</h3>
            
            <?php if (isset($_err['address'])): ?>
                <div class="error" style="color:#ff4757; margin-bottom:10px;"><?= $_err['address'] ?></div>
            <?php endif; ?>
            
            <?php if (!empty($addresses)): ?>
                <div class="saved-addresses-checkout">
                    <p style="margin-bottom: 15px; color: #666;">Select a saved address or add a new one:</p>
                    
                    <div class="address-options">
                        <?php foreach ($addresses as $index => $addr): ?>
                            <div class="address-option">
                                <input type="radio" 
                                       id="address_<?= $addr->id ?>" 
                                       name="selected_address_id" 
                                       value="<?= $addr->id ?>"
                                       <?= $index === 0 ? 'checked' : '' ?>>
                                <label for="address_<?= $addr->id ?>">
                                    <strong style="color: #ff1493;"><?= encode($addr->address_name) ?>:</strong><br>
                                    <?php if (!empty($addr->recipient_name)): ?>
                                        <span style="color: #555;">To: <?= encode($addr->recipient_name) ?> | ☎ <?= encode($addr->recipient_phone) ?></span><br>
                                    <?php endif; ?>
                                    <?= nl2br(encode($addr->full_address)) ?>
                                </label>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="address-option">
                            <input type="radio" id="new_address" name="selected_address_id" value="new">
                            <label for="new_address">
                                <strong style="color: #ff1493;">➕ Add New Address</strong><br>
                                <em style="color: #888; font-size: 0.9em;">Enter a different shipping address</em>
                            </label>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div style="background: #fff0f5; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                    <p style="color: #ff69b4; text-align: center;">
                        No saved addresses yet. Please enter your shipping address below.
                    </p>
                    <input type="hidden" name="selected_address_id" value="new">
                </div>
            <?php endif; ?>
            
            <!-- New Address Form -->
            <div id="new_address_form" style="<?= empty($addresses) ? '' : 'display: none;' ?>; margin-top: 20px; padding: 20px; background: #fff0f5; border-radius: 15px; border: 1px dashed #ff69b4;">
                <h4 style="color: #ff1493; margin-bottom: 15px;">Enter New Shipping Address</h4>
                
                <div style="margin-bottom: 15px;">
                    <input type="text" name="new_address_name" placeholder="Address Name (Optional): Home, Work, etc." 
                           style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ffb6c1; margin-bottom: 10px;">
                </div>
                
                <div style="margin-bottom: 15px;">
                    <textarea name="new_full_address" rows="3" placeholder="Full Address (Required): Street, City, State, ZIP Code, Country" 
                              style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid #ffb6c1;"><?= post('new_full_address') ?></textarea>
                </div>
                
                <label style="display: block; margin-bottom: 15px; color: #666;">
                    <input type="checkbox" name="save_new_address" checked>
                    Save this address for future orders
                </label>
            </div>
        </div>
        <!-- ========== END SHIPPING ADDRESS SECTION ========== -->

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
                        <input типу="text" name="card_number" placeholder="Card Number (1234 5678 9012 3456)" maxlength="19" style="width:100%; padding:12px; margin:8px 0; border-radius:8px; border:1px solid #ff69b4;">
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
    // Toggle new address form
    function toggleAddressForm() {
        const newAddressSelected = $('#new_address').is(':checked');
        $('#new_address_form').toggle(newAddressSelected || $('.address-option').length === 0);
    }
    
    // Toggle card fields
    function toggleCardFields() {
        if ($('input[name="payment_method"]:checked').val() === 'card') {
            $('#card-fields').show();
        } else {
            $('#card-fields').hide();
        }
    }
    
    // Phone number formatting
    $('#recipient_phone').on('input', function() {
        let value = this.value.replace(/\D/g, '');
        if (value.length > 3) {
            this.value = value.substring(0, 3) + '-' + value.substring(3, 11);
        }
    });

    // Initialize
    toggleAddressForm();
    toggleCardFields();
    
    // Event listeners
    $('input[name="selected_address_id"]').on('change', function() {
        toggleAddressForm();
        
        // Auto-fill recipient details when selecting saved address
        const selectedId = $(this).val();
        if (selectedId !== 'new') {
            const $label = $('label[for="address_' + selectedId + '"]');
            const recipientText = $label.find('span[style*="color: #555"]').text();
            if (recipientText) {
                const match = recipientText.match(/To: (.+?) \| ☎ (.+)/);
                if (match) {
                    $('#recipient_name').val(match[1].trim());
                    $('#recipient_phone').val(match[2].trim());
                }
            }
        }
    });
    
    $('input[name="payment_method"]').on('change', toggleCardFields);
    
    // Card number formatting
    $('input[name="card_number"]').on('input', function() {
        let v = this.value.replace(/\D/g, '').match(/(\d{0,4})(\d{0,4})(\d{0,4})(\d{0,4})/);
        this.value = v.slice(1).filter(Boolean).join(' ');
    });
    
    // Expiry formatting
    $('input[name="expiry"]').on('input', function() {
        let v = this.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,2})/);
        this.value = v[1] + (v[2] ? '/' + v[2] : '');
    });
    
    // CVV formatting
    $('input[name="cvv"]').on('input', function() {
        this.value = this.value.replace(/\D/g, '').slice(0, 3);
    });
});
</script>

<?php include '../_foot.php'; ?>