<?php

$sum_uderzhania = 0;

if (isset($arr_compensation)){
    foreach ($arr_compensation as $items) {
        $i++;
    
        
      
        
        
    ///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке
if ($items['posting']['posting_number'] != '') {
    $new_post_number = make_posting_number ($items['posting']['posting_number']);
      // техническая инфа 
    $arr_article[$new_post_number]['operation_id'][] = $items['operation_id'];

    $arr_article[$new_post_number]['UDERZHANIA'] = 'UDERZHANIA';
    $arr_article[$new_post_number]['order_date'] = $items['posting']['order_date'];

      // количество товаров в заказе, которые вернули
        $arr_article[$new_post_number]['count_compensation'] = @$arr_article[$new_post_number]['count_compensation'] + count($our_item);
    // Суммируем суммы операции, которые возвраты
        $arr_article[$new_post_number]['compensation'] = @$arr_article[$new_post_number]['compensation'] + $items['amount']; 

} else {
    $sum_uderzhania += $items['amount'];  // сумма всех удержаний
}
    }
    }