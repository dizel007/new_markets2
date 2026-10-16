<?php 

// echo "<pre>";
// print_r($arr_real_ozon_data);
// die();

//==========================================================
//     Достаёт значение из массива и удаляет ключ.
//==========================================================

if (!function_exists('getUnitNumber')) {
    function getUnitNumber(array &$array, string $key = 'unit_number')
    {
        if (!array_key_exists($key, $array)) {
            return 0;
        }
        $return_data = $array[$key];
        unset($array[$key]);
        return $return_data;
    }
}

/**********************************************************************************************
 * Оборачивает число в <span> с классом positive / negative в зависимости от знака.
 * Без разделителей разрядов — для количеств (шт).
 *************************************************************************************************/
if (!function_exists('colorNum')) {
    function colorNum($value)
    {
        if (!is_numeric($value)) {
            return $value;
        }
        $num = (float)$value;
        if ($num > 0) {
            return '<span class="positive">' . $value . '</span>';
        } elseif ($num < 0) {
            return '<span class="negative">' . $value . '</span>';
        }
        return '<span class="zero">' . $value . '</span>';
    }
}

/**********************************************************************************************
 * Форматирует денежную сумму: разделитель разрядов — пробел,
 * целые без копеек, дробные — 2 знака после запятой.
 * Оборачивает в <span> по знаку.
 *************************************************************************************************/

if (!function_exists('moneyNum')) {
    function moneyNum($value)
    {
        if (!is_numeric($value)) {
            return $value;
        }
        $num = (float)$value;
        $rounded = round($num, 2);

        if (abs($rounded - (int)$rounded) < 0.001) {
            $formatted = number_format($rounded, 0, '.', ' ');
        } else {
            $formatted = number_format($rounded, 2, '.', ' ');
        }

        if ($num > 0) {
            return '<span class="positive">' . $formatted . '</span>';
        } elseif ($num < 0) {
            return '<span class="negative">' . $formatted . '</span>';
        }
        return '<span class="zero">' . $formatted . '</span>';
    }
}

// ШАПКА ТАБЛИЦЫ
echo <<<HTML
<div class="h100proc_artikul">
<h3 class = "shapka_tabla">Юнит-экономика с возможностью сортировки по всем параметрам</h3>

<!-- Начинаем отрисовывать таблицу  -->
<table id="ancor_table" class="real_money fl-table">
<thead>
<tr>
    <th class="name_row">
        Наименование
        <div class="search-wrap">
            <input type="text"
                   id="nameSearch"
                   class="search-input"
                   placeholder="Поиск по названию / SKU / артикулу"
                   autocomplete="off">
        </div>
    </th>
    <th>SKU<br>Артикул</th>
    
    <th class="sortable" data-sort="count-direct" data-order="none" title="Кликните для сортировки">К-во<br>Заказ<br>(шт) <span class="sort-icon">↕</span></th>
    <th class="sortable" data-sort="count-return" data-order="none" title="Кликните для сортировки">К-во<br>Возвр<br>(шт) <span class="sort-icon">↕</span></th>
    <th class="sortable" data-sort="count-buy" data-order="none" title="Кликните для сортировки">К-во<br>продн<br>(шт) <span class="sort-icon">↕</span></th>
<!-- Стоимость в ЛК  -->
   <th class="sortable" data-sort="seller-price" data-order="none" title="Кликните для сортировки">Стоимость<br>товара<br>в ЛК (руб) <span class="sort-icon">↕</span></th>
<!-- Комиссия озон   -->
    <th class="sortable" data-sort="commission" data-order="none" title="Кликните для сортировки">Комиссия<br>озон<br>(руб) <span class="sort-icon">↕</span></th>
<!-- Логистика  -->
    <th class="sortable" data-sort="logistika" data-order="none" title="Кликните для сортировки">Стоимость<br>логистики<br>(руб) <span class="sort-icon">↕</span></th>
