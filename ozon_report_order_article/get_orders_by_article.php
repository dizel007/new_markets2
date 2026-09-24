<?php
/******************************************************************************************************************
 * Формируем массив для вывода на экран
 *******************************************************************************************************************/
$i = 0;
if (isset($orders_our_article)) {
    foreach ($orders_our_article as $items) {

        $array_for_print[$i]['posting_number'] = $items['posting_number'];
        $array_for_print[$i]['in_process_at']  = $items['in_process_at'];

        $array_for_print[$i]['order_number'] = $items['order_number'];
        $array_for_print[$i]['status']       = $items['status'];
        $array_for_print[$i]['substatus']    = $items['substatus'];

        if (isset($items['delivery_schema'])) {
            $array_for_print[$i]['delivery_schema'] = $items['delivery_schema'];
            $array_for_print[$i]['customer_price']  = $items['financial_data']['products'][0]['customer_price']['amount'];
        } else {
            $array_for_print[$i]['delivery_schema'] = 'fbo';
            $array_for_print[$i]['customer_price']  = $items['financial_data']['products'][0]['payout'];
        }

        $array_for_print[$i]['offer_id'] = $items['products'][0]['offer_id'];
        $array_for_print[$i]['name']     = $items['products'][0]['name'];
        $array_for_print[$i]['sku']      = $items['products'][0]['sku'];
        $array_for_print[$i]['quantity'] = $items['products'][0]['quantity'];
        $array_for_print[$i]['price']    = $items['products'][0]['price']['amount'];

        $array_for_print[$i]['cluster_from'] = $items['financial_data']['cluster_from'];
        $array_for_print[$i]['cluster_to']   = $items['financial_data']['cluster_to'];

        $i++;
    }
}

// --- сортировка массива по дате заказа ---
if (isset($array_for_print)) {
    usort($array_for_print, function ($a, $b) {
        return strcmp($a['in_process_at'], $b['in_process_at']);
    });
}

// --- Группировка: [id_buyer][order_number][posting_number] => item ---
$array_client_shoping = [];
if (isset($array_for_print)) {
    foreach ($array_for_print as $item_print) {
        $order_number   = $item_print['order_number'];
        $posting_number = $item_print['posting_number'];
        $id_buyer       = explode('-', $order_number)[0];

        $array_client_shoping[$id_buyer][$order_number][$posting_number] = $item_print;
    }
}
?>

<link rel="stylesheet" href="css/sell_fbo_fbs_table.css">

<?php if (empty($array_client_shoping)): ?>

    <div class="filter-buttons">
        <p>нет данных для вывода</p>
    </div>

