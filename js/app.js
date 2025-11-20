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