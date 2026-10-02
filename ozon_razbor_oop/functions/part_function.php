<?php
/**
 * Решение задачи о рюкзаке (0/1) для выбора заказов.
 * Выбирает подмножество заказов, чтобы суммарное количество целевого товара
 * было максимальным, но не превышало вместимость паллеты.
 *
 * @param array $orders Массив заказов, каждый с ключами 'id' и 'target_qty'
 * @param int $capacity Вместимость паллеты (максимальное суммарное количество)
 * @return array ['selected_ids' => int[], 'total_qty' => int]
 */
function knapsackSelectOrders(array $orders, int $capacity): array
{
    // Убираем заказы с нулевым или отрицательным количеством
    $orders = array_filter($orders, fn($o) => ($o['target_qty'] ?? 0) > 0);
    $orders = array_values($orders); // переиндексируем

    $n = count($orders);
    if ($n === 0 || $capacity <= 0) {
        return ['selected_ids' => [], 'total_qty' => 0];
    }

    // dp[c] = максимальная сумма target_qty при вместимости c
    $dp = array_fill(0, $capacity + 1, -1);
    $dp[0] = 0;
    // Для восстановления ответа: храним [индекс_заказа, предыдущая_вместимость]
    $prev = array_fill(0, $capacity + 1, null);

    foreach ($orders as $idx => $order) {
        $qty = $order['target_qty'];
        if ($qty <= 0) continue;

        for ($c = $capacity; $c >= $qty; $c--) {
            if ($dp[$c - $qty] !== -1 && $dp[$c - $qty] + $qty > $dp[$c]) {
                $dp[$c] = $dp[$c - $qty] + $qty;
                $prev[$c] = ['idx' => $idx, 'prev_cap' => $c - $qty];
            }
        }
    }

    // Находим лучшую достижимую сумму (не превышающую capacity)
    $bestCap = 0;
    for ($c = 1; $c <= $capacity; $c++) {
        if ($dp[$c] > $dp[$bestCap]) {
            $bestCap = $c;
        }
    }
    $totalQty = $dp[$bestCap];

    // Восстанавливаем выбранные заказы
    $selected = [];
    $cur = $bestCap;
    while ($cur > 0 && $prev[$cur] !== null) {
        $selected[] = $orders[$prev[$cur]['idx']]['id'];
        $cur = $prev[$cur]['prev_cap'];
    }

    return [
        'selected_ids' => array_reverse($selected),
        'total_qty' => $totalQty,
    ];
}