<!-- Сервисы -->
    <th class="sortable" data-sort="service" data-order="none" title="Кликните для сортировки">Стоимость<br>сервисов<br>(руб) <span class="sort-icon">↕</span></th>
    <th class="sortable" data-sort="equairing" data-order="none" title="Кликните для сортировки">Эквайринг<br>(руб) <span class="sort-icon">↕</span></th>
     <th class="sortable" data-sort="price-without" data-order="none" title="Кликните для сортировки">Сумма<br>продаж без<br>комис и логис<br>(руб) <span class="sort-icon">↕</span></th>
  <th class="sortable" data-sort="no-sku" data-order="none" title="Кликните для сортировки">доп.услуги<br>(руб) <span class="sort-icon">↕</span></th>
<!-- Цена за вычетом всего где есть артикул -->
    <th class="sortable" data-sort="viplata" data-order="none" title="Кликните для сортировки">Выплаты<br>(руб) <span class="sort-icon">↕</span></th>
    <th class="sortable" data-sort="ino_prodazhi" data-order="none" title="Кликните для сортировки">Ино_продажи<br>(руб) <span class="sort-icon">↕</span></th>
    <th class="sortable" data-sort="sebestoimost" data-order="none" title="Кликните для сортировки">Себестсть<br>(руб) <span class="sort-icon">↕</span></th>
    <th class="sortable" data-sort="profit" data-order="none" title="Кликните для сортировки">Прибыль<br>(руб) <span class="sort-icon">↕</span></th>
    
</tr>
</thead>
HTML;

echo "<tbody id=\"filterable-table-body\">";

$url_encoded = '#';

// Денежные суммы
$sum_seller_price                    = 0;
$sum_commission                      = 0;
$sum_logistika                       = 0;
$sum_price_without_comm_and_logist   = 0;
$sum_equairing                       = 0;
$sum_service                         = 0;
$sum_no_sku_trati_raschet            = 0;
$sum_viplata_na_rs                   = 0;
$sum_ino_prodazh                     = 0;
$sum_article_sebestoimost            = 0;
$sum_pribil                          = 0;

// Количества (шт)
$sum_count_direct = 0;
$sum_count_return = 0;
$sum_count_buy    = 0;

$sum_no_sku_trati         = 0;
$all_sum_seller           = 0;
$one_procent_no_sku_trati = 0;

// Нам нужна сумма продаж по цене продавца, чтобы найти 1% от суммы всех продаж
foreach ($sum_array_to_redakt as $sku_ozon => &$item_for_print) {
    @$all_sum_seller += $item_for_print['seller_price'];

    /// Обрабатываем массив NO_SKU
    if ($sku_ozon == 'NO_SKU') {
        foreach ($item_for_print as $key_no_sku => $serv) {
            $sum_no_sku_trati += getUnitNumber($item_for_print, $key_no_sku);
        }
        $one_procent_no_sku_trati = $sum_no_sku_trati / 100;
        unset($sum_array_to_redakt[$sku_ozon]);
        continue;
    }
}

$one_procent_form_sum_seller = $all_sum_seller / 100;

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////// НАЧИНАЕМ ОСНОВНОЙ РАЗБОР ДАННЫХ //////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

