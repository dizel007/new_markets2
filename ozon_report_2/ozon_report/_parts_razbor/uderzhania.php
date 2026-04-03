<?php

if (isset($arr_compensation)){
    foreach ($arr_compensation as $items) {
        $i++;
        $our_item = $items['items'];
        // print_r($items);
    // перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
            foreach ($our_item as $item) {
    
     ///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке
     $new_sku = $item['sku'];
    
                $arr_article[$new_sku]['name'] = $item['name'];
                $arr_article[$new_sku]['sku'] =  $new_sku;
        
            }
    // количество товаров в заказе, которые вернули
        $arr_article[$new_sku]['count_compensation'] = @$arr_article[$new_sku]['count_compensation'] + count($our_item);
    // Суммируем суммы операции, которые возвраты
        $arr_article[$new_sku]['compensation'] = @$arr_article[$new_sku]['compensation'] + $items['amount']; 
        $arr_for_compensation[$items['operation_type_name']] = @$arr_for_compensation[$items['operation_type_name']] + $items['amount'];
    }
    }

