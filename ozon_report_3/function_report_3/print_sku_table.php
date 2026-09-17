<?php 

// echo "<pre>";
// print_r($arr_real_ozon_data);
// die();

/**
 * Достаёт значение из массива и удаляет ключ.
 */
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

/**
 * Оборачивает число в <span> с классом positive / negative в зависимости от знака.
 * Без разделителей разрядов — для количеств (шт).
 */
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

/**
 * Форматирует денежную сумму: разделитель разрядов — пробел,
 * целые без копеек, дробные — 2 знака после запятой.
 * Оборачивает в <span> по знаку.
 */
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
    <th class="sortable" data-sort="sebestoimost" data-order="none" title="Кликните для сортировки">Себестсть<br>(руб) <span class="sort-icon">↕</span></th>
    <th class="sortable" data-sort="profit" data-order="none" title="Кликните для сортировки">Прибыль<br>(руб) <span class="sort-icon">↕</span></th>
    
</tr>
</thead>
HTML;

echo "<tbody id=\"filterable-table-body\">";

$url_encoded = 'ssss';

// Денежные суммы
$sum_seller_price                    = 0;
$sum_commission                      = 0;
$sum_logistika                       = 0;
$sum_price_without_comm_and_logist   = 0;
$sum_equairing                       = 0;
$sum_service                         = 0;
$sum_no_sku_trati_raschet            = 0;
$sum_viplata_na_rs                   = 0;
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

    // Считаем всё, что осталось из отчёта (СЕРВИСЫ)
    $service = 0;
    foreach ($item_for_print as $key_2 => $serv) {
        $service += getUnitNumber($item_for_print, $key_2);
    }
    unset($sum_array_to_redakt[$sku_ozon]);

    $price_without_comm_and_logist = $seller_price + $commission + $logistika + $equairing + $service;

    ////////////////////////////////////////////////////////////////////////////
    // распределение суммы NO_SKU (реклама, штрафы и прочее)
    $summa_raspredelenia_no_sku_trat = round(($seller_price / ($all_sum_seller * 0.01) * $one_procent_no_sku_trati), 2);

    // получаем сумму за вычетом NO_SKU трат
    $viplata_na_rs = round($price_without_comm_and_logist + $summa_raspredelenia_no_sku_trat, 2);

    $article_sebestoimost = $sebestoimost * $count_for_raschet;

    $pribil = round($viplata_na_rs - $article_sebestoimost, 2);

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
        . " data-sebestoimost=\"" . (float)$article_sebestoimost . "\""
        . " data-profit=\"" . (float)$pribil . "\""
        . " data-search=\"" . $search_blob_safe . "\""
        . ">";

    // Название товара — обрезается до 2 строк, полное в title
    $name_ozon_safe = htmlspecialchars((string)$name_ozon, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo "<td><a class=\"tovar_name\" href=\"\" target=\"_blank\" title=\"{$name_ozon_safe}\">{$name_ozon_safe}</a></td>";

    // Артикул и СКУ
    echo "<td>"
        . "<a href=\"../ozon_report_po_article/index_ozon_razbor_article.php?data=" . $url_encoded . "\" target=\"_blank\">"
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
    echo "<td data-total-key=\"sebestoimost\"><b>"   . moneyNum($sum_article_sebestoimost) . "</b></td>";
    echo "<td data-total-key=\"profit\"><b>"         . moneyNum($sum_pribil) . "</b></td>";
echo "</tr>";

echo <<<HTML
</tbody>
</table>

</div>

<script>
/**
 * 1) Клиентская сортировка таблицы по столбцам с классом .sortable.
 * 2) Поиск по столбцу «Наименование» (название + SKU + артикул).
 * 3) Пересчёт строки ИТОГО (включая количества) после фильтрации.
 */
(function () {
    var table = document.getElementById('ancor_table');
    if (!table) return;

    var tbody = document.getElementById('filterable-table-body');
    if (!tbody) return;

    var headers = table.querySelectorAll('th.sortable');
    var searchInput = document.getElementById('nameSearch');

    /* ---------- Форматирование денег (совпадает с PHP moneyNum) ---------- */
    function formatMoney(num) {
        var rounded = Math.round(num * 100) / 100;
        var isInt = Math.abs(rounded - Math.round(rounded)) < 0.001;
        var s = isInt ? String(Math.round(rounded)) : rounded.toFixed(2);

        var parts = s.split('.');
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        return parts.join('.');
    }

    function moneySpan(num) {
        var formatted = formatMoney(num);
        var cls = num > 0 ? 'positive' : (num < 0 ? 'negative' : 'zero');
        return '<span class="' + cls + '">' + formatted + '</span>';
    }

    /* ---------- Форматирование количеств (шт): просто с разрядами, без копеек ---------- */
    function countSpan(num) {
        var rounded = Math.round(num);
        var s = String(rounded).replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
        var cls = rounded > 0 ? 'positive' : (rounded < 0 ? 'negative' : 'zero');
        return '<span class="' + cls + '">' + s + '</span>';
    }

    /* ---------- Пересчёт ИТОГО по видимым строкам ---------- */
    function recalcTotals() {
        var totalCells = tbody.querySelectorAll('.total-row [data-total-key]');
        if (!totalCells.length) return;

        // Обнуляем суммы по всем ключам
        var totals = {};
        totalCells.forEach(function (cell) {
            totals[cell.dataset.totalKey] = 0;
        });

        // Складываем значения только по видимым строкам (не total-row)
        var rows = tbody.querySelectorAll('tr:not(.total-row)');
        rows.forEach(function (row) {
            if (row.style.display === 'none') return;
            totalCells.forEach(function (cell) {
                var key = cell.dataset.totalKey;
                var v = parseFloat(row.getAttribute('data-' + key));
                if (!isNaN(v)) totals[key] += v;
            });
        });

        // Обновляем содержимое ячеек итоговой строки с учётом типа
        totalCells.forEach(function (cell) {
            var key  = cell.dataset.totalKey;
            var type = cell.dataset.totalType || 'money';
            var html = (type === 'count') ? countSpan(totals[key]) : moneySpan(totals[key]);
            cell.innerHTML = '<b>' + html + '</b>';
        });
    }

    /* ---------- Сортировка ---------- */
    headers.forEach(function (th) {
        th.addEventListener('click', function () {
            var key      = th.dataset.sort;
            var attrName = 'data-' + key;
            var current  = th.dataset.order || 'none';
            var next     = (current === 'desc') ? 'asc' : 'desc';

            var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'))
                            .filter(function (r) { return !r.classList.contains('total-row'); });

            rows.sort(function (a, b) {
                var av = parseFloat(a.getAttribute(attrName));
                var bv = parseFloat(b.getAttribute(attrName));
                if (isNaN(av)) av = 0;
                if (isNaN(bv)) bv = 0;
                return (next === 'asc') ? (av - bv) : (bv - av);
            });

            var totalRow = tbody.querySelector('tr.total-row');
            var frag = document.createDocumentFragment();
            rows.forEach(function (r) { frag.appendChild(r); });
            if (totalRow) frag.appendChild(totalRow);
            tbody.appendChild(frag);

            headers.forEach(function (other) {
                var icon = other.querySelector('.sort-icon');
                if (other === th) {
                    other.dataset.order = next;
                    if (icon) icon.textContent = (next === 'asc') ? '▲' : '▼';
                } else {
                    other.dataset.order = 'none';
                    if (icon) icon.textContent = '↕';
                }
            });

            recalcTotals();
        });
    });

    /* ---------- Поиск по названию / SKU / артикулу ---------- */
    if (searchInput) {
        searchInput.addEventListener('click', function (e) { e.stopPropagation(); });

        searchInput.addEventListener('input', function () {
            var q = this.value.toLowerCase().trim();
            var rows = tbody.querySelectorAll('tr');

            rows.forEach(function (r) {
                if (r.classList.contains('total-row')) return;

                if (q === '') {
                    r.style.display = '';
                } else {
                    var hay = r.getAttribute('data-search') || '';
                    r.style.display = (hay.indexOf(q) !== -1) ? '' : 'none';
                }
            });

            // Пересчитываем ИТОГО по видимым строкам
            recalcTotals();
        });
    }
})();
</script>
HTML;