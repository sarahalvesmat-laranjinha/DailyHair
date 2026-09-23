document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input[type=password]').forEach(input => {
    input.addEventListener('keydown', e => { if (e.key === 'Enter') { const form = input.closest('form'); if (form) form.requestSubmit(); } });
  });
});
 