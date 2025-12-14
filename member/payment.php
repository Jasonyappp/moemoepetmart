<?php
require '../_base.php';
require_login();

$id = req('id');
$stm = $_db->prepare('SELECT * FROM orders WHERE order_id = ? AND user_id = ?');
$stm->execute([$id, current_user()->id]);
$o = $stm->fetch();

if (!$o) {
    temp('error', 'Invalid or already paid order.');
    redirect('/member/my_purchase.php');
}

// Local TNG QR image path (change filename if different)
$tng_qr_path = '/images_tng/tng_qr.jpeg';  // MUST start with /

 // Handle TNG refresh request
if (post('action') === 'refresh_qr') {
    // Refresh: update time only (same image)
    $_db->prepare('UPDATE orders SET qr_code = ?, qr_generated_at = NOW() WHERE order_id = ?')
         ->execute([$tng_qr_path, $id]);
    redirect('payment.php?id=' . $id);
}

// Process normal payment
if (is_post() && !post('action')) {
    $method = post('payment_method');

    if ($method === 'cod') {
        $_db->prepare('UPDATE orders SET order_status = "To Ship", payment_method = "Cash on Delivery" WHERE order_id = ?')
             ->execute([$id]);
        temp('info', 'Cash on Delivery selected ♡');
        redirect('receipt.php?id=' . $id);
    }

    if ($method === 'card') {
        $card_no = preg_replace('/\D/', '', post('card_number'));
        $expiry = trim(post('expiry'));
        $cvv = trim(post('cvv'));

        if (strlen($card_no) !== 16) $_err['card_number'] = 'Must be 16 digits';
        if (!preg_match('/^\d{2}\/\d{2}$/', $expiry)) $_err['expiry'] = 'Format: MM/YY';
        if (strlen($cvv) !== 3) $_err['cvv'] = 'Must be 3 digits';

        if (empty($_err)) {
            $last4 = substr($card_no, -4);
            $_db->prepare('UPDATE orders SET order_status = "To Ship", payment_method = "Credit/Debit Card", card_last4 = ? WHERE order_id = ?')
                 ->execute([$last4, $id]);
            temp('info', 'Card payment successful ♡');
            redirect('receipt.php?id=' . $id);
        }
    }

    if ($method === 'tng') {
        $_db->prepare('UPDATE orders SET payment_method = "Touch n Go", qr_code = ?, qr_generated_at = NOW() WHERE order_id = ?')
             ->execute([$tng_qr_path, $id]);
        redirect('payment.php?id=' . $id);
    }
}

// Check if TNG QR is active
$show_qr = false;
$time_left = 0;
if ($o->qr_code && $o->qr_generated_at) {
    $time_left = 60 - (time() - strtotime($o->qr_generated_at));
    $show_qr = $time_left > 0;
}

// Auto success after 60s - RELIABLE PHP VERSION
$stm_latest = $_db->prepare('SELECT payment_method, qr_generated_at, order_status FROM orders WHERE order_id = ?');
$stm_latest->execute([$id]);
$latest = $stm_latest->fetch();

if ($latest && $latest->payment_method === 'Touch n Go' && $latest->qr_generated_at) {
    $time_elapsed = time() - strtotime($latest->qr_generated_at);
    if ($time_elapsed >= 60 && $latest->order_status === 'Pending Payment') {
        $_db->prepare('UPDATE orders SET order_status = "To Ship" WHERE order_id = ?')->execute([$id]);
        temp('info', 'TNG payment successful! ♡');
        redirect('receipt.php?id=' . $id);
    }
}

$_title = 'Payment - Order #' . $id;
include '../_head.php';
?>

