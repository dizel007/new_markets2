<?php

require_once "../connect_db.php";

require_once "../mp_functions/ozon_api_functions.php";

require_once "../pdo_functions/pdo_functions.php";

require '../vendor/autoload.php';

require_once "function_report/razbor_type_item.php";
require_once "function_report/razbor_type_posting_number.php";
require_once "function_report/get_sebestoimost.php";
require_once "function_report/get_balance.php";
require_once "function_report/get_prodazhi.php";
require_once "function_report/get_token_by_shop.php";


/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
 *********************************************************************************************************/
// echo  "ПРОШЛИ КОННЕКТ<br>";
if (!isset($_GET['ozon_shop']) || $_GET['ozon_shop'] === '') {
    http_response_code(400);
    exit('Параметр ozon_shop обязателен');
} else {
$shop_name = $_GET['ozon_shop'];
}
// получаем токен запрашиваемого магазина
[$token , $client_id] = get_token_AND_idclient ($arr_tokens, $shop_name);


 //*********************************************************************************************************/
$arr_article_products = get_sebestoimost_tovarov($token, $client_id);
//******************************************************************************************************
//******************************************************************************************************
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


//// Отрисовываем форму вводы ДАТ
echo <<<HTML

<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="css/form_dates.css?v=<?php echo filemtime('css/form_dates.css'); ?>">
    <link rel="stylesheet" href="css/sku_table.css?v=<?php echo filemtime('css/sku_table.css'); ?>">




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

<script src="js/script.js?v= <?php echo filemtime('js/script.js'); ?>"></script>

HTML;

if (!isset($_GET['dateFrom'], $_GET['dateTo'])) {
    die('');
}

// =================================================================================
//===============   запрашиваем баланс с ОЗОНа ========================================
// =================================================================================

$ozon_array_balance = get_ozon_balance ($token, $client_id,  $date_from, $date_to);


/******************************************************************************
 *  ЗАПРОС фин отчета по проанным товарам  С ОЗОНА (ВСЕ ДАТЫ)
 ******************************************************************************/

//  Запрос товаров проданных в РФ
$file_name_ozon_mainSell = '_cache/'.$client_id . "_main_data" . ".json";

get_data_sell_in_mainSEll ($token, $client_id, $date_from, $date_to, $file_name_ozon_mainSell);

$data_by_days = json_decode(file_get_contents($file_name_ozon_mainSell), true);

//  Запрос инстранных товаров
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
$type_fees = json_decode(file_get_contents('spravochnik/types_spend.json'), true);
//////////////////////////////////////////////////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////////////////////////////////////////////////
//  Разбираем массив по категориям затрат
//////////////////////////////////////////////////////////////////////////////////////////////////////
if (isset ($arr_by_accrued_category['ITEM'])) {
    $sum_array = razbor_ITEM_category($sum_array, $arr_by_accrued_category['ITEM'], $type_fees);
}
if (isset ($arr_by_accrued_category['NON_ITEM'])) {
$sum_array = razbor_NON_ITEM_category($sum_array, $arr_by_accrued_category['NON_ITEM'], $type_fees);
}
if (isset ($arr_by_accrued_category['POSTING'])) {
$sum_array = razbor_POSTING_category($sum_array, $arr_by_accrued_category['POSTING'], $type_fees);
}
foreach ($sum_array as $item_sum) {
    foreach ($item_sum as $key_sum=>$sum) {
        @$sum_array_al_data[$key_sum] += $sum;
   }
}

//===============================================================================================
// цепляем иностарнные продажи *************************
//===============================================================================================
$ino_summa = 0;
if (isset ($arr_sell_v_strani_EAES['products'])) {
foreach ($arr_sell_v_strani_EAES['products'] as $ino_sku=>$ino_tovar) {
    foreach ($sum_array as $sku=>&$tovar) {
        if ($ino_sku == $sku) {
            $tovar['ino_prodazhi'] = $ino_tovar['amount'];
            $ino_summa += $ino_tovar['amount'];
            break 1;
        }
    }
}

}
$sum_array_to_redakt = $sum_array;

/******************************************************************************************
 * выводим на печать нашу таблицу
 * весь вывод сделал в отдельном файле
 * 
 ****************************************************************************************/
// echo "<pre>";
// print_r($ozon_array_balance );

require_once "print_info/print_sku_table.php";

die();
