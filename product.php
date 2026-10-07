<?php

require_once __DIR__ . '/auth.php';

require_login();

$page_title = 'Produto';

require_once __DIR__ . '/header.php';


/* =====================================================
   ID DO PRODUTO
===================================================== */

$id = (int) ($_GET['id'] ?? 0);

$conn = db();


/* =====================================================
   BUSCAR PRODUTO
===================================================== */

$stmt = $conn->prepare('
    SELECT
        p.*,
        COALESCE(AVG(r.rating), 0) AS avg_rating,
        COUNT(r.id) AS review_count
    FROM products p
    LEFT JOIN reviews r
        ON r.product_id = p.id
    WHERE p.id = ?
    GROUP BY p.id
');

if (!$stmt) {
    die('Erro na consulta do produto: ' . 'Consulte o log do servidor.');
}

$stmt->bindValue(1, $id, PDO::PARAM_INT);

$stmt->execute();

$p = $stmt->fetch();


/* =====================================================
   PRODUTO NÃO ENCONTRADO
===================================================== */

if (!$p) {

    echo '
        <div class="empty">
            <h2>Produto não encontrado.</h2>
        </div>
    ';

    require_once __DIR__ . '/footer.php';

    exit;
}


/* =====================================================
   USUÁRIO LOGADO
===================================================== */

$user = current_user();


/* =====================================================
   BUSCAR AVALIAÇÕES
===================================================== */

$stmt = $conn->prepare('
    SELECT
        r.*,
        u.id AS user_id,
        u.name,
        u.hair_type,
        u.curl_pattern,
        u.porosity,
        u.thickness,
        u.profile_image
    FROM reviews r
    JOIN users u
        ON u.id = r.user_id
    WHERE r.product_id = ?
    ORDER BY r.created_at DESC
');

if (!$stmt) {
    die('Erro na consulta de avaliações: ' . 'Consulte o log do servidor.');
}

$stmt->bindValue(1, $id, PDO::PARAM_INT);

$stmt->execute();

$reviews = $stmt->fetchAll();


/* =====================================================
   CONTAR PERFIS SEMELHANTES
===================================================== */

$profileMatch = 0;

foreach ($reviews as $r) {

    if (
        !empty($user['hair_type']) &&
        $r['hair_type'] === $user['hair_type']
    ) {
        $profileMatch++;
    }
}

?>


<!-- =====================================================
     VOLTAR
===================================================== -->

<a class="back-link" href="products.php">
    ← Voltar para produtos
</a>


<!-- =====================================================
     PRODUTO
===================================================== -->

<div class="product-detail panel">


    <!-- IMAGEM DO PRODUTO -->

    <div class="detail-image">

        <?php if (!empty($p['image'])): ?>

            <?php
            $product_image = basename($p['image']);
            ?>

            <img
                src="images/<?= e($product_image) ?>"
                alt="<?= e($p['name']) ?>"
            >

        <?php endif; ?>

    </div>


    <!-- INFORMAÇÕES -->

    <div class="detail-info">

        <span class="product-brand">
            <?= e($p['brand']) ?>
        </span>


        <h1>
            <?= e($p['name']) ?>
        </h1>


        <!-- AVALIAÇÃO GERAL -->

        <div class="big-rating">

            ★

            <?= number_format(
                (float) $p['avg_rating'],
                1,
                ',',
                '.'
            ) ?>

            <span>

                <?= (int) $p['review_count'] ?>

                avaliações

            </span>

        </div>


        <!-- DESCRIÇÃO -->

        <p>
            <?= e($p['description']) ?>
        </p>


        <!-- PERFIS SEMELHANTES -->

        <div class="match-box">

            <strong>
                ♡ Avaliações de perfis semelhantes
            </strong>

            <span>

                <?= $profileMatch ?>

                de

                <?= count($reviews) ?>

                avaliações têm tipo de cabelo igual ao seu.

            </span>

        </div>


        <!-- BOTÃO -->

        <a
            class="btn primary"
            href="#avaliar"
        >
            Avaliar este produto
        </a>

    </div>

</div>


<!-- =====================================================
     AVALIAÇÕES
===================================================== -->

<section
    id="avaliar"
    class="review-section"
>


    <!-- CABEÇALHO -->

    <div class="section-head review-heading">

        <div>

            <span class="eyebrow">
                SUA EXPERIÊNCIA
            </span>

            <h2>
                Avalie este produto
            </h2>

            <p>
                Conte como ele funcionou no seu cabelo
                e ajude outras pessoas.
            </p>

        </div>

    </div>


    <!-- =================================================
         FORMULÁRIO
    ================================================== -->

    <div class="review-form panel">

        <form
            method="post"
            action="review.php"
        >


            <!-- ID DO PRODUTO -->

            <input
                type="hidden"
                name="product_id"
                value="<?= (int) $p['id'] ?>"
            >


            <!-- NOTA -->

            <div class="rating-field">

                <label>
                    Que nota você dá para esse produto?
                </label>


                <div class="star-rating">


                    <!-- 1 ESTRELA -->

                    <label>

                        <input
                            type="radio"
                            name="rating"
                            value="1"
                            required
                        >

                        <span>★</span>

                    </label>


                    <!-- 2 ESTRELAS -->

                    <label>

                        <input
                            type="radio"
                            name="rating"
                            value="2"
                        >

                        <span>★</span>

                    </label>


                    <!-- 3 ESTRELAS -->

                    <label>

                        <input
                            type="radio"
                            name="rating"
                            value="3"
                        >

                        <span>★</span>

                    </label>


                    <!-- 4 ESTRELAS -->

                    <label>

                        <input
                            type="radio"
                            name="rating"
                            value="4"
                        >

                        <span>★</span>

                    </label>


                    <!-- 5 ESTRELAS -->

                    <label>

                        <input
                            type="radio"
                            name="rating"
                            value="5"
                        >

                        <span>★</span>

                    </label>

                </div>

            </div>


            <!-- =================================================
                 COMENTÁRIO
            ================================================== -->

            <div class="comment-field">

                <label for="comment">
                    Conte sua experiência
                </label>

                <textarea
                    id="comment"
                    name="comment"
                    rows="5"
                    required
                    placeholder="O que você achou? Como seu cabelo ficou depois de usar?"
                ></textarea>

            </div>


            <!-- =================================================
                 BOTÃO
            ================================================== -->

            <button
                class="btn primary"
                type="submit"
            >
                Publicar avaliação
            </button>


        </form>

    </div>


    <!-- =================================================
         AVALIAÇÕES DA COMUNIDADE
    ================================================== -->

    <div class="community-reviews">


        <div class="section-head">

            <div>

                <span class="eyebrow">
                    COMUNIDADE DAILY HAIR
                </span>

                <h2>
                    Experiências de outras pessoas
                </h2>

            </div>

        </div>


        <!-- =================================================
             SEM AVALIAÇÕES
        ================================================== -->

        <?php if (empty($reviews)): ?>

            <article class="review panel">

                <p class="no-reviews">

                    Ainda não há avaliações para este produto.
                    Seja a primeira pessoa a avaliar!

                </p>

            </article>


        <?php else: ?>


            <!-- =================================================
                 LISTA DE AVALIAÇÕES
            ================================================== -->

            <?php foreach ($reviews as $r): ?>

                <article class="review panel">


                    <!-- PARTE SUPERIOR -->

                    <div class="review-top">


                        <div>


                            <!-- NOME + FOTO + PERFIL -->

                            <a
                                class="review-user"
                                href="perfil-publico.php?id=<?= (int) $r['user_id'] ?>"
                            >


                                <!-- FOTO -->

                                <span class="review-avatar">

                                    <?php if (!empty($r['profile_image'])): ?>

                                        <img
                                            src="uploads/profiles/<?= e($r['profile_image']) ?>"
                                            alt="<?= e($r['name']) ?>"
                                        >

                                    <?php else: ?>

                                        <?= e(
                                            mb_strtoupper(
                                                mb_substr(
                                                    $r['name'],
                                                    0,
                                                    1
                                                )
                                            )
                                        ) ?>

                                    <?php endif; ?>

                                </span>


                                <!-- INFORMAÇÕES DA PESSOA -->

                                <span class="review-user-info">

                                    <strong>
                                        <?= e($r['name']) ?>
                                    </strong>

                                    <span>

                                        <?= e($r['hair_type']) ?>


                                        <?php if (!empty($r['curl_pattern'])): ?>

                                            · <?= e($r['curl_pattern']) ?>

                                        <?php endif; ?>


                                        <?php if (!empty($r['porosity'])): ?>

                                            · <?= e($r['porosity']) ?> porosidade

                                        <?php endif; ?>

                                    </span>

                                </span>


                            </a>


                        </div>


                        <!-- NOTA -->

                        <b class="stars">

                            <?= str_repeat(
                                '★',
                                max(
                                    0,
                                    min(
                                        5,
                                        (int) $r['rating']
                                    )
                                )
                            ) ?>


                            <?= str_repeat(
                                '☆',
                                5 - max(
                                    0,
                                    min(
                                        5,
                                        (int) $r['rating']
                                    )
                                )
                            ) ?>

                        </b>


                    </div>


                    <!-- COMENTÁRIO -->

                    <p>
                        <?= e($r['comment']) ?>
                    </p>


                    <!-- EXCLUIR A PRÓPRIA AVALIAÇÃO -->

                    <?php if ((int) $r['user_id'] === (int) $user['id']): ?>

                        <form
                            class="review-delete-form"
                            method="post"
                            action="review.php"
                            onsubmit="return confirm('Tem certeza que deseja excluir seu comentário?');"
                        >
                            <input
                                type="hidden"
                                name="action"
                                value="delete"
                            >

                            <input
                                type="hidden"
                                name="review_id"
                                value="<?= (int) $r['id'] ?>"
                            >

                            <input
                                type="hidden"
                                name="product_id"
                                value="<?= (int) $p['id'] ?>"
                            >

                            <button
                                class="review-delete-btn"
                                type="submit"
                            >
                                Excluir comentário
                            </button>
                        </form>

                    <?php endif; ?>


                    <!-- PERFIL SEMELHANTE -->

                    <?php if (
                        !empty($user['hair_type']) &&
                        $r['hair_type'] === $user['hair_type']
                    ): ?>

                        <span class="similar-badge">
                            ✓ Perfil semelhante ao seu
                        </span>

                    <?php endif; ?>


                </article>


            <?php endforeach; ?>


        <?php endif; ?>


    </div>


</section>


<?php

require_once __DIR__ . '/footer.php';

?>
