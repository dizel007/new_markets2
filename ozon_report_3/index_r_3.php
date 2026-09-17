<?php

require_once "../connect_db.php";

require_once "../mp_functions/ozon_api_functions.php";

require_once "../pdo_functions/pdo_functions.php";

require '../vendor/autoload.php';

require_once "function_report_3/razbor_type_item.php";
require_once "function_report_3/razbor_type_posting_number.php";
require_once "function_report_3/get_sebestoimost.php";
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
 *********************************************************************************************************/
// echo  "ПРОШЛИ КОННЕКТ<br>";

$shop_name = $_GET['ozon_shop'];

// $shop_name = 'ozon_ip_zel';

if ($shop_name == 'ozon_anmaks') {
    // ОЗОН АНМКАС
    $token  = $arr_tokens['ozon_anmaks']['token'];
    $client_id = $arr_tokens['ozon_anmaks']['id_market'];
} elseif ($shop_name == 'ozon_ip_zel') {
    // озон ИП зел
    $client_id = $arr_tokens['ozon_ip_zel']['id_market'];
    $token = $arr_tokens['ozon_ip_zel']['token'];
}

/**********************************************************************************************************
 *     *************** Получаем список товаров с себестоимсотью
 *       [article] => 82401-з
         [product_id] => 875165455
         [sebestoimost] => 350
         [sku] => 1423019221
         [name] => Пластиковый садовый бордюр Кантри зеленый, длина 10 м, высота 110 мм
 *********************************************************************************************************/
$arr_article_products = get_sebestoimost_tovarov($token, $client_id);
//******************************************************************************************************
//******************************************************************************************************

$priznak_date = 1;
// Настраиваем дату начала отпроса
if (isset($_GET['dateFrom'])) {
    $date_from = $_GET['dateFrom'];
} else {
    $date = date('Y-m-d');
    $day = '01';
    $month = date('m', strtotime($date));
    $year = date('Y', strtotime($date));
    $date_from = $year . '-' . $month . '-' . $day;
    $priznak_date = 0;
}

// Настраиваем дату окончания
if (isset($_GET['dateTo'])) {
    $date_to = $_GET['dateTo'];
} else {
    $date_to = date('Y-m-d');
}

// Настраиваем тип сортировки если он есть
if (isset($_GET['type_sort'])) {
    $type_sort = base64_decode(($_GET['type_sort']));
} else {
    $type_sort = '';
}

$queryString  =  "ozon_shop=" . $shop_name . "&dateFrom=" . $date_from . "&dateTo=" . $date_to;

//// Отрисовываем форму вводы ДАТ
echo <<<HTML

<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/form_dates.css">
    <title>OZON - аналитика</title>
</head>
<body>
    <div class="table-container">
    <div class="form-container">
        <form id="dateForm" method="get">

            <div class="form-content">
                <div class="date-fields">
                    <div class="date-group">
                        <label for="startDate" class="date-label">Начальная дата</label>
                        <input type="date" id="startDate" class="date-input" value="$date_from" required >
                   </div>
                    <div class="date-group">
                        <label for="endDate" class="date-label">Конечная дата</label>
                        <input type="date" id="endDate" class="date-input" value="$date_to" required>
                  </div>
                </div>

                 <div class="date-group">
                        <button type="submit" class="submit-btn">Запросить данные</button>
                 </div>
             </div>
            <input hidden type="text" id="ozon_shop" value="$shop_name">

        </form>
       </div>
    </div>

 <script src="css/script.js" type="text/javascript"></script>

HTML;

if (!isset($_GET['dateFrom'], $_GET['dateTo'])) {
    die();
}

/******************************************************************************
 *  ЗАПРОС ДАННЫХ С ОЗОНА (ВСЕ ДАТЫ)
 ******************************************************************************/

$file_name_ozon_mainSell = '_cache/'.$client_id . "_main_data" . ".json";
get_data_sell_in_mainSEll ($token, $client_id, $date_from, $date_to, $file_name_ozon_mainSell);

$data_by_days = json_decode(file_get_contents($file_name_ozon_mainSell), true);

//====================================================
//  Запрос инстранных товаров
//====================================================
$file_name_ozon_inostran_prodazhi = '_cache/'.$client_id . "_ino_main_data" . ".json";
$arr_sell_v_strani_EAES = get_data_sell_in_srtani_eaes($token, $client_id, $date_from, $date_to, $file_name_ozon_inostran_prodazhi);

/******************************************************************************
 * КОНЕЦ запроса данных с озона (ВСЕ ДАТЫ)
 ******************************************************************************/

 /// Разбиваем массив по категориям расходов ///////////////////////////////
foreach ($data_by_days as $one_data) {
    $category[$one_data['accrued_category']] = $one_data['accrued_category'];
    if (isset($one_data['accrued_category'])) {
        $arr_by_accrued_category[$one_data['accrued_category']][] = $one_data;
    } else {
        $arr_other[] = $one_data;  // или любой другой массив
    }
}
////////////////////////////////////////////////////////////////////
if (isset($arr_other)) {
    echo "<br>НАйден массив без категории<br>";
}

