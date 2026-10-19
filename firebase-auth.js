import { initializeApp } from 'https://www.gstatic.com/firebasejs/11.10.0/firebase-app.js';
import {
  getAuth, setPersistence, inMemoryPersistence,
  signInWithEmailAndPassword, createUserWithEmailAndPassword,
  GoogleAuthProvider, signInWithPopup, sendPasswordResetEmail, signOut
} from 'https://www.gstatic.com/firebasejs/11.10.0/firebase-auth.js';
import { firebaseConfig } from './firebase-config.php';

const auth = getAuth(initializeApp(firebaseConfig));
auth.languageCode = 'pt-BR';
const page = document.body.dataset.authPage;
const form = document.querySelector('form.auth-form');
const message = document.querySelector('#firebase-message');
const button = form?.querySelector('button[type="submit"]');

function show(text, success = false) {
  message.textContent = text;
  message.className = 'alert ' + (success ? 'success' : 'error');
  message.hidden = false;
}

async function openPhpSession(user, profile = {}) {
  const response = await fetch('firebase-session.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'same-origin',
    body: JSON.stringify({
      idToken: await user.getIdToken(),
      csrf: document.querySelector('meta[name="auth-csrf"]').content,
      profile,
      registration: page === 'register'
    })
  });
  const result = await response.json();
  if (!response.ok || !result.ok) throw new Error(result.error || 'Não foi possível entrar.');
  window.location.assign('dashboard.php');
}

function friendlyError(error) {
  switch (error.code) {
    case 'auth/email-already-in-use': return 'Este e-mail já está cadastrado no Firebase.';
    case 'auth/invalid-email': return 'Informe um e-mail válido.';
    case 'auth/weak-password': return 'Escolha uma senha mais forte.';
    case 'auth/invalid-credential': return 'E-mail ou senha incorretos. Se sua conta é antiga, use o acesso anterior.';
    case 'auth/popup-closed-by-user': return 'A janela do Google foi fechada.';
    case 'auth/unauthorized-domain': return 'Adicione este domínio aos domínios autorizados no Firebase.';
    default: return error.message || 'Não foi possível continuar.';
  }
}

if (page && form) {
  if (!firebaseConfig.apiKey || !firebaseConfig.authDomain || !firebaseConfig.projectId || !firebaseConfig.appId) {
    show('Configure as variáveis FIREBASE_WEB_* no servidor antes de usar o Firebase.');
  }

  form.addEventListener('submit', async (event) => {
    if (page === 'login' && new URLSearchParams(location.search).has('legacy')) return;
    event.preventDefault();
    button.disabled = true;
    try {
      const data = new FormData(form);
      const email = String(data.get('email') || '').trim();
      if (page === 'reset') {
        await sendPasswordResetEmail(auth, email);
        show('Se o e-mail estiver no Firebase, você receberá um link de redefinição.', true);
      } else if (page === 'register') {
        if (!form.checkValidity()) { form.reportValidity(); throw new Error('Preencha os campos obrigatórios do cadastro.'); }
        if (data.get('password') !== data.get('confirm')) throw new Error('As senhas não coincidem.');
        if (!data.get('aceite_termos')) throw new Error('Aceite os termos para criar a conta.');
        await setPersistence(auth, inMemoryPersistence);
        const credential = await createUserWithEmailAndPassword(auth, email, String(data.get('password')));
        await openPhpSession(credential.user, {
          name: String(data.get('name') || ''),
          hair_type: String(data.get('hair_type') || ''),
          curl_pattern: String(data.get('curl_pattern') || ''),
          needs: data.getAll('needs[]')
        });
      } else {
        await setPersistence(auth, inMemoryPersistence);
        const credential = await signInWithEmailAndPassword(auth, email, String(data.get('password')));
        await openPhpSession(credential.user);
      }
    } catch (error) {
      show(friendlyError(error));
      if (page !== 'reset') await signOut(auth).catch(() => {});
    } finally {
      button.disabled = false;
    }
  });

  const google = document.querySelector('#google-login');
  google?.addEventListener('click', async () => {
    if (page === 'register' && !form.querySelector('[name="curl_pattern"]').value) {
      form.querySelector('[name="curl_pattern"]').reportValidity();
      show('Escolha a curvatura do seu cabelo antes de continuar com Google.');
      return;
    }
    if (page === 'register' && !form.querySelector('[name="aceite_termos"]').checked) {
      show('Aceite os termos para criar a conta.');
      return;
    }
    google.disabled = true;
    try {
      await setPersistence(auth, inMemoryPersistence);
      const credential = await signInWithPopup(auth, new GoogleAuthProvider());
      await openPhpSession(credential.user, page === 'register' ? {
        name: String(new FormData(form).get('name') || credential.user.displayName || ''),
        hair_type: String(new FormData(form).get('hair_type') || ''),
        curl_pattern: String(new FormData(form).get('curl_pattern') || ''),
        needs: new FormData(form).getAll('needs[]')
      } : {});
    } catch (error) {
      show(friendlyError(error));
      await signOut(auth).catch(() => {});
    } finally {
      google.disabled = false;
    }
  });
}
