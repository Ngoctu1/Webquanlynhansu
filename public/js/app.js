document.addEventListener('DOMContentLoaded', () => {
    const passwordToggle = document.getElementById('togglePassword');
    passwordToggle?.addEventListener('click', () => {
        const input = document.getElementById('password');
        input.type = input.type === 'password' ? 'text' : 'password';
        passwordToggle.setAttribute('aria-label', input.type === 'password' ? 'Hiện mật khẩu' : 'Ẩn mật khẩu');
        passwordToggle.innerHTML = `<i class="bi bi-eye${input.type === 'password' ? '' : '-slash'}"></i>`;
    });
    const element = document.getElementById('confirmModal');
    if (!element || !window.bootstrap) return;
    const modal = new bootstrap.Modal(element);
    let pendingForm = null;
    document.querySelectorAll('form[data-confirm]').forEach(form => {
        form.addEventListener('submit', event => {
            if (form.dataset.confirmed === 'true') return;
            event.preventDefault();
            pendingForm = form;
            document.getElementById('confirmMessage').textContent = form.dataset.confirm;
            modal.show();
        });
    });
    document.getElementById('confirmSubmit').addEventListener('click', () => {
        if (!pendingForm) return;
        pendingForm.dataset.confirmed = 'true';
        pendingForm.requestSubmit();
        modal.hide();
    });
});
