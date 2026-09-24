<?php
/* =========================================================
   1. ДАННЫЕ
   ========================================================= */
// $data = [ ... ];


/* =========================================================
   2. НАСТРОЙКА СТОЛБЦОВ
   ---------------------------------------------------------
   Чтобы ДОБАВИТЬ столбец — добавьте запись в $columns.
   Чтобы УДАЛИТЬ столбец — удалите запись из $columns.
   Порядок столбцов = порядок записей.

   Параметры столбца (кроме label — все необязательны):
     label     — заголовок
     text      — выравнивание влево + значение как есть (без чисел)
     sortable  — разрешить сортировку
     no_color  — не подкрашивать +/- числа
     sum       — суммировать в итоговой строке (по умолчанию: авто)
                 true  — всегда суммировать,
                 false — не суммировать (напр., для %),
                 не указано — авто (суммируются все числовые)
     link      — шаблоны URL: ['fbs' => '...{id}', '_default' => null]
     width     — фиксированная ширина колонки (напр., '90px')
     hide      — прятать на узких экранах:
                 'lg' — скрыть при ширине <= 1400px
                 'md' — скрыть при ширине <= 1100px
                 'sm' — скрыть при ширине <=  800px
   ========================================================= */
$columns = [
    'article' => [
        'label' => 'Артикул',
        'text'  => true,
        'width' => '110px',
    ],
    'order_id' => [
        'label' => 'Заказ',
        'text'  => true,
        'width' => '90px',
        'hide'  => 'sm',
    ],
    'shipment_id' => [
        'label' => 'Отправление',
        'text'  => true,
        'width' => '110px',
        'hide'  => 'md',
        'link'  => [
            'fbs'      => 'https://seller.ozon.ru/app/postings/fbs/{id}',
            'fbo'      => 'https://seller.ozon.ru/app/postings/fbo/{id}',
            '_default' => null,
        ],
    ],
    'date_order' => [
        'label'    => 'Дата',
        'text'     => true,
        'sortable' => true,
        'width'    => '80px',
        'hide'     => 'sm',
    ],
    'delivery_schema' => [
        'label'    => 'Схема',
        'text'     => true,
        'sortable' => true,
        'width'    => '70px',
        'hide'     => 'md',
    ],
    'sale_price' => [
        'label'    => 'Цена прод',
        'sortable' => true,
        'width'    => '80px',
        'hide'     => 'sm',
    ],
    'commission_ratio' => [
        'label'    => 'Ком-я %',
        'sortable' => true,
        'sum'      => false,   // проценты не суммируем
        'width'    => '70px',
        'hide'     => 'lg',
    ],
    'seller_price' => [
        'label'    => 'Цена пр-ца',
        'sortable' => true,
        'width'    => '80px',
        'hide'     => 'md',
    ],
    'commission' => [
        'label'    => 'Ком-я',
        'sortable' => true,
        'width'    => '80px',
    ],
    'Логистика' => [
        'label'    => 'Лог-ка',
        'sortable' => true,
        'width'    => '80px',
    ],
    'Обратная логистика' => [
        'label'    => 'Обр.лог',
        'sortable' => true,
        'width'    => '80px',
        'hide'     => 'md',
    ],
    'Эквайринг' => [
        'label'    => 'Эквай',
        'sortable' => true,
        'width'    => '70px',
        'hide'     => 'lg',
    ],
    'Продвижение бренда' => [
        'label'    => 'Пр.брен',
        'sortable' => true,
        'width'    => '80px',
        'hide'     => 'lg',
    ],
    'dop_logistika' => [
        'label'    => 'Доп.лог',
        'sortable' => true,
        'width'    => '80px',
        'hide'     => 'md',
    ],
    'dop_strafi' => [
        'label'    => 'Доп.штр',
        'sortable' => true,
        'width'    => '80px',
        'hide'     => 'md',
    ],
    'deneg_na_rs' => [
        'label'    => 'на Р/С',
        'sortable' => true,
        'width'    => '80px',
    ],
    'dop_rashod' => [
        'label'    => 'Доп.расх',
        'sortable' => true,
        'width'    => '80px',
        'hide'     => 'md',
    ],
    'sebestoimost' => [
        'label'    => 'Себ-ть',
        'sortable' => true,
        'no_color' => true,
        'width'    => '80px',
        'hide'     => 'sm',
    ],
    'pribil' => [
        'label'    => 'Прибыль',
        'sortable' => true,
        'width'    => '90px',
    ],
];

$showRowNumber = true;
$showStatus    = true;
$statusKey     = 'is_return';
$statusHide    = 'sm';   // класс скрытия для колонки «Статус»


/* =========================================================
   3. ЛОГИКА
   ========================================================= */

