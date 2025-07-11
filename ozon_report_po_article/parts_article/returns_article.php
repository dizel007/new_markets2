<?php


// print_r($arr_returns);
$gg = 0;
foreach ($arr_returns as $big_key => $items) {

        $i++;

        $our_item = $items['items'];
  $new_post_number = make_posting_number($items['posting']['posting_number']);
        // техническая инфа 
        $arr_article[$new_post_number]['operation_id'][] = $items['operation_id'];
        

        // перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)

        ///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке
        
        

      
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

                // if ($new_post_number == '0102957581-0048') {
                //         echo "<br>***********************<br>";
                //         print_r($items);

                //         echo "<br>***********************<br>";
                // }
                // print_r($items);
                // Разбиваем стоиомть возвратов на логистику и обработку ... может еще что то
                //Доставка и обработка возврата, отмены, невыкупа
                if (($items['operation_type'] == 'OperationReturnGoodsFBSofRMS')  || ($items['operation_type'] == 'OperationItemReturn')
                        || ($items['operation_type'] == 'OperationAgentStornoDeliveredToCustomer')
                ) {



                        foreach ($items['services'] as $return_dop_obrabotka) {

                                // логистика 77777 99999
                                if ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDirectFlowLogistic') {
                                        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] =
                                                @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] + $return_dop_obrabotka['price'];
                                }
                                // логистика посленяя миля
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemRedistributionLastMileCourier') {
                                       $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] =
                                                @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] + $return_dop_obrabotka['price'];
                                
                                }
                                
                                // последняя миля (возрат обратная логистика) 99999
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDelivToCustomer') {
                                        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['last_mile11'] =
                                                @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['last_mile11']  + $return_dop_obrabotka['price'];
                                }
                                // обратная логистика 77777
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemReturnFlowLogistic') {
                                         $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] =
                                                @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] + $return_dop_obrabotka['price'];
                                }
                                // обработка отправления.      77777 99999
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDropoffSC') {
                                        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] =
                                                @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] + $return_dop_obrabotka['price'];
                                }
                                // обработка отправления в ПВЗ.
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDropoffPVZ') {
                                        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] =
                                                @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] + $return_dop_obrabotka['price'];
                                }

                                /// сборка заказа**************************************************************
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemFulfillment') {
                                        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] =
                                                @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] + $return_dop_obrabotka['price'];
                                }
                                /// перевыставление возвратов на ПВЗ. 77777
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemRedistributionReturnsPVZ') {
                                        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] =
                                                @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['logistika_vozvrat'] + $return_dop_obrabotka['price'];
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
                                /// обработка невыкупа. 77777
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
                        // возврат денег и возврат комиссии
                        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['accruals_for_sale'] =
                                @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['accruals_for_sale'] + $items['accruals_for_sale'];
                        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['sale_commission'] =
                                @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['sale_commission'] + $items['sale_commission'];
                        $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['amount'] =
                                @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['amount'] + $items['amount'];
                }
        }

        // Разбираем Получение возврата, отмены, невыкупа от покупателя
        if (($items['operation_type'] == 'OperationReturnGoodsFBSofRMS')  || ($items['operation_type'] == 'OperationItemReturn')) {
                // обработано выше (Где есть артикул товара)
        } elseif ($items['operation_type'] == 'ClientReturnAgentOperation') {
                $Summa_dostav_vozvratov = @$Summa_dostav_vozvratov  + $items['amount'];
        } elseif ($items['operation_type'] == 'OperationAgentStornoDeliveredToCustomer') { // Доставка покупателю — отмена начисления (нам деньги)

                $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['accruals_for_sale'] =
                        @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['accruals_for_sale'] + $items['accruals_for_sale'];

                $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['sale_commission'] =
                        @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['sale_commission'] + $items['sale_commission'];

                $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['amount'] =
                        @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['amount'] + $items['amount'];
                //  последняя миля

                foreach ($items['services'] as $return_dop_obrabotka_2) {
                        if ($return_dop_obrabotka_2['name'] == 'MarketplaceServiceItemDelivToCustomer') {
                                $arr_article[$new_post_number]['items_returns'][$new_post_number_full]['last_mile'] =
                                        @$arr_article[$new_post_number]['items_returns'][$new_post_number_full]['last_mile'] + $return_dop_obrabotka_2['price'];
                        }
                }
        } else {
                $arr_ALARM_vozvrztov[] = $items;
        }

 } // КОНЕЦ ПЕРЕБОРА МАССИВА


/***********************
 * 
 * 
 * 
 */




foreach ($arr_article as &$k_items) {
        if (isset($k_items['items_returns']) and (isset($k_items['items_buy']))) {

                // echo "<br>***********#########---------$new_post_number-----------################*******************<br>";

                foreach ($k_items['items_returns'] as $return_key => $return_items) {
                        foreach ($k_items['items_buy'] as $sell_key => &$sell_items) {
                                if ($sell_key === $return_key) {

                    
                                        $sell_items['delete_return'] = 1;

                                        $sell_items['accruals_for_sale'] += $return_items['accruals_for_sale'];
                                        $sell_items['sale_commission'] += $return_items['sale_commission'];
                                        $sell_items['amount'] += $return_items['amount'];




                                        if (isset($return_items['last_mile'])) {
                                                $sell_items['last_mile'] += $return_items['last_mile'];
                                        }
                                        if (isset($return_items['obrabotka_otpravlenii_v_SC'])) {
                                                $sell_items['obrabotka_otpravlenia'] += $return_items['obrabotka_otpravlenii_v_SC'];
                                        }
                                        if (isset($return_items['logistika_vozvrat'])) {
                                                $sell_items['logistika'] += $return_items['logistika_vozvrat'];
                                        }

                                        // echo  $sell_items['last_mile']."jjjjj".$return_items['last_mile']."<br>";
                                        //  print_r($sell_items);


                                }
                        }
                }
        }
}
/******************************************** */







if (isset($arr_ALARM_vozvrztov)) {
        echo "<br>******************************************< ARRAY ALARM VOZVRATOV ******************************************<br>";
        echo "<pre>";
        print_R($arr_ALARM_vozvrztov);
        echo "</pre>";
        echo "<br>******************************************< END ARRAY ALARM VOZVRATOV ******************************************<br>";
} else {
        // echo "НЕТ НЕОБРАБОТАННЫХ ВОЗВРАТОВ";
}
