<?php

require_once __DIR__ . '/auth.php';

require_login();

$page_title = 'Perfil';

$extra_css = 'style.css';




$conn = db();

$user = current_user();

$message = '';
$error = '';


/* =====================================================
   SALVAR ALTERAÇÕES
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name'] ?? '');
    $curl_pattern = trim($_POST['curl_pattern'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    /* Mantém a foto atual caso nenhuma nova seja escolhida */

    $profile_image = $user['profile_image'] ?? null;


    /* =================================================
       VALIDAR NOME
    ================================================== */

    if ($name === '' || mb_strlen($name) > 120 || mb_strlen($bio) > 150 || mb_strlen($curl_pattern) > 20) {

        $error = 'Digite um nome para o perfil.';

    }


    /* =================================================
       FOTO
    ================================================== */

    if (
        $error === '' &&
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {

            $error = in_array($_FILES['image']['error'], [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)
                ? 'A foto deve ter no máximo 5 MB.'
                : 'Não foi possível enviar a foto. Tente novamente.';

        } else {

            $file = $_FILES['image'];

            if (!is_uploaded_file($file['tmp_name']) || $file['size'] <= 0 || $file['size'] > 5 * 1024 * 1024) {

                $error = 'A foto deve ter no máximo 5 MB.';

            } else {

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']);

                $allowed = [
                    'image/jpeg',
                    'image/png',
                ];

                if (!in_array($mime, $allowed, true)) {

                    $error = 'Escolha uma imagem JPG ou PNG.';

                } else {

                    $bytes = file_get_contents($file['tmp_name']);

                    if ($bytes === false) {
                        $error = 'Não foi possível ler a foto enviada.';
                    } else {
                        /*
                         * Guarda a própria imagem no banco como Data URI.
                         * Isso evita perder a foto quando o Render reinicia
                         * ou faz um novo deploy.
                         */
                        $profile_image =
                            'data:' .
                            $mime .
                            ';base64,' .
                            base64_encode($bytes);
                    }

                }

            }

        }

    }


    /* =================================================
       SALVAR NO BANCO
    ================================================== */

    if ($error === '') {

        $stmt = $conn->prepare('
            UPDATE users
            SET
                name = ?,
                curl_pattern = ?,
                bio = ?,
                profile_image = ?
            WHERE id = ?
        ');


        if (!$stmt) {

            $error =
                'Erro ao atualizar o perfil: ' .
                'Consulte o log do servidor.';

        } else {

            $stmt->bindValue(1, $name, PDO::PARAM_STR);
            $stmt->bindValue(2, $curl_pattern, PDO::PARAM_STR);
            $stmt->bindValue(3, $bio, PDO::PARAM_STR);
            $stmt->bindValue(4, $profile_image, PDO::PARAM_STR);
            $stmt->bindValue(5, $user['id'], PDO::PARAM_INT);


            if ($stmt->execute()) {

               $message = 'Perfil atualizado com sucesso!';

$user = current_user();

header('Location: perfil-publico.php?id=' . $user['id']);
exit;
            } else {

                $error =
                    'Não foi possível salvar as alterações.';

            }

        }

    }

}

require_once __DIR__ . '/header.php';

?>


<div class="page-title">

    <span class="eyebrow">
        PERFIL DAILY HAIR
    </span>

    <h1>
        Personalize seu perfil
    </h1>

    <p>
        Atualize seu nome, sua foto de perfil ou sua bio!
    </p>

</div>


<?php if ($message): ?>

    <div class="profile-message success">
        <?= e($message) ?>
    </div>

<?php endif; ?>


<?php if ($error): ?>

    <div class="profile-message error">
        <?= e($error) ?>
    </div>

<?php endif; ?>


<div class="review-form panel">

    <form
        method="post"
        action="perfil.php"
        enctype="multipart/form-data"
    >
        <?= csrf_field() ?>


        <!-- =================================================
             FOTO DE PERFIL
        ================================================== -->

        <div class="rating-field">
            <label for="image">Foto de perfil:</label>
        </div>

        <?php
            $current_photo_url = profile_image_url($user);
            $profile_initial = mb_strtoupper(mb_substr($user['name'], 0, 1));
        ?>

        <div class="profile-photo-picker">

            <label class="profile-photo-label" for="image">

                <div id="profile-preview" class="profile-current-photo" aria-live="polite">

                    <?php if ($current_photo_url): ?>

                        <img
                            src="<?= e($current_photo_url) ?>"
                            alt="Foto de perfil"
                            onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
                        >
                        <span class="profile-photo-fallback" hidden>
                            <?= e($profile_initial) ?>
                        </span>

                    <?php else: ?>

                        <span class="profile-photo-fallback">
                            <?= e($profile_initial) ?>
                        </span>

                    <?php endif; ?>

                </div>

                <span class="profile-photo-action">
                    Clique para escolher outra foto
                </span>

            </label>

            <input
                class="profile-photo-input"
                type="file"
                id="image"
                name="image"
                accept="image/jpeg,image/png"
            >

            <div class="upload-hint">
                JPG ou PNG, até 5 MB
            </div>

        </div>


        <!-- =================================================
             NOME
        ================================================== -->

        <div class="rating-field">

            <label for="name">
                Nome do perfil:
            </label>

            <input
                type="text"
                id="name"
                name="name"
                required
                placeholder="Digite seu nome como deseja que apareça no perfil"
                value="<?= e($user['name'] ?? '') ?>"
            >

        </div>


        <!-- =================================================
             CURVATURA
        ================================================== -->

        <div class="rating-field">

            <label for="curl_pattern">
                Curvatura do cabelo:
            </label>

            <input
                type="text"
                id="curl_pattern"
                name="curl_pattern"
                placeholder="Informe a curvatura do cabelo (ex.: 2C)"
                value="<?= e($user['curl_pattern'] ?? '') ?>"
            >

        </div>


        <!-- =================================================
             BIO
        ================================================== -->

        <div class="rating-field">

            <label for="bio">
                Bio:
            </label>

            <input
                type="text"
                id="bio"
                name="bio"
                maxlength="150"
                placeholder="Conte um pouco sobre seu cabelo e seus cuidados"
                value="<?= e($user['bio'] ?? '') ?>"
            >

        </div>


        <br>


        <!-- =================================================
             BOTÃO
        ================================================== -->

        <button
            class="btn primary wide full"
            type="submit"
        >

            Salvar alterações

        </button>


    </form>

</div>

</div>

<style>
.profile-photo-picker {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
    margin: 8px 0 30px;
}

.profile-photo-label {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 12px;
    cursor: pointer;
}

.profile-current-photo {
    width: 150px;
    height: 150px;
    min-width: 150px;
    border-radius: 50%;
    overflow: hidden;
    background: #ecd3c8;
    border: 2px solid #ead9d0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--terracotta);
    font-size: 42px;
    font-weight: 700;
    box-shadow: 0 10px 28px rgba(83, 55, 43, .08);
    transition: border-color .2s ease, transform .2s ease;
}

.profile-photo-label:hover .profile-current-photo {
    border-color: var(--terracotta);
    transform: translateY(-2px);
}

.profile-current-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    display: block;
}