foreach ($sum_array_to_redakt as $sku_ozon => &$item_for_print) {

    /// ищем артикул товара 
    $article_ozon  = 'нд';
    $name_ozon     = 'нд';
    $product_id    = 'нд';
    $sebestoimost  = 0;

    foreach ($arr_article_products as $sku => $data_tovar) {
        if ($data_tovar['sku'] == $sku_ozon) {
            $article_ozon = $data_tovar['article'];
            $name_ozon    = $data_tovar['name'];
            $product_id   = $data_tovar['product_id'];
            $sebestoimost = $data_tovar['sebestoimost'];
            break;
        }
    }

    // просто удаляем лишнее, чтобы посмотреть, что осталось
    getUnitNumber($item_for_print, "delivery_summa");
    getUnitNumber($item_for_print, "sale_price");
    getUnitNumber($item_for_print, "sale_amount");
    getUnitNumber($item_for_print, "coinvestment");
    getUnitNumber($item_for_print, "sale_commission");
    getUnitNumber($item_for_print, "bonus");

    /// Высчитываем переменные для вывода таблицы
    $count_direct = getUnitNumber($item_for_print, "count_direct");
    $count_return = getUnitNumber($item_for_print, "count_return");
    $count_buy    = $count_direct - $count_return;

//***************************************************************************
//
// 
// ==========================================================================
// ==========================================================================
// ==========================================================================
// выбираем по какому числу будем считать количество товаров для себестоимости
    $count_for_raschet = $count_buy;
//
// ==========================================================================
// ==========================================================================
//***************************************************************************

    $seller_price = getUnitNumber($item_for_print, "seller_price");
    $commission   = getUnitNumber($item_for_print, "commission");
    $logistika    = getUnitNumber($item_for_print, "Логистика")
                  + getUnitNumber($item_for_print, "Обратная логистика")
                  + getUnitNumber($item_for_print, "Доставка до места выдачи силами Ozon");
    $equairing    = getUnitNumber($item_for_print, "Эквайринг");
    $ino_prodazhi  = getUnitNumber($item_for_print, "ino_prodazhi");

    // Считаем всё, что осталось из отчёта (СЕРВИСЫ)
    $service = 0;
    foreach ($item_for_print as $key_2 => $serv) {
        $service += getUnitNumber($item_for_print, $key_2);
    }
    unset($sum_array_to_redakt[$sku_ozon]);

    $price_without_comm_and_logist = $seller_price + $commission + $logistika + $equairing + $service;

    ////////////////////////////////////////////////////////////////////////////
    // распределение суммы NO_SKU (реклама, штрафы и прочее)
    if ($all_sum_seller <> 0 ) {
    $summa_raspredelenia_no_sku_trat = round(($seller_price / ($all_sum_seller * 0.01) * $one_procent_no_sku_trati), 2);
    } else {
       $summa_raspredelenia_no_sku_trat = 0; 
    }

    // получаем сумму за вычетом NO_SKU трат
    $viplata_na_rs = round($price_without_comm_and_logist + $summa_raspredelenia_no_sku_trat, 2);

    $article_sebestoimost = $sebestoimost * $count_for_raschet;

    $pribil = round($viplata_na_rs - $article_sebestoimost + $ino_prodazhi, 2);

    if ($count_for_raschet > 0) {
        $sthuka_pribil = round($pribil / $count_for_raschet, 2);
    } else {
        $sthuka_pribil = 0;
    }

    /////////////////////////////////////////
    // суммируем все цены и количества
    /////////////////////////////////////////
    $sum_seller_price                  += $seller_price;
    $sum_commission                    += $commission;
    $sum_logistika                     += $logistika;
    $sum_equairing                     += $equairing;
    $sum_service                       += $service;
    $sum_price_without_comm_and_logist += $price_without_comm_and_logist;
    $sum_no_sku_trati_raschet          += $summa_raspredelenia_no_sku_trat;
    $sum_viplata_na_rs                 += $viplata_na_rs;
    $sum_ino_prodazh                   += $ino_prodazhi;
    $sum_article_sebestoimost          += $article_sebestoimost;
    $sum_pribil                        += $pribil;

    $sum_count_direct += $count_direct;
    $sum_count_return += $count_return;
    $sum_count_buy    += $count_buy;

    // data-* атрибуты для сортировки — «сырые» числа без форматирования.
    // data-search — склеенный в нижнем регистре текст для поиска (название + артикул + SKU)
    $search_blob = mb_strtolower(
        (string)$name_ozon . ' ' . (string)$article_ozon . ' ' . (string)$sku_ozon,
        'UTF-8'
    );
    $search_blob_safe = htmlspecialchars($search_blob, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

    echo "<tr"
        . " data-count-direct=\"" . (float)$count_direct . "\""
        . " data-count-return=\"" . (float)$count_return . "\""
        . " data-count-buy=\"" . (float)$count_buy . "\""
        . " data-seller-price=\"" . (float)$seller_price . "\""
        . " data-commission=\"" . (float)$commission . "\""
        . " data-logistika=\"" . (float)$logistika . "\""
        . " data-service=\"" . (float)$service . "\""
        . " data-equairing=\"" . (float)$equairing . "\""
        . " data-price-without=\"" . (float)$price_without_comm_and_logist . "\""
        . " data-no-sku=\"" . (float)$summa_raspredelenia_no_sku_trat . "\""
        . " data-viplata=\"" . (float)$viplata_na_rs . "\""
        . " data-ino_prodazhi=\"" . (float)$ino_prodazhi . "\""
        . " data-sebestoimost=\"" . (float)$article_sebestoimost . "\""
        . " data-profit=\"" . (float)$pribil . "\""
        . " data-search=\"" . $search_blob_safe . "\""
        . ">";

    // Название товара — обрезается до 2 строк, полное в title
    $name_ozon_safe = htmlspecialchars((string)$name_ozon, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo "<td><a class=\"tovar_name\" href=\"https://www.ozon.ru/product/$sku_ozon\" target=\"_blank\" title=\"{$name_ozon_safe}\">{$name_ozon_safe}</a></td>";

    // Артикул и СКУ
    echo "<td>"
        
        . $article_ozon
        . "</a> <hr>"
        . "<a href=\"index_one_article.php?ozon_shop=$shop_name&art=" . $article_ozon . "&sku_ozon=" . $sku_ozon ."&procent=$one_procent_no_sku_trati". "\" target=\"_blank\">"
        . $sku_ozon
        . "</a> </td>";

    // Количество заказанных товаров (шт)
    echo "<td>" . colorNum($count_direct) . "</td>";
    // Количество возвратов товаров (шт)
    echo "<td>" . colorNum($count_return) . "</td>";
    // Количество выкупленных товаров (шт)
    echo "<td>" . colorNum($count_buy) . "</td>";

    // ============ ДЕНЕЖНЫЕ СУММЫ — с разделителями разрядов ============

    // Цена для покупателя (стоимость товара в личном кабинете)
    echo "<td>" . moneyNum($seller_price) . "</td>";

    // Комиссия озона
    echo "<td>" . moneyNum($commission) . "</td>";

    // Логистика
    echo "<td>" . moneyNum($logistika) . "</td>";

    // Сервисы
    echo "<td>" . moneyNum($service) . "</td>";

    // Эквайринг
    echo "<td>" . moneyNum($equairing) . "</td>";

    // Цена без комиссии и логистики
    echo "<td>" . moneyNum($price_without_comm_and_logist) . "</td>";

    // Процент распределения стоимости
    echo "<td>" . moneyNum($summa_raspredelenia_no_sku_trat) . "</td>";

    // Цена за вычетом всех расходов
    echo "<td>" . moneyNum($viplata_na_rs) . "</td>";


    // Выплаты по иностранным продажам
    echo "<td>" . moneyNum($ino_prodazhi) . "</td>";

    // Себестоимость: цена за штуку и общая
    echo "<td>" . moneyNum($sebestoimost) . "<br>" . moneyNum($article_sebestoimost) . "</td>";

    // Прибыль: на штуку и общая
    echo "<td>" . moneyNum($sthuka_pribil) . "<br>" . moneyNum($pribil) . "</td>";

    echo "</tr>";
}

// СТРОКА ИТОГО ТАБЛИЦЫ
// data-total-key — ключ, по которому JS пересчитывает итог при фильтрации.
// Ключ совпадает с суффиксом в data-<key> у строк <tr>.
// data-total-type: "money" (по умолчанию) или "count" — определяет формат вывода.
echo "<tr class=\"total-row\">";
    echo "<td>" . "ИТОГО" . "</td>";
    echo "<td>" . "" . "</td>";
    echo "<td data-total-key=\"count-direct\" data-total-type=\"count\"><b>" . colorNum($sum_count_direct) . "</b></td>";
    echo "<td data-total-key=\"count-return\" data-total-type=\"count\"><b>" . colorNum($sum_count_return) . "</b></td>";
    echo "<td data-total-key=\"count-buy\"    data-total-type=\"count\"><b>" . colorNum($sum_count_buy)    . "</b></td>";
    echo "<td data-total-key=\"seller-price\"><b>"   . moneyNum($sum_seller_price) . "</b></td>";
    echo "<td data-total-key=\"commission\"><b>"     . moneyNum($sum_commission) . "</b></td>";
    echo "<td data-total-key=\"logistika\"><b>"      . moneyNum($sum_logistika) . "</b></td>";
    echo "<td data-total-key=\"service\"><b>"        . moneyNum($sum_service) . "</b></td>";
    echo "<td data-total-key=\"equairing\"><b>"      . moneyNum($sum_equairing) . "</b></td>";
    echo "<td data-total-key=\"price-without\"><b>"  . moneyNum($sum_price_without_comm_and_logist) . "</b></td>";
    echo "<td data-total-key=\"no-sku\"><b>"         . moneyNum($sum_no_sku_trati_raschet) . "</b></td>";
    echo "<td data-total-key=\"viplata\"><b>"        . moneyNum($sum_viplata_na_rs) . "</b></td>";
    echo "<td data-total-key=\"ino_prodazhi\"><b>"   . moneyNum($sum_ino_prodazh) . "</b></td>";
    echo "<td data-total-key=\"sebestoimost\"><b>"   . moneyNum($sum_article_sebestoimost) . "</b></td>";
    echo "<td data-total-key=\"profit\"><b>"         . moneyNum($sum_pribil) . "</b></td>";
echo "</tr>";

echo <<<HTML
</tbody>
</table>

</div>


<!-- ================== Кнопка «Скачать Excel» под таблицей ================== -->
<div class="export-bar">
    <button type="button" class="submit-btn export-btn" id="exportExcelBtn">
        📥 Скачать Excel
    </button>
    <span class="export-hint" id="exportHint"></span>
</div>
HTML;
?>

<!-- ================== ТАБЛИЦА СРАВНЕНИЯ С БАЛАНСОМ OZON ================== -->
<?php
// Безопасно достаём значения из $ozon_array_balance (могут отсутствовать ключи)
$ozon_viplati    = isset($ozon_array_balance['viplati'])   ? (float)$ozon_array_balance['viplati']   : 0.0;
$ozon_cashflows  = isset($ozon_array_balance['cashflows']) ? (float)$ozon_array_balance['cashflows'] : 0.0;
$ozon_commision  = isset($ozon_array_balance['commision']) ? (float)$ozon_array_balance['commision'] : 0.0;

// Три пары сумм: [подпись, сумма из таблицы, сумма из баланса]
$compare_rows = [
    [
        'label' => 'Стоимость товара в ЛК',
        'table' => $sum_seller_price,
        'ozon'  => $ozon_cashflows,
    ],
    [
        'label' => 'Комиссия Ozon',
        'table' => $sum_commission,
        'ozon'  => $ozon_commision,
    ],
    [
        'label' => 'Выплаты',
        'table' => $sum_viplata_na_rs,
        'ozon'  => $ozon_viplati,
    ],
];
?>

<div class="h100proc_artikul" style="margin-top: 10px;">
    <h3 class="shapka_tabla">Сравнение сумм: Таблица ↔ Баланс Ozon</h3>
    <table class="real_money fl-table">
        <thead>
        <tr>
            <th>Показатель</th>
            <th>Сумма из таблицы (руб)</th>
            <th>Сумма из баланса Ozon (руб)</th>
            <th>Дельта (руб)</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($compare_rows as $row): ?>
            <?php
                $delta = round($row['table'] - $row['ozon'], 2);

                if ($delta > 0) {
                    $delta_class = 'positive';
                    $delta_str   = '+' . number_format($delta, 2, '.', ' ');
                } elseif ($delta < 0) {
                    $delta_class = 'negative';
                    $delta_str   = number_format($delta, 2, '.', ' ');
                } else {
                    $delta_class = 'zero';
                    $delta_str   = '0';
                }
            ?>
            <tr>
                <td><?= htmlspecialchars($row['label'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= moneyNum($row['table']) ?></td>
                <td><?= moneyNum($row['ozon']) ?></td>
                <td><span class="<?= $delta_class ?>"><?= $delta_str ?></span></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- ================== Ссылка в личный кабинет Ozon ================== -->
<div class="export-bar">
    <a href="https://seller.ozon.ru/app/finances/balance"
       target="_blank"
       rel="noopener noreferrer"
       class="ozon-cabinet-btn">
        🏪 Открыть личный кабинет Ozon
    </a>
</div>

<!-- SheetJS для клиентского экспорта (никаких запросов к серверу) -->
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<!-- Логика таблицы: сортировка, поиск, пересчёт итогов, экспорт в Excel -->
<script src="js/sku_table.js" defer></script>