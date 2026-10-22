<?php

require_once __DIR__ . '/auth.php';

require_login();

$page_title = 'Meu calendário';

require_once __DIR__ . '/header.php';

$user = current_user();

$month = (int) ($_GET['month'] ?? date('n'));
$year = (int) ($_GET['year'] ?? date('Y'));

if ($month < 1 || $month > 12) {
    $month = (int) date('n');
}

if ($year < 2000 || $year > 2100) {
    $year = (int) date('Y');
}

$days = (int) date(
    't',
    strtotime("$year-$month-01")
);

$first = (int) date(
    'N',
    strtotime("$year-$month-01")
);

$msg = '';

/*
|--------------------------------------------------------------------------
| FUNÇÃO PARA DESCOBRIR O TRATAMENTO DA ROTINA
|--------------------------------------------------------------------------
*/

function getTreatmentFromRoutine(array $routine): ?string
{
    foreach ($routine as $step) {

        $name = mb_strtolower(
            trim($step['name'] ?? ''),
            'UTF-8'
        );

        if (str_contains($name, 'hidratação')) {
            return 'Hidratação';
        }

        if (str_contains($name, 'nutrição')) {
            return 'Nutrição';
        }

        if (str_contains($name, 'reconstrução')) {
            return 'Reconstrução';
        }

        if (
            str_contains($name, 'máscara capilar') ||
            $name === 'máscara'
        ) {
            return 'Máscara';
        }
    }

    return null;
}

/*
|--------------------------------------------------------------------------
| SALVAR ROTINA PERSONALIZADA
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['save_routine'])
) {

    verify_csrf();
    $weekday = (int) ($_POST['weekday'] ?? 0);
    $steps = $_POST['steps'] ?? [];

    if (
        $weekday >= 1 &&
        $weekday <= 7 &&
        is_array($steps)
    ) {

        $conn = db();

        $conn->beginTransaction();
        try {
        /*
        | Remove a rotina antiga daquele dia
        */

        $delete = $conn->prepare(
            'DELETE FROM wash_routines
             WHERE user_id = ? AND weekday = ?'
        );

        $delete->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
        $delete->bindValue(2, $weekday, PDO::PARAM_INT);

        $delete->execute();

        /*
        | Insere novamente na ordem enviada
        */

        $insert = $conn->prepare(
            'INSERT INTO wash_routines
             (user_id, weekday, step_order, step_name)
             VALUES (?, ?, ?, ?)'
        );

        $order = 1;

        foreach ($steps as $step) {

            $step = trim($step);

            if ($step === '') {
                continue;
            }

            $insert->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);
            $insert->bindValue(2, $weekday, PDO::PARAM_INT);
            $insert->bindValue(3, $order, PDO::PARAM_INT);
            $insert->bindValue(4, $step, PDO::PARAM_STR);

            $insert->execute();

            $order++;
        }

        $conn->commit();
        $msg = 'Rotina atualizada com sucesso!';
        } catch (Throwable $e) {
            if ($conn->inTransaction()) $conn->rollBack();
            $msg = 'Não foi possível salvar. Sua rotina anterior foi preservada.';
        }
    }
}

/*
|--------------------------------------------------------------------------
| DADOS DO PERFIL
|--------------------------------------------------------------------------
*/

$hairType = trim($user['hair_type'] ?? '');
$curlPattern = trim($user['curl_pattern'] ?? '');
$porosity = trim($user['porosity'] ?? '');
$thickness = trim($user['thickness'] ?? '');

$needs = array_filter(
    array_map(
        'trim',
        explode(',', $user['needs'] ?? '')
    )
);

/*
|--------------------------------------------------------------------------
| FREQUÊNCIA DE LAVAGEM SUGERIDA
|--------------------------------------------------------------------------
*/

$washFrequencyScore = 0;

/*
| Necessidades que podem indicar maior frequência
*/

if (in_array('Frizz', $needs, true)) {
    $washFrequencyScore++;
}

if (in_array('Caspa', $needs, true)) {
    $washFrequencyScore++;
}

if (in_array('Excesso de volume', $needs, true)) {
    $washFrequencyScore++;
}

if (in_array('Crescimento saudável', $needs, true)) {
    $washFrequencyScore++;
}

