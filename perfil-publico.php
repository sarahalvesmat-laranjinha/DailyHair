<?php

require_once __DIR__ . '/auth.php';
require_login();

$page_title = 'Perfil';
require_once __DIR__ . '/header.php';

$conn = db();

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    echo '
        <div class="empty">
            <h2>Perfil não encontrado.</h2>
            <p>Esse perfil não existe ou o link está incorreto.</p>
            <a class="btn primary" href="dashboard.php">Voltar para o início</a>
        </div>
    ';
    require_once __DIR__ . '/footer.php';
    exit;
}


/* =====================================================
   BUSCAR USUÁRIO
===================================================== */

$stmt = $conn->prepare(
    'SELECT
        id,
        name,
        bio,
        profile_image,
        hair_type,
        curl_pattern,
        porosity,
        thickness,
        needs
     FROM users
     WHERE id = ?
     LIMIT 1'
);

if (!$stmt) {
    die('Erro ao buscar perfil: ' . 'Consulte o log do servidor.');
}

$stmt->bindValue(1, $id, PDO::PARAM_INT);
$stmt->execute();

$profile = $stmt->fetch();


if (!$profile) {
    echo '
        <div class="empty">
            <h2>Perfil não encontrado.</h2>
            <p>Esse usuário não existe.</p>
            <a class="btn primary" href="dashboard.php">Voltar para o início</a>
        </div>
    ';

    require_once __DIR__ . '/footer.php';
    exit;
}


/* =====================================================
   NECESSIDADES DO CABELO
===================================================== */

$needs = [];

if (!empty($profile['needs'])) {

    $decoded = json_decode($profile['needs'], true);

    if (is_array($decoded)) {
        $needs = $decoded;
    } else {
        $needs = array_filter(
            array_map(
                'trim',
                explode(',', $profile['needs'])
            )
        );
    }
}


/* =====================================================
   AVALIAÇÕES FEITAS PELA PESSOA
===================================================== */

$stmt = $conn->prepare(
    'SELECT
        r.rating,
        r.comment,
        r.created_at,
        p.id AS product_id,
        p.name AS product_name,
        p.brand AS product_brand,
        p.image AS product_image
     FROM reviews r
     JOIN products p ON p.id = r.product_id
     WHERE r.user_id = ?
     ORDER BY r.created_at DESC'
);

$stmt->bindValue(1, $id, PDO::PARAM_INT);
$stmt->execute();

$reviews = $stmt->fetchAll();

?>

<div class="public-profile">

    <a class="back-link" href="javascript:history.back()">← Voltar</a>


    <!-- =====================================================
         CABEÇALHO DO PERFIL
    ====================================================== -->

    <section class="public-profile-header panel">

        <div class="public-profile-photo">

            <?php
                $profile_photo_url = profile_image_url($profile);
                $profile_initial = mb_strtoupper(mb_substr($profile['name'], 0, 1));
            ?>

            <?php if ($profile_photo_url): ?>

                <img
                    src="<?= e($profile_photo_url) ?>"
                    alt="<?= e($profile['name']) ?>"
                    onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
                >
                <span hidden><?= e($profile_initial) ?></span>

            <?php else: ?>

                <span><?= e($profile_initial) ?></span>

            <?php endif; ?>

        </div>


        <div class="public-profile-info">

            <span class="eyebrow">PERFIL DAILY HAIR</span>

            <h1><?= e($profile['name']) ?></h1>


            <?php if (!empty($profile['bio'])): ?>

                <p class="public-profile-bio">
                    <?= e($profile['bio']) ?>
                </p>

            <?php else: ?>

                <p class="public-profile-bio muted">
                    Ainda não adicionou uma bio.
                </p>

            <?php endif; ?>

            <br>
            <p class="create-account"><a href="perfil.php">Editar perfil</a></p>

        </div>

    </section>


    <!-- =====================================================
         INFORMAÇÕES DO CABELO
    ====================================================== -->

    <section class="public-profile-section">

        <div class="section-head">

            <div>
                <span class="eyebrow">MEU CABELO</span>
                <h2>Características do cabelo</h2>
            </div>

        </div>


        <div class="hair-profile-grid">


            <div class="hair-profile-card panel">

                <span>Tipo de cabelo</span>

                <strong>
                    <?= e($profile['hair_type'] ?: 'Não informado') ?>
                </strong>

            </div>


            <div class="hair-profile-card panel">

                <span>Curvatura</span>

                <strong>
                    <?= e($profile['curl_pattern'] ?: 'Não informado') ?>
                </strong>

            </div>


            <div class="hair-profile-card panel">

                <span>Porosidade</span>

                <strong>
                    <?= e($profile['porosity'] ?: 'Não informado') ?>
                </strong>

            </div>


            <div class="hair-profile-card panel">

                <span>Espessura</span>

                <strong>
                    <?= e($profile['thickness'] ?: 'Não informado') ?>
                </strong>

            </div>

        </div>

    </section>


    <!-- =====================================================
         NECESSIDADES
    ====================================================== -->

    <?php if (!empty($needs)): ?>

        <section class="public-profile-section">

            <div class="section-head">

                <div>
                    <span class="eyebrow">CUIDADOS</span>
                    <h2>Necessidades do cabelo</h2>
                </div>

            </div>


            <div class="profile-tags">

                <?php foreach ($needs as $need): ?>

                    <span>
                        <?= e($need) ?>
                    </span>

                <?php endforeach; ?>

            </div>

        </section>

    <?php endif; ?>


    <!-- =====================================================
         AVALIAÇÕES
    ====================================================== -->

    <section class="public-profile-section">

        <div class="section-head">

            <div>
                <span class="eyebrow">COMUNIDADE DAILY HAIR</span>
                <h2>Avaliações feitas por <?= e($profile['name']) ?></h2>
            </div>

        </div>


        <?php if (empty($reviews)): ?>

            <div class="empty panel">

                <h3>Ainda não há avaliações</h3>

                <p>
                    <?= e($profile['name']) ?> ainda não avaliou nenhum produto.
                </p>

            </div>

        <?php else: ?>


            <div class="public-reviews">

                <?php foreach ($reviews as $review): ?>

                    <article class="public-review panel">


                        <div class="public-review-product">

                            <?php if (!empty($review['product_image'])): ?>

                                <img
                                    src="images/<?= e($review['product_image']) ?>"
                                    alt="<?= e($review['product_name']) ?>"
                                >

                            <?php endif; ?>


                            <div>

                                <span class="product-brand">
                                    <?= e($review['product_brand']) ?>
                                </span>

                                <a
                                    href="product.php?id=<?= (int) $review['product_id'] ?>"
                                    class="public-review-product-name"
                                >
                                    <?= e($review['product_name']) ?>
                                </a>

                            </div>

                        </div>


                        <div class="public-review-rating">

                            <span class="stars">
                                <?= str_repeat('★', (int) $review['rating']) ?><?= str_repeat('☆', 5 - (int) $review['rating']) ?>
                            </span>

                        </div>


                        <p>
                            <?= e($review['comment']) ?>
                        </p>


                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</div>


<?php require_once __DIR__ . '/footer.php'; ?>
