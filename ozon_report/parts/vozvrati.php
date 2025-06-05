<?php


// print_r($arr_returns);

foreach ($arr_returns as $items) {

        $i++;
        $our_item = $items['items'];
        // перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
        foreach ($our_item as $item) {
                // print_r($items);
                ///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке
                $new_sku = change_SKU_fbo_fbs($ozon_sebest, $items['items'][0]['sku']);

                $arr_article[$new_sku]['name'] = $item['name'];
                $arr_article[$new_sku]['sku'] = $new_sku;


                /// Разбиваем стоиомть возвратов на логистику и обработку ... может еще что то
                //Доставка и обработка возврата, отмены, невыкупа
                if (($items['operation_type'] == 'OperationReturnGoodsFBSofRMS')  || ($items['operation_type'] == 'OperationItemReturn')) {
                        foreach ($items['services'] as $return_dop_obrabotka) {
                                // логистика
                                if ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDirectFlowLogistic') {
                                        $arr_article[$new_sku]['logistika_vozvrat'] = @$arr_article[$new_sku]['logistika_vozvrat'] + $return_dop_obrabotka['price'];
                                        $arr_article[$new_sku]['logistika_vozvrat_count'] = @$arr_article[$new_sku]['logistika_vozvrat_count'] + 1;
                                        $logistika_vozvrat_count = @$logistika_vozvrat_count + 1;
                                }
                                // логистика посленяя миля
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemRedistributionLastMileCourier') {
                                        $arr_article[$new_sku]['logistika_vozvrat'] = @$arr_article[$new_sku]['logistika_vozvrat'] + $return_dop_obrabotka['price'];
                                }
                                
                                // обратная логистика
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemReturnFlowLogistic') {

                                        $arr_article[$new_sku]['back_logistika_vozvrat'] = @$arr_article[$new_sku]['back_logistika_vozvrat'] + $return_dop_obrabotka['price'];
                                        $arr_article[$new_sku]['back_logistika_count'] = @$arr_article[$new_sku]['back_logistika_count'] + 1;
                                        $back_logistika_count = @$back_logistika_count + 1;
                                }
                                // обработка отправления.
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDropoffSC') {
                                        $arr_article[$new_sku]['obrabotka_otpravlenii_v_SC'] = @$arr_article[$new_sku]['obrabotka_otpravlenii_v_SC'] + $return_dop_obrabotka['price'];
                                }
                                // обработка возврата,
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDelivToCustomer') {
                                        $arr_article[$new_sku]['obrabotka_otpravlenii_v_SC'] = @$arr_article[$new_sku]['obrabotka_otpravlenii_v_SC'] + $return_dop_obrabotka['price'];
                                }
                                
                                // обработка отправления в ПВЗ.
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDropoffPVZ') {
                                        $arr_article[$new_sku]['obrabotka_otpravlenii_v_PVZ'] = @$arr_article[$new_sku]['obrabotka_otpravlenii_v_PVZ'] + $return_dop_obrabotka['price'];
                                }
                                
                                /// сборка заказа
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemFulfillment') {
                                        $arr_article[$new_sku]['sborka_zakaza'] = @$arr_article[$new_sku]['sborka_zakaza'] + $return_dop_obrabotka['price'];
                                }
                                /// перевыставление возвратов на ПВЗ.
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemRedistributionReturnsPVZ') {
                                        $arr_article[$new_sku]['return_obrabotka'] = @$arr_article[$new_sku]['return_obrabotka'] + $return_dop_obrabotka['price'];
                                }

                                /// Обработка отмен.
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemReturnNotDelivToCustomer') {
                                        $arr_article[$new_sku]['work_otmen'] = @$arr_article[$new_sku]['work_otmen'] + $return_dop_obrabotka['price'];
                                }
                                /// обработка возврата.
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemReturnAfterDelivToCustomer') {
                                        $arr_article[$new_sku]['work_vozvrata'] = @$arr_article[$new_sku]['work_vozvrata'] + $return_dop_obrabotka['price'];
                                }

                                /// магистраль
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDirectFlowTrans') {
                                        $arr_article[$new_sku]['logistika_magistral'] = @$arr_article[$new_sku]['logistika_magistral'] + $return_dop_obrabotka['price'];
                                        $logistika_magistral = @$logistika_magistral + $return_dop_obrabotka['price'];
                                }
                                /// обратная магистраль
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemReturnFlowTrans') {
                                        $arr_article[$new_sku]['logistika_obrat_magistral'] = @$arr_article[$new_sku]['logistika_obrat_magistral'] + $return_dop_obrabotka['price'];
                                }
                                /// обработка невыкупа.
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemReturnPartGoodsCustomer') {
                                        $arr_article[$new_sku]['obtabotka_nevikupa'] = @$arr_article[$new_sku]['obtabotka_nevikupa'] + $return_dop_obrabotka['price'];
                                } else {
                                        print_R($items);
                                        echo "<br>***************** " . $return_dop_obrabotka['name'] . " *************************<br>";
                                        $arr_ALARM_vozvrztov['СЕРВИСЫ_ВОЗВРАТОВ'][] = $items;
                                }
                        }
                        //Доставка и обработка возврата, отмены, невыкупа
                        $Summa_dostav_i_obrabotyka_vozvratov = @$Summa_dostav_i_obrabotyka_vozvratov  + $items['amount'];
                        $arr_returns_article[$new_sku] = @$arr_returns_article[$new_sku] + 1;
                } // Получение возврата, отмены, невыкупа от покупателя
                elseif ($items['operation_type'] == 'ClientReturnAgentOperation') {

                        // количество товаров в заказе, которые вернули
                        $arr_article[$new_sku]['get_vozvrat_count'] = @$arr_article[$new_sku]['get_vozvrat_count'] + 1;
                        // Суммируем суммы операции, которые возвраты
                        $arr_article[$new_sku]['get_vozvrat_amount'] = @$arr_article[$new_sku]['get_vozvrat_amount'] + $items['amount'];
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