<div class="container" style="max-width:600px; margin:40px auto; text-align:center;">
    <h2 style="color:#ff69b4;">Payment for Order #<?= $id ?> ♡</h2>
    <p style="font-size:1.3rem;"><strong>Total: RM <?= number_format($o->total_amount, 2) ?></strong></p>

    <?php if ($show_qr): ?>
        <div style="background:#f0f8ff; padding:30px; border-radius:20px; margin:30px 0;">
            <h3>Scan with Touch 'n Go eWallet ♡</h3>
            <img src="<?= $tng_qr_path ?>?v=<?= time() ?>" alt="TNG QR Code" style="width:350px; border:3px solid #ff69b4; border-radius:15px;">
            <p id="timer" style="font-size:2rem; color:#ff1493; margin:20px 0; font-weight:bold;">
                Time left: <?= $time_left ?> seconds
            </p>
            
            <form method="post" style="margin-top:20px;">
                <input type="hidden" name="action" value="refresh_qr">
                <button type="submit" style="padding:12px 30px; background:#ff69b4; color:white; border:none; border-radius:12px; font-size:1.1rem;">
                    Refresh QR Code ♡
                </button>
            </form>
            
            <p style="margin-top:20px; color:#666;">QR Code will display within 60 seconds</p>
        </div>

        <script>
            let timeLeft = <?= $time_left ?>;
            const timerEl = document.getElementById('timer');
            const interval = setInterval(() => {
                timeLeft--;
                if (timeLeft <= 0) {
                    clearInterval(interval);
                    timerEl.innerHTML = '<span style="color:green;">Payment Successful! ♡</span>';
                    setTimeout(() => location.reload(), 2000);  // Reload to trigger PHP update + redirect
                } else {
                    timerEl.textContent = 'Time left: ' + timeLeft + ' seconds';
                }
            }, 1000);
        </script>

    <?php else: ?>
        <form method="post">
            <h3 style="margin-bottom:20px;">Choose Payment Method</h3>

            <label style="display:block; padding:20px; background:white; border:2px solid #ffdee6; border-radius:15px; margin:15px 0; cursor:pointer;">
                <input type="radio" name="payment_method" value="cod" required style="transform:scale(1.5); margin-right:15px;">
                <strong>Cash on Delivery</strong><br><small>Pay when you receive ♡</small>
            </label>

            <label style="display:block; padding:20px; background:white; border:2px solid #ffdee6; border-radius:15px; margin:15px 0; cursor:pointer;">
                <input type="radio" name="payment_method" value="tng" style="transform:scale(1.5); margin-right:15px;">
                <strong>Touch 'n Go eWallet</strong><br><small>QR code valid for 60 seconds</small>
            </label>

            <label style="display:block; padding:20px; background:white; border:2px solid #ffdee6; border-radius:15px; margin:15px 0; cursor:pointer;">
                <input type="radio" name="payment_method" value="card" style="transform:scale(1.5); margin-right:15px;">
                <strong>Credit/Debit Card</strong><br><small>Fake secure payment</small>
            </label>

            <div id="card-fields" style="display:none; margin:30px 0; padding:20px; background:#fff5f9; border-radius:15px;">
                <input type="text" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19" style="width:100%; padding:12px; margin:10px 0;">
                <?= err('card_number') ?>

                <div style="display:flex; gap:15px;">
                    <div style="flex:1;">
                        <input type="text" name="expiry" placeholder="MM/YY" maxlength="5" style="width:100%; padding:12px;">
                        <?= err('expiry') ?>
                    </div>
                    <div style="flex:1;">
                        <input type="text" name="cvv" placeholder="123" maxlength="3" inputmode="numeric" style="width:100%; padding:12px;">
                        <?= err('cvv') ?>
                    </div>
                </div>
            </div>

            <button type="submit" style="width:100%; padding:15px; background:#ff69b4; color:white; border:none; border-radius:15px; font-size:1.3rem; margin-top:20px;">
                Confirm ♡
            </button>
        </form>

        <script>
            document.querySelectorAll('[name="payment_method"]').forEach(r => {
                r.addEventListener('change', () => {
                    document.getElementById('card-fields').style.display = r.value === 'card' ? 'block' : 'none';
                });
            });

            // Card formatting
            document.querySelector('[name="card_number"]').addEventListener('input', function() {
                let v = this.value.replace(/\D/g, '').match(/(\d{0,4})(\d{0,4})(\d{0,4})(\d{0,4})/);
                this.value = v.slice(1).filter(Boolean).join(' ');
            });
        </script>
    <?php endif; ?>
</div>

<?php include '../_foot.php'; ?>