/*
| Necessidades que podem indicar menor frequência
*/

if (
    in_array('Danos por calor', $needs, true) ||
    in_array('Danos por química', $needs, true)
) {
    $washFrequencyScore--;
}

if ($washFrequencyScore >= 2) {

    $suggestedFrequency = 'Alta';

} elseif ($washFrequencyScore >= 1) {

    $suggestedFrequency = 'Média/alta';

} elseif ($washFrequencyScore <= -1) {

    $suggestedFrequency = 'Baixa';

} else {

    $suggestedFrequency = 'Média';
}

/*
|--------------------------------------------------------------------------
| DIAS DE LAVAGEM ESCOLHIDOS PELO USUÁRIO
|--------------------------------------------------------------------------
|
| 1 = Segunda
| 2 = Terça
| 3 = Quarta
| 4 = Quinta
| 5 = Sexta
| 6 = Sábado
| 7 = Domingo
|
*/

$washDays = [];

if (!empty($user['wash_days'])) {

    $washDays = array_map(
        'intval',
        explode(',', $user['wash_days'])
    );

    $washDays = array_filter(
        $washDays,
        fn($day) => $day >= 1 && $day <= 7
    );
}

/*
|--------------------------------------------------------------------------
| ROTINAS PERSONALIZADAS
|--------------------------------------------------------------------------
*/

$routines = [];

$stmtRoutine = db()->prepare(
    'SELECT weekday, step_order, step_name
     FROM wash_routines
     WHERE user_id = ?
     ORDER BY weekday, step_order'
);

$stmtRoutine->bindValue(1, $_SESSION['user_id'], PDO::PARAM_INT);

$stmtRoutine->execute();

$resultRoutine = $stmtRoutine;

while ($row = $resultRoutine->fetch()) {

    $weekday = (int) $row['weekday'];

    if (!isset($routines[$weekday])) {
        $routines[$weekday] = [];
    }

    $routines[$weekday][] = [
        'order' => (int) $row['step_order'],
        'name' => $row['step_name']
    ];
}

/*
|--------------------------------------------------------------------------
| TRATAMENTOS
|--------------------------------------------------------------------------
*/

$plan = [

    'Hidratação' =>
        '<img src="outras.png/hidrata.png"
        class="calendar-icon"
        alt="Hidratação">',

    'Nutrição' =>
        '<img src="outras.png/nutri.png"
        class="calendar-icon"
        alt="Nutrição">',

    'Reconstrução' =>
        '<img src="outras.png/reconst.png"
        class="calendar-icon"
        alt="Reconstrução">',

    'Máscara' =>
        '<img src="outras.png/mascara.png"
        class="calendar-icon"
        alt="Máscara">'
];

/*
|--------------------------------------------------------------------------
| FREQUÊNCIA DOS TRATAMENTOS
|--------------------------------------------------------------------------
*/

$hydration = 3;
$nutrition = 2;
$reconstruction = 1;

/*
|--------------------------------------------------------------------------
| NECESSIDADES DO CABELO
|--------------------------------------------------------------------------
*/

/*
| Necessidades que pedem mais hidratação
*/

$hydrationNeeds = [
    'Ressecamento',
    'Falta de brilho',
    'Manter a hidratação',
    'Falta de definição'
];

foreach ($hydrationNeeds as $need) {

    if (in_array($need, $needs, true)) {
        $hydration++;
    }
}

/*
| Necessidades que pedem mais nutrição
*/

$nutritionNeeds = [
    'Frizz',
    'Cabelo poroso',
    'Pontas duplas',
    'Falta de brilho'
];

foreach ($nutritionNeeds as $need) {

    if (in_array($need, $needs, true)) {
        $nutrition++;
    }
}

/*
| Necessidades que justificam mais reconstrução
*/

$reconstructionNeeds = [
    'Quebra',
    'Enfraquecimento',
    'Danos por química',
    'Descoloração'
];

foreach ($reconstructionNeeds as $need) {

    if (in_array($need, $needs, true)) {
        $reconstruction++;
    }
}

/*
|--------------------------------------------------------------------------
| POROSIDADE
|--------------------------------------------------------------------------
*/

