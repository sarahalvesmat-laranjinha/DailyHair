<?php

require_once __DIR__ . '/auth.php';
require_login();

$page_title = 'Cadastrar produto';
$extra_css  = 'product-form.css';

require_once __DIR__ . '/header.php';

$erro = $_GET['error'] ?? '';
$enviado = isset($_GET['sent']);

?>

<div class="page-title">
    <span class="eyebrow">COMUNIDADE DAILY HAIR</span>
    <h1>Cadastrar produto</h1>
    <p>Não achou o produto que você usa? Cadastre aqui — ele passa por uma análise rápida antes de aparecer para todo mundo.</p>
</div>

<?php if ($erro): ?>
    <div class="alert error"><?= e($erro) ?></div>
<?php endif; ?>

<?php if ($enviado): ?>
    <div class="alert success">
        Produto enviado! Ele será analisado antes de aparecer no Daily Hair.
    </div>
<?php endif; ?>

<div class="review-form panel">

    <form
        method="post"
        action="product-form-save.php"
        enctype="multipart/form-data"
    >

        <div class="rating-field">
            <label for="name">Nome do produto</label>
            <input
                type="text"
                id="name"
                name="name"
                required
                placeholder="Ex.: Máscara de Hidratação"
                value="<?= e($_POST['name'] ?? '') ?>"
            >
        </div>

        <div class="rating-field">
            <label for="brand">Marca</label>
            <input
                type="text"
                id="brand"
                name="brand"
                required
                placeholder="Ex.: Lola Cosmetics"
                value="<?= e($_POST['brand'] ?? '') ?>"
            >
        </div>

       <div class="rating-field">
    <label for="category">Categoria</label>

    <select id="category" name="category" required>
        <textarea
        placeholder="Selecione uma categoria"
        ></textarea>

        <option value="Hidratação"
            <?= ($_POST['category'] ?? '') === 'Hidratação' ? 'selected' : '' ?>>
            Hidratação
        </option>

        <option value="Nutrição"
            <?= ($_POST['category'] ?? '') === 'Nutrição' ? 'selected' : '' ?>>
            Nutrição
        </option>

        <option value="Reconstrução"
            <?= ($_POST['category'] ?? '') === 'Reconstrução' ? 'selected' : '' ?>>
            Reconstrução
</option>
    </select>
</div>

        <div class="comment-field">
            <label for="description">Descrição</label>
            <textarea
                id="description"
                name="description"
                rows="4"
                required
                placeholder="Para que serve o produto?"
            ><?= e($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="comment-field">
            <label>Foto do produto</label>
            <center>
            <div class="upload-box" id="upload-box">
                <input
                    type="file"
                    id="image"
                    name="image"
                    accept="image/jpeg,image/png"
                    required
                    onchange="document.getElementById('upload-label').textContent = this.files[0] ? this.files[0].name : 'Adicionar imagem';"
                >
                <span class="plus">+</span>
                <strong id="upload-label">Adicionar imagem</strong>
                <span>Clique ou arraste o arquivo aqui</span>
            </div>

            <span class="upload-hint">
                JPG, PNG , até 5MB. Mostre apenas o produto.
            </span>
        </div>
        </center>

        <button class="btn primary" type="submit">
            Cadastrar produto
        </button>

    </form>

</div>

<?php

require_once __DIR__ . '/footer.php';

?>
