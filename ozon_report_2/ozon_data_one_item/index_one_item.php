<?php

require_once "../../connect_db.php";
require_once "../../mp_functions/ozon_api_functions.php";

/*****************************************************************
 * Вычитываем информацию о товаре - цену / скидки и прочую хрень
 **************************************************************/
// echo "Вычитываем информацию о товаре - цену / скидки и прочую хрень"."<br>";

      try {  
        $pdo = new PDO('mysql:host='.$host.';dbname='.$db.';charset=utf8', $user, $password);
        $pdo->exec('SET NAMES utf8');
        } catch (PDOException $e) {
          print "Has errors: " . $e->getMessage();  die();
        }


$queryString = $_SERVER['QUERY_STRING'] ?? '';

// Разобрать вручную
parse_str($queryString, $params);

// находим ID клиента
if (isset($params['ozon_shop'])) {
    $article = $params['art'];
    $shop_name = $params['ozon_shop'];

if ($shop_name == 'ozon_anmaks') {
     // ОЗОН АНМКАС
    $token  = $arr_tokens['ozon_anmaks']['token'];
    $client_id = $arr_tokens['ozon_anmaks']['id_market'];

} elseif ($shop_name == 'ozon_ip_zel') {
    // озон ИП зел
    $client_id = $arr_tokens['ozon_ip_zel']['id_market'];
    $token = $arr_tokens['ozon_ip_zel']['token'];
}
     
} else {
    die('Не нашли файл с данными');
}

// находим время доставки за последнюю неделю 
$ozon_dop_url = "v1/analytics/average-delivery-time/summary";
$send_data = '';
$average_delivery_time = post_with_data_ozon($token, $client_id, $send_data, $ozon_dop_url ) ;

// берем информацию по данному артикулу

$ozon_dop_url = "v5/product/info/prices";
$send_data =  array(
"cursor" => "",
"filter" => array (
                   "offer_id"=> array ("$article" ),
                //    "visibility" => "ALL",
                //    "visibility" => "IN_SALE"
                   ),
"limit" => 1000
);
$send_data = json_encode($send_data);

$data = post_with_data_ozon($token, $client_id, $send_data, $ozon_dop_url ) ;

// echo "<pre>";
// print_r($data);
// die();

require_once "index_y.php";