<?php


// echo  "ПРОШЛИ КОННЕКТ<br>";
// require_once ("../main_info.php");
require_once '../../pdo_functions/pdo_functions.php'; // подключаем функции  взаимодейцстя  с БД

/// берем цены товаров и СКУ арицепим

$ozon_dop_url = "v5/product/info/prices";
$send_data =  array(
  "cursor" => "",
  "filter" => array(
    //    "offer_id"=> array ("6210" ),
    "visibility" => "ALL",
    //  "visibility" => "IN_SALE"
  ),
  "limit" => 1000
);
$send_data = json_encode($send_data);
$data_prices = post_with_data_ozon($token, $client_id, $send_data, $ozon_dop_url);

// если не получили данные - ждем 2 секунды и снова пробуем
if (!isset($data_prices['items'])) {
  sleep(2);
  $data_prices = post_with_data_ozon($token, $client_id, $send_data, $ozon_dop_url);
}
foreach ($data_prices['items'] as $items) {

  $arr_article_products[$items['offer_id']]['article'] = $items['offer_id'];
  $arr_article_products[$items['offer_id']]['product_id'] = $items['product_id'];
  $arr_article_products[$items['offer_id']]['sebestoimost'] = $items['price']['net_price'];
  $arr_id_products[] = $items['product_id'];
}


$ozon_dop_url = "v4/product/info/attributes";
$send_data = array(
  "filter" => array(
    "product_id" => $arr_id_products,
    "visibility" => "ALL"
  ),
  "limit" => 1000,
  "sort_dir" => "ASC"
);
$send_data = json_encode($send_data);
$data_all_kharacteristic = post_with_data_ozon($token, $client_id, $send_data, $ozon_dop_url);
foreach ($data_all_kharacteristic['result'] as $items) {
  foreach ($arr_article_products as &$tovar) {
    if ($items['id'] == $tovar['product_id']) {
      $tovar['sku'] = $items['sku'];
    }
  }
}