if (strcasecmp($porosity, 'Alta') === 0) {

    $hydration++;
    $nutrition++;

    if (
        in_array('Quebra', $needs, true) ||
        in_array('Enfraquecimento', $needs, true) ||
        in_array('Danos por química', $needs, true) ||
        in_array('Descoloração', $needs, true)
    ) {
        $reconstruction++;
    }

} elseif (strcasecmp($porosity, 'Baixa') === 0) {

    $hydration++;
    $nutrition--;
}

/*
|--------------------------------------------------------------------------
| ESPESSURA
|--------------------------------------------------------------------------
*/

if (strcasecmp($thickness, 'Fina') === 0) {

    $reconstruction = min(
        $reconstruction,
        2
    );

} elseif (strcasecmp($thickness, 'Grossa') === 0) {

    $nutrition++;
}

/*
|--------------------------------------------------------------------------
| OLEOSIDADE
|--------------------------------------------------------------------------
*/

if (in_array('Oleosidade', $needs, true)) {

    $nutrition = max(
        1,
        $nutrition - 1
    );
}

/*
|--------------------------------------------------------------------------
| LIMITES
|--------------------------------------------------------------------------
*/

$hydration = max(
    2,
    min($hydration, 5)
);

$nutrition = max(
    1,
    min($nutrition, 4)
);

$reconstruction = max(
    1,
    min($reconstruction, 2)
);

/*
|--------------------------------------------------------------------------
| MONTA A SEQUÊNCIA PERSONALIZADA
|--------------------------------------------------------------------------
*/

$counts = [

    'Hidratação' => $hydration,

    'Nutrição' => $nutrition,

    'Reconstrução' => $reconstruction
];

$finalSequence = [];

$lastTreatment = '';

while (
    $counts['Hidratação'] > 0 ||
    $counts['Nutrição'] > 0 ||
    $counts['Reconstrução'] > 0
) {

    $available = [];

    foreach ($counts as $treatment => $count) {

        if (
            $count > 0 &&
            $treatment !== $lastTreatment
        ) {
            $available[$treatment] = $count;
        }
    }

    /*
    | Se só sobrou o mesmo tratamento,
    | permite a repetição.
    */

    if (empty($available)) {

        foreach ($counts as $treatment => $count) {

            if ($count > 0) {
                $available[$treatment] = $count;
            }
        }
    }

    arsort($available);

    $treatment = array_key_first($available);

    $finalSequence[] = $treatment;

    $counts[$treatment]--;

    $lastTreatment = $treatment;
}

$sequence = $finalSequence;

/*
|--------------------------------------------------------------------------
| EVITA ERRO CASO A SEQUÊNCIA ESTEJA VAZIA
|--------------------------------------------------------------------------
*/

if (empty($sequence)) {

    $sequence = [
        'Hidratação'
    ];
}

/*
|--------------------------------------------------------------------------
| DATA ATUAL
|--------------------------------------------------------------------------
*/

$today = (int) date('j');

$isCurrent =
    $month === (int) date('n') &&
    $year === (int) date('Y');

/*
|--------------------------------------------------------------------------
| NOME DO MÊS
|--------------------------------------------------------------------------
*/

$monthNames = [

    1 => 'Janeiro',

    2 => 'Fevereiro',

    3 => 'Março',

    4 => 'Abril',

    5 => 'Maio',

    6 => 'Junho',

    7 => 'Julho',

    8 => 'Agosto',

    9 => 'Setembro',

    10 => 'Outubro',

    11 => 'Novembro',

    12 => 'Dezembro'
];

$monthName = $monthNames[$month];

?>

<?php if (!empty($msg)): ?>

    <div class="routine-success">
        <?= e($msg) ?>
    </div>

<?php endif; ?>


<div class="page-title">

    <span class="eyebrow">
        ROTINA PERSONALIZADA
    </span>

    <h1>
        Meu calendário capilar
    </h1>

    <p>

        Uma rotina baseada nas informações do seu perfil:

        <strong>
            <?= e($hairType) ?>,
            <?= e($porosity) ?> porosidade
        </strong>.

    </p>

</div>


