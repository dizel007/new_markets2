<?php


foreach ($arr_returns as $item222s) {
        $gggg[$item222s['operation_type']] = $item222s['operation_type'];
}

echo "<pre>";
print_r($gggg);
print_r($arr_returns);

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
                if (($items['operation_type'] == 'OperationReturnGoodsFBSofRMS') || ($items['operation_type'] == 'OperationItemReturn')) //Доставка и обработка возврата, отмены, невыкупа
                {
                        foreach ($items['services'] as $return_dop_obrabotka) {
                                // Стоимость прямой логистики для возврата
                                if ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemDirectFlowLogistic') {
                                        $arr_article[$new_sku]['logistika'] = @$arr_article[$new_sku]['logistika'] + $return_dop_obrabotka['price'];
                                }
                                // Стоимость обратной логистик для возвартов
                                elseif ($return_dop_obrabotka['name'] == 'MarketplaceServiceItemReturnFlowLogistic') {

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
                                } else {
                                        $arr_ALARM_vozvrztov['СЕРВИСЫ_ВОЗВРАТОВ'][] = $items;
                                }
                        }
                        //Доставка и обработка возврата, отмены, невыкупа
                        $Summa_dostav_i_obrabotyka_vozvratov = @$Summa_dostav_i_obrabotyka_vozvratov  + $items['amount'];
                        $count_returns = @$count_returns + 1;
                        $arr_returns_article[$new_sku] = @$arr_returns_article[$new_sku] + 1;
                }
                if ($items['operation_type'] == 'ClientReturnAgentOperation')
                // Разбираем Получение возврата, отмены, невыкупа от покупателя
                {
                        // количество товаров в заказе, которые вернули
                        $arr_article[$new_sku]['count_vozvrat'] = @$arr_article[$new_sku]['count_vozvrat'] + 1;
                        // Суммируем суммы операции, которые возвраты
                        $arr_article[$new_sku]['amount_vozrat'] = @$arr_article[$new_sku]['amount_vozrat'] + $items['amount'] / count($our_item);
                }
                // else {
                //         $arr_ALARM_vozvrztov['СЕРВИСЫ_ВОЗВРАТОВ'][]=$items;
                // }

        }


        if ($items['operation_type'] == 'ClientReturnAgentOperation')
        // Разбираем Получение возврата, отмены, невыкупа от покупателя
        {
                $Summa_dostav_vozvratov = @$Summa_dostav_vozvratov  + $items['amount'];
        } elseif ($items['operation_type'] == 'OperationAgentStornoDeliveredToCustomer') { // какая то отмена при оплате последней мили
                $Sum_dop_last_mile = @$Sum_dop_last_mile + $items['amount']; ////////////////******* DELETE */
        } else {
                $arr_nerazjbrannoe[] = $items;
        }
}



echo "Summa_dostav_i_obrabotyka_vozvratov= $Summa_dostav_i_obrabotyka_vozvratov<br>";
echo "Sum_dop_last_mile= $Sum_dop_last_mile<br>";
echo "Summa_dostav_vozvratov= $Summa_dostav_vozvratov<br>";
echo "count_returns= $count_returns<br>";


print_R($arr_returns_article);
