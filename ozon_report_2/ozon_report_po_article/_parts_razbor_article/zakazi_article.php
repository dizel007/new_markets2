<?php 

// echo "<pre>";
// print_r($arr_type_items_WITH_POSTING_NUMBER['orders'][17]);

$summa_accruals_for_sale = 0;
foreach ($arr_type_items_WITH_POSTING_NUMBER['orders'] as $items) {
    $priznak_nashego_sku = 0;
    $i_orders++;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
    foreach ($our_item as $item) {
                
        $new_posting_number = $items['posting']['posting_number']; // ставим сюда нормер заказа
        $arr_article[$new_posting_number]['posting_number'] =  $new_posting_number;
        $arr_article[$new_posting_number]['order_date'] = $items['posting']['order_date'];
        
        $arr_article[$new_posting_number]['delivery_schema'] = $items['posting']['delivery_schema'];
        
        
        
        $arr_article[$new_posting_number]['name'] = $item['name'];
        $arr_article[$new_posting_number]['sku'] =  $item['sku'];

        $arr_article[$new_posting_number]['type_operation']['direct'] =  'Продажа';
     // количество товаров в заказе 
        $arr_article[$new_posting_number]['count']['direct'] = @$arr_article[$new_posting_number]['count']['direct'] + 1;
     // Суммируем суммы операции
        $arr_article[$new_posting_number]['amount'] = @$arr_article[$new_posting_number]['amount'] + $items['amount']/count($our_item); 
     // Суммируем Комиссию за продажу     
        $arr_article[$new_posting_number]['sale_commission'] = @$arr_article[$new_posting_number]['sale_commission'] + $items['sale_commission']/count($our_item);
     // Цена для покупателя    
        $arr_article[$new_posting_number]['accruals_for_sale'] = @$arr_article[$new_posting_number]['accruals_for_sale'] + $items['accruals_for_sale']/count($our_item);
    
     // суммы с накопительным итогом
        $arr_summa_data['summa_accruals_for_sale'] =  @$arr_summa_data['summa_accruals_for_sale'] + $items['accruals_for_sale']/count($our_item); // 
        $arr_summa_data['sale_commission'] = @$arr_summa_data['sale_commission'] + $items['sale_commission']/count($our_item); // 
        $arr_summa_data['amount'] = @$arr_summa_data['amount']+ $items['amount']/count($our_item);  // 
        $arr_summa_data['count']['direct'] = @$arr_summa_data['count']['direct'] + 1 ; // 

    }

// перебираем массив services 
 foreach ($items['services'] as $services) { // перебираем массив services 
         // если не наше СКУ, то нужно пролистать его 
        $arr_article[$new_posting_number]['logistika']['direct'][$services['name']] = @$arr_article[$new_posting_number]['logistika']['direct'][$services['name']] + $services['price'];
        $arr_sum_logistika[$services['name']] = @$arr_sum_logistika[$services['name']] + $services['price'];
        /// сумма логистики с накопительным итогом
         $arr_summa_data['logistika']['direct'] =  @$arr_summa_data['logistika']['direct'] + $services['price']; ; // 



    }

}

// echo "<pre>";
// print_r( $arr_article);
// print_r( $arr_summa_data);



