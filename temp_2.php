<?php
$offset = "";
require_once $offset . "connect_db.php";
require_once $offset . "mp_functions/ozon_api_functions.php";


/********************************
 * ПОЛУЧАЕМ остатки ФБО
 ****************************/

$ozon_dop_url = "v1/analytics/manage/stocks";
$yyy = '{ "limit": 1000, "offset": 0 }';


// die();
// $result_array = post_with_data_ozon($token_ozon, $client_id_ozon, $yyy, $ozon_dop_url ) ;
// file_put_contents('file.json', json_encode($result_array, JSON_UNESCAPED_UNICODE));

$result_array = json_decode(file_get_contents('file.json') , true);

echo "<pre>";
 // print_r($result_array);


foreach ($result_array['items'] as $item ){

  $arr_ostatok_fbo[$item['offer_id']][$item['warehouse_name']] = $item['valid_stock_count'];
}

print_r($arr_ostatok_fbo);