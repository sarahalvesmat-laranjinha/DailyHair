<?php

require_once __DIR__ . '/auth.php';

require_login();

$page_title = 'Perfil';

$extra_css = 'style.css';

require_once __DIR__ . '/header.php';


$conn = db();

$user = current_user();

$message = '';
$error = '';


/* =====================================================
   SALVAR ALTERAÇÕES
===================================================== */

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name = trim($_POST['name'] ?? '');
    $curl_pattern = trim($_POST['curl_pattern'] ?? '');
    $bio = trim($_POST['bio'] ?? '');

    /* Mantém a foto atual caso nenhuma nova seja escolhida */

    $profile_image = $user['profile_image'] ?? null;


    /* =================================================
       VALIDAR NOME
    ================================================== */

    if ($name === '') {

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

            $error = 'Não foi possível enviar a foto.';

        } else {

            $file = $_FILES['image'];


            /* Limite de 5 MB */

            if ($file['size'] > 5 * 1024 * 1024) {

                $error = 'A foto deve ter no máximo 5 MB.';

            } else {

                /* Verifica o tipo real da imagem */

                $finfo = new finfo(FILEINFO_MIME_TYPE);

                $mime = $finfo->file($file['tmp_name']);


                $allowed = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    
                ];


                if (!isset($allowed[$mime])) {

                    $error = 'Escolha uma imagem JPG, PNG .';

                } else {


                    /* =================================
                       CRIAR PASTA
                    ================================== */

                    $upload_dir = __DIR__ . '/uploads/profiles';


                    if (!is_dir($upload_dir)) {

                        mkdir($upload_dir, 0755, true);

                    }


                    /* =================================
                       NOME DO ARQUIVO
                    ================================== */

                    $extension = $allowed[$mime];

                    $filename =
                        'profile_' .
                        $user['id'] .
                        '_' .
                        time() .
                        '.' .
                        $extension;


                    $destination =
                        $upload_dir .
                        '/' .
                        $filename;


                    /* =================================
                       SALVAR IMAGEM
                    ================================== */

                    if (
                        move_uploaded_file(
                            $file['tmp_name'],
                            $destination
                        )
                    ) {

                        /* Apaga a foto antiga */

                        if (!empty($profile_image)) {

                            $old_file =
                                $upload_dir .
                                '/' .
                                basename($profile_image);


                            if (
                                is_file($old_file) &&
                                $old_file !== $destination
                            ) {

                                unlink($old_file);

                            }

                        }


                        /*
                         * Aqui está o ponto principal:
                         * o nome da nova imagem passa a ser
                         * o profile_image do usuário.
                         */

                        $profile_image = $filename;

                    } else {

                        $error = 'Não foi possível salvar a foto.';

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


        <!-- =================================================
             FOTO DE PERFIL
        ================================================== -->

        <div class="rating-field">
         <label for="name">
                Foto de perfil:
            </label>
</div>

<center>

                <div
                    class="upload-box"
                    id="upload-box"
                >

                    <input
                        type="file"
                        id="image"
                        name="image"
                        accept="image/jpeg,image/png"
                        onchange="
                            document.getElementById('upload-label').textContent =
                            this.files[0]
                            ? this.files[0].name
                            : 'Foto de perfil';
                        "
                    >

                    <strong id="upload-label">
                        Foto de perfil
                    </strong>

                </div>

            


            <div class="upload-hint">

                Clique ou arraste o arquivo aqui

            </div>
</center>
        


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
                placeholder="Nome"
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
                placeholder="Ex: 1B"
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
                placeholder="Ex: Meu cabelo, meus cuidados e minhas descobertas"
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

.profile-upload-area {
    display: flex;
    align-items: center;
    gap: 20px;
    margin-top: 10px;
}

.profile-current-photo {
    width: 90px;
    height: 90px;
    min-width: 90px;

    border-radius: 50%;
    overflow: hidden;

    background: #ecd3c8;

    display: flex;
    align-items: center;
    justify-content: center;

    color: var(--terracotta);
    font-size: 30px;
    font-weight: 700;
}

.profile-current-photo img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.upload-box {
    position: relative;
}

.upload-box input[type="file"] {
    cursor: pointer;
}

.upload-hint {
    margin-top: 8px;
    color: var(--muted);
    font-size: 12px;
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

    .profile-upload-area {
        flex-direction: column;
        align-items: flex-start;
    }

}

</style>

<script>
const imageInput = document.getElementById('image');
const preview = document.getElementById('profile-preview');
const uploadLabel = document.getElementById('upload-label');

imageInput.addEventListener('change', function () {

    const file = this.files[0];

    if (!file) {
        return;
    }

    uploadLabel.textContent = file.name;

    const reader = new FileReader();

    reader.onload = function (e) {

        preview.innerHTML = `
            <img
                src="${e.target.result}"
                alt="Foto de perfil"
            >
        `;

    };

    reader.readAsDataURL(file);

});
</script>

<?php

require_once __DIR__ . '/footer.php';

?>
