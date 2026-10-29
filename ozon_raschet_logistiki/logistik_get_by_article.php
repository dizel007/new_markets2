<?php
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";
// require_once "functions_orders_fbo_fbs.php";

$shop_name = $_GET['shopname'];

// доставем всю номенсклатуру
// $arr_all_nomenklatura = select_active_nomenklaturu($pdo);
// foreach ($arr_all_nomenklatura as $zzz) {
//    $arr_poriadkovii_number[mb_strtolower($zzz['main_article_1c'])] = $zzz['number_in_spisok'];
// }
//// Проверяем и есть нет папки, то создаем ее, для каждого пользователя своя папка в !cache
 
$dir_for_cache = "../!cache/".$userdata['user_login']."/ozon_fbs_fbo_clusters/";
$filename_fbs_orders = $dir_for_cache."json_fbs_Orders_".$shop_name.".json";
$filename_fbo_orders = $dir_for_cache."json_fbo_Orders_".$shop_name.".json";
// echo "<pre>";




$article = mb_strtolower($_GET['article']);




$delivery_schema = $_GET['delivery_schema'] ?? '';

$schemaLower = strtolower($delivery_schema);


// print_r($delivery_schema);

if ($schemaLower === 'fbs') {
    // FBS
    $ordersFBS = json_decode(file_get_contents($filename_fbs_orders),true);
    // выбиираем товары только с нашим артикулом из ФБС
foreach ($ordersFBS as $itemFbs) {
   foreach ($itemFbs['products'] as $product) {
         $offer_id = mb_strtolower($product['offer_id']);
         if ($article == $offer_id) {
            $work_article_array[] = $itemFbs;
            break;
         }
   }
}
unset($ordersFBS);
unset($itemFbs);
unset($product);


} elseif ($schemaLower === 'fbo') {
       // FBO
    $ordersFB0 = json_decode(file_get_contents($filename_fbo_orders),true);
// выбиираем товары только с нашим артикулом из ФБO
foreach ($ordersFB0 as $itemFbo) {
   foreach ($itemFbo['products'] as $product) {
         $offer_id = mb_strtolower($product['offer_id']);
         if ($article == $offer_id) {
            $work_article_array[] = $itemFbo;
            break;
         }
   }
}
unset($ordersFB0);
unset($itemFbo);
unset($product);



} else {
    // Нет схемы
$ordersFBS = json_decode(file_get_contents($filename_fbs_orders),true);
$ordersFB0 = json_decode(file_get_contents($filename_fbo_orders),true);
// выбиираем товары только с нашим артикулом из ФБС
foreach ($ordersFBS as $itemFbs) {
   foreach ($itemFbs['products'] as $product) {
         $offer_id = mb_strtolower($product['offer_id']);
         if ($article == $offer_id) {
            $work_article_array[] = $itemFbs;
            break;
         }
   }
}
unset($ordersFBS);
unset($itemFbs);
unset($product);

// print_r($ordersFBS);

// выбиираем товары только с нашим артикулом из ФБO
foreach ($ordersFB0 as $itemFbo) {
   foreach ($itemFbo['products'] as $product) {
         $offer_id = mb_strtolower($product['offer_id']);
         if ($article == $offer_id) {
            $work_article_array[] = $itemFbo;
            break;
         }
   }
}
unset($ordersFB0);
unset($itemFbo);
unset($product);

}






////////////////////////////////////////
// ищем все статусы отправления
////////////////////////////////////////////////////////////
// foreach ($work_article_array as $t_item) {
//   $statuses[$t_item['substatus']]  = $t_item['substatus'];
// }


/******************************************************************************************************************
 * формируем массив для вывода на экран
 *******************************************************************************************************************/
