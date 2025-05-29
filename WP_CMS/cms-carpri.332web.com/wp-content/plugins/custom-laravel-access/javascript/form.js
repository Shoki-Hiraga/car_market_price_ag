document.addEventListener('DOMContentLoaded', function() {
    const toggle = document.getElementById('fixed-form-toggle');
    const container = document.getElementById('fixed-form-container');
    let isOpen = true;
    toggle.addEventListener('click', function() {
        isOpen = !isOpen;
        container.style.display = isOpen ? 'block' : 'none';
        toggle.textContent = isOpen ? '✕ フォームを閉じる' : '＋ フォームを開く';
    });
});
