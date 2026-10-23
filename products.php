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
        <p>Encontre produtos para o seu cabelo. Pesquise por nome, marca ou palavras-chave; combine a busca com os filtros de tipo e benefício. Abra um produto para consultar sua descrição e as experiências compartilhadas pela comunidade.</p>
    </div>
</div>


<!-- Barra de pesquisa e visualização -->

<div class="products-toolbar">

    <div class="product-search">

        <span class="search-icon">⌕</span>

        <input
            type="text"
            id="product-search"
            placeholder="Pesquise pelo nome, marca ou característica do produto"
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


    <div class="dh-filter-control">
        <label class="dh-filter-label" for="product-filter">Filtrar por</label>
        <select id="product-filter" aria-label="Filtrar produtos por característica">
            <option value="">Todos os produtos</option>
            <optgroup label="Tipo de produto">
                <option value="tipo:kit">Kit capilar</option>
                <option value="tipo:shampoo">Shampoo</option>
                <option value="tipo:condicionador">Condicionador</option>
                <option value="tipo:mascara">Máscara capilar</option>
                <option value="tipo:creme">Creme para pentear</option>
                <option value="tipo:oleo">Óleo capilar</option>
                <option value="tipo:finalizador">Finalizador</option>
            </optgroup>
            <optgroup label="Benefícios">
                <option value="beneficio:hidratacao">Hidratação</option>
                <option value="beneficio:nutricao">Nutrição</option>
                <option value="beneficio:reconstrucao">Reconstrução</option>
                <option value="beneficio:cachos">Definição de cachos</option>
                <option value="beneficio:reparacao">Reparação e fortalecimento</option>
            </optgroup>
        </select>
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
                    ($p['description'] ?? '') . ' ' .
                    ($p['category'] ?? '') . ' ' .
                    ($p['type'] ?? '')
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
<p id="products-result-count" class="dh-result-count" role="status" aria-live="polite"></p>
<div id="products-empty" class="dh-products-empty" hidden>Nenhum produto encontrado. Experimente outra palavra-chave ou remova o filtro.</div>

<style>
/* Busca e filtros: mantém a paleta e não interfere nos outros formulários. */
.dh-filter-control{display:flex;align-items:center;gap:9px;flex-shrink:0}
.dh-filter-label{font-size:12px;font-weight:700;color:var(--muted);white-space:nowrap}
.dh-filter-control select{max-width:205px;min-width:165px;height:42px;padding:0 32px 0 14px;border-radius:24px;border:1px solid var(--line);background-color:var(--card);color:var(--text);font-size:13px;cursor:pointer;outline:none}
.dh-filter-control select:focus-visible{border-color:var(--terracotta);box-shadow:0 0 0 3px rgba(198,83,47,.12)}
.dh-result-count{font-size:12px;color:var(--muted);margin:22px 0 17px}
.dh-products-empty{background:var(--card);border:1px dashed var(--line);border-radius:16px;padding:26px;text-align:center;color:var(--muted)}
.dh-products-empty[hidden]{display:none}
@media(max-width:900px){.products-toolbar{flex-wrap:wrap}.product-search{flex:1 1 100%}.dh-filter-control{flex:1 1 auto}.dh-filter-control select{max-width:100%;width:100%}}
@media(max-width:480px){.dh-filter-label{display:none}.dh-filter-control{min-width:0}.dh-filter-control select{min-width:0}.dh-result-count{margin-top:18px}}
</style>

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


// Busca com palavras separadas, acentos, variações comuns e sinônimos.
// A classificação usa apenas os campos cadastrados no produto.
const filterSelect = document.getElementById('product-filter');
const resultCount = document.getElementById('products-result-count');
const emptyMessage = document.getElementById('products-empty');
const normalize = value => String(value || '').toLocaleLowerCase('pt-BR')
    .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-z0-9]+/g, ' ').trim();
const keywordGroups = [
    ['hidratacao','hidratar','hidratante','hidratacao','umectacao','umectante'],
    ['nutricao','nutritivo','nutritiva','nutrir','nutriente'],
    ['reconstrucao','reconstrutor','reconstrutora','reconstruir','reparacao','reparador','reparadora','fortalecimento','fortalecedor'],
    ['antifrizz','frizz','anti frizz','controle de frizz'],
    ['cachos','cacheado','cacheada','ondulado','ondulada','definicao','definidor','definidora'],
    ['mascara','mascara capilar','mascara de tratamento'],
    ['oleo','oleo capilar','oleos'],
    ['creme','creme para pentear','leave in','leavein'],
    ['finalizador','finalizacao','finalizante'],
    ['shampoo','xampu'],
    ['condicionador','condicionamento']
].map(group => group.map(normalize));
const productIndex = Array.from(productCards, card => ({
    card, text: normalize(card.dataset.product),
    words: normalize(card.dataset.product).split(' ').filter(Boolean)
}));
function matchesWord(product, word) {
    if (!word) return true;
    const alternatives = keywordGroups.find(group => group.some(term => term === word));
    const queries = alternatives || [word];
    return queries.some(term => product.text.includes(term) ||
        (word.length >= 3 && product.words.some(w => w.startsWith(term))));
}
function matchesQuery(product, query) {
    const words = normalize(query).split(' ').filter(w => w.length > 1 &&
        !['de','da','do','para','com','em','e','a','o','um','uma'].includes(w));
    return words.every(word => matchesWord(product, word));
}
function applyProductSearch() {
    const query = searchInput.value;
    const selected = filterSelect.value;
    const keyword = selected ? selected.split(':')[1] : '';
    let visible = 0;
    productIndex.forEach(product => {
        const match = matchesQuery(product, query) && (!keyword || matchesWord(product, keyword));
        product.card.hidden = !match;
        product.card.style.display = match ? '' : 'none';
        if (match) visible++;
    });
    clearButton.classList.toggle('show', query.trim().length > 0);
    resultCount.textContent = visible + (visible === 1 ? ' produto encontrado' : ' produtos encontrados');
    emptyMessage.hidden = visible !== 0;
}
searchInput.addEventListener('input', applyProductSearch);
filterSelect.addEventListener('change', applyProductSearch);
clearButton.addEventListener('click', function () {
    searchInput.value = '';
    applyProductSearch();
    searchInput.focus();
});
applyProductSearch();


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
