<?php
$offset = "../";
require_once $offset . "connect_db.php";
require_once $offset . "mp_functions/ozon_api_functions.php";

/*****************************************************************
 * Получаем и систематизируем возвраты на озон
 * 
 * Вычитываем  все возвраты и формируем массив который показывает,
 *  какой клиент сколько раз вернул товары
 **************************************************************/



echo "Получаем и систематизируем возвраты на озон"."<br>";

   // озон ИП зел
    $client_id_ozon_ip = $arr_tokens['ozon_anmaks']['id_market'];
    $token_ozon_ip = $arr_tokens['ozon_anmaks']['token'];

    // $client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
    // $token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];


// получаем
$ozon_dop_url = "v1/returns/list";

$send_data = array(
    "filter"=>array(
            "logistic_return_date" =>  array(
            "time_from"  => "2026-04-01T14:15:22Z",
            "time_to"    => "2026-05-31T14:15:22Z"
),
),
"limit" => 500,
"last_id" => 0
);

$send_data = json_encode($send_data);

$result_array_temp = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $send_data, $ozon_dop_url ) ;
echo "<pre>";
// print_r($result_array_temp);

// die();


foreach ($result_array_temp['returns'] as $return_items) {

$parts = explode('-', $return_items['order_number']);
$firstPart = $parts[0];

// $arr_all_returns[$firstPart][$return_items['order_number']]['order_number'] = $return_items['order_number'];
$arr_all_returns[$firstPart][$return_items['order_number']]['product'][] = $return_items['product']['offer_id'];
$arr_all_returns[$firstPart][$return_items['order_number']]['price'] = @$arr_all_returns[$firstPart][$return_items['order_number']]['price'] + $return_items['product']['price']['price'];


}

foreach ($arr_all_returns as $bad_costomer => $items){
    foreach ($items as $item){

        $jjj_costomer[$bad_costomer] = count($item['product']);

    }
}
print_r($arr_all_returns);

arsort($jjj_costomer);
echo "<br>";
echo  "Покупателей с возвратами = ".count($jjj_costomer);
echo "<br>";
print_r($jjj_costomer);
