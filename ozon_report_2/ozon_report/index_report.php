<?php

require_once "../../connect_db.php";

require_once "../../mp_functions/ozon_api_functions.php";

require_once "../../pdo_functions/pdo_functions.php";


/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/
// echo  "ПРОШЛИ КОННЕКТ<br>";

$shop_name = $_GET['ozon_shop'];

if ($shop_name == 'ozon_anmaks') {
     // ОЗОН АНМКАС
    $token  = $arr_tokens['ozon_anmaks']['token'];
    $client_id = $arr_tokens['ozon_anmaks']['id_market'];

} elseif ($shop_name == 'ozon_ip_zel') {
    // озон ИП зел
    $client_id = $arr_tokens['ozon_ip_zel']['id_market'];
    $token = $arr_tokens['ozon_ip_zel']['token'];
}
  

$priznak_date = 1; 
// Настраиваем дату начала отпроса
if (isset($_GET['dateFrom'])) { $date_from = $_GET['dateFrom'];} 
else {
    $date = date('Y-m-d');
    $day = '01';
    $month = date('m', strtotime($date));
    $year = date('Y', strtotime($date));
    $date_from = $year.'-'.$month.'-'.$day;
    $priznak_date = 0; 
    
}

// Настраиваем дату окончания
if (isset($_GET['dateTo'])) {$date_to = $_GET['dateTo'];} else {$date_to = date('Y-m-d');}

// Настраиваем тип сортировки если он есть 
if (isset($_GET['type_sort'])) {

    $type_sort = base64_decode(($_GET['type_sort']));

    // формируем Query строку без этого парамаетра, чтобы он постоянно не прилипал к строке при кажой сортировке
    // $queryString = http_build_query($params);

} else {
   $type_sort = ''; 
}

$queryString  =  "ozon_shop=".$shop_name."&dateFrom=".$date_from."&dateTo=".$date_to;
// ."&type_sort=".$type_sort;
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
            <input hidden type="text" id = "ozon_shop" value="$shop_name">
        </form>
       </div>
    </div>
 <script src="css/script.js" type="text/javascript"></script>

HTML;

// die();

if ($priznak_date == 0)  {die ('');} 

// формируем название папки и файла
$path = "../../!cache";
$file_name_ozon = $path ."/".$client_id."_main_data".".json";
$file_name_ozon_inostran_prodazhi = $path ."/".$client_id."_inostran_prodazhi".".json";
$file_name_ozon_small = $client_id."_";


// Непосредственный запрос данных с озона и сохранение данных в файл
//*********************************************************************************************************************** */
// если вернулись сюда с параметров изменения типа сортировки массива, то новые данные не запрашиваем
if ($type_sort == '') {
   
        $prod_array = query_report_data_from_api_ozon($token, $client_id, $date_from, $date_to);
        file_put_contents($file_name_ozon,json_encode($prod_array, JSON_UNESCAPED_UNICODE));
        if ($prod_array === false) {die('НЕТ ДАННЫХ для выдачи');} // Если нам ничего ОЗон не вернул
}
//*********************************************************************************************************************** */


// Тянем данные по товаром проданным в страны ЕАЭС
$arr_data_sell_in_srtani_eaes_temp = get_data_sell_in_srtani_eaes($token, $client_id, $date_from, $date_to, $file_name_ozon_inostran_prodazhi);
if (isset($arr_data_sell_in_srtani_eaes_temp['products'])) {
$arr_data_sell_in_srtani_eaes = $arr_data_sell_in_srtani_eaes_temp['products'];
}


$summa_prodannogo_v_strani_EAES = $arr_data_sell_in_srtani_eaes_temp['summa_prodannogo'];


// echo "<pre>";
// print_r($arr_data_sell_in_srtani_eaes);
// die();
// Берем из БД себестоимость и желаемую цену 
require_once "get_sebestoimost.php";

// берем данные из файла
$prod_array = json_decode(file_get_contents($file_name_ozon) ,true);

// echo "<pre>";
// print_r ($prod_array);

require_once "razbor_dannih.php";

die();

function query_report_data_from_api_ozon($token, $client_id, $date_from, $date_to) {
    // echo "Период запроса с ($date_from) по  ($date_to)<br>";
$ozon_link = 'v3/finance/transaction/list';
$send_data = array(
    "filter" => array(
        "date" => array (
            "from" => $date_from."T00:00:00.000Z",
            "to"=> $date_to."T00:00:00.000Z"
    ),
        "operation_type" => [],
        "posting_number" => "",
        "transaction_type" => "all"
    ),
    "page" => 1,
    "page_size" => 1000
);


$send_data = json_encode($send_data);
$res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_link );

// если ошибка при обмене то выводим е
if (isset($res['message'])) {
    // echo "<pre>";
    print_r($res);
    die('ОШИБКА ПРИ ЗАПРОСЕ');
}

$page_count = $res['result']['page_count'];
// $row_count = $res['result']['row_count'];
for ($i=1; $i <=$page_count; $i ++) {
    $send_data = array(
        "filter" => array(
            "date" => array (
                "from" => $date_from."T00:00:00.000Z",
                "to"=> $date_to."T00:00:00.000Z"
        ),
            "operation_type" => [],
            "posting_number" => "",
            "transaction_type" => "all"
        ),
        "page" => $i,
        "page_size" => 1000
    );
    $send_data = json_encode($send_data);
    $res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_link );
    $prod_array[] = $res['result']['operations'];

}
if (isset($prod_array)) {return  $prod_array;}
else {return false;}
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