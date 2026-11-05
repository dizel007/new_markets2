<?php

// 1. Группируем строки по первому ключу и сразу считаем сумму группы
$groups = [];
foreach ($array_by_fake_users as $group => $codes) {
    $groupRows = [];
    $groupSum  = 0;

    foreach ($codes as $code => $items) {
        foreach ($items as $sku => $value) {
            $groupRows[] = [
                'group'        => $group,
                'order_number' => $code,
                'sku'          => $sku,
                'value'        => $value,
            ];
            $groupSum += (int)$value;
        }
    }

    // 2. Внутри группы сортируем по значению (5-й столбец) по убыванию
    usort($groupRows, function ($a, $b) {
        return (int)$b['value'] <=> (int)$a['value'];
    });

    $groups[$group] = [
        'rows' => $groupRows,
        'sum'  => $groupSum,
    ];
}

// 3. Сортируем группы по сумме (6-й столбец) по убыванию
uasort($groups, function ($a, $b) {
    return (int)$b['sum'] <=> (int)$a['sum'];
});

// 4. Формируем плоский список с rowspan для группы и для суммы
$rows = [];
foreach ($groups as $group => $groupData) {
    $groupRows = $groupData['rows'];
    $groupSum  = $groupData['sum'];
    $count     = count($groupRows);

    foreach ($groupRows as $idx => $row) {
        $row['rowspan'] = ($idx === 0) ? $count : 0;
        $row['sum']     = $groupSum;
        $rows[] = $row;
    }
}

// 5. Цепляем ссылку на заказ Ozon (по unit_number)
foreach ($rows as &$needLink) {
    foreach ($array_unit_number as $unit_number => $delivery_shema) {
      if ((string)$needLink['order_number'] === (string)$unit_number) {
            $needLink['delivery_shema'] = $delivery_shema;
            if (mb_strtolower($delivery_shema) === 'fbs') {
                $needLink['link'] = 'https://seller.ozon.ru/app/postings/fbs?tab=delivered&postingDetails=' . $unit_number;
            } else {
                $needLink['link'] = 'https://seller.ozon.ru/app/postings/fbo/details?posting_number=' . $unit_number;
            }

            $needLink['ozonLink'] = 'https://www.ozon.ru/product/'. $needLink['sku'];
            break; // нашли — дальше не ищем

        }
    }
}
unset($needLink);

// echo "<pre>";
// print_r($rows);
// die();

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Таблица данных</title>
    <link rel="stylesheet" href="css/table.css">
</head>
<body>



<h1 class="page-title">Таблица данных</h1>

<div class="table-wrap">
    <table class="data-table">
        <thead>
            <tr>
                <th>пп</th>
                <th>ID покупателя</th>
                <th>Номер заказа</th>
                <th>СКУ</th>
                <th>Колво у покупателя</th>
                <th>ИТОГО</th>
            </tr>
        </thead>
<tbody>
    <?php $i = 1; foreach ($rows as $row): ?>
        <!-- Добавляем data-group="<?= $row['group'] ?>" -->
        <tr data-group="<?= htmlspecialchars($row['group'], ENT_QUOTES, 'UTF-8') ?>">
            <td class="num"><?= $i++ ?></td>

            <?php if ($row['rowspan'] > 0): ?>
                <td class="group-cell" rowspan="<?= $row['rowspan'] ?>">
                    <a href="search_fake_buyer.php?search_id=<?= urlencode($row['group']) ?>" 
                       class="group-link" target="_blank"
                       title="Посмотреть все заказы этого покупателя">
                        <?= htmlspecialchars($row['group'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </td>
            <?php endif; ?>

            <td>
                <?php if (!empty($row['link'])): ?>
                    <a href="<?= htmlspecialchars($row['link'], ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" rel="noopener noreferrer" class="order-link">
                        <?= htmlspecialchars($row['order_number'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php else: ?>
                    <?= htmlspecialchars($row['order_number'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </td>

            <td class="num">
                <?php if (!empty($row['ozonLink'])): ?>
                    <a href="<?= htmlspecialchars($row['ozonLink'], ENT_QUOTES, 'UTF-8') ?>"
                       target="_blank" rel="noopener noreferrer" class="sku-link">
                        <?= htmlspecialchars($row['sku'], ENT_QUOTES, 'UTF-8') ?>
                    </a>
                <?php else: ?>
                    <?= htmlspecialchars($row['sku'], ENT_QUOTES, 'UTF-8') ?>
                <?php endif; ?>
            </td>

            <td class="num"><?= (int)$row['value'] ?></td>

            <?php if ($row['rowspan'] > 0): ?>
                <td class="num total-cell" rowspan="<?= $row['rowspan'] ?>">
                    <?= (int)$row['sum'] ?>
                </td>
            <?php endif; ?>
        </tr>
    <?php endforeach; ?>
</tbody>
    </table>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('.data-table tbody tr');

    rows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            const groupId = this.getAttribute('data-group');
            if (!groupId) return;
            // Находим все строки с таким же ID и добавляем класс
            document.querySelectorAll(`.data-table tbody tr[data-group="${groupId}"]`).forEach(r => {
                r.classList.add('highlight-group');
            });
        });

        row.addEventListener('mouseleave', function() {
            const groupId = this.getAttribute('data-group');
            if (!groupId) return;
            // Убираем класс со всех строк
            document.querySelectorAll(`.data-table tbody tr[data-group="${groupId}"]`).forEach(r => {
                r.classList.remove('highlight-group');
            });
        });
    });
});
</script>
</body>
</html>