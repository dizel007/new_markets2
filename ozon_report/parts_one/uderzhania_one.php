<?php

if (isset($arr_compensation)){
    foreach ($arr_compensation as $items) {
        $i++;
    ///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке

    $new_post_number = make_posting_number ($items['posting']['posting_number']);
    $arr_article[$new_post_number]['UDERZHANIA'] = 'UDERZHANIA';
    $arr_article[$new_post_number]['order_date'] = $items['posting']['order_date'];

    $new_sku = change_SKU_fbo_fbs($ozon_sebest, $item['sku']);
    $arr_article[$new_post_number]['name'] = $item['name'];
    $arr_article[$new_post_number]['sku'] =  $new_sku;

      // количество товаров в заказе, которые вернули
        $arr_article[$new_post_number]['count_compensation'] = @$arr_article[$new_post_number]['count_compensation'] + count($our_item);
    // Суммируем суммы операции, которые возвраты
        $arr_article[$new_post_number]['compensation'] = @$arr_article[$new_post_number]['compensation'] + $items['amount']; 
    }
    }