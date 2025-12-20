    // Initiate GET request
    $('[data-get]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.get;
        location = url || location;
    });

    // Initiate POST request
    $('[data-post]').on('click', e => {
        e.preventDefault();
        const url = e.target.dataset.post;
        const f = $('<form>').appendTo(document.body)[0];
        f.method = 'POST';
        f.action = url || location;
        f.submit();
    });

    $(function applyFilters() {
    const search = document.querySelector('input[name="search"]').value;
    const category = document.querySelector('select[name="category"]').value;
    const stock = document.querySelector('select[name="stock_status"]').value;
    const url = new URL(location);
    url.searchParams.set('search', search);
    url.searchParams.set('category', category);
    url.searchParams.set('stock_status', stock);
    url.searchParams.set('page', 1);
    location = url;});

    // ===================== MOE MOE FLASH MESSAGE  =====================
document.addEventListener('DOMContentLoaded', () => {
    const flashMsg = document.body.dataset.flash || '';
    if (flashMsg) {
        const flash = document.getElementById('moe-flash');
        flash.innerHTML = `
            ${flashMsg}
            <span class="close-btn" onclick="this.parentElement.classList.remove('show')">&times;</span>
        `;
        flash.classList.add('show');

        // Auto hide after 6 seconds
        setTimeout(() => {
            flash.classList.remove('show');
            setTimeout(() => flash.innerHTML = '', 700);
        }, 6000);
    }
});
// ===================== END FLASH =====================

// Helper function to show flash messages (for JavaScript/AJAX)
function showFlashMessage(message) {
    const flash = document.getElementById('moe-flash');
    flash.innerHTML = `
        ${message}
        <span class="close-btn" onclick="this.parentElement.classList.remove('show')">&times;</span>
    `;
    flash.classList.add('show');

    // Auto hide after 4 seconds
    setTimeout(() => {
        flash.classList.remove('show');
        setTimeout(() => flash.innerHTML = '', 700);
    }, 4000);
}

// 让 Apply 按钮真的能提交筛选
function applyFilters() {
    const search = document.querySelector('input[name="search"]').value;
    const category = document.querySelector('select[name="category"]').value;
    const stock = document.querySelector('select[name="stock_status"]').value;
    const url = new URL(location);
    url.searchParams.set('search', search);
    url.searchParams.set('category', category);
    url.searchParams.set('stock_status', stock);
    url.searchParams.set('page', 1);
    location = url;
}

// ADD TO CART – WORKS EVEN IF FILES ARE IN member/ FOLDER
$(document).ready(function () {
    $('.add-to-cart').on('click', function () {
        const btn   = $(this);
        const id    = btn.data('id');
        const name  = btn.data('name');
        const price = btn.data('price');

        if (!id || !name || !price) {
            showFlashMessage('Product info missing! ♡');
            return;
        }

        btn.prop('disabled', true).html('Adding... ♡');

        $.post('add_to_cart.php', {
            product_id: id,
            product_name: name,
            price: price,
            qty: 1
        }, function (res) {
            if (res.success) {
                $('.cart-count, .cart-badge').text(res.total_items);
                if (res.total_items > 0) {
                    $('.cart-count, .cart-badge').fadeIn(300);
                }
                showFlashMessage('Added to cart! ♡');
            } else {
                showFlashMessage(res.message || 'Cannot add to cart~');
            }
        }, 'json')
        .fail(function() {
            showFlashMessage('Error!!!😖 Please login your account & try again ♡');
        })
        .always(function () {
            btn.prop('disabled', false).html('Add to Cart ♡');
        });
    });

    // PRODUCT DETAIL: + / - buttons + custom input + Add to Cart with quantity
    $('.qty-minus').on('click', function () {
        const $input = $(this).siblings('.qty-input');
        let val = parseInt($input.val());
        if (val > 1) {
            $input.val(--val);
        }
    });

    $('.qty-plus').on('click', function () {
        const $input = $(this).siblings('.qty-input');
        const max = parseInt($input.attr('max'));
        let val = parseInt($input.val());
        if (val < max) {
            $input.val(++val);
        }
    });

    // Allow typing quantity + auto-correct
    $('.qty-input').on('input', function () {
        let val = parseInt(this.value);
        const max = parseInt($(this).attr('max'));
        val = Math.max(0, Math.min(val, max));
        this.value = val;
    });

    // Add to Cart from detail page (with selected quantity)
    $('.btn-add-to-cart-premium').on('click', function () {
        const btn = $(this);
        const id = btn.data('id');
        const name = btn.data('name');
        const price = btn.data('price');
        const qty = parseInt(btn.closest('.product-info').find('.qty-input').val());

        if (!id || !name || !price || qty < 1) {
            showFlashMessage('Invalid quantity! ♡');
            return;
        }

        btn.prop('disabled', true).html('Adding... ♡');

        $.post('add_to_cart.php', {
            product_id: id,
            product_name: name,
            price: price,
            qty: qty
        }, function (res) {
            if (res.success) {
                $('.cart-count, .cart-badge').text(res.total_items);
                if (res.total_items > 0) {
                    $('.cart-count, .cart-badge').fadeIn(300);
                }
                showFlashMessage(`Added ${qty} × ${name} to cart! ♡`);
            } else {
                showFlashMessage(res.message || 'Cannot add to cart~');
            }
        }, 'json')
        .fail(function() {
            showFlashMessage('Error!!!😖 Please login your account & try again ♡');
        })
        .always(function () {
            btn.prop('disabled', false).html('🛒 Add to Cart ♡');
        });
    });
});