<div class="panel calendar-large">

    <div class="calendar-toolbar">

        <?php

        $previousMonth = $month - 1;
        $previousYear = $year;

        if ($previousMonth < 1) {

            $previousMonth = 12;
            $previousYear--;
        }

        ?>

        <a class="icon-btn" href="calendar.php?month=<?= $previousMonth ?>&year=<?= $previousYear ?>">
            ‹
        </a>


        <h2>

            <?= $monthName ?>
            <?= $year ?>

        </h2>


        <?php

        $nextMonth = $month + 1;
        $nextYear = $year;

        if ($nextMonth > 12) {

            $nextMonth = 1;
            $nextYear++;
        }

        ?>

        <a class="icon-btn" href="calendar.php?month=<?= $nextMonth ?>&year=<?= $nextYear ?>">
            ›
        </a>

    </div>


    <div class="weekdays">

        <span>Seg</span>
        <span>Ter</span>
        <span>Qua</span>
        <span>Qui</span>
        <span>Sex</span>
        <span>Sáb</span>
        <span>Dom</span>

    </div>


    <div class="calendar-days large">

        <?php for ($i = 1; $i < $first; $i++): ?>

            <span></span>

        <?php endfor; ?>


        <?php

        for ($d = 1; $d <= $days; $d++):

            $dayOfWeek = (int) date(
                'N',
                strtotime("$year-$month-$d")
            );

            /*
            | Verifica se é dia de lavagem
            */

            $isWashDay = in_array(
                $dayOfWeek,
                $washDays,
                true
            );

            /*
            | Descobre o tratamento
            */

            $t = null;

            if ($isWashDay) {

                /*
                | Primeiro verifica se existe
                | uma rotina personalizada
                */

                $dayRoutine = $routines[$dayOfWeek] ?? [];

                if (!empty($dayRoutine)) {

                    $t = getTreatmentFromRoutine(
                        $dayRoutine
                    );

                } else {

                    /*
                    | Se não houver rotina personalizada,
                    | usa a sequência automática.
                    */

                    $washIndex = 0;

                    for (
                        $checkDay = 1;
                        $checkDay <= $d;
                        $checkDay++
                    ) {

                        $checkWeekDay = (int) date(
                            'N',
                            strtotime(
                                "$year-$month-$checkDay"
                            )
                        );

                        if (
                            in_array(
                                $checkWeekDay,
                                $washDays,
                                true
                            )
                        ) {

                            if ($checkDay < $d) {
                                $washIndex++;
                            }
                        }
                    }

                    $t = $sequence[
                        $washIndex % count($sequence)
                    ];
                }
            }

            ?>


            <a class="day large <?= ($isCurrent && $d === $today) ? 'today' : '' ?>"
                href="calendar.php?day=<?= $d ?>&month=<?= $month ?>&year=<?= $year ?>">

                <b>
                    <?= $d ?>
                </b>


                <span>

                    <?php if ($isWashDay): ?>

                        <img src="outras.png/lavar.png" class="calendar-icon" alt="Lavar">

                    <?php else: ?>

                        <span class="no-wash">
                            —
                        </span>

                    <?php endif; ?>

                </span>


                <small>

                    <?php if ($isWashDay): ?>

                        <?php if ($t): ?>

                            Lavar +
                            <?= e($t) ?>

                        <?php else: ?>

                            Lavar

                        <?php endif; ?>

                    <?php else: ?>

                        Dia sem lavagem

                    <?php endif; ?>

                </small>

            </a>


        <?php endfor; ?>

    </div>

</div>


<div class="legend">

    <span>

        <img src="outras.png/hidrata.png" class="calendar-icon" alt="Hidratação">

        Hidratação

    </span>


    <span>

        <img src="outras.png/nutri.png" class="calendar-icon" alt="Nutrição">

        Nutrição

    </span>


    <span>

        <img src="outras.png/reconst.png" class="calendar-icon" alt="Reconstrução">

        Reconstrução

    </span>

</div>


<?php

/*
|--------------------------------------------------------------------------
| DETALHES DO DIA
|--------------------------------------------------------------------------
*/

