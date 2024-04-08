<?php 


foreach ($arr_returns as $items) {
    $i++;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
        foreach ($our_item as $item) {

///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке
            $new_sku = change_SKU_fbo_fbs($ozon_sebest, $item['sku']);

            $arr_article[$new_sku]['name'] = $item['name'];
            $arr_article[$new_sku]['sku'] = $new_sku;
    // количество товаров в заказе, которые вернули
            $arr_article[$new_sku]['count_vozvrat'] = @$arr_article[$new_sku]['count_vozvrat'] + 1;
  // Суммируем суммы операции, которые возвраты
  $arr_article[$new_sku]['amount_vozrat'] = @$arr_article[$new_sku]['amount_vozrat'] + $items['amount']/count($our_item);  
        }

    
 
}