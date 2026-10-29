<?php
/**********************************************************************************************************
 *  Детальная информация по одному отправлению (posting)
 *  GET: shopname, delivery_schema, posting_number
 *********************************************************************************************************/
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";

$shop_name = $_GET['shopname'] ?? '';

$dir_for_cache       = "../!cache/".$userdata['user_login']."/ozon_fbs_fbo_clusters/";
$filename_fbs_orders = $dir_for_cache."json_fbs_Orders_".$shop_name.".json";
$filename_fbo_orders = $dir_for_cache."json_fbo_Orders_".$shop_name.".json";

$delivery_schema     = $_GET['delivery_schema'] ?? '';
$schemaLower         = strtolower($delivery_schema);
$need_posting_number = mb_strtolower($_GET['posting_number'] ?? '');

if ($need_posting_number === '') {
    die('Не передан Номер заказа');
}

// ---------- Поиск posting в файле ----------
function findPostingInFile(string $filePath, string $needPostingNumber): ?array {
    if (!is_file($filePath)) return null;
    $data = json_decode(file_get_contents($filePath), true);
    if (!is_array($data)) return null;
    foreach ($data as $item) {
        $pn = mb_strtolower($item['posting_number'] ?? '');
        if ($pn === $needPostingNumber) return $item;
    }
    return null;
}

$posting    = null;
$schemaUsed = '';

if ($schemaLower === 'fbs') {
    $posting    = findPostingInFile($filename_fbs_orders, $need_posting_number);
    $schemaUsed = 'fbs';
} elseif ($schemaLower === 'fbo') {
    $posting    = findPostingInFile($filename_fbo_orders, $need_posting_number);
    $schemaUsed = 'fbo';
} else {
    $posting = findPostingInFile($filename_fbs_orders, $need_posting_number);
    if ($posting) { $schemaUsed = 'fbs'; }
    else {
        $posting = findPostingInFile($filename_fbo_orders, $need_posting_number);
        if ($posting) { $schemaUsed = 'fbo'; }
    }
}

if (!$posting) {
    die('Отправление ' . htmlspecialchars($need_posting_number) . ' не найдено в кэше.');
}


// echo "<pre>";
// print_r($posting);
// die();



// ---------- Нормализация ----------
$isFbs = $schemaUsed === 'fbs';

$status    = $posting['status']    ?? '';
$substatus = $posting['substatus'] ?? '';

// Товар (берём первый — в 99% случаев он один)
$product = $posting['products'][0] ?? [];
$offerId = $product['offer_id'] ?? '';
$name    = $product['name']     ?? '';
$sku     = $product['sku']      ?? '';
$qty     = (int)($product['quantity'] ?? 0);
$pricePerUnit = (float)($product['price']['amount'] ?? 0);
$priceTotal   = $pricePerUnit * $qty;

// Финансы (в financial_data.products[0] суммы — за ВСЮ позицию, т.е. × qty)
$finProduct     = $posting['financial_data']['products'][0] ?? [];

// ВАЖНО: customer_price, old_price, total_discount_value — ЗА 1 ШТ.
// payout, commission.amount — ЗА ВЕСЬ POSTING.
$customerPriceUnit = (float)($finProduct['customer_price']['amount'] ?? 0);
$oldPriceUnit      = (float)($finProduct['old_price'] ?? 0);
$discountValueUnit = (float)($finProduct['total_discount_value'] ?? 0);
$discountPct       = (float)($finProduct['total_discount_percent'] ?? 0);

$payoutTotal    = (float)($finProduct['payout'] ?? 0);
$commissionAmt  = (float)($finProduct['commission']['amount'] ?? 0);
$commissionPct  = (float)($finProduct['commission']['percent'] ?? 0);

$customerPriceTotal = $customerPriceUnit * $qty;
$oldPriceTotal      = $oldPriceUnit * $qty;
// Скидка за весь posting = скидка за 1 шт. × количество
$discountTotal      = $discountValueUnit * $qty;

$actions = $finProduct['actions'] ?? [];

// Кластеры
$clusterFrom = $posting['financial_data']['cluster_from'] ?? '';
$clusterTo   = $posting['financial_data']['cluster_to']   ?? '';