if (isset($_GET['day'])):

    $d = max(
        1,
        min(
            $days,
            (int) $_GET['day']
        )
    );


    $dayOfWeek = (int) date(
        'N',
        strtotime("$year-$month-$d")
    );


    $isWashDay = in_array(
        $dayOfWeek,
        $washDays,
        true
    );


    $t = null;


    /*
    | Descobre o tratamento daquele dia
    */

    if ($isWashDay) {

        $dayRoutine = $routines[$dayOfWeek] ?? [];

        /*
        | Se existe rotina personalizada,
        | usa o tratamento dela.
        */

        if (!empty($dayRoutine)) {

            $t = getTreatmentFromRoutine(
                $dayRoutine
            );

        } else {

            /*
            | Caso contrário, usa a sequência automática.
            */

            $washIndex = 0;

            for (
                $checkDay = 1;
                $checkDay < $d;
                $checkDay++
            ) {

                $checkWeekDay = (int) date(
                    'N',
                    strtotime(
                        "$year-$month-$checkDay"
                    )
                );

                if (
                    in_array(
                        $checkWeekDay,
                        $washDays,
                        true
                    )
                ) {

                    $washIndex++;
                }
            }

            $t = $sequence[
                $washIndex % count($sequence)
            ];
        }
    }


    /*
    |--------------------------------------------------------------------------
    | BUSCA PRODUTOS SUGERIDOS
    |--------------------------------------------------------------------------
    */

    $productStep = trim($_GET['product_step'] ?? '');

    $routineProductSteps = [

        'Hidratação',
        'Nutrição',
        'Reconstrução',
    ];

    if (!in_array($productStep, $routineProductSteps, true)) {
        $productStep = $t ?: 'Hidratação';
    }

    $recommendedProducts = [];

    if ($isWashDay) {

        /*
         * A lavagem existe independentemente de haver Hidratação,
         * Nutrição ou Reconstrução.
         *
         * Quando existe um tratamento principal, usamos ele como
         * sugestão inicial. Quando não existe, mostramos produtos
         * gerais de rotina de lavagem.
         */

        if ($t === 'Máscara') {

            $stmtProducts = db()->prepare(
                'SELECT
                    id,
                    name,
                    brand,
                    category,
                    description,
                    image
                 FROM products
                 WHERE LOWER(category) LIKE \'%máscara%\'
                    OR LOWER(name) LIKE \'%máscara%\'
                 ORDER BY id DESC
                 LIMIT 3'
            );

        } elseif ($t) {

            $stmtProducts = db()->prepare(
                'SELECT
                    id,
                    name,
                    brand,
                    category,
                    description,
                    image
                 FROM products
                 WHERE LOWER(category) LIKE CONCAT(\'%\', LOWER(CAST(? AS text)), \'%\')
                 ORDER BY id DESC
                 LIMIT 3'
            );

            $stmtProducts->bindValue(1, $productStep, PDO::PARAM_STR);

        } else {

            /*
             * Sem tratamento: não transforma o dia em "sem lavagem".
             * Mostra produtos de etapas comuns da lavagem.
             */
            $stmtProducts = db()->prepare(
                'SELECT
                    id,
                    name,
                    brand,
                    category,
                    description,
                    image
                 FROM products
                 WHERE LOWER(category) LIKE CONCAT(\'%\', LOWER(CAST(? AS text)), \'%\')
                    OR LOWER(name) LIKE CONCAT(\'%\', LOWER(CAST(? AS text)), \'%\')
                 ORDER BY id DESC
                 LIMIT 3'
            );

            $stmtProducts->bindValue(1, $productStep, PDO::PARAM_STR);
            $stmtProducts->bindValue(2, $productStep, PDO::PARAM_STR);
        }

        $stmtProducts->execute();

        $resultProducts = $stmtProducts;

        while ($product = $resultProducts->fetch()) {
            $recommendedProducts[] = $product;
        }
    }

    ?>


    <div class="panel day-detail">

        <?php if ($isWashDay): ?>


            <div>

                <span class="eyebrow">
                    DIA <?= $d ?>
                </span>


                <h2>

                    <?php if ($t && isset($plan[$t])): ?>
                        <?= $plan[$t] ?>
                        <?= e($t) ?>
                    <?php else: ?>
                        Rotina de lavagem
                    <?php endif; ?>

                </h2>


                <p>

                    <?php if ($t): ?>
                        Hoje é dia de <?= strtolower(e($t)) ?>.
                    <?php else: ?>
                        Hoje é dia de lavagem.
                    <?php endif; ?>

                    Organize as etapas da sua rotina
                    e escolha os produtos adequados ao seu perfil.

                </p>


                <div class="routine-editor">

                    <span class="eyebrow">
                        DIA <?= $d ?>
                    </span>


                    <h2>
                        Minha rotina
                    </h2>


                    <p>
                        Organize as etapas dessa lavagem
                        na ordem que preferir.
                    </p>


                    <?php

                    $dayRoutine =
                        $routines[$dayOfWeek] ?? [];

                    if (empty($dayRoutine)) {

                        $dayRoutine = [

                            [
                                'name' =>
                                    'Pré-shampoo'
                            ],

                            [
                                'name' =>
                                    'Shampoo'
                            ],

                            [
                                'name' =>
                                    $t
                            ],

                            [
                                'name' =>
                                    'Condicionador'
                            ],

                            [
                                'name' =>
                                    'Finalização'
                            ]
                        ];
                    }

                    ?>


                    <form method="post" class="routine-form">
        <?= csrf_field() ?>

                        <input type="hidden" name="save_routine" value="1">

                        <input type="hidden" name="weekday" value="<?= $dayOfWeek ?>">


                        <div class="routine-list" id="routine-list">

                            <?php foreach ($dayRoutine as $step): ?>

                                <div class="routine-step" draggable="true">

                                    <span class="drag-handle">
                                        ☰
                                    </span>


                                    <input type="text" name="steps[]" value="<?= e($step['name']) ?>" placeholder="Ex.: Hidratação ou aplicação de máscara"" placeholder="Nome da etapa">


                                    <button type="button" class="remove-step" onclick="removeStep(this)">
                                        ×
                                    </button>

                                </div>

                            <?php endforeach; ?>

                        </div>


                        <div class="routine-actions">

                            <button type="button" class="btn routine-add" onclick="addStep()">
                                + Adicionar etapa
                            </button>


                            <button type="submit" class="btn primary">
                                Salvar rotina
                            </button>


                            <a class="btn primary" href="products.php?search=<?= urlencode($t) ?>">
                                Encontrar produtos
                            </a>

                        </div>

                    </form>

                </div>


                <!-- =====================================================
                     PRODUTOS SUGERIDOS
                ====================================================== -->

                <div class="recommended-products">

                    <span class="eyebrow">
                        SUGESTÕES PARA SUA ROTINA
                    </span>


                    <h2>
                        Sugestões para sua rotina
                    </h2>

                    <div class="product-step-selector" style="margin: 16px 0;">
                        <label for="product-step">
                            Ver produtos para:
                        </label>

                        <select id="product-step" onchange="changeProductStep(this.value)">
                            <?php foreach ($routineProductSteps as $stepName): ?>
                                <option value="<?= e($stepName) ?>" <?= $productStep === $stepName ? 'selected' : '' ?>>
                                    <?= e($stepName) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    <p class="recommended-subtitle">

                        Alguns produtos que podem ser usados
                        em <?= e(strtolower($productStep)) ?>.

                    </p>


                    <?php if (!empty($recommendedProducts)): ?>


                        <div class="recommended-products-grid">


                            <?php foreach ($recommendedProducts as $product): ?>


                                <article class="recommended-product-card">

                                    <div class="recommended-product-image">

                                        <?php $product_image_url = product_image_url($product['image'] ?? null); ?>

                                        <?php if ($product_image_url): ?>

                                            <img
                                                src="<?= e($product_image_url) ?>"
                                                alt="<?= e($product['name']) ?>"
                                            >

                                        <?php else: ?>

                                            <div class="no-product-image">
                                                Imagem indisponível
                                            </div>

                                        <?php endif; ?>

                                    </div>


                                    <div class="recommended-product-body">

                                        <span class="product-brand">

                                            <?= e(
                                                $product['brand'] ?? ''
                                            ) ?>

                                        </span>


                                        <h3>

                                            <?= e(
                                                $product['name'] ?? ''
                                            ) ?>

                                        </h3>


                                        <p>

                                            <?= e(
                                                $product['description'] ?? ''
                                            ) ?>

                                        </p>


                                        <a href="product.php?id=<?= (int) $product['id'] ?>" class="btn primary">
                                            Ver produto
                                        </a>

                                    </div>

                                </article>


                            <?php endforeach; ?>


                        </div>


                        <a href="products.php" class="see-all-products">
                            Ver todos os produtos →
                        </a>


                    <?php else: ?>


                        <div class="no-recommended-products">

                            <p>

                                Ainda não temos produtos cadastrados
                                para as etapas desta rotina.

                            </p>


                            <a href="products.php" class="btn primary">
                                Ver produtos
                            </a>

                        </div>


                    <?php endif; ?>

                </div>


            </div>


        <?php else: ?>


            <div>

                <span class="eyebrow">
                    DIA <?= $d ?>
                </span>


                <h2>
                    Dia sem lavagem
                </h2>


                <p>

                    Hoje não está marcado como um dia
                    de lavagem. Aproveite para deixar
                    o cabelo descansar.

                </p>

            </div>


        <?php endif; ?>

    </div>


    <script>

        function changeProductStep(step) {
            const params = new URLSearchParams(window.location.search);
            params.set('product_step', step);
            window.location.href = 'calendar.php?' + params.toString();
        }

        /*
        |--------------------------------------------------------------------------
        | ADICIONAR ETAPA
        |--------------------------------------------------------------------------
        */

        function addStep() {

            const list =
                document.getElementById('routine-list');

            if (!list) {
                return;
            }

            const step =
                document.createElement('div');

            step.className =
                'routine-step';

            step.setAttribute(
                'draggable',
                'true'
            );

            step.innerHTML = `

                <span class="drag-handle">
                    ☰
                </span>

                <input
                    type="text"
                    name="steps[]"
                    placeholder="Ex.: Hidratação ou aplicação de máscara"
                >

                <button
                    type="button"
                    class="remove-step"
                    onclick="removeStep(this)"
                >
                    ×
                </button>

            `;

            list.appendChild(step);
        }


        /*
        |--------------------------------------------------------------------------
        | REMOVER ETAPA
        |--------------------------------------------------------------------------
        */

        function removeStep(button) {

            const step =
                button.closest('.routine-step');

            if (step) {
                step.remove();
            }
        }


        /*
        |--------------------------------------------------------------------------
        | ARRASTAR E REORDENAR
        |--------------------------------------------------------------------------
        */

        const routineList =
            document.getElementById('routine-list');

        let draggedItem = null;


        if (routineList) {

            routineList.addEventListener(
                'dragstart',
                function (event) {

                    const step =
                        event.target.closest(
                            '.routine-step'
                        );

                    if (!step) {
                        return;
                    }

                    draggedItem = step;

                    step.classList.add(
                        'dragging'
                    );
                }
            );


            routineList.addEventListener(
                'dragend',
                function (event) {

                    const step =
                        event.target.closest(
                            '.routine-step'
                        );

                    if (step) {

                        step.classList.remove(
                            'dragging'
                        );
                    }

                    draggedItem = null;
                }
            );


            routineList.addEventListener(
                'dragover',
                function (event) {

                    event.preventDefault();

                    if (!draggedItem) {
                        return;
                    }

                    const afterElement =
                        getDragAfterElement(
                            routineList,
                            event.clientY
                        );

                    if (!afterElement) {

                        routineList.appendChild(
                            draggedItem
                        );

                    } else {

                        routineList.insertBefore(
                            draggedItem,
                            afterElement
                        );
                    }
                }
            );
        }


        function getDragAfterElement(
            container,
            y
        ) {

            const elements = [
                ...container.querySelectorAll(
                    '.routine-step:not(.dragging)'
                )
            ];

            return elements.reduce(
                function (
                    closest,
                    child
                ) {

                    const box =
                        child.getBoundingClientRect();

                    const offset =
                        y -
                        box.top -
                        box.height / 2;

                    if (
                        offset < 0 &&
                        offset > closest.offset
                    ) {

                        return {

                            offset: offset,

                            element: child

                        };

                    } else {

                        return closest;
                    }

                },
                {
                    offset:
                        Number.NEGATIVE_INFINITY
                }
            ).element;
        }

    </script>


<?php endif; ?>


<?php

require_once __DIR__ . '/footer.php';

?>
