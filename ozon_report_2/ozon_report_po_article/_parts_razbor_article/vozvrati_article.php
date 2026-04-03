<?php

// echo "<pre>";
// print_r($arr_type_items_WITH_POSTING_NUMBER['returns']);

// die();

foreach ($arr_type_items_WITH_POSTING_NUMBER['returns'] as $items) {
    $i_returns++;
        $our_item = $items['items'];
        if (isset($items['items'][0]['sku'])) {
              
            // перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
          $new_posting_number = $items['posting']['posting_number']; // ставим сюда нормер заказа


          $arr_article[$new_posting_number]['posting_number'] =  $new_posting_number;
          $arr_article[$new_posting_number]['order_date'] = $items['posting']['order_date'];
          $arr_article[$new_posting_number]['delivery_schema'] = $items['posting']['delivery_schema'];

          $arr_article[$new_posting_number]['sku'] = $items['items'][0]['sku'];
          $arr_article[$new_posting_number]['name'] = $items['items'][0]['name'];
          $arr_article[$new_posting_number]['type_operation']['return'] =  'Возврат';

      
        

 $arr_summa_data['count']['return'] = @$arr_summa_data['count']['return'] + 1 ; // 

// Разбиваем стоиомть возвратов на логистику и обработку ... может еще что то
// Доставка и обработка возврата, отмены, невыкупа
                if (($items['operation_type'] == 'OperationReturnGoodsFBSofRMS') || ($items['operation_type'] == 'OperationItemReturn')
                        || ($items['operation_type'] == 'OperationAgentStornoDeliveredToCustomer')) { 
                   foreach ($items['services'] as $return_dop_obrabotka) {
                        $arr_article[$new_posting_number]['logistika']['return'][$return_dop_obrabotka['name']] = @$arr_article[$new_posting_number]['logistika']['return'][$return_dop_obrabotka['name']]
                         + $return_dop_obrabotka['price'];

                          }
                } elseif ($items['operation_type'] == 'ClientReturnAgentOperation') {
        // Получение возврата, отмены, невыкупа от покупател
                // количество товаров в заказе 
        
                $arr_article[$new_posting_number]['count']['return'] = @ $arr_article[$new_posting_number]['count']['return'] + 1;
                
                // Суммируем суммы операции
                        $arr_article[$new_posting_number]['amount'] = @$arr_article[$new_posting_number]['amount'] + $items['amount']/count($our_item); 
                // Суммируем Комиссию за продажу     
                        $arr_article[$new_posting_number]['sale_commission'] = @$arr_article[$new_posting_number]['sale_commission'] + $items['sale_commission']/count($our_item);
                // Цена для покупателя    
                        $arr_article[$new_posting_number]['accruals_for_sale']  = @$arr_article[$new_posting_number]['accruals_for_sale'] + $items['accruals_for_sale']/count($our_item);

                } else {
                     $arr_nerazjbrannoe['returns'][] = $items;
                }     
        
        } 
    }


// if (isset($arr_nerazjbrannoe) )
// {
//         echo "<br>******************************************< ARRAY ALARM VOZVRATOV ******************************************<br>";
//         echo "<pre>";
//         print_R($arr_nerazjbrannoe);
//         echo "</pre>";
//         echo "<br>******************************************< END ARRAY ALARM VOZVRATOV ******************************************<br>";
// } 
// echo "<pre>";
// print_r( $arr_article);
// print_r( $arr_summa_data);