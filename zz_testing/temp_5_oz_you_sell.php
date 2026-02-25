<?php
$offset = "";
require_once $offset . "connect_db.php";
require_once $offset . "mp_functions/ozon_api_functions.php";

/*****************************************************************
 * СПробуем мотоды ОЗОН ЛОгистики
 **************************************************************/
echo "Пробуем мотоды ОЗОН-Логистики"."<br>";


    // озон ИП зел
    $client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
    $token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];



    // [operation_id] => 0199a3b0-7534-70c3-b84b-5ae247a73efa



$ozon_dop_url = "v1/delivery/check";
$ozon_dop_url = "v5/product/info/prices"; 
// $yyy = '{"client_phone": "79122020299"}';
$yyy ='{
"cursor": "",
"filter": {
"product_id": [
"1103169675"
],
"visibility": "ALL"
},
"limit": 100
}';

$result_array = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $yyy, $ozon_dop_url ) ;

echo "<pre>";
print_r($result_array);




die();














// $ozon_dop_url = "v1/cluster/list";
// $yyy = '{
// "cluster_type": "CLUSTER_TYPE_OZON"
// }';
// $result_array = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $yyy, $ozon_dop_url ) ;
// echo "<pre>";
// print_r($result_array);


/**********************************************************************************************

**********************************************************************************************/

// $ozon_dop_url = "v1/warehouse/fbo/list";
// $yyy = '{
// "filter_by_supply_type": ["CREATE_TYPE_DIRECT"],
// "search": "Домодедово"
// }';

// $result_array = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $yyy, $ozon_dop_url ) ;
// echo "<pre>";
// print_r($result_array);



/**********************************************************************************************
Создаем черновик Заявки на поставку
**********************************************************************************************/
$ozon_dop_url = "v1/draft/create";
$yyy = '{

"items": [
{"quantity": "300",
"sku": "2544935988"}
],
"type": "CREATE_TYPE_DIRECT"
}';

$result_array = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $yyy, $ozon_dop_url ) ;
echo "<pre>";
print_r($result_array);



die();
