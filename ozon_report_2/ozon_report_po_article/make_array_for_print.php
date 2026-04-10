<?php
// echo "<pre>";
// print_r($arr_type_find_servives);
// die();
  
//     $arr_type_find_servives['Услуги доставки'] = array(
//                                 'MarketplaceServiceItemDirectFlowLogistic' => 'Прямая логистика',
//                                 'MarketplaceServiceItemReturnFlowLogistic' => 'Обратная логистика',
//                                 'MarketplaceServiceItemDropoffSC' => 'Обработка отправления Drop-off',
//                                 'MarketplaceServiceItemDeliveryToHandoverPlaceOzon' => 'Доставка до места выдачи силами Ozon',
//                                 );


//     $arr_type_find_servives['Услуги агентов'] = array(
//                                 'MarketplaceServiceItemRedistributionLastMileCourier' => 'Доставка до места выдачи',
//                                 'MarketplaceServiceItemRedistributionReturnsPVZ' => 'Обработка возвратов, отмен и невыкупов партнёрами',
//                                 'MarketplaceServiceItemDelivToCustomer' => 'Выдача товара',
//                                 'MarketplaceServiceItemRedistributionLastMilePVZ' => 'Выдача товара_'
//                                 ) ;

//   // добавляем эту трату вручную, потому что в озон она в типе операуии и в типах сервисах разные  
//    $arr_services_types['MarketplaceReturnStorageServiceAtThePickupPointFbsItem'] = 'Начисление за хранение/утилизацию возвратов';       

//     $arr_type_find_servives['сервисы'] =  $arr_services_types;
     
  