/* Список сортируемых ключей */
$sortableKeys = [];
foreach ($columns as $key => $cfg) {
    if (!empty($cfg['sortable'])) $sortableKeys[] = $key;
}
if ($showStatus) $sortableKeys[] = $statusKey;

/* Разворачиваем дерево в плоский список */
$rows = [];
foreach ($data as $orderId => $items) {
    foreach ($items as $shipmentId => $item) {
        $item['order_id']    = $orderId;
        $item['shipment_id'] = $shipmentId;
        $item['is_return']   = array_key_exists('_return_', $item);
        $item['is_ino']      = array_key_exists('ino', $item); // ключ 'ino' из index_one_article.php
        $rows[] = $item;
    }
}

/* Сортировка */
$sortKey = $_GET['sort'] ?? '';
$sortDir = ($_GET['dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

if ($sortKey !== '' && in_array($sortKey, $sortableKeys, true)) {
    usort($rows, function ($a, $b) use ($sortKey, $sortDir, $statusKey) {
        $va = $a[$sortKey] ?? null;
        $vb = $b[$sortKey] ?? null;
        if ($va === null && $vb === null) return 0;
        if ($va === null) return 1;
        if ($vb === null) return -1;

        if ($sortKey === $statusKey) {
            $cmp = (int)(bool)$va <=> (int)(bool)$vb;
        } elseif (is_numeric($va) && is_numeric($vb)) {
            $cmp = (float)$va <=> (float)$vb;
        } else {
            $cmp = strcmp((string)$va, (string)$vb);
        }
        return $sortDir === 'desc' ? -$cmp : $cmp;
    });
} else {
    $sortKey = '';
}

/* Итоговые суммы по числовым столбцам */
$totals = [];
foreach ($columns as $key => $cfg) {
    if (!empty($cfg['text'])) continue;                          // текстовые
    if (!empty($cfg['link'])) continue;                          // ссылочные
    if (isset($cfg['sum']) && $cfg['sum'] === false) continue;   // явно отключено

    $sum   = 0.0;
    $count = 0;
    foreach ($rows as $r) {
        $v = $r[$key] ?? null;
        if (is_numeric($v)) { $sum += (float)$v; $count++; }
    }
    if ($count > 0) $totals[$key] = $sum;
}

/* Цвет числа */
function numClass($v) {
    if (!is_numeric($v)) return '';
    $n = (float)$v;
    if ($n < 0) return 'num-neg';
    if ($n > 0) return 'num-pos';
    return 'num-zero';
}

/* Класс скрытия колонки */
function hideClass(?array $cfg): string {
    return (!empty($cfg['hide'])) ? ' hide-' . $cfg['hide'] : '';
}

/* Значение ячейки: [html, css-класс] */
function cellValue($row, $key, $cfg) {
    $v = $row[$key] ?? null;
    if ($v === null) return ['', ''];

    if (!empty($cfg['link']) && is_array($cfg['link'])) {
        $text   = htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
        $schema = strtolower((string)($row['delivery_schema'] ?? ''));
        $tpl    = $cfg['link'][$schema] ?? ($cfg['link']['_default'] ?? null);

        if ($tpl === null || $tpl === '') return [$text, ''];

        $url    = str_replace('{id}', rawurlencode((string)$v), $tpl);
        $urlEsc = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');
        $html   = '<a class="shipment-link" href="' . $urlEsc . '" target="_blank" rel="noopener">'
                . $text . '</a>';
        return [$html, ''];
    }

    if (!empty($cfg['text']) || !is_numeric($v)) {
        return [htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'), ''];
    }

    $text = number_format((float)$v, 2, '.', ' ');
    $cls  = !empty($cfg['no_color']) ? '' : numClass($v);
    return [$text, $cls];
}

/* Отрисовка заголовка */
function renderHeader($key, $label, $isText, $sortableKeys, $sortKey, $sortDir, $extraClasses = '') {
    $isSortable = in_array($key, $sortableKeys, true);
    $cls = trim(
        ($isText ? 'text' : '')
        . ($isSortable ? ' sortable' : '')
        . ($extraClasses ? ' ' . $extraClasses : '')
    );
    $orderAttr = ($isSortable && $sortKey === $key)
        ? ' data-order="' . htmlspecialchars($sortDir, ENT_QUOTES, 'UTF-8') . '"'
        : '';

    $out = '<th class="' . $cls . '"' . $orderAttr . '>';

    if ($isSortable) {
        $nextDir = ($sortKey === $key && $sortDir === 'asc') ? 'desc' : 'asc';
        $url     = '?' . http_build_query(array_merge($_GET, ['sort' => $key, 'dir' => $nextDir]));
        $icon    = ($sortKey === $key) ? ($sortDir === 'asc' ? '↑' : '↓') : '↕';

        $out .= '<a class="sort-link" href="' . htmlspecialchars($url, ENT_QUOTES, 'UTF-8') . '">'
              . htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
              . '<span class="sort-icon">' . $icon . '</span>'
              . '</a>';
    } else {
        $out .= htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    }

    return $out . '</th>';
}

/* CSS */
$cssFile   = 'css/article_data_table.css';
$cssExists = is_file($cssFile);

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Отчёт по заказам</title>
    <?php if ($cssExists): ?>
        <link rel="stylesheet" href="css/article_data_table.css?v=<?= filemtime($cssFile) ?>">
    <?php endif; ?>
</head>
<body>

<div class="table-scroll">
    <table>

        <!-- Управление шириной колонок -->
        <colgroup>
            <?php if ($showRowNumber): ?>
                <col style="width:32px">
            <?php endif; ?>

            <?php foreach ($columns as $cfg): ?>
                <col<?= !empty($cfg['width']) ? ' style="width:' . htmlspecialchars($cfg['width'], ENT_QUOTES, 'UTF-8') . '"' : '' ?>>
            <?php endforeach; ?>

            <?php if ($showStatus): ?>
                <col style="width:60px">
            <?php endif; ?>
        </colgroup>

        <thead>
            <tr>
                <?php if ($showRowNumber): ?>
                    <th class="text">№</th>
                <?php endif; ?>

                <?php foreach ($columns as $key => $cfg): ?>
                    <?= renderHeader(
                            $key,
                            $cfg['label'] ?? $key,
                            !empty($cfg['text']),
                            $sortableKeys,
                            $sortKey,
                            $sortDir,
                            hideClass($cfg)
                        ) ?>
                <?php endforeach; ?>

                <?php if ($showStatus): ?>
                    <?= renderHeader(
                            $statusKey,
                            'Статус',
                            true,
                            $sortableKeys,
                            $sortKey,
                            $sortDir,
                            $statusHide ? 'hide-' . $statusHide : ''
                        ) ?>
                <?php endif; ?>
            </tr>
        </thead>

        <tbody>
            <?php if (empty($rows)): ?>
                <tr>
                    <td colspan="<?= count($columns) + (int)$showRowNumber + (int)$showStatus ?>"
                        class="center_text">
                        Нет данных
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($rows as $i => $r): ?>
                    <?php
                        $isReturn = !empty($r['is_return']);
                        $isIno    = !empty($r['is_ino']);

                        /* Приоритет: возврат > ино.
                           Если строка одновременно return и ino — считаем её возвратом. */
                        if ($isReturn) {
                            $rowClass = 'return';
                            $statuses = ['Возврат'];
                        } elseif ($isIno) {
                            $rowClass = 'ino';
                            $statuses = ['Иностр'];
                        } else {
                            $rowClass = '';
                            $statuses = [];
                        }
                    ?>
                    <tr class="<?= $rowClass ?>">

                        <?php if ($showRowNumber): ?>
                            <td class="text"><?= $i + 1 ?></td>
                        <?php endif; ?>

                        <?php foreach ($columns as $key => $cfg): ?>
                            <?php [$out, $cls] = cellValue($r, $key, $cfg); ?>
                            <td class="<?= trim(
                                    (!empty($cfg['text']) ? 'text ' : '')
                                    . $cls
                                    . hideClass($cfg)
                                ) ?>">
                                <?= $out ?>
                            </td>
                        <?php endforeach; ?>

                        <?php if ($showStatus): ?>
                            <td class="text status<?= $statusHide ? ' hide-' . $statusHide : '' ?>">
                                <?= $statuses ? implode(' / ', $statuses) : '—' ?>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>

        <?php if (!empty($totals)): ?>
        <tfoot>
            <tr>
                <?php if ($showRowNumber): ?>
                    <td class="text total-label">Итого</td>
                <?php endif; ?>

                <?php foreach ($columns as $key => $cfg): ?>
                    <?php
                        $has    = array_key_exists($key, $totals);
                        $val    = $has ? $totals[$key] : null;
                        $isText = !empty($cfg['text']);
                        $cls    = '';
                        if ($has) {
                            $cls = !empty($cfg['no_color']) ? '' : numClass($val);
                        }
                    ?>
                    <td class="<?= trim(
                            ($isText ? 'text ' : '')
                            . $cls
                            . hideClass($cfg)
                        ) ?>">
                        <?= $has ? number_format($val, 2, '.', ' ') : '' ?>
                    </td>
                <?php endforeach; ?>

                <?php if ($showStatus): ?>
                    <td class="<?= $statusHide ? 'hide-' . $statusHide : '' ?>"></td>
                <?php endif; ?>
            </tr>
        </tfoot>
        <?php endif; ?>
    </table>
</div>

</body>
</html>