document.addEventListener('DOMContentLoaded', function () {
    const fileInput     = document.getElementById('file-input');
    const dropzone      = document.getElementById('dropzone-upload');
    const placeholder   = document.getElementById('placeholder');
    const previewArea   = document.getElementById('preview-area');
    const previewImg    = document.getElementById('preview-img');
    const cancelBtn     = document.getElementById('cancel-preview');
    const cancelContainer = document.getElementById('cancel-container');

    function showPreview(file) {
        if (!file) return;

        if (file.size > 10 * 1024 * 1024) {
            alert('Too big! Please choose an image under 10MB ♡');
            fileInput.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            placeholder.classList.add('hidden');
            previewArea.classList.remove('hidden');
            cancelContainer.classList.remove('hidden'); // Show cancel button
        };
        reader.readAsDataURL(file);
    }

    fileInput.addEventListener('change', () => {
        if (fileInput.files[0]) showPreview(fileInput.files[0]);
    });

    cancelBtn.addEventListener('click', () => {
        fileInput.value = '';
        previewArea.classList.add('hidden');
        placeholder.classList.remove('hidden');
        cancelContainer.classList.add('hidden'); // Hide cancel button
    });

    dropzone.addEventListener('click', () => fileInput.click());

    // Drag & drop effects
    ['dragover', 'dragenter'].forEach(ev => {
        dropzone.addEventListener(ev, e => {
            e.preventDefault();
            dropzone.style.background = '#fff0f8';
            dropzone.style.borderColor = '#ff1493';
        });
    });

    ['dragleave', 'dragend', 'drop'].forEach(ev => {
        dropzone.addEventListener(ev, e => {
            e.preventDefault();
            dropzone.style.background = '';
            dropzone.style.borderColor = '#ff69b4';
        });
    });

    dropzone.addEventListener('drop', e => {
        e.preventDefault();
        if (e.dataTransfer.files[0]) {
            fileInput.files = e.dataTransfer.files;
            showPreview(e.dataTransfer.files[0]);
        }
    });
});

// Enhanced Add to Cart with Quantity Validation
document.querySelectorAll('.btn-add-to-cart, .btn-add-to-cart-premium').forEach(button => {
    button.addEventListener('click', function(e) {
        e.preventDefault();
        const productId = this.dataset.id;
        const qtyInput = this.closest('.product-info, .add-to-cart-section').querySelector('.qty-input');
        let qty = parseInt(qtyInput ? qtyInput.value : 1);  // Fallback to 1 if no input

        // Validation
        if (isNaN(qty) || qty < 1) {
            alert('Invalid quantity! Please select at least 1. ♡');
            if (qtyInput) qtyInput.focus();
            return;
        }

        const maxStock = parseInt(this.dataset.max || qtyInput?.max || 999);
        if (qty > maxStock) {
            alert(`Not enough stock! Maximum available: ${maxStock} ♡`);
            if (qtyInput) qtyInput.value = maxStock;
            return;
        }

        // Proceed with AJAX add to cart (assuming existing fetch or $.post logic)
        fetch('/member/add_to_cart.php', {  // Adjust path if needed
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                product_id: productId,
                qty: qty
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Added to cart successfully! ♡ Total items: ' + data.total_items);
                // Update cart badge (if exists)
                const badge = document.querySelector('.cart-badge');
                if (badge) badge.textContent = data.total_items;
            } else {
                alert(data.message || 'Failed to add to cart. Please try again ♡');
            }
        })
        .catch(error => {
            console.error('Add to cart error:', error);
            alert('Error adding to cart. Please check your connection ♡');
        });
    });
});

// Quantity +/- buttons (ensure min 1)
document.querySelectorAll('.qty-minus').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.nextElementSibling;  // Assuming structure: - input +
        let val = parseInt(input.value);
        if (val > 1) input.value = val - 1;
    });
});

document.querySelectorAll('.qty-plus').forEach(btn => {
    btn.addEventListener('click', () => {
        const input = btn.previousElementSibling;
        const max = parseInt(input.max);
        let val = parseInt(input.value);
        if (val < max) input.value = val + 1;
    });
});

//payment
// Card number formatting (XXXX XXXX XXXX XXXX)
const cardNumber = document.querySelector('input[name="card_number"]');
if (cardNumber) {
    cardNumber.addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '').match(/(\d{0,4})(\d{0,4})(\d{0,4})(\d{0,4})/);
        this.value = v.slice(1).filter(Boolean).join(' ');
    });
}

// Expiry formatting (MM/YY)
const expiry = document.querySelector('input[name="card_expiry"]');
if (expiry) {
    expiry.addEventListener('input', function() {
        let v = this.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,2})/);
        this.value = v[1] + (v[2] ? '/' + v[2] : '');
    });
}

$(document).ready(function() {
    // Function to show/hide card fields
    function toggleCardFields() {
        if ($('input[name="payment_method"]:checked').val() === 'card') {
            $('#card-fields-row').show();
        } else {
            $('#card-fields-row').hide();
        }
    }

    // Run immediately on page load (important for back button or pre-checked)
    toggleCardFields();

    // Run every time a payment method radio is changed
    $('input[name="payment_method"]').on('change', toggleCardFields);

    // Card number formatting: XXXX XXXX XXXX XXXX
    $('input[name="card_number"]').on('input', function() {
        let v = this.value.replace(/\D/g, '').match(/(\d{0,4})(\d{0,4})(\d{0,4})(\d{0,4})/);
        this.value = v.slice(1).filter(Boolean).join(' ');
    });

    // Expiry date formatting: MM/YY
    $('input[name="expiry"]').on('input', function() {
        let v = this.value.replace(/\D/g, '').match(/(\d{0,2})(\d{0,2})/);
        this.value = v[1] + (v[2] ? '/' + v[2] : '');
    });
});