<?php else: ?>

    <?php
    /* ==========================================================================
     * 0. РАСПРЕДЕЛЕНИЕ ЗАКАЗОВ ПО КОЛИЧЕСТВУ ТОВАРОВ
     *    Считаем сумму quantity по всем отправлениям заказа и группируем заказы
     *    по этому значению.
     * ========================================================================== */
    $distribution      = []; // [кол-во_товаров_в_заказе => сколько_таких_заказов]
    $total_dist_orders = 0;
    $total_dist_qty    = 0;
    $arr_order_number  = [];

    foreach ($array_client_shoping as $id_buyer => $orders) {
        foreach ($orders as $order_number => $postings) {
            $qty_in_order = 0;
            // суммарное количество товаров в заказе
            foreach ($postings as $p) {
                $qty_in_order += (int)($p['quantity']);
            }
            @$distribution[$qty_in_order]++;
            $arr_order_number[$qty_in_order][] = $order_number;
            $total_dist_qty += $qty_in_order;

            $total_dist_orders++;
        }
    }

    // Сортируем по количеству товаров в заказе по возрастанию
    ksort($distribution);
    ksort($arr_order_number);

    // Хелпер: строит ссылку на группу продавца в детальной таблице
    $build_order_link = function (string $on): string {
        $id_buyer = explode('-', $on)[0];
        $anchor   = 'buyer-' . preg_replace('/[^A-Za-z0-9_-]/', '_', $id_buyer);
        return '<a href="#' . htmlspecialchars($anchor, ENT_QUOTES) . '">'
             . htmlspecialchars($on) . '</a>';
    };
    ?>

    <h3 class="summary-title">Распределение заказов по количеству товаров</h3>

    <table class="sell_mp_table w90 summary-table dist-table">
        <thead>
            <tr>
                <th style="width:80px;">пп</th>
                <th>Количество товаров в заказе</th>
                <th>Сколько таких заказов</th>
                <th>Номера заказов</th>
            </tr>
        </thead>
        <tbody>
            <?php $n = 0; foreach ($distribution as $qty_in_order => $orders_count): $n++; ?>
                <tr>
                    <td><?= $n ?></td>
                    <td><?= (int)$qty_in_order ?></td>
                    <td><?= (int)$orders_count ?></td>
                    <td class="order-numbers">
                        <?php
                        $orders   = $arr_order_number[$qty_in_order] ?? [];
                        $total    = count($orders);
                        $limit    = 20;
                        $collapse = $total > $limit;

                        if (!$collapse) {
                            echo implode(', ', array_map($build_order_link, $orders));
                        } else {
                            $uid  = 'dist-' . $n;
                            $head = array_slice($orders, 0, $limit);
                            $tail = array_slice($orders, $limit);
                            ?>
                            <input type="checkbox" id="<?= $uid ?>" class="order-expand-cb" hidden>
                            <span class="order-links">
                                <?= implode(', ', array_map($build_order_link, $head)) ?><span class="order-links-tail">, <?= implode(', ', array_map($build_order_link, $tail)) ?></span>
                            </span>
                            <label for="<?= $uid ?>" class="order-expand-label"
                                   data-more="показать все (<?= $total ?>)"
                                   data-less="свернуть"></label>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>

            <tr class="summary-total">
                <td colspan="2"><strong>Итого заказов</strong></td>
                <td><strong><?= (int)$total_dist_orders ?></strong></td>
                <td></td>
            </tr>
        </tbody>
    </table>


    <?php
    /* ==========================================================================
     * 2. ДЕТАЛЬНАЯ ТАБЛИЦА — группировка по продавцу
     * ========================================================================== */
    ?>
    <h3 class="summary-title">Детализация по отправлениям</h3>

    <table class="sell_mp_table w90">
        <thead>
            <tr>
                <th>пп</th>
                <th>ozon</th>
                <th>№ заказа</th>
                <th>Дата</th>
                <th>Статус</th>
                <th>Схема</th>
                <th>Кол-во</th>
                <th>Цена</th>
                <th>Откуда</th>
                <th>Куда</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $i = 0;

            foreach ($array_client_shoping as $id_buyer => $orders):

                $anchor = 'buyer-' . preg_replace('/[^A-Za-z0-9_-]/', '_', $id_buyer);

                $buyer_qty    = 0;
                $buyer_sum    = 0;
                $buyer_count  = 0;
                $buyer_orders = [];

                foreach ($orders as $order_number => $postings) {
                    $buyer_orders[$order_number] = true;
                    foreach ($postings as $p) {
                        $q = (int)($p['quantity'] ?? 0);
                        $buyer_qty   += $q;
                        $buyer_sum   += (float)($p['price'] ?? 0) * $q;
                        $buyer_count++;
                    }
                }
                ?>

                <tr class="buyer-group-header" id="<?= htmlspecialchars($anchor) ?>">
                    <td colspan="3" style="text-align:left;">
                        <strong>Продавец ID: <?= htmlspecialchars($id_buyer) ?></strong>
                        <span style="color:#667085;">
                            &nbsp;|&nbsp; заказов: <?= count($buyer_orders) ?>
                            &nbsp;|&nbsp; №: <?= htmlspecialchars(implode(', ', array_keys($buyer_orders))) ?>
                        </span>
                    </td>
                    <td colspan="2">
                        Отправлений: <strong><?= $buyer_count ?></strong>
                    </td>
                    <td colspan="2">
                        Всего штук: <strong><?= $buyer_qty ?></strong>
                    </td>
                    <td colspan="3" style="text-align:right;">
                        Сумма: <strong><?= number_format($buyer_sum, 0, '.', ' ') ?> ₽</strong>
                    </td>
                </tr>

                <?php
                foreach ($orders as $order_number => $postings):
                    foreach ($postings as $posting_number => $item): $i++;
                        ?>
                        <?php
                        if (($item['delivery_schema'] ?? '') === 'fbs') {
                            $link_for_ozon_seller = 'https://seller.ozon.ru/app/postings/fbs/' . urlencode($item['posting_number'] ?? '');
                        } else {
                            $link_for_ozon_seller = 'https://seller.ozon.ru/app/postings/fbo/' . urlencode($item['posting_number'] ?? '');
                        }

                        $statusClass = '';
                        $status      = $item['status'] ?? '';
                        $substatus   = $item['substatus'] ?? '';
                        if ($status === 'cancelled' || $substatus === 'posting_canceled') {
                            $statusClass = 'status-cancelled';
                        } elseif ($status === 'delivered' || $substatus === 'posting_received') {
                            $statusClass = 'status-delivered';
                        } elseif ($status === 'delivering' || (is_string($substatus) && strpos($substatus, 'on_way') !== false)) {
                            $statusClass = 'status-delivering';
                        }
                        ?>
                        <tr class="<?= $statusClass ?>">
                            <td><?= $i ?></td>
                            <td>
                                <a href="<?= $link_for_ozon_seller ?>" target="_blank">
                                    <?= htmlspecialchars($item['posting_number'] ?? '') ?>
                                </a>
                            </td>
                            <td><?= htmlspecialchars($item['order_number'] ?? $order_number) ?></td>
                            <td><?= !empty($item['in_process_at']) ? date('d.m.Y H:i', strtotime($item['in_process_at'])) : '' ?></td>
                            <td><?= htmlspecialchars($substatus ?: $status) ?></td>
                            <td><?= htmlspecialchars($item['delivery_schema'] ?? '') ?></td>
                            <td><?= (int)($item['quantity'] ?? 0) ?></td>
                            <td><?= number_format((float)($item['price'] ?? 0), 0, '.', ' ') ?></td>
                            <td><?= htmlspecialchars(mb_strimwidth($item['cluster_from'] ?? '', 0, 14, '..')) ?></td>
                            <td><?= htmlspecialchars(mb_strimwidth($item['cluster_to'] ?? '', 0, 15, '..')) ?></td>
                        </tr>
                    <?php endforeach; // posting ?>
                <?php endforeach; // order_number ?>

            <?php endforeach; // id_buyer ?>

        </tbody>
    </table>

<?php endif; ?>