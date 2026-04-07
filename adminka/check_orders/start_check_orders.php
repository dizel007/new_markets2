<?php
$offset = "../../";
require_once $offset . "connect_db.php";
require_once $offset ."mp_functions/ozon_api_functions.php";



// ОЗОН АНМКАС
$shop_name = 'ozon_anmaks';
    $client_id_ozon = $arr_tokens[$shop_name]['id_market'];
    $token_ozon = $arr_tokens[$shop_name]['token'];

$date_query_ozon = date('Y-m-d');
$dop_days_query = 15;

$res = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, "awaiting_packaging", $dop_days_query);

echo "<pre>";

print_r($res);
/* * ********
Выводим список заказов ОЗОН на определенную дату 
РАБОЧАЯ ВЕРСИЯ 
*** ожидает упаковки ****
*** */
function get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, $send_status, $dop_days_query){
    // awaiting_packaging - заказы ожидают сборку
    // awaiting_deliver   - заказы ожидают отгрузку 
// echo "<br>";
// echo $token."<br>";
// echo $client_id."<br>";
// echo $date_query_ozon."<br>";

$temp_dop_day = "+".$dop_days_query.' day';
$date_query_ozon_end = date('Y-m-d', strtotime($temp_dop_day, strtotime($date_query_ozon)));

                        
// echo "<br>";


$send_data=  array(
    "dir" => "ASC",
    "filter" => array(
    "cutoff_from" => $date_query_ozon."T00:00:00Z",
    "cutoff_to" =>   $date_query_ozon_end."T23:59:59Z",
    "delivery_method_id" => [ ],
    "provider_id" => [ ],
    "status" => $send_status,
    "warehouse_id" => [ ]
    ),
    "limit" => 1000,
    "offset" => 0,
    "with" => array(
    "analytics_data"  => true,
    "barcodes"  => true,
    "financial_data" => true,
    "translit" => true
    )
    );

 $send_data = json_encode($send_data, JSON_UNESCAPED_UNICODE)  ;  


$ozon_dop_url = "v3/posting/fbs/unfulfilled/list";


// запустили запрос на озона
$res = send_injection_on_ozon($token_ozon, $client_id_ozon, $send_data, $ozon_dop_url );
return $res;
}