// Склад / способ доставки
if ($isFbs) {
    $warehouse      = $posting['delivery_method']['warehouse']       ?? '';
    $warehouseId    = $posting['delivery_method']['warehouse_id']    ?? '';
    $deliveryName   = $posting['delivery_method']['name']            ?? '';
    $tplProvider    = $posting['delivery_method']['tpl_provider']    ?? '';
    $deliveryType   = $posting['analytics_data']['delivery_type']    ?? '';
    $paymentType    = $posting['analytics_data']['payment_type_group_name'] ?? '';
    $deliveryBegin  = $posting['analytics_data']['delivery_date_begin'] ?? '';
    $deliveryEnd    = $posting['analytics_data']['delivery_date_end']   ?? '';
} else {
    $warehouse      = $posting['analytics_data']['warehouse_name']   ?? '';
    $warehouseId    = $posting['analytics_data']['warehouse_id']     ?? '';
    $deliveryName   = '';
    $tplProvider    = '';
    $deliveryType   = $posting['analytics_data']['delivery_type']    ?? '';
    $paymentType    = $posting['analytics_data']['payment_type_group_name'] ?? '';
    $deliveryBegin  = $posting['analytics_data']['client_delivery_date_begin'] ?? '';
    $deliveryEnd    = $posting['analytics_data']['client_delivery_date_end']   ?? '';
}

// Даты
$inProcessAt    = $posting['in_process_at']   ?? '';
$createdAt      = $posting['created_at']      ?? '';
$shipmentDate   = $posting['shipment_date']   ?? '';
$deliveringDate = $posting['delivering_date'] ?? '';

// Отмена (все поля могут быть пустыми — тогда блок не показываем)
$cancelReason    = $posting['cancellation']['cancel_reason']         ?? '';
$cancelInitiator = $posting['cancellation']['cancellation_initiator'] ?? '';
$cancelType      = $posting['cancellation']['cancellation_type']      ?? '';

// Тариф (tariffication_fact)
$tariffRate = $posting['tariffication_fact']['tariff_rate'] ?? null;
$tariffType = $posting['tariffication_fact']['tariff_type'] ?? '';

// Прочее
$orderNumber    = $posting['order_number']    ?? '';
$orderId        = $posting['order_id']        ?? '';
$trackingNumber = $posting['tracking_number'] ?? '';
$isExpress      = !empty($posting['is_express']);
$isPremium      = !empty($posting['analytics_data']['is_premium']);
$isLegal        = !empty($posting['analytics_data']['is_legal']);
$isClickCollect = !empty($posting['is_click_and_collect']);
$multiBoxQty    = (int)($posting['multi_box_qty'] ?? 0);
$scanit         = $posting['scanit'] ?? '';

// ---------- Человекочитаемые статусы ----------
$statusMap = [
    'awaiting_packaging' => 'Ожидает упаковки',
    'awaiting_deliver'   => 'Ожидает отгрузки',
    'delivering'         => 'В доставке',
    'delivered'          => 'Доставлен',
    'cancelled'          => 'Отменён',
    'arbitration'        => 'Арбитраж',
    'client_arbitration' => 'Арбитраж клиента',
    'driver_pickup'      => 'У водителя',
    'not_accepted'       => 'Не принят',
];
$substatusMap = [
    'posting_acceptance_in_progress'   => 'Приёмка в процессе',
    'posting_in_arbitration'           => 'В арбитраже',
    'posting_created'                  => 'Создан',
    'posting_in_carriage'              => 'В перевозке',
    'posting_not_in_carriage'          => 'Не в перевозке',
    'posting_registered'               => 'Зарегистрирован',
    'posting_transferring_to_delivery' => 'Передаётся в доставку',
    'posting_awaiting_passport_data'   => 'Ожидает паспортных данных',
    'posting_awaiting_registration'    => 'Ожидает регистрации',
    'posting_awaiting_verification'    => 'Ожидает проверки',
    'posting_canceled'                 => 'Отменён',
    'posting_received'                 => 'Получен',
    'posting_returned'                 => 'Возвращён',
    'posting_delivering'               => 'Доставляется',
    'posting_on_way'                   => 'В пути',
    'posting_delivered'                => 'Доставлен',
];
$statusHuman    = $statusMap[$status]       ?? $status;
$substatusHuman = $substatusMap[$substatus] ?? $substatus;

// CSS-класс статуса
$statusClass = 'status-other';
if ($status === 'cancelled' || $substatus === 'posting_canceled') {
    $statusClass = 'status-cancelled';
} elseif ($status === 'delivered' || $substatus === 'posting_received') {
    $statusClass = 'status-delivered';
} elseif ($status === 'delivering' || strpos($substatus, 'on_way') !== false || $substatus === 'posting_delivering') {
    $statusClass = 'status-delivering';
} elseif ($status === 'awaiting_packaging' || $status === 'awaiting_deliver') {
    $statusClass = 'status-awaiting';
}

