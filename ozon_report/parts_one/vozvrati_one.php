<?php


// print_r($arr_returns);

foreach ($arr_returns as $items) {

        $i++;
        $our_item = $items['items'];
        // перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)

                        ///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке
        
        $new_post_number = make_posting_number ($items['posting']['posting_number']);
        $new_post_number_full = $items['posting']['posting_number'];
        $arr_article[$new_post_number]['post_number'] = $new_post_number;
        $arr_article[$new_post_number]['RETURN'] = 'RETURN';
        $arr_article[$new_post_number]['order_date'] = $items['posting']['order_date'];
        $arr_article[$new_post_number]['delivery_schema'] = $items['posting']['delivery_schema'];

 foreach ($our_item as $item) {

        $arr_number_tovar[$new_post_number] = @$arr_number_tovar[$new_post_number] + 1; // порядковый номер товара в заказе
        $new_sku = change_SKU_fbo_fbs($ozon_sebest, $item['sku']); 
        $c_1c_article = get_article_by_sku_fbs($ozon_sebest, $new_sku);
        
        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['sku'] = $new_sku;
        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['name'] =  $item['name'];
        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['c_1c_article'] = $c_1c_article;


    // print_r($items);
    // Разбиваем стоиомть возвратов на логистику и обработку ... может еще что то
    //Доставка и обработка возврата, отмены, невыкупа
if (($items['operation_type'] == 'OperationReturnGoodsFBSofRMS')  || ($items['operation_type'] == 'OperationItemReturn')) {
   foreach ($items['services'] as $return_dop_obrabotka) {
        
    // логистика
  if ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDirectFlowLogistic') {
        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] = $return_dop_obrabotka['price'];
   }
  // обратная логистика
  elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemReturnFlowLogistic') {
        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['back_logistika_vozvrat'] = $return_dop_obrabotka['price'];
   }
  // обработка отправления.
  elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDropoffSC') {
        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['obrabotka_otpravlenii_v_SC'] = $return_dop_obrabotka['price'];
      }
                                // обработка отправления в ПВЗ.
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDropoffPVZ') {
                                        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['obrabotka_otpravlenii_v_PVZ'] = $return_dop_obrabotka['price'];
                                }
                                
                                /// сборка заказа**************************************************************
        elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemFulfillment') {
                $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['sborka_zakaza'] = $return_dop_obrabotka['price'];

        }
        /// перевыставление возвратов на ПВЗ.
        elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemRedistributionReturnsPVZ') {
                $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['return_obrabotka'] = $return_dop_obrabotka['price'];
        }

        /// Обработка отмен.
        elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemReturnNotDelivToCustomer') {
              $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['work_otmen'] = $return_dop_obrabotka['price'];
        }
        /// обработка возврата.
        elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemReturnAfterDelivToCustomer') {
                $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['work_vozvrata'] = $return_dop_obrabotka['price'];
        }
        /// магистраль
        elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDirectFlowTrans') {
               $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_magistral'] = $return_dop_obrabotka['price'];
        }
        /// обратная магистраль
        elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemReturnFlowTrans') {
                $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_obrat_magistral'] = $return_dop_obrabotka['price'];
        }
        /// обработка невыкупа.
        elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemReturnPartGoodsCustomer') {
                $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['obtabotka_nevikupa'] = $return_dop_obrabotka['price'];
        } else {
                print_R($items);
                echo "<br>***************** " . $return_dop_obrabotka['name'] . " *************************<br>";
                $arr_ALARM_vozvrztov['СЕРВИСЫ_ВОЗВРАТОВ'][] = $items;
        }
                        }
                        //Доставка и обработка возврата, отмены, невыкупа
        } // Получение возврата, отмены, невыкупа от покупателя

        elseif ($items['operation_type'] == 'ClientReturnAgentOperation') {
        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['obtabotka_nevikupa'] = $items['amount'];
        // Суммируем суммы операции, которые возвраты
            }
     
        }

 // Разбираем Получение возврата, отмены, невыкупа от покупателя
        if (($items['operation_type'] == 'OperationReturnGoodsFBSofRMS')  || ($items['operation_type'] == 'OperationItemReturn')) {
                // обработано выше (Где есть артикул товара)
        } elseif ($items['operation_type'] == 'ClientReturnAgentOperation') {
                // обработано выше (Где есть артикул товара)
        } elseif ($items['operation_type'] == 'ClientReturnAgentOperation') {
                $Summa_dostav_vozvratov = @$Summa_dostav_vozvratov  + $items['amount'];
        } elseif ($items['operation_type'] == 'OperationAgentStornoDeliveredToCustomer') { // какая то отмена при оплате последней мили
                $Sum_dop_last_mile = @$Sum_dop_last_mile + $items['amount']; ////////////////******* DELETE */
        } else {
                $arr_ALARM_vozvrztov[] = $items;
        }
}









if (isset($arr_ALARM_vozvrztov) ){
echo "<br>******************************************< ARRAY ALARM VOZVRATOV ******************************************<br>";
echo "<pre>";
 print_R($arr_ALARM_vozvrztov);
 echo "</pre>";
 echo "<br>******************************************< END ARRAY ALARM VOZVRATOV ******************************************<br>";
       
} else {
        // echo "НЕТ НЕОБРАБОТАННЫХ ВОЗВРАТОВ";
}
