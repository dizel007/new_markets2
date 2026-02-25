<?php

/***********************************************************************
 *  Вычитываем коэффициенты логистики для складов ВБ 
 **********************************************************************/

$offset = "";
require_once $offset . "connect_db.php";
require_once $offset . "mp_functions/wb_api_functions.php";

echo "jj";
$date = date('Y-m-d');
$link_wb = "https://content-api.wildberries.ru/content/v2/get/cards/list";
// $link_wb = "https://common-api.wildberries.ru/api/v1/tariffs/box?date=$date";
$data_2 = array (
     "settings" => array( 
       "cursor" => array (
         "limit" => 100
       ),
       "filter" => array (
         "withPhoto" => -1
       )
     )
) ;
//  $res = light_query_without_data($token_wb, $link_wb);
 $res = light_query_with_data($token_wb, $link_wb, $data_2);
 echo "<pre>";
 print_r($res['cards'][0]);

 foreach ($res['cards'] as $cards) {
    // все габариты
    // $arr_cards[$cards['vendorCode']]['dimensions'] = $cards['dimensions']; 
    // литры
    $arr_cards[$cards['vendorCode']]['volume'] = round($cards['dimensions']['width'] * $cards['dimensions']['height']  * $cards['dimensions']['length']/1000,2) ;
    // СКУ
    $arr_cards[$cards['vendorCode']]['sku'] = $cards['nmID'];
    // Баркод
    $arr_cards[$cards['vendorCode']]['barcode'] = $cards['sizes'][0]['skus'][0];
    // Стоимость базового тарифа
    //Стоимость логистики до покупателя рассчитывается по формуле: (46 ₽ за 1 л + 14 ₽ за каждый доп. литр) × коэффициент склада × ИЛ на дату заказа
    $arr_cards[$cards['vendorCode']]['base_tarif'] = ($arr_cards[$cards['vendorCode']]['volume'] - 1) * 14 + 46 ;

 }

 print_r($arr_cards);