// ---------- Ссылка на Ozon Seller ----------
$ozonLink = $isFbs
    ? 'https://seller.ozon.ru/app/postings/fbs?postingDetails=' . urlencode($posting['posting_number'] ?? '')
    : 'https://seller.ozon.ru/app/postings/fbo/' . urlencode($posting['posting_number'] ?? '');

// ---------- Хелперы ----------
function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function money($v) {
    $v = (float)$v;
    if ($v == 0) return '—';
    return number_format($v, 2, ',', ' ') . ' ₽';
}
function dt($iso, $withTime = true) {
    if (empty($iso) || strpos($iso, '1970-01-01') === 0) return '—';
    $ts = strtotime($iso);
    if (!$ts) return '—';
    return date($withTime ? 'd.m.Y H:i' : 'd.m.Y', $ts);
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Заказ <?= h($posting['posting_number'] ?? '') ?></title>
    <link rel="stylesheet" href="css/posting_details.css">
</head>
<body>

<div class="pd-container">

    <!-- ================= ЗАГОЛОВОК ================= -->
    <header class="pd-header <?= $statusClass ?>">
        <div class="pd-header__main">
            <div class="pd-header__schema"><?= h(strtoupper($schemaUsed)) ?></div>
            <h1 class="pd-header__title">Отправление <?= h($posting['posting_number'] ?? '') ?></h1>
            <div class="pd-header__meta">
                <span>Заказ № <strong><?= h($orderNumber) ?></strong></span>
                <?php if ($orderId): ?>
                    <span class="pd-dot">•</span>
                    <span>ID <strong><?= h($orderId) ?></strong></span>
                <?php endif; ?>
            </div>
        </div>
        <div class="pd-header__status">
            <div class="pd-badge <?= $statusClass ?>">
                <?= h($substatusHuman ?: $statusHuman) ?>
            </div>
            <?php if ($substatusHuman && $substatusHuman !== $statusHuman): ?>
                <div class="pd-header__status-main"><?= h($statusHuman) ?></div>
            <?php endif; ?>
            <a class="pd-btn pd-btn--primary" href="<?= h($ozonLink) ?>" target="_blank" rel="noopener">
                Открыть в Ozon
            </a>
        </div>
    </header>

    <!-- ================= БЛОК ОТМЕНЫ ================= -->
    <?php if ($cancelReason): ?>
        <div class="pd-alert pd-alert--danger">
            <div class="pd-alert__icon">✕</div>
            <div class="pd-alert__body">
                <div class="pd-alert__title">Причина отмены</div>
                <div class="pd-alert__text"><?= h($cancelReason) ?></div>
                <div class="pd-alert__meta">
                    <?php if ($cancelInitiator): ?>Инициатор: <strong><?= h($cancelInitiator) ?></strong><?php endif; ?>
                    <?php if ($cancelType): ?> • Тип: <strong><?= h($cancelType) ?></strong><?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ================= СЕТКА КАРТОЧЕК ================= -->
    <div class="pd-grid">

        <!-- ===== ТОВАР ===== -->
        <section class="pd-card pd-card--wide">
            <div class="pd-card__head">
                <h2>Товар</h2>
                <span class="pd-chip">SKU <?= h($sku) ?></span>
            </div>
            <div class="pd-product">
                <div class="pd-product__icon">📦</div>
                <div class="pd-product__body">
                    <div class="pd-product__name"><?= h($name) ?></div>
                    <div class="pd-product__article">Артикул продавца: <strong><?= h($offerId) ?></strong></div>
                </div>
                <div class="pd-product__prices">
                    <div class="pd-price-row">
                        <span class="pd-price-row__label">Кол-во</span>
                        <span class="pd-price-row__value"><?= $qty ?> шт.</span>
                    </div>
                    <div class="pd-price-row">
                        <span class="pd-price-row__label">Цена за 1 шт.</span>
                        <span class="pd-price-row__value"><?= money($pricePerUnit) ?></span>
                    </div>
                    <?php if ($qty > 1): ?>
                    <div class="pd-price-row">
                        <span class="pd-price-row__label">Итого по позиции</span>
                        <span class="pd-price-row__value pd-positive"><?= money($priceTotal) ?></span>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </section>

        <!-- ===== ФИНАНСЫ ===== -->
        <section class="pd-card">
            <div class="pd-card__head">
                <h2>Финансы</h2>
                <?php if ($qty > 1): ?>
                    <span class="pd-chip">за <?= $qty ?> шт.</span>
                <?php endif; ?>
            </div>
            <dl class="pd-dl">
                <dt>Цена покупателя</dt>
                <dd>
                    <strong><?= money($customerPriceTotal) ?></strong>
                    <?php if ($qty > 1): ?>
                        <div class="pd-sub"><?= money($customerPriceUnit) ?> / шт.</div>
                    <?php endif; ?>
                </dd>

                <dt>Выплата до вычета логистики и сервисов</dt>
                <dd class="<?= $payoutTotal > 0 ? 'pd-positive' : 'pd-muted' ?>">
                    <strong><?= money($payoutTotal) ?></strong>
                </dd>

                <?php if ($commissionAmt != 0): ?>
                <dt>Комиссия</dt>
                <dd class="pd-negative">
                    <?= money($commissionAmt) ?>
                    <?php if ($commissionPct): ?><span class="pd-muted">(<?= h($commissionPct) ?>%)</span><?php endif; ?>
                </dd>
                <?php endif; ?>



                <?php if ($tariffRate !== null && $tariffRate > 0): ?>
                <dt>Тариф</dt>
                <dd>
                    <?= h(round($tariffRate * 100, 2)) ?>%
                    <?php if ($tariffType): ?><span class="pd-muted">(<?= h($tariffType) ?>)</span><?php endif; ?>
                </dd>
                <?php endif; ?>
            </dl>

            <?php if (!empty($actions)): ?>
                <details class="pd-details">
                    <summary>Применённые акции (<?= count($actions) ?>)</summary>
                    <ul class="pd-list">
                        <?php foreach ($actions as $a): ?>
                            <li><?= h($a) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
            <?php endif; ?>
        </section>

        <!-- ===== ДОСТАВКА ===== -->
        <section class="pd-card">
            <div class="pd-card__head">
                <h2>Доставка</h2>
                <span class="pd-chip"><?= h(strtoupper($deliveryType ?: '—')) ?></span>
            </div>
            <dl class="pd-dl">
                <dt>Склад</dt>
                <dd><?= h($warehouse ?: '—') ?></dd>

                <?php if ($warehouseId): ?>
                <dt>ID склада</dt>
                <dd class="pd-mono"><?= h($warehouseId) ?></dd>
                <?php endif; ?>

                <?php if ($deliveryName): ?>
                <dt>Способ доставки</dt>
                <dd><?= h($deliveryName) ?></dd>
                <?php endif; ?>

                <?php if ($tplProvider): ?>
                <dt>Перевозчик</dt>
                <dd><?= h($tplProvider) ?></dd>
                <?php endif; ?>

                <?php if ($paymentType): ?>
                <dt>Оплата</dt>
                <dd><?= h($paymentType) ?></dd>
                <?php endif; ?>

                <?php if ($trackingNumber): ?>
                <dt>Трек-номер</dt>
                <dd class="pd-mono"><?= h($trackingNumber) ?></dd>
                <?php endif; ?>

                <?php if ($scanit): ?>
                <dt>ScanIt</dt>
                <dd class="pd-mono"><?= h($scanit) ?></dd>
                <?php endif; ?>
            </dl>
        </section>

        <!-- ===== КЛАСТЕРЫ ===== -->
        <section class="pd-card">
            <div class="pd-card__head">
                <h2>Маршрут</h2>
            </div>
            <div class="pd-route">
                <div class="pd-route__point">
                    <div class="pd-route__label">Откуда</div>
                    <div class="pd-route__value"><?= h($clusterFrom ?: '—') ?></div>
                </div>
                <div class="pd-route__arrow">→</div>
                <div class="pd-route__point">
                    <div class="pd-route__label">Куда</div>
                    <div class="pd-route__value"><?= h($clusterTo ?: '—') ?></div>
                </div>
            </div>
        </section>

        <!-- ===== ДАТЫ ===== -->
        <section class="pd-card pd-card--wide">
            <div class="pd-card__head">
                <h2>Ключевые даты</h2>
            </div>
            <div class="pd-timeline">
                <?php
                $dates = [
                    ['Создан',                dt($createdAt, true)],
                    ['В обработке',           dt($inProcessAt, true)],
                    ['Планируемая отгрузка',  dt($shipmentDate, true)],
                    ['Планируемая доставка',  dt($deliveryBegin, true)],
                    
                ];
                foreach ($dates as $d):
                    if ($d[1] === '—') continue;
                ?>
                    <div class="pd-timeline__item">
                        <div class="pd-timeline__label"><?= h($d[0]) ?></div>
                        <div class="pd-timeline__value"><?= h($d[1]) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>


    </div>



</div>

</body>
</html>