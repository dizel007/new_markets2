<?php

	
/* * ******************************************************************************************************
Выводим список заказов ОЗОН на определенную дату 
РАБОЧАЯ ВЕРСИЯ 
*** ожидает упаковки ****
*************************************************************************************************************** */
function get_all_waiting_posts_for_need_date($token, $client_id, $date_query_ozon, $send_status, $dop_days_query){
    // awaiting_packaging - заказы ожидают сборку
    // awaiting_deliver   - заказы ожидают отгрузку 



$temp_dop_day = "+".$dop_days_query.' day';
$date_query_ozon_end = date('Y-m-d', strtotime($temp_dop_day, strtotime($date_query_ozon)));


$send_data=  array(
    "dir" => "ASC",
    "filter" => array(
    "cutoff_from" => $date_query_ozon."T00:00:00Z",
    "cutoff_to" =>   $date_query_ozon_end."T23:59:59Z",
    "delivery_method_id" => [ ],
    "provider_id" => [ ],
    "status" => $send_status,
    "warehouse_id" => [ ]
    ),
    "limit" => 1000,
    "offset" => 0,
    "with" => array(
    "analytics_data"  => true,
    "barcodes"  => true,
    "financial_data" => true,
    "translit" => true
    )
    );

 $send_data = json_encode($send_data, JSON_UNESCAPED_UNICODE)  ;  


$ozon_dop_url = "v3/posting/fbs/unfulfilled/list";


// запустили запрос на озона
$res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url );
return $res;
}


/*******************************************************************************************************
********      Достаем фактические остатки товаров и цепляем их к каталогу товаров***********************
*******************************************************************************************************/
function  get_ostatki_ozon ($token_ozon, $client_id_ozon, $ozon_catalog) {
    // FПолучаем фактическое количество товаров указанное на складе ОЗОН
    $ozon_dop_url = 'v1/product/info/stocks-by-warehouse/fbs';
    $data = '';
    
    foreach ($ozon_catalog as $item)
     {
         $data .="\"".$item['sku']."\",";
    }
    $data = substr($data, 0, -1);
    $send_data ='{"sku": ['.$data.']}';
    
    $res = send_injection_on_ozon($token_ozon, $client_id_ozon, $send_data, $ozon_dop_url );
    
// echo "<pre>";
// print_r($res) ;
// die();

    foreach ($res['result'] as $items) {
    foreach ($ozon_catalog as &$prods) {
        if ($prods['sku'] == $items['sku']) {
            $prods['quantity'] = $items['present'] - $items['reserved'];
            break 1;
        }
    }
    }
    return $ozon_catalog;
     }

/*******************************************************************************************************
********      Достаем фактические заказанные товары и цепляем их к каталогу товаров*********************
*******************************************************************************************************/

function get_new_zakazi_ozon ($token_ozon, $client_id_ozon, $ozon_catalog) {
    $date_query_ozon = date('Y-m-d');
    $date_query_ozon = date('Y-m-d', strtotime('-4 day', strtotime($date_query_ozon))); // начальную датк на 4 дня раньше берем
    
    $dop_days_query = 14; // захватывает 14 дней после сегодняшней даты
    
    //  Получаем фактические заказы с сайта озона (4 дня доо и 14 после сегодняшне йдаты)
    $res = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, 'awaiting_packaging', $dop_days_query);
    
    // echo "<pre>";
    
    // print_r($res);
    

    if ($res['result']['count'] <> 0 ) { // если нет заказов на озоне, то просто возвращаем массив товаров назад
        foreach ($res['result']['postings'] as $items) {
            foreach ($items['products'] as $product) {
                
                $arr_products[$product['offer_id']] = @$arr_products[$product['offer_id']] + $product['quantity'];
                $arr_summa_sell_products[$product['offer_id']] = @$arr_summa_sell_products[$product['offer_id']] + $product['price']*$product['quantity'];
                

            }
            
        }

    //  print_r ($arr_summa_sell_products);   

// добавляем в каталог данные о количестве проданного товара
        foreach ($arr_products as $key=>$prods) {
            foreach ($ozon_catalog as &$items_ozon) {

                if ( mb_strtolower((string)$key) ==  mb_strtolower((string)$items_ozon['mp_article'])) {

                    $items_ozon['sell_count'] = $prods;
                } 
            }
        }
  // добавляем в каталог данные о сумме проданного товара      
        foreach ($arr_summa_sell_products as $key=>$Sell_summa) {
            foreach ($ozon_catalog as &$items_ozon) {

                if ( mb_strtolower((string)$key) ==  mb_strtolower((string)$items_ozon['mp_article'])) {

                    $items_ozon['sell_summa'] = $Sell_summa;
                } 
            }
        }

    }
    
    return $ozon_catalog;
    }



/*******************************************************************************************************
********      Достаем фактические заказанные товары выбранную дату ОЗОН *********************
*******************************************************************************************************/
    function get_new_zakazi_ozon_one_date ($token_ozon, $client_id_ozon, $ozon_catalog, $date_query_ozon) {
        // $date_query_ozon = date('Y-m-d');
        // $date_query_ozon = date('Y-m-d', strtotime('-4 day', strtotime($date_query_ozon))); // начальную датк на 4 дня раньше берем
        
        $dop_days_query = 0; // захватывает 14 дней после сегодняшней даты
        
        //  Получаем фактические заказы с сайта озона (4 дня доо и 14 после сегодняшне йдаты)
        $res = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, 'awaiting_packaging', $dop_days_query);
        
        // echo "<pre>";
        
        // print_r($res);
        
    
        if ($res['result']['count'] <> 0 ) { // если нет заказов на озоне, то просто возвращаем массив товаров назад
            foreach ($res['result']['postings'] as $items) {
                foreach ($items['products'] as $product) {
                    
                    $arr_products[$product['offer_id']] = @$arr_products[$product['offer_id']] + $product['quantity'];
                    $arr_summa_sell_products[$product['offer_id']] = @$arr_summa_sell_products[$product['offer_id']] + $product['price']*$product['quantity'];
                    
    
                }
                
            }
    
        //  print_r ($arr_summa_sell_products);   
    
    // добавляем в каталог данные о количестве проданного товара
            foreach ($arr_products as $key=>$prods) {
                foreach ($ozon_catalog as &$items_ozon) {
    
                    if ( mb_strtolower((string)$key) ==  mb_strtolower((string)$items_ozon['mp_article'])) {
    
                        $items_ozon['sell_count'] = $prods;
                    } 
                }
            }
      // добавляем в каталог данные о сумме проданного товара      
            foreach ($arr_summa_sell_products as $key=>$Sell_summa) {
                foreach ($ozon_catalog as &$items_ozon) {
    
                    if ( mb_strtolower((string)$key) ==  mb_strtolower((string)$items_ozon['mp_article'])) {
    
                        $items_ozon['sell_summa'] = $Sell_summa;
                    } 
                }
            }
    
        }
        
        return $ozon_catalog;
        }
    
    
    
    