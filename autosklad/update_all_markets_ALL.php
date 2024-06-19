<?php
require_once '../connect_db.php';
require_once '../pdo_functions/pdo_functions.php';
require_once "functions/razbor_post_array_ALL.php"; // массиво с каталогов наших товаров
require_once "../mp_functions/ozon_api_functions.php";
require_once "../mp_functions/ozon_functions.php";
require_once "../mp_functions/wb_api_functions.php";
require_once "../mp_functions/wb_functions.php";
require_once "../mp_functions/yandex_api_functions.php";
require_once "../mp_functions/yandex_functions.php";

// Получаем все токены
$arr_tokens = get_tokens($pdo);

// Названия магазинов
$wb_anmaks = 'wb_anmaks';
$wb_ip = 'wb_ip_zel';
$ozon_anmaks = 'ozon_anmaks';
$ozon_ip = 'ozon_ip_zel';
$yandex_anmaks_fbs = 'ya_anmaks_fbs';

// НАзвание магазина, который обновляем
// echo "<pre>";

// /* **************************  Запуск Обновления остатков ЯНДЕКС ФБС  *********************************** */
update_ostatki_Yandex_fbs($arr_tokens,$pdo, $yandex_anmaks_fbs);

// /* **************************  Запуск Обновления остатков для обновления ВБ *********************************** */
    $warehouseId = 34790;
     update_ostatki_WB($arr_tokens, $warehouseId , $wb_anmaks) ;
// /* **************************  Запуск Обновления остатков WB IP oбновления *********************************** */
//     // ВБ Зел
    $warehouseId =  946290;
    update_ostatki_WB($arr_tokens, $warehouseId , $wb_ip) ;
// /* **************************  Запуск Обновления остатков ОЗОН ООО  *********************************** */
    
// Озон АНмакс 
    // update_ostatki_OZON($arr_tokens,$pdo, $ozon_anmaks) ; // Старый метод
    update_ostatki_OZON_v2($arr_tokens,$pdo, $ozon_anmaks, "20848498763000") ;

    
// /* **************************  Запуск Обновления остатков ОЗОН ИП ЗЕЛ  *********************************** */
//     // озон ИП зел
    // update_ostatki_OZON($arr_tokens,$pdo, $ozon_ip) ; // Старый метод
    update_ostatki_OZON_v2($arr_tokens,$pdo, $ozon_ip, "1020001356628000") ;
                                                        




/* *************** возвращаемся к таблице*/
    header('Location: get_all_ostatki_skladov_new_ALL.php?return=777', true, 301);

 die('ОБновили все остатки, но не перенаправились на начальную страницу');

 /***************************************************************
 ******** Обновление остаток на ВБ из POST  запроса
**************************************************************** */

 function update_ostatki_WB($arr_tokens, $warehouseId , $shop_name) {
    $token_wb = $arr_tokens[$shop_name]['token'];
    $wb_update_items_quantity = razbor_post_massive_mp_2($_POST, $shop_name);


    if ($wb_update_items_quantity <> "no_data") {
        foreach ($wb_update_items_quantity as $wb_item) {
            $data_wb["stocks"][] = $wb_item;
        }
   
        $link_wb = 'https://suppliers-api.wildberries.ru/api/v3/stocks/'.$warehouseId; 
        $res = wb_put_query_with_data($token_wb, $link_wb, $data_wb);
     }

}


 /***************************************************************
 ******** Обновление остаток на OZON из POST  запроса
**************************************************************** */

function update_ostatki_OZON($arr_tokens,$pdo, $shop_name) {

   // ОЗОН АНМКАС
   $client_id_ozon = $arr_tokens[$shop_name]['id_market'];
   $token_ozon = $arr_tokens[$shop_name]['token'];
   
   
   $ozon_update_items_quantity = razbor_post_massive_mp_2($_POST, $shop_name);
   $arr_catalog =  get_catalog_tovarov_v_mp($shop_name, $pdo, 'active');
   
   if ($ozon_update_items_quantity <> "no_data") {
   
       // добавляем к массиву артикул
       foreach ($ozon_update_items_quantity as &$item) {
       
           foreach ($arr_catalog as $prods) {
            if ($item ['sku'] == $prods['barcode']) {
               $item['article'] = $prods['mp_article'];
               $item['real_sku'] = $prods['sku'];
            }
           }
       }
   
       unset($item);
       
       // Формируем массив для метода ОЗОНа по обновления остатков
       foreach ($ozon_update_items_quantity as $prods) {
           $temp_data_send[] = 
               array(
                   "offer_id" =>  $prods['article'],
                   "product_id" =>   $prods['real_sku'], // для обновления нужен СКУ а не баркод (поэтому подставляем СКУ)
                   "stock" => $prods['amount'],
                  );
           }
       $send_data =  array("stocks" => $temp_data_send);
       $send_data = json_encode($send_data, JSON_UNESCAPED_UNICODE)  ;
       $ozon_dop_url = "v1/product/import/stocks";
    // $ozon_dop_url = "v2/products/stocks";
    
       $result_ozon = post_with_data_ozon($token_ozon, $client_id_ozon, $send_data, $ozon_dop_url );
       }

print_r($result_ozon); 
}

 /***************************************************************
 ******** Обновление остаток на YANDEX из POST  запроса
**************************************************************** */