foreach ($arr_article as $key=>&$one_tovar) {
/***************************************************************** */ 
// Иностранные товары  добавляем их стоимость
/***************************************************************** */ 

$one_tovar['ino_prodazha'] = 0;
foreach ($arr_prod_inostran_prodazhi['products'] as $ino_sells_data) {
    
    if ($ino_sells_data['posting_number'] == $one_tovar['posting_number']) {
        $one_tovar['ino'] = 'ino';
        $one_tovar['accruals_for_sale'] = $ino_sells_data['amount'];
    }
}

/******************************************************************************** */ 
// Цепляем стоимость сервисов / ;от рекламных продаж (его в OTHER находим)
/********************************************************************************* */ 
// print_r($one_tovar);
$one_tovar['services_summa'] = 0;
if (isset($one_tovar['services']))
foreach ($one_tovar['services'] as $serv_price) {
//     echo "<br>iiiiiiiiiiiiiiiiiiiiiiiiiii ;";
// print_r($temp_6666);

   $one_tovar['services_summa'] = $one_tovar['services_summa'] + $serv_price;
 }

// если есть проданные товары 
//     
         $one_tovar['post_number_gruzomesto'] = $key;

        if (!isset($one_tovar['accruals_for_sale'])) {
            $one_tovar['accruals_for_sale'] = 0;
            $one_tovar['one_procent_accruals_for_sale'] = 0;
        } else {
            $one_tovar['accruals_for_sale'] = $one_tovar['accruals_for_sale']; // Цена в личном кабинете
            $one_tovar['one_procent_accruals_for_sale'] = round($one_tovar['accruals_for_sale']/100,2);
        }


// комиися озона
        if (!isset($one_tovar['sale_commission'])) {
            $one_tovar['sale_commission'] = 0;
            $one_tovar['ozon_procent_commition'] = 0;
        } else {
            if ($one_tovar['one_procent_accruals_for_sale']  !=0) {
                $one_tovar['ozon_procent_commition'] = round(-$one_tovar['sale_commission']/$one_tovar['one_procent_accruals_for_sale'] ,2);
            } else {
                $one_tovar['ozon_procent_commition'] = 0;  
            }

        }
 //// теперь переберем всю логистику   
       if (isset($one_tovar['logistika']['direct'])){
           $temp_direct_logistika = 0;
        foreach ($one_tovar['logistika']['direct'] as $type_dost=>$summa_dost) {
            $temp_direct_logistika = $temp_direct_logistika + $summa_dost;
        }
       $one_tovar['logistika_direct'] = $temp_direct_logistika;
        // процент стоимости доставки от цены кабинета
        if ($one_tovar['one_procent_accruals_for_sale'] != 0) {
             $one_tovar['procent_stoimosti_logistika_direct'] = $procent_stoimosti_logistiki =  round( -$temp_direct_logistika / $one_tovar['one_procent_accruals_for_sale'] , 1);
            } 
            else {
              $one_tovar['procent_stoimosti_logistika_direct'] = 0;  
            }
   
    } else {
        $one_tovar['logistika_direct'] = 0;
        $one_tovar['procent_stoimosti_logistika_direct'] = 0;  
    }    
/// обратная логистика  
       if (isset($one_tovar['logistika']['return'])){
           $temp_return_logistika = 0;
        foreach ($one_tovar['logistika']['return'] as $type_dost=>$summa_dost) {
            $temp_return_logistika = $temp_return_logistika + $summa_dost;
        }
       $one_tovar['logistika_return'] =  $temp_return_logistika;
        // процент стоимости доставки от цены кабинета
        if ($one_tovar['one_procent_accruals_for_sale'] != 0) {
             $one_tovar['procent_stoimosti_logistika_return'] = $procent_stoimosti_logistiki =  round( -$temp_return_logistika / $one_tovar['one_procent_accruals_for_sale'] , 1);
            } 
            else {
              $one_tovar['procent_stoimosti_logistika_return'] = '';  
            }
   
    } else {
        $one_tovar['logistika_return'] = 0;
        $one_tovar['procent_stoimosti_logistika_return'] = '';  
    }
// за вычетом всего
     $one_tovar['amount'] =  round($one_tovar['accruals_for_sale'] + 
                             $one_tovar['sale_commission']  +  
                             $one_tovar['logistika_direct']+
                             $one_tovar['logistika_return'],2) ;


// добавляем врцяную эквайринг 

if ($one_tovar['accruals_for_sale'] != 0) {
    // $one_tovar['acquiring'] = round($acquiring_sum/$acquiring_count,0);
    $one_tovar['acquiring'] = round($one_tovar['accruals_for_sale']*0.01,0);
    
} else {
    $one_tovar['acquiring'] = 0;
}
// дополнительные расходы
if ($one_tovar['accruals_for_sale'] != 0) {
    $one_tovar['dop_uslugi'] = round($dop_uslugi/$acquiring_count,0);
} else {
    $one_tovar['dop_uslugi'] = 0;
}

// цепляем у заказу штрафы если они есть 
$one_tovar['penalty'] = 0;
foreach ($arr_penalty_posting_numbers as $penalty_data) {
    if ($penalty_data['posting']['posting_number'] == $one_tovar['posting_number']) {
        $one_tovar['penalty'] = $penalty_data['amount'];
    }
}



// считаем итого на р/с
$one_tovar['amount_na_rs'] = $one_tovar['amount'] +  
                             $one_tovar['acquiring'] + 
                            //  $one_tovar['penalty'] +
                             $one_tovar['dop_uslugi'] + 
                             $one_tovar['services_summa'] ;

// Прибыль с заказа 
 /// если есть обратная логистика, то себестоиомсть не вычитаем
    if ( $one_tovar['logistika_return'] != 0 ) {
        $one_tovar['pribil'] = $one_tovar['amount_na_rs'];
    } else {
        $one_tovar['pribil'] = $one_tovar['amount_na_rs'] - ARTICLE_SEBESTOIMOST;
    }
/********************************************************************************************
Формируем массив с суммами
********************************************************************************************/
$arr_sum_article_data['accruals_for_sale'] = @$arr_sum_article_data['accruals_for_sale'] +  $one_tovar['accruals_for_sale'] ;
$arr_sum_article_data['sale_commission'] = @$arr_sum_article_data['sale_commission'] +  $one_tovar['sale_commission'];
$arr_sum_article_data['logistika_direct'] = @$arr_sum_article_data['logistika_direct'] +  $one_tovar['logistika_direct'];
$arr_sum_article_data['logistika_return'] = @$arr_sum_article_data['logistika_return'] +  $one_tovar['logistika_return'];
$arr_sum_article_data['amount'] = @$arr_sum_article_data['amount'] +  $one_tovar['amount'];
$arr_sum_article_data['acquiring'] = @$arr_sum_article_data['acquiring'] +  $one_tovar['acquiring'];
$arr_sum_article_data['dop_uslugi'] = @$arr_sum_article_data['dop_uslugi'] +  $one_tovar['dop_uslugi'];
$arr_sum_article_data['services_summa'] = @$arr_sum_article_data['services_summa'] +  $one_tovar['services_summa'];
$arr_sum_article_data['penalty'] = @$arr_sum_article_data['penalty'] +  $one_tovar['penalty'];
$arr_sum_article_data['amount_na_rs'] = @$arr_sum_article_data['amount_na_rs'] +  $one_tovar['amount_na_rs'];
$arr_sum_article_data['pribil'] = @$arr_sum_article_data['pribil'] +  $one_tovar['pribil'];



 }




//    print_R($arr_article['07016903-0234-2'])    ;