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
