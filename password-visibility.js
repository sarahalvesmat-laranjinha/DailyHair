document.addEventListener('click', (event) => {
    const button = event.target.closest('.password-visibility-toggle');
    if (!button) return;
    const input = button.closest('.password-visibility-field')?.querySelector('input');
    if (!input) return;
    const visible = input.type === 'password';
    input.type = visible ? 'text' : 'password';
    button.textContent = visible ? 'Ocultar' : 'Mostrar';
    button.setAttribute('aria-label', visible ? 'Ocultar senha' : 'Mostrar senha');
    button.setAttribute('aria-pressed', String(visible));
});
