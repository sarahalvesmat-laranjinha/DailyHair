<?php

require_once __DIR__ . '/auth.php';

require_login();

$conn = db();

/*
=====================================================
VERIFICAR SE O USUÁRIO É ADMIN
=====================================================
*/

$user = current_user();

if (!$user || empty($user['is_admin'])) {
    http_response_code(403);
    exit('Acesso negado.');
}


/*
=====================================================
APROVAR OU REPROVAR PRODUTO
=====================================================
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $conn->beginTransaction();
    try {
    $request_id = (int) ($_POST['request_id'] ?? 0);
    $acao = $_POST['acao'] ?? '';

    if ($request_id > 0) {

        /*
        =============================================
        APROVAR
        =============================================
        */

        if ($acao === 'aprovar') {

            // Busca o produto que está em análise
            $stmt = $conn->prepare("
                SELECT name, brand, category, type, description, image, created_by
                FROM aprove_products
                WHERE id = ?
                AND status = 'Em análise'
                FOR UPDATE
            ");

            $stmt->bindValue(1, $request_id, PDO::PARAM_INT);
            $stmt->execute();

            $produto = $stmt->fetch();

            if ($produto) {

                // Coloca o produto na tabela oficial
                $stmtInsert = $conn->prepare("
                    INSERT INTO products
                    (name, brand, category, description, image, type, created_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");

                $stmtInsert->bindValue(1, $produto['name'], PDO::PARAM_STR);
                $stmtInsert->bindValue(2, $produto['brand'], PDO::PARAM_STR);
                $stmtInsert->bindValue(3, $produto['category'], PDO::PARAM_STR);
                $stmtInsert->bindValue(4, $produto['description'], PDO::PARAM_STR);
                $stmtInsert->bindValue(5, $produto['image'], PDO::PARAM_STR);

                $stmtInsert->bindValue(6, $produto['type'], PDO::PARAM_STR);
                $stmtInsert->bindValue(7, $produto['created_by'], PDO::PARAM_INT);
                $stmtInsert->execute();

                // Marca a solicitação como aprovada
                $stmtUpdate = $conn->prepare("
                    UPDATE aprove_products
                    SET status = 'Aprovado'
                    WHERE id = ?
                ");

                $stmtUpdate->bindValue(1, $request_id, PDO::PARAM_INT);
                $stmtUpdate->execute();
            }
        }


        /*
        =============================================
        REPROVAR
        =============================================
        */ elseif ($acao === 'reprovar') {

            $stmt = $conn->prepare("
                UPDATE aprove_products
                SET status = 'Recusado'
                WHERE id = ?
                AND status = 'Em análise'
            ");

            $stmt->bindValue(1, $request_id, PDO::PARAM_INT);
            $stmt->execute();
        }
    }

    /*
    Evita reenviar o formulário ao atualizar a página
    */

    $conn->commit();
    } catch (Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();
        http_response_code(500);
        exit('Não foi possível salvar a aprovação. Nenhuma alteração foi aplicada.');
    }
    header('Location: admin_products.php');
    exit;
}


/*
=====================================================
BUSCAR PRODUTOS EM ANÁLISE
=====================================================
*/

$stmt = $conn->prepare("
    SELECT 
        pr.id,
        pr.name,
        pr.brand,
        pr.category,
        pr.description,
        pr.image,
        pr.created_at,
        u.name AS creator_name
    FROM aprove_products pr
    INNER JOIN users u ON u.id = pr.created_by
    WHERE pr.status = 'Em análise'
    ORDER BY pr.created_at DESC
");

$stmt->execute();

$produtos = $stmt->fetchAll();


/*
=====================================================
HTML
=====================================================
*/

$page_title = 'Aprovação de produtos';

require_once __DIR__ . '/header.php';

?>

<main class="admin-products">


    <div class="admin-products-header">

        <span class="eyebrow">DAILY HAIR</span>

        <h1>Produtos para aprovação</h1>

        <p>
            Confira os produtos cadastrados pelos usuários
            antes de disponibilizá-los no site.
        </p>

    </div>


    <?php if (count($produtos) === 0): ?>

        <section class="admin-empty panel">

            <h2>Nenhum produto aguardando aprovação</h2>

            <p>
                Todos os produtos cadastrados já foram analisados.
            </p>

        </section>

    <?php else: ?>

        <section class="admin-products-grid">

            <?php foreach ($produtos as $produto): ?>

                <article class="admin-product-card panel">

                    <div class="admin-product-image">

                        <?php $product_image_url = product_image_url($produto['image'] ?? null); ?>

                        <?php if ($product_image_url): ?>

                            <img src="<?= e($product_image_url) ?>" alt="<?= e($produto['name']) ?>">

                        <?php else: ?>

                            <div class="no-image">
                                Imagem indisponível
                            </div>

                        <?php endif; ?>

                    </div>


                    <div class="admin-product-content">

                        <span class="product-category">
                            <?= e($produto['category']) ?>
                        </span>

                        <h2>
                            <?= e($produto['name']) ?>
                        </h2>

                        <p class="product-brand">
                            <?= e($produto['brand']) ?>
                        </p>

                        <p class="product-description">
                            <?= e($produto['description']) ?>
                        </p>


                        <div class="product-info">

                            <p>
                                <strong>Cadastrado por:</strong>
                                <?= e($produto['creator_name']) ?>
                            </p>

                            <p>
                                <strong>Data:</strong>
                                <?php
                                $created_at_utc = new DateTimeImmutable(
                                    $produto['created_at'],
                                    new DateTimeZone('UTC')
                                );

                                echo $created_at_utc
                                    ->setTimezone(new DateTimeZone('America/Sao_Paulo'))
                                    ->format('d/m/Y H:i');
                                ?>
                            </p>

                            <p>
                                <strong>Status:</strong>
                                <span class="status-analysis">
                                    Em análise
                                </span>
                            </p>

                        </div>


                        <div class="admin-product-actions">

                            <form method="POST">
                                <?= csrf_field() ?>

                                <input type="hidden" name="request_id" value="<?= (int) $produto['id'] ?>">

                                <input type="hidden" name="acao" value="aprovar">

                                <button type="submit" class="btn-approve">
                                    Aprovar
                                </button>

                            </form>


                            <form method="POST">
                                <?= csrf_field() ?>

                                <input type="hidden" name="request_id" value="<?= (int) $produto['id'] ?>">

                                <input type="hidden" name="acao" value="reprovar">

                                <button type="submit" class="btn-reject"
                                    onclick="return confirm('Tem certeza que deseja reprovar este produto?')">
                                    Reprovar
                                </button>

                            </form>

                        </div>

                    </div>

                </article>

            <?php endforeach; ?>

        </section>

    <?php endif; ?>

</main>
