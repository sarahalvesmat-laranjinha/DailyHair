<?php

require_once __DIR__ . '/auth.php';

$user = current_user();

$page = basename($_SERVER['PHP_SELF']);

?>

<!doctype html>

<html lang="pt-BR">

<head>

    <meta charset="utf-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        <?= isset($page_title) ? e($page_title) . ' — Daily Hair' : 'Daily Hair' ?>
    </title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap"
        rel="stylesheet">

    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="mobile.css">

    <?php if (!empty($extra_css) && $extra_css !== 'style.css'): ?>
        <link rel="stylesheet" href="<?= e($extra_css) ?>">
    <?php endif; ?>

    <script src="mobile-nav.js" defer></script>

</head>

<body>

    <header class="topbar">

        <!-- LOGO -->
        <a class="brand" href="dashboard.php">

            <span class="brand-mark">
                <img src="Logo.png" alt="Daily Hair">
            </span>

            <span>Daily Hair</span>

        </a>


        <!-- MENU -->
        <nav class="nav" id="main-nav">

            <a
                class="<?= $page === 'dashboard.php' ? 'active' : '' ?>"
                href="dashboard.php"
            >
                Início
            </a>

            <a
                class="<?= $page === 'calendar.php' ? 'active' : '' ?>"
                href="calendar.php"
            >
                Meu calendário
            </a>

            <a
                class="<?= $page === 'products.php' || $page === 'product.php' ? 'active' : '' ?>"
                href="products.php"
            >
                Produtos
            </a>

            <a
                class="<?= $page === 'meucabelo.php' ? 'active' : '' ?>"
                href="meucabelo.php"
            >
                Meu cabelo
            </a>

            <a
                class="<?= $page === 'product-form.php' ? 'active' : '' ?>"
                href="product-form.php"
            >
                Cadastro de produtos
            </a>

        </nav>


        <!-- USUÁRIO -->
        <div class="user-menu">

            <a class="avatar" href="perfil-publico.php?id=<?= (int)$user['id'] ?>">
                <?php if ($user): ?>
                    <?php
                        $avatar_url = profile_image_url($user);
                        $avatar_initial = mb_strtoupper(mb_substr($user['name'], 0, 1));
                    ?>

                    <?php if ($avatar_url): ?>
                        <img
                            src="<?= e($avatar_url) ?>"
                            alt="<?= e($user['name']) ?>"
                            onerror="this.hidden=true; this.nextElementSibling.hidden=false;"
                        >
                        <span hidden><?= e($avatar_initial) ?></span>
                    <?php else: ?>
                        <?= e($avatar_initial) ?>
                    <?php endif; ?>
                <?php else: ?>
                    ?
                <?php endif; ?>
            </a>

            <?php if ($user): ?>

                <a class="user-name" href="perfil.php">
                    <?= e($user['name']) ?>
                </a>

            <?php endif; ?>

            <a class="logout" href="logout.php">
                Sair
            </a>

        </div>

        <button
            class="menu-toggle"
            type="button"
            aria-label="Abrir menu"
            aria-controls="main-nav"
            aria-expanded="false"
        >
            <span></span>
            <span></span>
            <span></span>
        </button>

    </header>


    <main class="container">