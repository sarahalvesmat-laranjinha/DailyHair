(() => {
  'use strict';
  const key = 'dailyhair_cookie_consent_v1';
  const banner = document.getElementById('dh-cookie-banner');
  const dialog = document.getElementById('dh-cookie-dialog');
  const settings = document.getElementById('dh-cookie-settings');
  const optional = document.getElementById('dh-cookie-optional');
  if (!banner || !dialog || !settings || !optional) return;
  let lastFocus = null;
  const read = () => { try { const c = JSON.parse(localStorage.getItem(key)); return c && c.version === 1 && typeof c.optional === 'boolean' ? c : null; } catch { return null; } };
  const save = (allowed) => {
    const choice = { version: 1, necessary: true, optional: !!allowed, savedAt: new Date().toISOString() };
    try { localStorage.setItem(key, JSON.stringify(choice)); } catch { /* Storage blocked: ask again on next visit. */ }
    window.dispatchEvent(new CustomEvent('dailyhair:cookie-consent', { detail: choice }));
    banner.hidden = true; dialog.hidden = true; settings.hidden = false;
    document.body.style.removeProperty('overflow');
    if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
  };
  const open = () => { lastFocus = document.activeElement; optional.checked = read()?.optional || false; dialog.hidden = false; document.body.style.overflow = 'hidden'; dialog.querySelector('.dh-cookie-dialog').focus(); };
  const close = () => { dialog.hidden = true; document.body.style.removeProperty('overflow'); if (lastFocus?.focus) lastFocus.focus(); };
  document.getElementById('dh-cookie-accept').addEventListener('click', () => save(true));
  document.getElementById('dh-cookie-reject').addEventListener('click', () => save(false));
  document.getElementById('dh-cookie-manage').addEventListener('click', open);
  document.getElementById('dh-cookie-save').addEventListener('click', () => save(optional.checked));
  document.getElementById('dh-cookie-cancel').addEventListener('click', close);
  settings.addEventListener('click', open);
  dialog.addEventListener('click', e => { if (e.target === dialog) close(); });
  document.addEventListener('keydown', e => {
    if (dialog.hidden) return;
    if (e.key === 'Escape') { close(); return; }
    if (e.key === 'Tab') {
      const nodes = [...dialog.querySelectorAll('button:not([disabled]), input:not([disabled])')];
      if (!nodes.length) return;
      const first = nodes[0], last = nodes[nodes.length - 1];
      if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });
  const prior = read(); banner.hidden = !!prior; settings.hidden = !prior;
})();