//////////////////////////////////////////////////////////////////////////////////////////////////////
// берем из JSON файла статьи всех расходов с ID и названием и описанием
//////////////////////////////////////////////////////////////////////////////////////////////////////
$type_fees = json_decode(file_get_contents('types_spend.json'), true);
//////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////
//  Разбираем массив по категориям затрат
//////////////////////////////////////////////////////////////////////////////////////////////////////

$sum_array = razbor_ITEM_category($sum_array, $arr_by_accrued_category['ITEM'], $type_fees);
$sum_array = razbor_NON_ITEM_category($sum_array, $arr_by_accrued_category['NON_ITEM'], $type_fees);
$sum_array = razbor_POSTING_category($sum_array, $arr_by_accrued_category['POSTING'], $type_fees);

foreach ($sum_array as $item_sum) {
    foreach ($item_sum as $key_sum=>$sum) {
        @$sum_array_al_data[$key_sum] += $sum;
   }
}

//===============================================================================================
// цепляем иностарнные продажи *************************
//===============================================================================================
$ino_summa = 0;
foreach ($arr_sell_v_strani_EAES['products'] as $ino_sku=>$ino_tovar) {
    foreach ($sum_array as $sku=>&$tovar) {
        if ($ino_sku == $sku) {
            $tovar['ino_prodazhi'] = $ino_tovar['amount'];
            $ino_summa += $ino_tovar['amount'];
            break 1;
        }
    }
}

$sum_array_to_redakt = $sum_array;


require_once "function_report_3/print_sku_table.php";

die();

/*****************************************************************************************************************
 * Функция получения данных о продажах товаров (ежедневные начисления)
 *****************************************************************************************************************/
function get_data_sell_in_mainSEll ($token, $client_id, $date_from, $date_to, $file_name_ozon_mainSell) {

$ozon_link = 'v1/finance/accrual/by-day';
$last_id = '';

$current = strtotime($date_from);
$end_ts  = strtotime($date_to);

while ($current <= $end_ts) {
    $query_date = date('Y-m-d', $current);
    do {
        $send_data = array(
            "date" => $query_date,
            "last_id" => $last_id
        );
        $send_data = json_encode($send_data);
        $data_by_day_temp = send_injection_on_ozon($token, $client_id, $send_data, $ozon_link);
        $last_id = $data_by_day_temp['last_id'];

        foreach ($data_by_day_temp['accruals'] as $t_data) {
            $data_by_days[] = $t_data;
        }
    } while ($last_id  <> '');
    $current = strtotime('+1 day', $current);
}

// сырые данные складываем в файл
$json_types = json_encode($data_by_days, JSON_UNESCAPED_UNICODE);
file_put_contents($file_name_ozon_mainSell, $json_types);
}

/*****************************************************************************************************************
 * Функция получения данных о продажах товаров в страны ЕАЭС
 *****************************************************************************************************************/
function get_data_sell_in_srtani_eaes($token, $client_id, $date_from, $date_to, $file_name_ozon_inostran_prodazhi)
{
    $ozon_dop_url = "v1/finance/products/buyout";
    $send_data = '
            {
            "date_from": "' . $date_from . '",
            "date_to": "' . $date_to . '"
            }';
    $arr_sell_v_strani_EAES = send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url);

    if (isset($arr_sell_v_strani_EAES)) {
           file_put_contents($file_name_ozon_inostran_prodazhi,json_encode($arr_sell_v_strani_EAES, JSON_UNESCAPED_UNICODE));
    }

$summa_prodazh = 0;
    foreach ($arr_sell_v_strani_EAES['products'] as $items) {
        $new_arr_sell_v_strani_EAES['products'][$items['sku']]['sku'] = $items['sku'];
        $new_arr_sell_v_strani_EAES['products'][$items['sku']]['offer_id'] = $items['offer_id'];
        $new_arr_sell_v_strani_EAES['products'][$items['sku']]['amount'] = @$new_arr_sell_v_strani_EAES['products'][$items['sku']]['amount'] + $items['amount'];
        $new_arr_sell_v_strani_EAES['products'][$items['sku']]['quantity'] = @$new_arr_sell_v_strani_EAES['products'][$items['sku']]['quantity'] + $items['quantity'];
        $new_arr_sell_v_strani_EAES['products'][$items['sku']]['seller_price_per_instance'] = @$new_arr_sell_v_strani_EAES['products'][$items['sku']]['seller_price_per_instance'] + $items['seller_price_per_instance'];

        $summa_prodazh = $summa_prodazh + $items['amount'];
    }
 $new_arr_sell_v_strani_EAES['summa_prodannogo'] = $summa_prodazh;
 $new_arr_sell_v_strani_EAES['date_from'] = $date_from;
 $new_arr_sell_v_strani_EAES['date_to'] = $date_to;

    unset ($arr_sell_v_strani_EAES);

    return $new_arr_sell_v_strani_EAES;
}