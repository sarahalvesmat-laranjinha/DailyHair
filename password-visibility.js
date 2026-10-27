// Alternância acessível da visibilidade da senha, com ícones de olho.
const eyeOpen = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/></svg>';
const eyeClosed = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 3l18 18M10.6 5.1A11 11 0 0 1 12 5c6.4 0 10 7 10 7a16 16 0 0 1-3.2 4.1M6.2 6.2C3.5 8.1 2 12 2 12s3.6 7 10 7a10.7 10.7 0 0 0 4.2-.9"/><path d="M10 10a3 3 0 0 0 4 4"/></svg>';
function syncPasswordButton(button, input) {
    const visible = input.type === 'text';
    button.innerHTML = visible ? eyeOpen : eyeClosed;
    button.setAttribute('aria-label', visible ? 'Ocultar senha' : 'Mostrar senha');
    button.setAttribute('title', visible ? 'Ocultar senha' : 'Mostrar senha');
    button.setAttribute('aria-pressed', String(visible));
}
document.querySelectorAll('.password-visibility-toggle').forEach(button => {
    const input = button.closest('.password-visibility-field')?.querySelector('input');
    if (input) syncPasswordButton(button, input);
});
document.addEventListener('click', event => {
    const button = event.target.closest('.password-visibility-toggle');
    if (!button) return;
    const input = button.closest('.password-visibility-field')?.querySelector('input');
    if (!input) return;
    const start = input.selectionStart;
    const end = input.selectionEnd;
    input.type = input.type === 'password' ? 'text' : 'password';
    syncPasswordButton(button, input);
    input.focus({preventScroll: true});
    try { input.setSelectionRange(start, end); } catch (_) {}
});