// print_r($work_article_array);
$i = 0;
if (isset($work_article_array)) {
foreach ($work_article_array as $items) {

$array_for_print[$i]['posting_number'] = $items['posting_number'];
$array_for_print[$i]['in_process_at'] = $items['in_process_at'];

$array_for_print[$i]['order_number'] = $items['order_number'];
$array_for_print[$i]['status'] = $items['status'];
$array_for_print[$i]['substatus'] = $items['substatus'];

if (isset($items['delivery_schema'])) {
   $array_for_print[$i]['delivery_schema'] = $items['delivery_schema'];
   $array_for_print[$i]['customer_price'] = $items['financial_data']['products'][0]['customer_price']['amount'];
   
} else {
   $array_for_print[$i]['delivery_schema'] = 'fbo';
   $array_for_print[$i]['customer_price'] = $items['financial_data']['products'][0]['payout'];
}
$array_for_print[$i]['offer_id'] = $items['products'][0]['offer_id'];
$array_for_print[$i]['name'] = $items['products'][0]['name'];
$array_for_print[$i]['sku'] = $items['products'][0]['sku'];
$array_for_print[$i]['quantity'] = $items['products'][0]['quantity'];
$array_for_print[$i]['price'] = $items['products'][0]['price']['amount'];

$array_for_print[$i]['cluster_from'] = $items['financial_data']['cluster_from'];
$array_for_print[$i]['cluster_to'] = $items['financial_data']['cluster_to'];




$i++;
}
}
if (isset($array_for_print)) {
// сортировка массива по дате заказа
   usort($array_for_print, function($a, $b) {
    return strcmp($a['in_process_at'], $b['in_process_at']);
});

// ============================================================
// 1. Читаем все фильтры из GET
// ============================================================

// Накопительный фильтр по "Откуда"
$cluster_from_filter = $_GET['cluster_from'] ?? [];
if (!is_array($cluster_from_filter)) {
    $cluster_from_filter = [$cluster_from_filter];
}
$cluster_from_filter = array_values(array_filter($cluster_from_filter, 'strlen'));

// Накопительный фильтр по "Куда"
$cluster_to_filter = $_GET['cluster_to'] ?? [];
if (!is_array($cluster_to_filter)) {
    $cluster_to_filter = [$cluster_to_filter];
}
$cluster_to_filter = array_values(array_filter($cluster_to_filter, 'strlen'));

// ============================================================
// 2. Собираем доступные варианты для каждого фильтра
//    с учётом ВСЕХ ДРУГИХ активных фильтров (каскадное сужение)
// ============================================================

// 2a. Доступные "Откуда" — из строк, прошедших ВСЕ фильтры, КРОМЕ "Откуда"
$all_clusters_from = [];
foreach ($array_for_print as $item) {
    // проверяем "Куда"
    if (!empty($cluster_to_filter)) {
        if (!in_array($item['cluster_to'] ?? '', $cluster_to_filter, true)) continue;
    }
    if (!empty($item['cluster_from'])) {
        $all_clusters_from[$item['cluster_from']] = $item['cluster_from'];
    }
}
ksort($all_clusters_from);

// 2b. Доступные "Куда" — из строк, прошедших ВСЕ фильтры, КРОМЕ "Куда"
$all_clusters_to = [];
foreach ($array_for_print as $item) {
    // проверяем "Откуда"
    if (!empty($cluster_from_filter)) {
        if (!in_array($item['cluster_from'] ?? '', $cluster_from_filter, true)) continue;
    }
    if (!empty($item['cluster_to'])) {
        $all_clusters_to[$item['cluster_to']] = $item['cluster_to'];
    }
}
ksort($all_clusters_to);

// ============================================================
// 3. Применяем фильтры к $array_for_print (для вывода таблицы)
// ============================================================

if (!empty($cluster_from_filter)) {
    $array_for_print = array_filter($array_for_print, function($item) use ($cluster_from_filter) {
        return in_array($item['cluster_from'] ?? '', $cluster_from_filter, true);
    });
}

if (!empty($cluster_to_filter)) {
    $array_for_print = array_filter($array_for_print, function($item) use ($cluster_to_filter) {
        return in_array($item['cluster_to'] ?? '', $cluster_to_filter, true);
    });
}

// ============================================================
// 4. Считаем итоговую строку по отфильтрованным заказам
// ============================================================
$total_sum    = 0;
$total_qty    = 0;
$total_orders = count($array_for_print);
$total_schemas = [];
$total_from   = [];
$total_to     = [];
$total_article = '';

foreach ($array_for_print as $item) {
    $qty   = (int)($item['quantity'] ?? 0);
    $price = (float)($item['price'] ?? 0);
    $total_sum += $price * $qty;
    $total_qty += $qty;

    if (!empty($item['delivery_schema'])) {
        $total_schemas[$item['delivery_schema']] = $item['delivery_schema'];
    }
    if (!empty($item['cluster_from'])) {
        $total_from[$item['cluster_from']] = $item['cluster_from'];
    }
    if (!empty($item['cluster_to'])) {
        $total_to[$item['cluster_to']] = $item['cluster_to'];
    }
}

// Артикул в итоговой строке — тот, по которому фильтровали
$total_article = $article;

// Схемы: если одна — выводим её, иначе — перечисляем через запятую
$schema_str = implode(', ', $total_schemas);

// Откуда/Куда: если один — выводим, иначе — показываем количество уникальных
$from_str = (count($total_from) === 1) ? reset($total_from) : ('разных: ' . count($total_from));
$to_str   = (count($total_to)   === 1) ? reset($total_to)   : ('разных: ' . count($total_to));

// ============================================================
// 5. Сводка по маршрутам "Откуда → Куда" с количеством
// ============================================================
$routes_summary = [];
$rowspans = [];
$from_totals = []; // Новый массив для общих сумм по кластерам "Откуда"

foreach ($array_for_print as $item) {
    $from = $item['cluster_from'] ?? '';
    $to   = $item['cluster_to']   ?? '';
    $qty  = (int)($item['quantity'] ?? 0);
    if ($from === '' || $to === '') continue;

    // Суммируем общий объем по "Откуда"
    if (!isset($from_totals[$from])) {
        $from_totals[$from] = 0;
    }
    $from_totals[$from] += $qty;

    // Формируем строки маршрутов
    $key = $from . '|||' . $to;
    if (!isset($routes_summary[$key])) {
        $routes_summary[$key] = [
            'from' => $from,
            'to'   => $to,
            'qty'  => 0,
        ];
    }
    $routes_summary[$key]['qty'] += $qty;
}

// сортируем по "Откуда", затем по "Куда"
usort($routes_summary, function($a, $b) {
    $cmp = strcmp($a['from'], $b['from']);
    return $cmp !== 0 ? $cmp : strcmp($a['to'], $b['to']);
});

// вычисляем rowspan для каждого уникального "Откуда"
$prev_from  = null;
$prev_index = -1;
foreach ($routes_summary as $idx => $row) {
    if ($row['from'] !== $prev_from) {
        $prev_from  = $row['from'];
        $prev_index = $idx;
        $rowspans[$idx] = 1;
    } else {
        $rowspans[$prev_index]++;
    }
}

// ============================================================
// 6. Сводка по маршрутам "Куда → Откуда" с количеством (обратная)
// ============================================================
$reverse_routes_summary = [];
$reverse_rowspans = [];

foreach ($array_for_print as $item) {
    $to   = $item['cluster_to']   ?? '';
    $from = $item['cluster_from'] ?? '';
    $qty  = (int)($item['quantity'] ?? 0);
    if ($to === '' || $from === '') continue;

    $key = $to . '|||' . $from;
    if (!isset($reverse_routes_summary[$key])) {
        $reverse_routes_summary[$key] = [
            'to'   => $to,
            'from' => $from,
            'qty'  => 0,
        ];
    }
    $reverse_routes_summary[$key]['qty'] += $qty;
}

// сортируем по "Куда", затем по "Откуда"
usort($reverse_routes_summary, function($a, $b) {
    $cmp = strcmp($a['to'], $b['to']);
    return $cmp !== 0 ? $cmp : strcmp($a['from'], $b['from']);
});

// вычисляем rowspan для каждого уникального "Куда"
$prev_to    = null;
$prev_index = -1;
foreach ($reverse_routes_summary as $idx => $row) {
    if ($row['to'] !== $prev_to) {
        $prev_to    = $row['to'];
        $prev_index = $idx;
        $reverse_rowspans[$idx] = 1;
    } else {
        $reverse_rowspans[$prev_index]++;
    }
}

}
// echo "<pre>";
// print_r($array_for_print);

