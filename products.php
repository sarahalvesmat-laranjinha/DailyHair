<?php

require_once __DIR__ . '/auth.php';

require_login();

$page_title = 'Produtos';

require_once __DIR__ . '/header.php';

$conn = db();

$stmt = $conn->prepare('SELECT * FROM products ORDER BY name');
$stmt->execute();

$products = $stmt->fetchAll();

?>

<div class="section-head">
    <div>
        <span class="eyebrow">DAILY HAIR</span>
        <h1>Produtos</h1>
        <p>Encontre produtos para o seu cabelo.</p>
    </div>
</div>


<!-- Barra de pesquisa e visualização -->

<div class="products-toolbar">

    <div class="product-search">

        <span class="search-icon">⌕</span>

        <input
            type="text"
            id="product-search"
            placeholder="Pesquisar produto..."
            autocomplete="off"
        >

        <button
            type="button"
            class="clear-search"
            id="clear-search"
            aria-label="Limpar pesquisa"
        >
            ×
        </button>

    </div>


    <div class="view-options">

        <button
            type="button"
            class="view-btn active"
            id="grid-view"
            title="Visualização em grade"
            aria-label="Visualização em grade"
        >
            ▦
        </button>

        <button
            type="button"
            class="view-btn"
            id="list-view"
            title="Visualização em lista"
            aria-label="Visualização em lista"
        >
            ☰
        </button>

    </div>

</div>


<!-- Lista de produtos -->

<div class="products-grid" id="products-grid">

    <?php foreach ($products as $p): ?>

        <article
            class="product-card panel"
            data-product="<?= e(
                strtolower(
                    ($p['name'] ?? '') . ' ' .
                    ($p['brand'] ?? '') . ' ' .
                    ($p['description'] ?? '')
                )
            ) ?>"
        >

            <div class="product-image">

                <?php $product_image_url = product_image_url($p['image'] ?? null); ?>

                <?php if ($product_image_url): ?>

                    <img
                        src="<?= e($product_image_url) ?>"
                        alt="<?= e($p['name']) ?>"
                    >

                <?php else: ?>

                    <span class="no-image">Imagem indisponível</span>

                <?php endif; ?>

            </div>


            <div class="product-body">

                <span class="product-brand">
                    <?= e($p['brand'] ?? '') ?>
                </span>

                <h2>
                    <?= e($p['name'] ?? '') ?>
                </h2>

                <p>
                    <?= e($p['description'] ?? '') ?>
                </p>

                <a
                    class="btn primary"
                    href="product.php?id=<?= (int)$p['id'] ?>"
                >
                    Ver produto
                </a>

            </div>

        </article>

    <?php endforeach; ?>

</div>


<script>

// ========================================
// ELEMENTOS
// ========================================

const searchInput = document.getElementById('product-search');
const clearButton = document.getElementById('clear-search');

const gridButton = document.getElementById('grid-view');
const listButton = document.getElementById('list-view');

const productsGrid = document.getElementById('products-grid');

const productCards = document.querySelectorAll('.product-card');


// ========================================
// PESQUISA
// ========================================

searchInput.addEventListener('input', function () {

    const search = searchInput.value
        .toLowerCase()
        .trim();

    productCards.forEach(function (card) {

        const productText = card.dataset.product;

        if (productText.includes(search)) {
            card.style.display = '';
        } else {
            card.style.display = 'none';
        }

    });


    // Mostra ou esconde o X

    if (search.length > 0) {
        clearButton.classList.add('show');
    } else {
        clearButton.classList.remove('show');
    }

});


// ========================================
// LIMPAR PESQUISA
// ========================================

clearButton.addEventListener('click', function () {

    searchInput.value = '';

    searchInput.dispatchEvent(new Event('input'));

    searchInput.focus();

});


// ========================================
// VISUALIZAÇÃO EM GRADE
// ========================================

gridButton.addEventListener('click', function () {

    productsGrid.classList.remove('list-view');

    gridButton.classList.add('active');

    listButton.classList.remove('active');

});


// ========================================
// VISUALIZAÇÃO EM LISTA
// ========================================

listButton.addEventListener('click', function () {

    productsGrid.classList.add('list-view');

    listButton.classList.add('active');

    gridButton.classList.remove('active');

});

</script>


<?php require_once __DIR__ . '/footer.php'; ?>
