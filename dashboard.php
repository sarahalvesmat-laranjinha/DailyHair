<?php
require_once __DIR__ . '/auth.php';
require_login();
$page_title = 'Início';
require_once __DIR__ . '/header.php';
$user = current_user();
$conn = db();
$products = $conn->query('SELECT p.*, COALESCE(AVG(r.rating),0) avg_rating, COUNT(r.id) review_count FROM products p LEFT JOIN reviews r ON r.product_id=p.id GROUP BY p.id ORDER BY avg_rating DESC, p.name LIMIT 4')->fetchAll();
$month = date('n');
$year = date('Y');
$days = (int) date('t');
$first = (int) date('N', strtotime("$year-$month-01"));
$types = ['Hidratação', 'Nutrição', 'Umectação', 'Reconstrução'];
$month_names = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];
?>



<!-- Guias fixos: ficam apenas nas faixas laterais, sem alterar os cartões centrais. -->
<aside class="dh-hair-guides" aria-label="Guia sobre os tipos de cabelo">
  <div class="dh-hair-bubble dh-guide-left dh-guide-top" tabindex="0">
    <strong>01 · Liso</strong><span>Fios que caem sem formar ondas definidas. Curvaturas 1A, 1B e 1C; podem variar em volume e espessura.</span>
  </div>
  <div class="dh-hair-bubble dh-guide-left dh-guide-bottom" tabindex="0">
    <strong>02 · Ondulado</strong><span>Ondas em formato de S, das mais suaves às marcadas. Curvaturas 2A, 2B e 2C; a definição varia ao longo dos fios.</span>
  </div>
  <div class="dh-hair-bubble dh-guide-right" tabindex="0">
    <strong>03 · Cacheado e crespo</strong><span>Cacheados (3A–3C) formam espirais. Crespos (4A–4C) apresentam curvas bem fechadas ou em zigue-zague. Cada cabelo é único.</span>
  </div>
</aside>
<style>
/* Áreas laterais fixas do dashboard; não ocupam nem sobrepõem a coluna central. */
@media (min-width: 1560px) {
  .dh-hair-bubble {position:fixed;z-index:3;width:clamp(155px,12vw,210px);aspect-ratio:1;border:1.5px solid #d9a791;border-radius:50%;background:#fffaf6;box-shadow:0 9px 24px #52352b13;display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:20px 19px;color:#65473c;gap:8px;overflow:hidden}
  .dh-hair-bubble strong {font-family:'Playfair Display',serif;color:#a94427;font-size:17px}
  .dh-hair-bubble span {font-size:11px;line-height:1.5}
  .dh-guide-left {left:max(18px,calc((100vw - 1180px)/4 - 110px))}
  .dh-guide-top {top:15vh}
  .dh-guide-bottom {top:60vh}
  .dh-guide-right {right:max(18px,calc((100vw - 1180px)/4 - 110px));top:41vh}
}
@media (max-width:1559px) {
 .dh-hair-guides {display:flex;gap:12px;flex-wrap:wrap;margin:0 0 24px}
 .dh-hair-bubble {flex:1 1 210px;padding:15px 18px;background:#fffaf6;border:1px solid #eaded4;border-radius:18px;display:flex;flex-direction:column;gap:7px;color:#65473c;box-shadow:0 4px 15px #52352b0c}
 .dh-hair-bubble strong {color:#a94427;font-family:'Playfair Display',serif}
 .dh-hair-bubble span {font-size:13px;line-height:1.5}
}
</style>

<div class="hero">
    <div><span class="eyebrow">SEU CUIDADO, DO SEU JEITO</span>
        <h1>Olá, <?= e(explode(' ', $user['name'])[0]) ?>!</h1>
        <p>Seu Daily Hair está pronto para ajudar você a cuidar do seu cabelo.</p>
    </div><a class="btn primary" href="perfil.php">Atualizar meu perfil</a>
</div>

<section class="dashboard-grid">
    <div class="panel calendar-mini">
        <div class="panel-head">
            <div>
                <h2>Seu calendário</h2>
                <p><?= e($month_names[$month]) ?> de <?= $year ?></p>
            </div><a href="calendar.php">Ver tudo →</a>
        </div>
        <div class="weekdays">
            <span>Seg</span><span>Ter</span><span>Qua</span><span>Qui</span><span>Sex</span><span>Sáb</span><span>Dom</span>
        </div>
        <div class="calendar-days">
            <?php for ($i = 1; $i < $first; $i++): ?><span></span><?php endfor; ?>
            <?php for ($d = 1; $d <= $days; $d++):
                $t = $types[($d - 1) % count($types)]; ?><a href="calendar.php?day=<?= $d ?>"
                    class="day <?= $d == (int) date('j') ? 'today' : '' ?>"><b><?= $d ?></b><small><?= $t === 'Hidratação' ? '<img src="outras.png/hidrata.png" class="calendar-icon">' : ($t === 'Nutrição' ? '<img src="outras.png/nutri.png" class="calendar-icon">' : '<img src="outras.png/reconst.png" class="calendar-icon">') ?></small></a><?php endfor; ?>
        </div>
    </div>

    <div class="panel profile-card"><span class="eyebrow">SEU PERFIL CAPILAR</span>
        <h2><?= e($user['hair_type']) ?> <?= $user['curl_pattern'] ? '· ' . e($user['curl_pattern']) : '' ?></h2>
        <div class="profile-tags"><span><?= e($user['porosity']) ?> porosidade</span><span>Espessura
                <?= e($user['thickness']) ?></span></div>
        <p><?= e($user['needs'] ?: 'Você ainda não cadastrou suas principais necessidades.') ?></p><a class="text-link"
            href="meucabelo.php">Editar perfil →</a>
    </div>

</section>
<section class="section-head">
    <div><span class="eyebrow">PARA VOCÊ</span>
        <h2>Produtos bem avaliados</h2>
    </div><a href="products.php">Ver todos →</a>
</section>

<div class="products-grid">
    <?php foreach ($products as $p): ?>

        <article class="product-card">

            <a href="product.php?id=<?= (int)$p['id'] ?>">
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
            </a>

            <div class="product-body">

                <span class="product-brand">
                    <?= e($p['brand']) ?>
                </span>

                <h3>
                    <?= e($p['name']) ?>
                </h3>

                <div class="rating">
                    ★ <?= number_format((float) $p['avg_rating'], 1, ',', '.') ?>
                    <span>(<?= $p['review_count'] ?>)</span>
                </div>

                <span class="compatibility">
                    ♡ Compatível com perfis semelhantes
                </span>

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

<?php require_once __DIR__ . '/footer.php'; ?>
