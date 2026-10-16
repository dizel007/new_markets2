<?php

// Собираем все возможные интервалы (чтобы колонки были одинаковыми)
$intervals = [];
foreach ($array_date_time as $row) {
    foreach (array_keys($row) as $key) {
        $intervals[$key] = true;
    }
}
$intervals = array_keys($intervals);
// Сортируем по началу интервала: 0-8, 8-16, 16-24
usort($intervals, function ($a, $b) {
    return (int)explode('-', $a)[0] <=> (int)explode('-', $b)[0];
});

// Считаем итоги по колонкам и общий
$totals = array_fill_keys($intervals, 0);
$grandTotal = 0;
foreach ($array_date_time as $row) {
    foreach ($row as $key => $val) {
        $totals[$key] += $val;
        $grandTotal += $val;
    }
}
?>

<table class="sell_mp_table">
    <thead>
        <tr>
            <th>Дата</th>
            <?php foreach ($intervals as $interval): ?>
                <th><?= htmlspecialchars($interval) ?></th>
            <?php endforeach; ?>
            <th>Итого</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($array_date_time as $date => $row): ?>
            <?php
                $rowTotal = array_sum($row);
            ?>
            <tr>
                <td><?= htmlspecialchars($date) ?></td>
                <?php foreach ($intervals as $interval): ?>
                    <?php
                        $val = isset($row[$interval]) ? (int)$row[$interval] : null;
                        if ($val === null) {
                            // Пропущенного интервала нет — просто прочерк
                            echo '<td>—</td>';
                            continue;
                        }
                        $percent = $rowTotal > 0
                            ? round($val / $rowTotal * 100, 1)
                            : 0;
                    ?>
                    <td>
                        <div class="cell-value"><?= $val ?> <p class="cell-percent">(<?= $percent ?>%)</p></div>
                    </td>
                <?php endforeach; ?>
                <td><strong><?= $rowTotal ?></strong></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td>Итого</td>
            <?php foreach ($intervals as $interval): ?>
                <?php
                    $tVal = $totals[$interval];
                    $tPercent = $grandTotal > 0
                        ? round($tVal / $grandTotal * 100, 1)
                        : 0;
                ?>
                <td>
                    <div class="cell-value"><?= $tVal ?></div>
                    <div class="cell-percent"><?= $tPercent ?>%</div>
                </td>
            <?php endforeach; ?>
            <td><?= $grandTotal ?></td>
        </tr>
    </tfoot>
</table>