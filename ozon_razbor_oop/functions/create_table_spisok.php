<?php
/**
 * Таблица 1: заказы Ozon (с разворачиванием товаров через rowspan)
 */
function make_spisok_sendings_ozon($new_array_create_sends)
{
    if (empty($new_array_create_sends)) {
        echo '<p class="center_text">Нет данных</p>';
        return;
    }

    $total_qty        = 0;
    $total_sum        = 0;
    $total_positions  = 0;
    $rows_html        = '';
    $i = 1;

    foreach ($new_array_create_sends as $item) {
        $products = $item['products'] ?? [];
        $count_td = max(count($products), 1);
        $j1       = 0;

        if (empty($products)) {
            $rows_html .= '<tr>'
                . '<td class="num">' . $i . '</td>'
                . '<td class="text">' . htmlspecialchars($item['posting_number']) . '</td>'
                . '<td class="text">' . htmlspecialchars($item['shipment_date']) . '</td>'
                . '<td class="status">' . htmlspecialchars($item['status']) . '</td>'
                . '<td class="text">—</td><td class="text">—</td>'
                . '<td class="num">0</td><td class="num">0.00</td>'
                . '</tr>';
            $i++;
            continue;
        }

        foreach ($products as $prods) {
            $j1++;
            $qty   = (int)($prods['quantity'] ?? 0);
            $price = (float)($prods['price']['amount'] ?? 0);

            $total_qty       += $qty;
            $total_sum       += $qty * $price;
            $total_positions++;

            $rows_html .= '<tr>';

            if ($j1 === 1) {
                $rows_html .= '<td class="num"  rowspan="' . $count_td . '">' . $i . '</td>';
                $rows_html .= '<td class="text" rowspan="' . $count_td . '">'
                    . htmlspecialchars($item['posting_number']) . '</td>';
                $rows_html .= '<td class="text" rowspan="' . $count_td . '">' . htmlspecialchars($item['shipment_date']) . '</td>';
                $rows_html .= '<td class="status" rowspan="' . $count_td . '">' . htmlspecialchars($item['status']) . '</td>';
            }

            $rows_html .= '<td class="text">' . htmlspecialchars($prods['offer_id']) . '</td>';
            $rows_html .= '<td class="text">' . htmlspecialchars($prods['name']) . '</td>';
            $rows_html .= '<td class="num">'  . $qty . '</td>';
            $rows_html .= '<td class="num">'  . number_format($price, 2, '.', ' ') . '</td>';
            $rows_html .= '</tr>';
        }
        $i++;
    }

    echo '<div class="table-scroll">';
    echo '<h2 class="table-title">Заказы Ozon</h2>';
    echo '<table class="ozon-table">';
    echo '<colgroup>
            <col style="width:48px">
            <col style="width:170px">
            <col style="width:150px">
            <col style="width:130px">
            <col style="width:130px">
            <col>
            <col style="width:80px">
            <col style="width:100px">
          </colgroup>';
    echo '<thead><tr>
            <th class="text">№</th>
            <th class="text">Номер отправления</th>
            <th class="text">Дата отправления</th>
            <th class="text">Статус</th>
            <th class="text">Арт</th>
            <th class="text">Наименование</th>
            <th class="num">Кол-во</th>
            <th class="num">Цена за шт</th>
          </tr></thead>';
    echo '<tbody>' . $rows_html . '</tbody>';
    echo '<tfoot><tr>
            <td colspan="6" class="text total-label">Позиций: ' . $total_positions . '</td>
            <td class="num">' . $total_qty . '</td>
            <td class="num">' . number_format($total_sum, 2, '.', ' ') . '</td>
          </tr></tfoot>';
    echo '</table>';
    echo '</div>';
}


/**
 * Таблица 2: сводка для 1С — сколько чего купили
 */
function make_spisok_sendings_ozon_1С($array_prods)
{
    if (empty($array_prods)) {
        echo '<p class="center_text">Нет данных</p>';
        return;
    }

    $rows_html = '';
    $i = 1;
    $total_qty = 0;
    $total_sum = 0;

    foreach ($array_prods as $key => $item) {
        $qty   = (int)($item['quantity'] ?? 0);
        $price = (float)($item['price'] ?? 0);
        $sum   = $qty * $price;

        $total_qty += $qty;
        $total_sum += $sum;

        $rows_html .= '<tr>'
            . '<td class="num">'  . $i . '</td>'
            . '<td class="text">' . htmlspecialchars($key) . '</td>'
            . '<td class="text">' . htmlspecialchars($item['name']) . '</td>'
            . '<td class="num">'  . $qty . '</td>'
            . '<td class="num">'  . number_format($price, 2, '.', ' ') . '</td>'
            . '<td class="num">'  . number_format($sum,   2, '.', ' ') . '</td>'
            . '</tr>';
        $i++;
    }

    echo '<div class="table-scroll">';
    echo '<h2 class="table-title">Сводка для 1С</h2>';
    echo '<table class="ozon-table">';
    echo '<colgroup>
            <col style="width:48px">
            <col style="width:150px">
            <col>
            <col style="width:90px">
            <col style="width:110px">
            <col style="width:120px">
          </colgroup>';
    echo '<thead><tr>
            <th class="text">№</th>
            <th class="text">Артикул</th>
            <th class="text">Наименование</th>
            <th class="num">Кол-во</th>
            <th class="num">Цена за шт</th>
            <th class="num">Сумма</th>
          </tr></thead>';
    echo '<tbody>' . $rows_html . '</tbody>';
    echo '<tfoot><tr>
            <td colspan="3" class="text total-label">Итого</td>
            <td class="num">' . $total_qty . '</td>
            <td class="num">—</td>
            <td class="num">' . number_format($total_sum, 2, '.', ' ') . '</td>
          </tr></tfoot>';
    echo '</table>';
    echo '</div>';
}
?>