<?php

/**
 * Вывод таблицы продаж по городам.
 *
 * @param array $arr_warehouse  [ 'артикул' => [ 'Город' => количество, ... ], ... ]
 * @param array $art_ar         [ 'артикул' => что-то, ... ]
 */
function print_sell_po_gorodam($arr_warehouse, $art_ar)
{
    echo '<link rel="stylesheet" href="css/town_mp_table.css">';

    // --- 1. Уникальные города -------------------------------------------------
    $arr_gorod = [];
    foreach ($arr_warehouse as $item) {
        if (!is_array($item)) {
            continue;
        }
        foreach ($item as $gorod => $z) {
            if ($gorod !== '' && !isset($arr_gorod[$gorod])) {
                $arr_gorod[$gorod] = $gorod;
            }
        }
    }

    // --- 2. Нормализация ------------------------------------------------------
    $warehouse_norm = [];
    foreach ($arr_warehouse as $key_art => $item) {
        if (!is_array($item)) {
            continue;
        }
        $key = mb_strtolower((string)$key_art);
        if (!isset($warehouse_norm[$key])) {
            $warehouse_norm[$key] = [];
        }
        foreach ($item as $gorod => $count) {
            if (!isset($warehouse_norm[$key][$gorod])) {
                $warehouse_norm[$key][$gorod] = 0;
            }
            $warehouse_norm[$key][$gorod] += (int)$count;
        }
    }

    // --- 3. Обёртка для горизонтальной прокрутки ------------------------------
    echo '<div class="town_mp_table_wrap">';
    echo '<table class="town_mp_table">';

    echo '<thead><tr>';
    echo '<th>Артикул</th>';
    foreach ($arr_gorod as $gorod) {
        echo '<th>' . htmlspecialchars($gorod, ENT_QUOTES, 'UTF-8') . '</th>';
    }
    echo '</tr></thead>';

    echo '<tbody>';
    foreach ($art_ar as $nomenklatura => $x) {
        echo '<tr>';

        $art_safe = htmlspecialchars((string)$nomenklatura, ENT_QUOTES, 'UTF-8');
        echo '<td title="' . $art_safe . '">' . $art_safe . '</td>';

        $key_lower = mb_strtolower((string)$nomenklatura);
        $row_data  = $warehouse_norm[$key_lower] ?? null;

        foreach ($arr_gorod as $city) {
            if ($row_data === null || !array_key_exists($city, $row_data)) {
                echo '<td class="dash">-</td>';
                continue;
            }
            $value = (int)$row_data[$city];
            echo $value === 0
                ? '<td class="zero">0</td>'
                : '<td>' . $value . '</td>';
        }

        echo '</tr>';
    }
    echo '</tbody>';

    echo '</table>';
    echo '</div>';
}