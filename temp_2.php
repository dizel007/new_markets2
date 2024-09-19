<?php
$offset = "";
require_once $offset . "connect_db.php";
require_once $offset . "pdo_functions/pdo_functions.php";
require_once $offset . "mp_functions/ozon_api_functions.php";
require_once $offset . "mp_functions/ozon_functions.php";

    // озон ИП зел
 
    $ozon_shop = 'ozon_anmaks';

$client_id = $arr_tokens['ozon_anmaks']['id_market'];
	$token_ozon = $arr_tokens['ozon_anmaks']['token'];
// наxодим ID товаров озона 
// $ozon_catalog    = get_catalog_tovarov_v_mp($ozon_shop , $pdo, 'active'); // получаем озон каталог

// $ozon_dop_url = "v2/product/info";
// foreach ($ozon_catalog as &$items) {
// 	$send_data = array(
// "offer_id" => "",
// "product_id" => 0,
// "sku" => $items['sku']);
// $send_data = json_encode($send_data);
// $ozcatalog = post_with_data_ozon($token_ozon, $client_id, $send_data, $ozon_dop_url);
// $items['id_ozon'] = $ozcatalog['result']['id'];
// }

// file_put_contents('1.json', json_encode($ozon_catalog, JSON_UNESCAPED_UNICODE));

$ozon_catalog = json_decode(file_get_contents('1.json'), true);
echo "<pre>";
// print_r($ozon_catalog);


foreach ($ozon_catalog as $itmmm) {

$send_data = '{
    "filter": {
    "product_id": [
    "'.$itmmm['product_id'].'"
    ],
    "visibility": "ALL"
    },
    "limit": 100,
    "last_id": "",
    "sort_dir": "ASC"
    }';

    $ozon_dop_url = "v3/products/info/attributes";
$ozcatalog = post_with_data_ozon($token_ozon, $client_id, $send_data, $ozon_dop_url);



echo  "<br>".$ozcatalog['result'][0]['offer_id'];
echo  "<br>height=".$ozcatalog['result'][0]['height'];
echo  "<br>depth=".$ozcatalog['result'][0]['depth'];
echo  "<br>width=".$ozcatalog['result'][0]['width'];
echo  "<br><br>";
}