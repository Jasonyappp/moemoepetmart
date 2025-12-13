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

// ADD TO CART — WORKS EVEN IF FILES ARE IN member/ FOLDER
$(document).ready(function () {
    $('.add-to-cart').on('click', function () {
        const btn   = $(this);
        const id    = btn.data('id');
        const name  = btn.data('name');
        const price = btn.data('price');

        if (!id || !name || !price) {
            alert('Product info missing! ♡');
            return;
        }

        btn.prop('disabled', true).html('Adding... ♡');

        // THIS LINE IS THE FIX!!!
        $.post('add_to_cart.php', {   // ← removed the leading slash /
            product_id: id,
            product_name: name,
            price: price
        }, function (res) {
            if (res.success) {
                $('.cart-count, .cart-badge').text(res.total_items);
                if (res.total_items > 0) {
                    $('.cart-count, .cart-badge').fadeIn(300);
                }
                alert('Added to cart! ♡');
            } else {
                alert(res.message || 'Cannot add to cart~');
            }
        }, 'json')
        .fail(function() {
            alert('Connection error! Please try again ♡');
        })
        .always(function () {
            btn.prop('disabled', false).html('Add to Cart ♡');
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