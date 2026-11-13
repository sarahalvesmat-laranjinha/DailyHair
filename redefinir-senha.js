import { initializeApp } from 'https://www.gstatic.com/firebasejs/11.10.0/firebase-app.js';
import { getAuth, verifyPasswordResetCode, confirmPasswordReset } from 'https://www.gstatic.com/firebasejs/11.10.0/firebase-auth.js';
import { firebaseConfig } from './firebase-config.php';

const auth = getAuth(initializeApp(firebaseConfig));
auth.languageCode = 'pt-BR';
const params = new URLSearchParams(window.location.search);
const mode = params.get('mode');
const code = params.get('oobCode');
const form = document.getElementById('reset-form');
const message = document.getElementById('reset-message');
const submit = form.querySelector('[type="submit"]');

function notify(text, success = false) {
  message.hidden = false;
  message.textContent = text;
  message.className = 'dh-reset-message ' + (success ? 'success' : 'error');
}

for (const toggle of document.querySelectorAll('[data-toggle]')) {
  toggle.addEventListener('click', () => {
    const field = document.getElementById(toggle.dataset.toggle);
    const visible = field.type === 'password';
    field.type = visible ? 'text' : 'password';
    toggle.textContent = visible ? 'Ocultar' : 'Mostrar';
    toggle.setAttribute('aria-label', (visible ? 'Ocultar' : 'Mostrar') + ' senha');
  });
}

async function prepare() {
  if (mode !== 'resetPassword' || !code) {
    notify('Link inválido. Solicite um novo e-mail de redefinição de senha.');
    return;
  }
  try {
    await verifyPasswordResetCode(auth, code);
    form.hidden = false;
  } catch {
    notify('Este link expirou ou já foi utilizado. Solicite um novo e-mail de redefinição.');
  }
}

form.addEventListener('submit', async event => {
  event.preventDefault();
  const password = document.getElementById('new-password').value;
  const confirm = document.getElementById('confirm-password').value;
  if (password !== confirm) {
    notify('As senhas não coincidem.');
    return;
  }
  submit.disabled = true;
  try {
    await confirmPasswordReset(auth, code, password);
    form.hidden = true;
    notify('Senha alterada com sucesso! Agora você pode entrar com sua nova senha.', true);
  } catch (error) {
    notify(error.code === 'auth/weak-password'
      ? 'Escolha uma senha mais forte.'
      : 'Não foi possível redefinir a senha. O link pode ter expirado. Solicite um novo.');
  } finally {
    submit.disabled = false;
  }
});

prepare();
