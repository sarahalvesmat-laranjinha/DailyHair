// Usa apenas o projeto configurado no servidor, nunca a apiKey recebida pela URL.
const params = new URLSearchParams(location.search);
const mode = params.get('mode');
const code = params.get('oobCode');
const message = document.querySelector('#action-message');
const title = document.querySelector('#action-title');
const subtitle = document.querySelector('#action-subtitle');
const form = document.querySelector('#password-form');
const submit = document.querySelector('#save-password');
const confirmAction = document.querySelector('#confirm-action');
const requestLink = document.querySelector('#request-link');

function show(text, success = false) {
  message.textContent = text;
  message.className = 'alert ' + (success ? 'success' : 'error');
  message.hidden = false;
}
function invalidLink() {
  form.hidden = true;
  confirmAction.hidden = true;
  requestLink.hidden = false;
  show('Este link é inválido, expirou ou já foi usado. Solicite um novo link.');
}
function errorMessage(error) {
  if (['auth/expired-action-code', 'auth/invalid-action-code', 'auth/user-disabled', 'auth/user-not-found'].includes(error.code)) {
    invalidLink();
    return;
  }
  if (['auth/weak-password', 'auth/password-does-not-meet-requirements'].includes(error.code)) {
    show('A senha não atende aos requisitos da conta. Use uma senha mais forte.');
  } else if (error.code === 'auth/network-request-failed') {
    show('Não foi possível conectar. Confira sua internet e tente novamente.');
  } else if (error.code === 'auth/too-many-requests') {
    show('Muitas tentativas. Aguarde um pouco e tente novamente.');
  } else {
    show('Não foi possível concluir. Tente novamente ou solicite outro link.');
  }
}

async function start() {
  if (!code || !['resetPassword', 'verifyEmail', 'recoverEmail'].includes(mode)) {
    invalidLink();
    return;
  }
  const [{ initializeApp }, sdk, { firebaseConfig }] = await Promise.all([
    import('https://www.gstatic.com/firebasejs/11.10.0/firebase-app.js'),
    import('https://www.gstatic.com/firebasejs/11.10.0/firebase-auth.js'),
    import('./firebase-config.php')
  ]);
  if (!firebaseConfig.apiKey || !firebaseConfig.projectId || !firebaseConfig.appId || !firebaseConfig.authDomain) {
    show('A recuperação está temporariamente indisponível. Tente novamente mais tarde.');
    return;
  }
  const auth = sdk.getAuth(initializeApp(firebaseConfig));
  auth.languageCode = 'pt-BR';
  if (mode === 'resetPassword') {
    await sdk.verifyPasswordResetCode(auth, code);
    message.hidden = true;
    form.hidden = false;
    document.querySelector('#new-password').focus();
    form.addEventListener('submit', async (event) => {
      event.preventDefault();
      const password = document.querySelector('#new-password').value;
      const confirmation = document.querySelector('#confirm-password').value;
      if (password !== confirmation) {
        show('As senhas não coincidem.');
        return;
      }
      if (password.length < 8) {
        show('Use pelo menos 8 caracteres.');
        return;
      }
      submit.disabled = true;
      submit.textContent = 'Salvando…';
      try {
        await sdk.confirmPasswordReset(auth, code, password);
        form.reset();
        form.hidden = true;
        title.textContent = 'Senha atualizada!';
        subtitle.textContent = 'Agora você pode entrar com sua nova senha.';
        show('Sua senha foi alterada com sucesso.', true);
        // O código já foi utilizado; remova-o do endereço.
        history.replaceState(null, '', location.pathname);
      } catch (error) {
        errorMessage(error);
      } finally {
        submit.disabled = false;
        submit.textContent = 'Salvar nova senha';
      }
    });
    return;
  }
  const info = await sdk.checkActionCode(auth, code);
  const expected = mode === 'verifyEmail' ? 'VERIFY_EMAIL' : 'RECOVER_EMAIL';
  if (info.operation !== expected) {
    invalidLink();
    return;
  }
  title.textContent = mode === 'verifyEmail' ? 'Confirmar e-mail' : 'Recuperar e-mail';
  subtitle.textContent = mode === 'verifyEmail'
    ? 'Confirme seu endereço de e-mail para sua conta Daily Hair.'
    : 'Restaure o endereço de e-mail anterior da sua conta.';
  message.hidden = true;
  confirmAction.textContent = mode === 'verifyEmail' ? 'Confirmar e-mail' : 'Restaurar e-mail';
  confirmAction.hidden = false;
  confirmAction.addEventListener('click', async () => {
    confirmAction.disabled = true;
    try {
      await sdk.applyActionCode(auth, code);
      confirmAction.hidden = true;
      show(mode === 'verifyEmail' ? 'E-mail confirmado com sucesso!' : 'E-mail anterior restaurado. Por segurança, solicite também uma nova senha.', true);
      if (mode === 'recoverEmail') requestLink.hidden = false;
      history.replaceState(null, '', location.pathname);
    } catch (error) {
      errorMessage(error);
    } finally {
      confirmAction.disabled = false;
    }
  });
}

start().catch(errorMessage);
