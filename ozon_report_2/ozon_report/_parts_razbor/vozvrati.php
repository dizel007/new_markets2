<?php

// echo "<pre>";
// print_r($arr_returns );
// die();

$arr_test_spisok_returns_postnumbers = array();
foreach ($arr_returns as $items) {
    $i++;
        $our_item = $items['items'];
        if (isset($items['items'][0]['sku'])) {
              
            // перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
            ///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке
                $new_sku =  $items['items'][0]['sku'];
                $arr_article[$new_sku]['sku'] = $new_sku;
                // $arr_article[$new_sku]['name'] = $item['name'];
                $arr_article[$new_sku]['name'] = $items['items'][0]['name'];
                

//*********************************************************************************************************************************** */
// т.к. логистика и сервисы разбиты на несколько массивово,  то создадим массив с номера отправлений, которые мы уже посчитали
// и если там нет номера, то дабавим его и будем проверять его в дальнейшем
//*********************************************************************************************************************************** */
        $temp_posting_number = $items['posting']['posting_number'];
            if (!in_array($temp_posting_number, $arr_test_spisok_returns_postnumbers)) 
                {
                      $arr_article[$new_sku]['count']['return'] = @$arr_article[$new_sku]['count']['return'] + 1;
                      $arr_test_spisok_returns_postnumbers[] = $temp_posting_number;
                } 
//*********************************************************************************************************************************** */
//*********************************************************************************************************************************** */

               
              
// Разбиваем стоиомть возвратов на логистику и обработку ... может еще что то
// Доставка и обработка возврата, отмены, невыкупа
                if (($items['operation_type'] == 'OperationReturnGoodsFBSofRMS') || ($items['operation_type'] == 'OperationItemReturn')
                        || ($items['operation_type'] == 'OperationAgentStornoDeliveredToCustomer')) { 

       
                   foreach ($items['services'] as $return_dop_obrabotka) {
                        $arr_article[$new_sku]['logistika']['return'][$return_dop_obrabotka['name']] = @$arr_article[$new_sku]['logistika']['return'][$return_dop_obrabotka['name']]
                         + $return_dop_obrabotka['price'];
                //
               if ($items['posting']['delivery_schema'] == 'FBO') {
                        $arr_article[$new_sku]['logistika']['FBO_return'][$return_dop_obrabotka['name']] = @$arr_article[$new_sku]['logistika']['FBO_return'][$return_dop_obrabotka['name']] + 
                         + $return_dop_obrabotka['price'];
                } else if ($items['posting']['delivery_schema'] == 'FBS') {
                        $arr_article[$new_sku]['logistika']['FBS_return'][$return_dop_obrabotka['name']] = @$arr_article[$new_sku]['logistika']['FBS_return'][$return_dop_obrabotka['name']] + 
                         + $return_dop_obrabotka['price'];
                }

                //     $array_type_services_returns_logistika[$return_dop_obrabotka['name']] =  @$array_type_services_returns_logistika[$return_dop_obrabotka['name']] 
                //          + $return_dop_obrabotka['price'];
                       
                          }
                } elseif ($items['operation_type'] == 'ClientReturnAgentOperation') {
        // Получение возврата, отмены, невыкупа от покупател
                // количество товаров в заказе 
        
                // $arr_article[$new_sku]['count']['return'] = @$arr_article[$new_sku]['count']['return'] + 1;
                if ($items['posting']['delivery_schema'] == 'FBO') {
                        $arr_article[$new_sku]['count']['returnFBO'] = @$arr_article[$new_sku]['count']['returnFBO'] + 1;
                } else if ($items['posting']['delivery_schema'] == 'FBS') {
                        $arr_article[$new_sku]['count']['returnFBS'] = @$arr_article[$new_sku]['count']['returnFBS'] + 1;
                }

                
                // Суммируем суммы операции
                        $arr_article[$new_sku]['amount']['return'] = @$arr_article[$new_sku]['amount']['return'] + $items['amount']/count($our_item); 
                // Суммируем Комиссию за продажу     
                        $arr_article[$new_sku]['sale_commission']['return'] = @$arr_article[$new_sku]['sale_commission']['return'] + $items['sale_commission']/count($our_item);
                // Цена для покупателя    
                        $arr_article[$new_sku]['accruals_for_sale']['return']  = @$arr_article[$new_sku]['accruals_for_sale']['return']  + $items['accruals_for_sale']/count($our_item);

                } else {
                     $arr_nerazjbrannoe['returns'][] = $items;
                }     
        
        } else {
        // условие когда нет ску товара в массиве возврата
        $arr_returns_bez_tovara[$items['operation_type_name']] =  @$arr_returns_bez_tovara[$items['operation_type_name']] + $items['amount'];
        }

    }


// if (isset($arr_nerazjbrannoe) )
// {
//         echo "<br>******************************************< ARRAY ALARM VOZVRATOV ******************************************<br>";
        // echo "<pre>";
        // print_R($arr_test_spisok_returns_postnumbers);
//         echo "</pre>";
//         echo "<br>******************************************< END ARRAY ALARM VOZVRATOV ******************************************<br>";
// } 
