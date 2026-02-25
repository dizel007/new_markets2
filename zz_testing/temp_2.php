<?php
$offset = "";
require_once $offset . "connect_db.php";
require_once $offset . "mp_functions/ozon_api_functions.php";


/********************************
 * ПОЛУЧАЕМ данные по отправлению
 ****************************/

$ozon_dop_url = "v3/posting/fbs/get";
$ozon_dop_url = "v2/posting/fbo/get";
$yyy = '{
"posting_number": "0132003136-0035-1",
"with": {
"analytics_data": false,
"barcodes": false,
"financial_data": false,
"legal_info": false,
"product_exemplars": false,
"related_postings": true,
"translit": false
}
}';


// die();
$result_array = post_with_data_ozon($token_ozon, $client_id_ozon, $yyy, $ozon_dop_url ) ;
// file_put_contents('file.json', json_encode($result_array, JSON_UNESCAPED_UNICODE));

// $result_array = json_decode(file_get_contents('file.json') , true);

echo "<pre>";
 print_r($result_array);


// foreach ($result_array['items'] as $item ){

//   $arr_ostatok_fbo[$item['offer_id']][$item['warehouse_name']] = $item['valid_stock_count'];
// }

// print_r($arr_ostatok_fbo);