function update_ostatki_Yandex_fbs($arr_tokens,$pdo, $shop_name) {
    $ya_token = $arr_tokens[$shop_name]['token'];
    $campaignId = $arr_tokens[$shop_name]['id_market'];
    $yandex_update_items_quantity = razbor_post_massive_mp_2($_POST, $shop_name);

// echo "<pre>";
// print_r($yandex_update_items_quantity);

// die();

// "updatedAt"=> "2024-04-17T12:05:01Z" 
$time = time() - 3 * 60 * 60; // уменьшаем время на 3 часа
$date_now = date('Y-m-d\TH:i:s\Z', $time);

if ($yandex_update_items_quantity <> "no_data") {
// Формируем массив для обновления количества всех товаров
foreach( $yandex_update_items_quantity as $key => $yandex_items) {

//костыль для обновления количества решеток 
($key == 'ANM_39-59')?$key = "ANM.39-59": $key = $key;

// массив товаров для обновлния 
    $sku_update_array[] = array(
        "sku" =>  "$key",
        "items" => array(array(
                "count" => (int)$yandex_items['amount'],
                "updatedAt"=> "$date_now"
        ),
                        )
    );
    // $sku_update_array[] = $temp_update_array;
}


$ya_data = array("skus" => ($sku_update_array));
    

$ya_link = 'https://api.partner.market.yandex.ru/campaigns/'.$campaignId.'/offers/stocks';
$res = yandex_put_query_with_data($ya_token, $ya_link, $ya_data);
// print_r($res);
// die();

}
}


/***************************************************************
 ******** Обновление остаток на OZON из POST  запроса
**************************************************************** */

function update_ostatki_OZON_v2($arr_tokens,$pdo, $shop_name, $warehouse_id) {

   // ОЗОН АНМКАС
   $client_id_ozon = $arr_tokens[$shop_name]['id_market'];
   $token_ozon = $arr_tokens[$shop_name]['token'];
//   echo $client_id_ozon."<br>";
  
   $ozon_update_items_quantity = razbor_post_massive_mp_2($_POST, $shop_name);
   $arr_catalog =  get_catalog_tovarov_v_mp($shop_name, $pdo, 'active');
   
   if ($ozon_update_items_quantity <> "no_data") {
   
       // добавляем к массиву артикул
       foreach ($ozon_update_items_quantity as &$item) {
       
           foreach ($arr_catalog as $prods) {
            if ($item ['sku'] == $prods['barcode']) {
               $item['article'] = $prods['mp_article'];
               $item['real_sku'] = $prods['sku'];
            }
           }
       }
   
       unset($item);
       
       // Формируем массив для метода ОЗОНа по обновления остатков
       foreach ($ozon_update_items_quantity as $prods) {
           $temp_data_send[] = 
               array(
                   "offer_id" =>  $prods['article'],
                   "product_id" =>   $prods['real_sku'], // для обновления нужен СКУ а не баркод (поэтому подставляем СКУ)
                   "stock" => $prods['amount'],
                   "warehouse_id" => $warehouse_id,
                  );
           }
       $send_data =  array("stocks" => $temp_data_send);
    // echo "<pre>";
    
    
       $send_data = json_encode($send_data, JSON_UNESCAPED_UNICODE)  ;
       $ozon_dop_url = "v2/products/stocks";
    
       $result_ozon = post_with_data_ozon($token_ozon, $client_id_ozon, $send_data, $ozon_dop_url );

    //    print_r($result_ozon);
       }

}