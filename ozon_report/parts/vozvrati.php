<?php 


foreach ($arr_returns as $items) {
       
    $i++;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
        foreach ($our_item as $item) {
// print_r($items);
///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке
            $new_sku = change_SKU_fbo_fbs($ozon_sebest, $item['sku']);

            $arr_article[$new_sku]['name'] = $item['name'];
            $arr_article[$new_sku]['sku'] = $new_sku;
   

/// Разбиваем стоиомть возвратов на логистику и обработку ... может еще что то
                if (($items['operation_type'] == 'OperationReturnGoodsFBSofRMS') || ($items['operation_type'] == 'OperationItemReturn')) //Доставка и обработка возврата, отмены, невыкупа
                {
                       foreach ($items['services'] as $return_dop_obrabotka) {
                        // $ggg[$return_dop_obrabotka['name']] =  $return_dop_obrabotka['name'];
// Стоимость прямой логистики для возврата
                        if ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDirectFlowLogistic') {
                                $arr_article[$new_sku]['logistika'] = @$arr_article[$new_sku]['logistika'] + $return_dop_obrabotka['price']; 
                        }
// Стоимость обратной логистик для возвартов
                        if ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemReturnFlowLogistic') {

                                $arr_article[$new_sku]['back_logistika'] = @$arr_article[$new_sku]['back_logistika'] + $return_dop_obrabotka['price']; 
                                $summa_obratnoy_logistik = @$summa_obratnoy_logistik + $return_dop_obrabotka['price'];
                                }

                                

                        elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDropoffSC') {
                                $arr_article[$new_sku]['sborka'] = @$arr_article[$new_sku]['sborka'] + $return_dop_obrabotka['price']; 
                                $arr_article[$new_sku]['back_sborka'] = @$arr_article[$new_sku]['back_sborka'] + $return_dop_obrabotka['price']; 
                                $back_sborka = @$back_sborka + $return_dop_obrabotka['price']; 
                        }
/// Обработка возвратов, отмен и невыкупов Партнёрами Ozon
                        elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemRedistributionReturnsPVZ') {
                                $arr_article[$new_sku]['return_obrabotka'] = @$arr_article[$new_sku]['return_obrabotka'] + $return_dop_obrabotka['price']; 
                                $return_obrabotka = @$return_obrabotka + $return_dop_obrabotka['price'];
                        }
                        else {
                           $arr_nerazjbrannoe[] = $items;      
                        }



            }
        }

// Hfp,bhftv Получение возврата, отмены, невыкупа от покупателя
                if ($items['operation_type'] == 'ClientReturnAgentOperation') //Доставка и обработка возврата, отмены, невыкупа
                {
                     // количество товаров в заказе, которые вернули
                      $arr_article[$new_sku]['count_vozvrat'] = @$arr_article[$new_sku]['count_vozvrat'] + 1;
                      // Суммируем суммы операции, которые возвраты
                     $arr_article[$new_sku]['amount_vozrat'] = @$arr_article[$new_sku]['amount_vozrat'] + $items['amount']/count($our_item);  
  
                }

        }

    
 
}

// print_R($ggg);