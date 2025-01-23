<?php
/************************************************************************
 * Получаем остатки по всем артикулам
 ************************************************************************/
function get_ostatki_yandex ($yam_token, $campaignId_FBS, $ya_fbs_catalog) {
    $ya_data = array (
        "withTurnover" => false,
        "archived" => false,
        // "offerIds" => array("7245-К-10")
     );
    
    $ya_link = 'https://api.partner.market.yandex.ru/campaigns/'.$campaignId_FBS.'/offers/stocks';
    
    $arr_all_stocks = yandex_post_query_with_data($yam_token, $ya_link, $ya_data);
    
// Формируем массив с остатками товаров
if (isset($arr_all_stocks['result']['warehouses'][0]['offers'])) {
    foreach ($arr_all_stocks['result']['warehouses'][0]['offers'] as $stocks) {
    
        // $arr_stocks_by_artickle[$stocks['offerId']] = $stocks['offerId'];
        foreach ($stocks['stocks'] as $type_stock) {
            if ($type_stock['type'] == 'AVAILABLE') {
                $arr_stocks_by_artickle[$stocks['offerId']] = $type_stock['count'];
            } 
           
        }
        
    }
}

// Цепляем остатки товаров к каталогу яндекс
if (isset($arr_stocks_by_artickle)) {
foreach ($ya_fbs_catalog as &$items) {
    foreach ($arr_stocks_by_artickle as $key=>$znachenia) 

    if ($key == $items['sku']) {
        $items['quantity'] = $znachenia;
        break 1;
    } else {
        $items['quantity'] = 0; // принулительно стави мноль т.к. если товара в яндексе нет, то он не возварщает его в остатках
    }

}
}

    return $ya_fbs_catalog;
    }
    

/************************************************************************
 * Получаем даннные о новых заказах 
 ************************************************************************/


    function yandex_get_new_orders($ya_token, $campaignId) {

        $substatus = 'substatus=STARTED';
        $ya_link = 'https://api.partner.market.yandex.ru/campaigns/'.$campaignId.'/orders/?'.$substatus ;
        
        $result = yandex_get_query_without_data($ya_token, $ya_link);
        
        return $result;
        }
        
 /************************************************************************
 * Получаем даннные о новых заказах и обновляем каталог Яндекса
 ************************************************************************/       
function get_new_zakazi_yandex ($yam_token, $campaignId_FBS, $ya_fbs_catalog){

 $arr_all_new_orders = yandex_get_new_orders($yam_token, $campaignId_FBS);

// формируем массив проданные заказов
 if  (isset($arr_all_new_orders)) {
    foreach ($arr_all_new_orders['orders'] as $order) { // перебираем все новые заказы
       foreach ($order['items'] as $items) { // перебираем все товары из выбранного заказа
            unset ($items['subsidies']);
                $arr_all_items[] = $items;
              }
          }

}


// print_r($arr_all_items);
// перебираем массив и добавляем проданные товары в каталог яндекса
if (isset($arr_all_items)){ // Проверяем если ли вообще заказы на эту дату
    foreach ($ya_fbs_catalog as &$items) {
        foreach ($arr_all_items as $orders) {
            if ($orders['offerId'] == $items['sku']) {
                $items['sell_count'] = @$items['sell_count'] + $orders['count'];
                $items['sell_summa'] = @$items['sell_summa'] + $orders['buyerPrice']*$orders['count'];
            }
        }



    }
}

return $ya_fbs_catalog;
}


 /************************************************************************
 * Получаем даннные о новых заказах и обновляем каталог Яндекса
 ************************************************************************/       
function get_new_zakazi_yandex_one_date ($yam_token, $campaignId_FBS, $ya_fbs_catalog, $date_sbora){
    $date_sbora = date('d-m-Y' , strtotime($date_sbora)); 
    $arr_all_new_orders = yandex_get_new_orders($yam_token, $campaignId_FBS);
   echo "<pre>";

    // print_r ( $arr_all_new_orders);
   // формируем массив проданные заказов
    if  (isset($arr_all_new_orders)) {
       foreach ($arr_all_new_orders['orders'] as $order) { // перебираем все новые заказы

        $need_ship_date = $order['delivery']['shipments'][0]['shipmentDate'];

        if ($date_sbora == $need_ship_date)  {    /// выбор даты дня отгрузки
          foreach ($order['items'] as $items) { // перебираем все товары из выбранного заказа
               unset ($items['subsidies']);
                   $arr_all_items[] = $items;
                 }
             }
            }
   }
   
   
//    print_r($arr_all_items);
   // перебираем массив и добавляем проданные товары в каталог яндекса
if (isset($arr_all_items)){ // Проверяем если ли вообще заказы на эту дату
   foreach ($ya_fbs_catalog as &$items) {
       foreach ($arr_all_items as $orders) {
           if ($orders['offerId'] == $items['sku']) {
               $items['sell_count'] = @$items['sell_count'] + $orders['count'];
               $items['sell_summa'] = @$items['sell_summa'] + $orders['buyerPrice']*$orders['count'];
           }
       }
   
   
   
   }
}
   
   return $ya_fbs_catalog;
   }
