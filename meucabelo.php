<?php

require_once __DIR__ . '/auth.php';

require_login();

$page_title = 'Meu cabelo';

require_once __DIR__ . '/header.php';

$user = current_user();

$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $hair_type = $_POST['hair_type'] ?? '';
    $curl = trim($_POST['curl_pattern'] ?? '');
    $por = $_POST['porosity'] ?? '';
    $thick = $_POST['thickness'] ?? '';

    $needs_array = $_POST['needs'] ?? [];

    if (is_array($needs_array)) {
        $needs = implode(', ', $needs_array);
    } else {
        $needs = trim($needs_array);
    }

    // Dias em que a pessoa lava o cabelo
    $wash_days_array = $_POST['wash_days'] ?? [];

    if (is_array($wash_days_array)) {
        $wash_days_array = array_map('intval', $wash_days_array);

        // Garante que só existam dias válidos de 1 a 7
        $wash_days_array = array_values(
            array_unique(
                array_filter(
                    $wash_days_array,
                    fn($day) => $day >= 1 && $day <= 7
                )
            )
        );

        sort($wash_days_array);

        $wash_days = implode(',', $wash_days_array);
    } else {
        $wash_days = '';
    }

    $stmt = db()->prepare(
        'UPDATE users
         SET hair_type=?,
             curl_pattern=?,
             porosity=?,
             thickness=?,
             needs=?,
             wash_days=?
         WHERE id=?'
    );

    $stmt->bindValue(1, $hair_type, PDO::PARAM_STR);
    $stmt->bindValue(2, $curl, PDO::PARAM_STR);
    $stmt->bindValue(3, $por, PDO::PARAM_STR);
    $stmt->bindValue(4, $thick, PDO::PARAM_STR);
    $stmt->bindValue(5, $needs, PDO::PARAM_STR);
    $stmt->bindValue(6, $wash_days, PDO::PARAM_STR);
    $stmt->bindValue(7, $_SESSION['user_id'], PDO::PARAM_INT);

    $stmt->execute();

    $user = current_user();

    $msg = 'Perfil atualizado com sucesso!';
}

$selectedNeeds = array_filter(
    array_map(
        'trim',
        explode(',', $user['needs'] ?? '')
    )
);

// Recupera os dias já salvos
$selectedWashDays = [];

if (!empty($user['wash_days'])) {
    $selectedWashDays = array_map(
        'intval',
        explode(',', $user['wash_days'])
    );
}

?>

<div class="page-title">

    <span class="eyebrow">
        PERSONALIZE SUA EXPERIÊNCIA
    </span>

    <h1>
        Meu cabelo
    </h1>

    <p>
        Quanto mais completo seu perfil, melhores ficam as comparações de produtos.
    </p>

</div>


<?php if ($msg): ?>

    <div class="alert success">
        <?= e($msg) ?>
    </div>

<?php endif; ?>


<form class="panel profile-form" method="post">

    <div class="form-grid">

        <label>
            Tipo de cabelo

            <select name="hair_type">

                <?php foreach (['Liso', 'Ondulado', 'Cacheado', 'Crespo'] as $v): ?>

                    <option
                        value="<?= e($v) ?>"
                        <?= $user['hair_type'] === $v ? 'selected' : '' ?>
                    >
                        <?= e($v) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </label>


        <label>
            Curvatura

            <input
                name="curl_pattern"
                value="<?= e($user['curl_pattern'] ?? '') ?>" placeholder="Informe a curvatura do cabelo (ex.: 2C)"
                placeholder="Ex.: 3A"
            >

        </label>


        <label>
            Porosidade

            <select name="porosity">

                <?php foreach (['Baixa', 'Média', 'Alta'] as $v): ?>

                    <option
                        value="<?= e($v) ?>"
                        <?= ($user['porosity'] ?? '') === $v ? 'selected' : '' ?>
                    >
                        <?= e($v) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </label>


        <label>
            Espessura

            <select name="thickness">

                <?php foreach (['Fina', 'Média', 'Grossa'] as $v): ?>

                    <option
                        value="<?= e($v) ?>"
                        <?= ($user['thickness'] ?? '') === $v ? 'selected' : '' ?>
                    >
                        <?= e($v) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </label>


        <label class="full">
            Principais necessidades:

            <select
                name="needs[]"
                multiple
                size="1"
            >

                <?php

                $needsOptions = [
                    'Frizz',
                    'Ressecamento',
                    'Falta de definição',
                    'Falta de brilho',
                    'Queda',
                    'Quebra',
                    'Pontas duplas',
                    'Oleosidade',
                    'Caspa',
                    'Danos por química',
                    'Danos por calor',
                    'Falta de volume',
                    'Excesso de volume',
                    'Cabelo poroso',
                    'Enfraquecimento',
                    'Descoloração',
                    'Manter a hidratação',
                    'Crescimento saudável'
                ];

                ?>

                <?php foreach ($needsOptions as $need): ?>

                    <option
                        value="<?= e($need) ?>"
                        <?= in_array($need, $selectedNeeds, true) ? 'selected' : '' ?>
                    >
                        <?= e($need) ?>
                    </option>

                <?php endforeach; ?>

            </select>

        </label>


        <!-- DIAS DE LAVAGEM -->
<div class="full wash-days-section">

    <label>
        Dias de lavagem
    </label>

    <p class="wash-days-description">
        Escolha os dias da semana em que você costuma lavar o cabelo.
        Você poderá personalizar a rotina de cada dia no calendário.
    </p>

    <div class="wash-days">
        <?php
        $days = [
            1 => 'Seg',
            2 => 'Ter',
            3 => 'Qua',
            4 => 'Qui',
            5 => 'Sex',
            6 => 'Sáb',
            7 => 'Dom'
        ];
        ?>

        <?php foreach ($days as $dayNumber => $dayName): ?>

            <label class="wash-day">

                <input
                    type="checkbox"
                    name="wash_days[]"
                    value="<?= $dayNumber ?>"
                    <?= in_array($dayNumber, $selectedWashDays, true) ? 'checked' : '' ?>
                >

                <span>
                    <?= $dayName ?>
                </span>

            </label>

        <?php endforeach; ?>

    </div>

</div>

        <button
            class="btn primary wide full"
            type="submit"
        >
            Salvar alterações
        </button>

    </div>

</form>


<style>

.wash-days-section {
    margin-top: 10px;
}

.wash-days-description {
    margin: 6px 0 14px;
    font-size: 14px;
    opacity: 0.7;
}

.wash-days {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 10px;
}

.wash-day {
    cursor: pointer;
    margin: 0;
}

.wash-day input {
    position: absolute;
    opacity: 0;
    pointer-events: none;
}

.wash-day span {
    display: flex;
    align-items: center;
    justify-content: center;

    height: 46px;

    border: 1px solid #d8c5bd;
    border-radius: 12px;

    background: #fffaf7;
    color: #403331;

    font-size: 14px;

    transition: 0.2s ease;
}

.wash-day span:hover {
    border-color: #c6532f;
}

.wash-day input:checked + span {
    background: #c6532f;
    border-color: #c6532f;
    color: white;
}

@media (max-width: 600px) {

    .wash-days {
        grid-template-columns: repeat(4, 1fr);
    }

}

</style>


<?php require_once __DIR__ . '/footer.php'; ?>