// die();



?>

<link rel="stylesheet" href="css/logistik_data.css">


<!-- Блок фильтрации по схеме доставки -->
<div class="filter-buttons">
    <?php
    // Определяем активную схему
    $current_schema = $_GET['delivery_schema'] ?? '';
    $base_url = "?shopname=" . urlencode($shop_name) . "&article=" . urlencode($article);
    // сохраняем текущие фильтры при переключении схемы
    if (!empty($cluster_from_filter)) {
        foreach ($cluster_from_filter as $cf) {
            $base_url .= "&cluster_from[]=" . urlencode($cf);
        }
    }
    if (!empty($cluster_to_filter)) {
        foreach ($cluster_to_filter as $ct) {
            $base_url .= "&cluster_to[]=" . urlencode($ct);
        }
    }
    ?>
    <a href="<?= $base_url ?>&delivery_schema=fbs" 
       class="btn-filter <?= ($current_schema === 'fbs') ? 'active' : '' ?>">FBS</a>
    <a href="<?= $base_url ?>&delivery_schema=fbo" 
       class="btn-filter <?= ($current_schema === 'fbo') ? 'active' : '' ?>">FBO</a>
    <a href="<?= $base_url ?>" 
       class="btn-filter <?= ($current_schema === '') ? 'active' : '' ?>">ВСЕ</a>
</div>

<?php if (!isset($array_for_print)) {

echo "<div class=\"filter-buttons\">";
    
    echo "<p>нет данных для вывода</p>";
echo "</div>";

   die('');
}
 ?>