.profile-photo-action {
    color: var(--terracotta);
    font-size: 13px;
    font-weight: 600;
}

.profile-photo-input {
    position: fixed;
    left: -9999px;
    width: 1px;
    height: 1px;
    opacity: 0;
}

.upload-hint {
    color: var(--muted);
    font-size: 12px;
    text-align: center;
}

.profile-message {
    max-width: 850px;
    margin: 0 auto 20px;
    padding: 12px 16px;
    border-radius: 12px;
    font-size: 14px;
}

.profile-message.success {
    background: #edf5ec;
    color: #52704d;
}

.profile-message.error {
    background: #f8e8e4;
    color: #a94427;
}

@media (max-width: 600px) {
    .profile-current-photo {
        width: 130px;
        height: 130px;
        min-width: 130px;
        font-size: 36px;
    }
}
</style>

<script>
const imageInput = document.getElementById('image');
const preview = document.getElementById('profile-preview');

imageInput?.addEventListener('change', function () {
    const file = this.files[0];

    if (!file) {
        return;
    }

    const reader = new FileReader();

    reader.onload = function (event) {
        if (imageInput.files[0] !== file) {
            return;
        }

        const image = document.createElement('img');
        image.src = event.target.result;
        image.alt = 'Prévia da foto de perfil';

        preview.replaceChildren(image);
    };

    reader.readAsDataURL(file);
});
</script>

<?php

require_once __DIR__ . '/footer.php';

?>
