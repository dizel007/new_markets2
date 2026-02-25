<?php
$offset = "";
require_once $offset . "connect_db.php";
require_once $offset . "mp_functions/ozon_api_functions.php";

/*****************************************************************
 * Получаем и систематизируем возвраты на озон
 **************************************************************/



echo "Получаем и систематизируем возвраты на озон"."<br>";

   // озон ИП зел
    $client_id_ozon_ip = $arr_tokens['ozon_anmaks']['id_market'];
    $token_ozon_ip = $arr_tokens['ozon_anmaks']['token'];
// получаем
$ozon_dop_url = "v1/returns/list";

$send_data = array(
    "filter"=>array(
            "logistic_return_date" =>  array(
            "time_from"  => "2025-03-01T14:15:22Z",
            "time_to"    => "2025-10-31T14:15:22Z"
),
),
"limit" => 500,
"last_id" => 0
);

$send_data = json_encode($send_data);

$result_array_temp = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $send_data, $ozon_dop_url ) ;
echo "<pre>";
print_r($result_array_temp['returns'][10]);

// die();


foreach ($result_array_temp['returns'] as $return_items) {

$parts = explode('-', $return_items['order_number']);
$firstPart = $parts[0];

// $arr_all_returns[$firstPart][$return_items['order_number']]['order_number'] = $return_items['order_number'];
$arr_all_returns[$firstPart][$return_items['order_number']]['product'][] = $return_items['product']['offer_id'];
$arr_all_returns[$firstPart][$return_items['order_number']]['price'] = @$arr_all_returns[$firstPart][$return_items['order_number']]['price'] + $return_items['product']['price']['price'];


}

foreach ($arr_all_returns as $item){
if (count($item) > 2) {
    $ggg[] = $item;
}
}
print_r($ggg);
