<?php
$offset = "";
require_once $offset . "connect_db.php";
require_once $offset . "mp_functions/ozon_api_functions.php";


/*****************************************************************
 * СОЗДДАЕТ ЧЕРНОВИК ПОСТАВКИ И ИЩЕМ СЛОТЫ
 **************************************************************/
echo "СОЗДДАЕТ ЧЕРНОВИК ПОСТАВКИ И ИЩЕМ СЛОТЫ"."<br>";


    // ОЗОН АНМКАС
    $client_id_ozon = $arr_tokens['ozon_anmaks']['id_market'];
    $token_ozon = $arr_tokens['ozon_anmaks']['token'];
    // озон ИП зел
    $client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
    $token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];

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
// echo "<pre>";
print_r($result_array);



die();
