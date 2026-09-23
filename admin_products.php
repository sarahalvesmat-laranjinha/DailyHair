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
                SELECT name, brand, category, description, image
                FROM aprove_products
                WHERE id = ?
                AND status = 'Em análise'
            ");

            $stmt->bind_param('i', $request_id);
            $stmt->execute();

            $produto = $stmt->get_result()->fetch_assoc();

            if ($produto) {

                // Coloca o produto na tabela oficial
                $stmtInsert = $conn->prepare("
                    INSERT INTO products
                    (name, brand, category, description, image)
                    VALUES (?, ?, ?, ?, ?)
                ");

                $stmtInsert->bind_param(
                    'sssss',
                    $produto['name'],
                    $produto['brand'],
                    $produto['category'],
                    $produto['description'],
                    $produto['images']
                );

                $stmtInsert->execute();

                // Marca a solicitação como aprovada
                $stmtUpdate = $conn->prepare("
                    UPDATE aprove_products
                    SET status = 'Aprovado'
                    WHERE id = ?
                ");

                $stmtUpdate->bind_param('i', $request_id);
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

            $stmt->bind_param('i', $request_id);
            $stmt->execute();
        }
    }

    /*
    Evita reenviar o formulário ao atualizar a página
    */

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

$produtos = $stmt->get_result();


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


    <?php if ($produtos->num_rows === 0): ?>

        <section class="admin-empty panel">

            <h2>Nenhum produto aguardando aprovação</h2>

            <p>
                Todos os produtos cadastrados já foram analisados.
            </p>

        </section>

    <?php else: ?>

        <section class="admin-products-grid">

            <?php while ($produto = $produtos->fetch_assoc()): ?>

                <article class="admin-product-card panel">

                    <div class="admin-product-image">

                        <?php if (!empty($produto['image'])): ?>

                            <img src="images/<?= e($produto['image']) ?>" alt="<?= e($produto['name']) ?>">

                        <?php else: ?>

                            <div class="no-image">
                                Sem imagem
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
                                <?= date(
                                    'd/m/Y H:i',
                                    strtotime($produto['created_at'])
                                ) ?>
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

                                <input type="hidden" name="request_id" value="<?= (int) $produto['id'] ?>">

                                <input type="hidden" name="acao" value="aprovar">

                                <button type="submit" class="btn-approve">
                                    Aprovar
                                </button>

                            </form>


                            <form method="POST">

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

            <?php endwhile; ?>

        </section>

    <?php endif; ?>

</main>