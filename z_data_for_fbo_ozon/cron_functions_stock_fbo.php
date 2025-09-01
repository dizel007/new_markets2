<?php
$offset = "";
require_once $offset . "../connect_db.php";
require_once $offset . "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";

$date = date('Y-m-d');
/*********************************************************************
 * ПОЛУЧАЕМ остатки ФБО
 *********************************************************************/

//// для ООО
$token_ozon =  $token_ozon;
$client_id_ozon = $client_id_ozon;
$shop_name = 'ozon_anmaks';

$ozon_catalog    = get_catalog_tovarov_v_mp($shop_name, $pdo ,'active'); // получаем озон каталог

foreach ($ozon_catalog as $ozon_items) {
    $ozon_sku_items[] = $ozon_items['sku'];
}

$ozon_dop_url = "v1/analytics/stocks";
$json_data = json_encode(array("skus" => $ozon_sku_items));
$result_array = post_with_data_ozon($token_ozon, $client_id_ozon, $json_data, $ozon_dop_url ) ;

// перебираем массив с остатвками  и формируем свой для запсии в БД
foreach ($result_array['items'] as $ozon_items) {
    if ($ozon_items['available_stock_count'] > 0) {
        $arr_ozon_fbo_stocks[mb_strtolower($ozon_items['offer_id'])] =  @$arr_ozon_fbo_stocks[mb_strtolower($ozon_items['offer_id'])] + $ozon_items['available_stock_count'];
    }
}

// echo "<pre>";
// print_r($arr_ozon_fbo_stocks);
// записываем все в БД
foreach ($arr_ozon_fbo_stocks as $key=>$count) {
   $a_1c_article = $key;
   $fbo_in_stock = $count;
   insert_data_about_stock_fbo_ozon($pdo, $shop_name, $a_1c_article, $fbo_in_stock, $date);
}
unset($result_array);
unset($arr_ozon_fbo_stocks);

//**********************************************************************************
//  для ИП
//**********************************************************************************

$token_ozon =  $token_ozon_ip;
$client_id_ozon = $client_id_ozon_ip;
$shop_name = 'ozon_ip_zel';

$ozon_catalog    = get_catalog_tovarov_v_mp($shop_name, $pdo ,'active'); // получаем озон каталог

foreach ($ozon_catalog as $ozon_items) {
    $ozon_sku_items[] = $ozon_items['sku'];
}

$ozon_dop_url = "v1/analytics/stocks";
$json_data = json_encode(array("skus" => $ozon_sku_items));
$result_array = post_with_data_ozon($token_ozon, $client_id_ozon, $json_data, $ozon_dop_url ) ;

// перебираем массив с остатвками  и формируем свой для запсии в БД
foreach ($result_array['items'] as $ozon_items) {
    if ($ozon_items['available_stock_count'] > 0) {
        $arr_ozon_fbo_stocks[mb_strtolower($ozon_items['offer_id'])] =  @$arr_ozon_fbo_stocks[mb_strtolower($ozon_items['offer_id'])] + $ozon_items['available_stock_count'];
    }
}

// echo "<pre>";
// print_r($arr_ozon_fbo_stocks);
// записываем все в БД
foreach ($arr_ozon_fbo_stocks as $key=>$count) {
   $a_1c_article = $key;
   $fbo_in_stock = $count;
   insert_data_about_stock_fbo_ozon($pdo, $shop_name, $a_1c_article, $fbo_in_stock, $date);
}
unset($result_array);
unset($arr_ozon_fbo_stocks);
die();



/**************************************************************
 * фунция записи в таблицу остатков
 ***************************************************************/
function insert_data_about_stock_fbo_ozon($pdo, $shop_name, $a_1c_article, $fbo_in_stock, $date) {
$sth = $pdo->prepare("INSERT INTO `z_ozon_fbo_stocks` SET `shop_name`= :shop_name, `1c_article` = :1c_article, 
                                       `fbo_in_stock`= :fbo_in_stock, `date` =:date");

$sth->execute(array('shop_name' => $shop_name, 
                    '1c_article' => $a_1c_article,
                    'fbo_in_stock' => $fbo_in_stock,
                    'date' => $date));


}