<!-- Блок фильтрации по Откуда и Куда -->
<div class="filter-status">
    <form method="get" action="" style="display:flex; align-items:center; gap:10px; flex-wrap:wrap; background:transparent; border:none; padding:0; margin:0;">
        <!-- скрытые параметры, чтобы сохранить текущие фильтры -->
        <input type="hidden" name="shopname" value="<?= htmlspecialchars($shop_name) ?>">
        <input type="hidden" name="article" value="<?= htmlspecialchars($article) ?>">
        <input type="hidden" name="delivery_schema" value="<?= htmlspecialchars($delivery_schema) ?>">

        <!-- Фильтр Откуда (накопительный, только доступные варианты) -->
        <label for="cluster_from"><strong>Откуда:</strong></label>
        <select name="cluster_from[]" id="cluster_from" multiple size="1"
                onchange="this.form.submit()"
                title="Ctrl/Cmd + клик — выбрать несколько"
                style="min-width:180px; padding:8px 14px; border-radius:8px; border:1px solid #d0d5dd; font-size:14px; background:#fafbfc;">
            <?php if (empty($all_clusters_from)): ?>
                <option value="" disabled>нет доступных</option>
            <?php else: ?>
                <?php foreach ($all_clusters_from as $cf): ?>
                    <option value="<?= htmlspecialchars($cf) ?>"
                        <?= in_array($cf, $cluster_from_filter, true) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cf) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>

        <!-- Фильтр Куда (накопительный, только доступные варианты) -->
        <label for="cluster_to"><strong>Куда:</strong></label>
        <select name="cluster_to[]" id="cluster_to" multiple size="1"
                onchange="this.form.submit()"
                title="Ctrl/Cmd + клик — выбрать несколько"
                style="min-width:180px; padding:8px 14px; border-radius:8px; border:1px solid #d0d5dd; font-size:14px; background:#fafbfc;">
            <?php if (empty($all_clusters_to)): ?>
                <option value="" disabled>нет доступных</option>
            <?php else: ?>
                <?php foreach ($all_clusters_to as $ct): ?>
                    <option value="<?= htmlspecialchars($ct) ?>"
                        <?= in_array($ct, $cluster_to_filter, true) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($ct) ?>
                    </option>
                <?php endforeach; ?>
            <?php endif; ?>
        </select>

        <!-- Сброс всех фильтров, кроме схемы/магазина/артикула -->
        <a href="?shopname=<?= urlencode($shop_name) ?>&article=<?= urlencode($article) ?>&delivery_schema=<?= urlencode($delivery_schema) ?>"
           class="btn-filter" style="padding:8px 14px; border-radius:8px; border:1px solid #d0d5dd; text-decoration:none; color:#333; background:#fff;">
            Сбросить
        </a>
    </form>
</div>


<!-- Основная таблица — итоговая строка -->
<table class="sell_mp_table w90">
  <thead>
    <tr>
       <th>пп</th>
      <th>Артикул</th>
      <th>Схема</th>
      <th>Кол-во</th>
      <th>Откуда</th>
      <th>Куда</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <td>1</td>
      <td><?= htmlspecialchars($total_article ?? '') ?></td>
      <td><?= htmlspecialchars($schema_str ?? '') ?></td>
      <td><?= (int)$total_qty ?></td>
      <td><?= htmlspecialchars($from_str ?? '') ?></td>
      <td><?= htmlspecialchars($to_str ?? '') ?></td>
    </tr>
  </tbody>
</table>

<!-- Кнопка перехода к расчету стоимости логистики -->
<div class="action-button-container">
    <a href="../adminka/cost_claster_logistik/index_cost.php" class="btn-action" target="_blank">
        Перейти в расчет стоимость логистики
    </a>
</div>

<!-- Сводная таблица 1: Откуда → Куда (с добавленным итогом на 2-м месте) -->
<?php if (!empty($routes_summary)): ?>
<table class="route_summary_table has-totals">
  <thead>
    <tr>
      <th>Кластер отправления</th>
      <th>Всего из кластера</th>
      <th>Кластер назначения</th>
      <th>Количество</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($routes_summary as $idx => $row): ?>
      <tr>
        <?php if (isset($rowspans[$idx])): ?>
          <!-- Объединяем первую ячейку (Кластер отправления) -->
          <td rowspan="<?= (int)$rowspans[$idx] ?>">
            <?= htmlspecialchars($row['from']) ?>
          </td>
          
          <!-- Объединяем вторую ячейку (Всего из кластера) -->
          <td rowspan="<?= (int)$rowspans[$idx] ?>">
            <?= (int)$from_totals[$row['from']] ?>
          </td>
        <?php endif; ?>
        
        <!-- Название кластера назначения -->
        <td><?= htmlspecialchars($row['to']) ?></td>
        
        <!-- Количество по конкретному маршруту -->
        <td><?= (int)$row['qty'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php endif; ?>