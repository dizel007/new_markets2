<?php
$offset = "";
require_once $offset . "connect_db.php";
require_once $offset . "mp_functions/ozon_api_functions.php";

/*****************************************************************
 * Пробуем составить аналитику товаров заказаннных через ФБО Географию товаров
 **************************************************************/
echo "Пробуем составить аналитику товаров заказаннных через ФБО Географию товаров"."<br>";

   // озон ИП зел
    $client_id_ozon_ip = $arr_tokens['ozon_anmaks']['id_market'];
    $token_ozon_ip = $arr_tokens['ozon_anmaks']['token'];
// получаем
$ozon_dop_url = "v1/cluster/list";
$send_data='{
"cluster_type": "CLUSTER_TYPE_OZON"
}';

$result_array_temp = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $send_data, $ozon_dop_url ) ;
echo "<pre>";
print_r($result_array_temp['clusters'][0]['name']);

// die();

$since_date ="2025-01-01";
$to_date ="2025-12-31";
$offset = 0;

$ozon_dop_url = "v2/posting/fbo/list";
$send_data = array(
"dir" => "ASC",
"filter" =>  array(
    "since"  => $since_date."T00:00:00.000Z",
    "status" => "delivered",
    "to"     => $to_date."T10:44:12.828Z"
),
"limit"    => 1000,
"offset"   => $offset,
"translit" => true,
"with"     => array (
    "analytics_data" => true,
    "financial_data" => true,
    "legal_info"     => false
)
);
$send_data = json_encode($send_data);
$result_array_temp = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $send_data, $ozon_dop_url ) ;
echo "dddddddd_1 = ".count($result_array_temp['result']). "<br>";
$result_array[] = $result_array_temp['result'];

while (count($result_array_temp['result']) >=1000) {

$offset = $offset + 1000;

$send_data = array(
"dir" => "ASC",
"filter" =>  array(
    "since"  => $since_date."T00:00:00.000Z",
    "status" => "delivered",
    "to"     => $to_date."T10:44:12.828Z"
),
"limit"    => 1000,
"offset"   => $offset,
"translit" => true,
"with"     => array (
    "analytics_data" => true,
    "financial_data" => true,
    "legal_info"     => false
)
);
$send_data = json_encode($send_data);
$result_array_temp = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $send_data, $ozon_dop_url ) ;

// print_r($result_array['result'][0]);
$result_array[] = $result_array_temp['result'];

echo "dddddddd_cycle = ".count($result_array_temp['result']). "<br>";
sleep(1);
} ;

foreach ($result_array as $temp_arr_sum) {
    foreach ($temp_arr_sum as $orders) {
        $arrr_all_sell[] = $orders ;
    }

}
echo " <br>количество отправлений = ".(count($arrr_all_sell))."<br>";
// die();
// Перебираем массив заказов ФБО и формируем удобный нам

    foreach ($arrr_all_sell as $order_fbo) {
    $arr_for_print[$order_fbo['posting_number']]['posting_number'] =  $order_fbo['posting_number']; // номер заказа
    $arr_for_print[$order_fbo['posting_number']]['status'] =  $order_fbo['status']; // статус
    $arr_for_print[$order_fbo['posting_number']]['created_at'] =  $order_fbo['created_at']; // дата созжания
    $arr_for_print[$order_fbo['posting_number']]['offer_id'] =  $order_fbo['products'][0]['offer_id']; //артикул
    $arr_for_print[$order_fbo['posting_number']]['sku'] =  $order_fbo['products'][0]['sku']; // ску
    // аналитические данные
    $arr_for_print[$order_fbo['posting_number']]['city'] =  $order_fbo['analytics_data']['city'];
    $arr_for_print[$order_fbo['posting_number']]['warehouse_name'] =  $order_fbo['analytics_data']['warehouse_name'];
    $arr_for_print[$order_fbo['posting_number']]['cluster_from'] =  $order_fbo['financial_data']['cluster_from'];
    $arr_for_print[$order_fbo['posting_number']]['cluster_to'] =   $order_fbo['financial_data']['cluster_to'];
    }

unset($order_fbo);
echo " <br>ggg = ".(count($arr_for_print))."<br>";
$summa= 0;
foreach ($arr_for_print as $order_fbo) {
    $arr_cluster_to_sort_article[$order_fbo['cluster_to']][$order_fbo['offer_id']] = @$arr_cluster_to_sort_article[$order_fbo['cluster_to']][$order_fbo['offer_id']] + 1;
    $arr_cluster_sum[$order_fbo['cluster_to']] = @$arr_cluster_sum[$order_fbo['cluster_to']] + 1;
    $summa++;
}


echo "<pre>";
arsort($arr_cluster_sum);

print_r($arr_cluster_sum);
echo "dddddddddd SUMMA = ".$summa;

print_r($arr_cluster_to_sort_article);

die();