<?php 

// echo "<pre>";
// print_r( $arr_orders);


foreach ($arr_orders as $items) {


    $i++;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
    foreach ($our_item as $item) {
// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке

        $new_sku =  $item['sku'];
        
        $arr_article[$new_sku]['name'] = $item['name'];
        $arr_article[$new_sku]['sku'] = $new_sku;
     // количество товаров в заказе 
        $arr_article[$new_sku]['count']['direct'] = @$arr_article[$new_sku]['count']['direct'] + 1;
     // Суммируем суммы операции
        $arr_article[$new_sku]['amount']['direct'] = @$arr_article[$new_sku]['amount']['direct'] + $items['amount']/count($our_item); 
     // Суммируем Комиссию за продажу     
        $arr_article[$new_sku]['sale_commission']['direct'] = @$arr_article[$new_sku]['sale_commission']['direct'] + $items['sale_commission']/count($our_item);
     // Цена для покупателя    
        $arr_article[$new_sku]['accruals_for_sale']['direct'] = @$arr_article[$new_sku]['accruals_for_sale']['direct'] + $items['accruals_for_sale']/count($our_item);

//***************************** РАЗБИВАЕМ ТОВАРЫ ПО СХЕМЕ ПОСТАВКИ ************************ */
        if ($items['posting']['delivery_schema'] == 'FBO') {
            // количество товаров в заказе 
            $arr_article[$new_sku]['count']['FBO_direct'] = @$arr_article[$new_sku]['count']['FBO_direct'] + 1;
            // Суммируем суммы операции
            $arr_article[$new_sku]['amount']['FBO_direct'] = @$arr_article[$new_sku]['amount']['FBO_direct'] + $items['amount']/count($our_item); 
       // Суммируем Комиссию за продажу     
       $arr_article[$new_sku]['sale_commission']['FBO_direct'] = @$arr_article[$new_sku]['sale_commission']['FBO_direct'] + $items['sale_commission']/count($our_item);
        // Цена для покупателя    
        $arr_article[$new_sku]['accruals_for_sale']['FBO_direct'] = @$arr_article[$new_sku]['accruals_for_sale']['FBO_direct'] + $items['accruals_for_sale']/count($our_item);
    
    } elseif ($items['posting']['delivery_schema'] == 'FBS') {
            // количество товаров в заказе 
            $arr_article[$new_sku]['count']['FBS_direct'] = @$arr_article[$new_sku]['count']['FBS_direct'] + 1;
            // Суммируем суммы операции
            $arr_article[$new_sku]['amount']['FBS_direct'] = @$arr_article[$new_sku]['amount']['FBS_direct'] + $items['amount']/count($our_item); 

               // Суммируем Комиссию за продажу     
      $arr_article[$new_sku]['sale_commission']['FBS_direct'] = @$arr_article[$new_sku]['sale_commission']['FBS_direct'] + $items['sale_commission']/count($our_item);
              // Цена для покупателя    
        $arr_article[$new_sku]['accruals_for_sale']['FBS_direct'] = @$arr_article[$new_sku]['accruals_for_sale']['FBS_direct'] + $items['accruals_for_sale']/count($our_item);
    

        } else {
            // количество товаров в заказе 
            $arr_article[$new_sku]['countXXX'] = @$arr_article[$new_sku]['countXXX'] + 1;
            // Суммируем суммы операции
            $arr_article[$new_sku]['amountXXX'] = @$arr_article[$new_sku]['amountXXX'] + $items['amount']/count($our_item);   
            // Суммируем Комиссию за продажу     
           $arr_article[$new_sku]['sale_commissionXXX'] = @$arr_article[$new_sku]['sale_commissionXXX'] + $items['sale_commission']/count($our_item);
        }
//*************************************************** */

    }

// перебираем массив services 

    foreach ($items['services'] as $services) { // перебираем массив services 

        $arr_article[$new_sku]['logistika']['direct'][$services['name']] = @$arr_article[$new_sku]['logistika']['direct'][$services['name']] + $services['price'];

        $arr_sum_logistika[$services['name']] = @$arr_sum_logistika[$services['name']] + $services['price'];



    }

////////////////////////////// Разбираем массив Севисы по типу поставки ФБО или ФБС //////////////////////////////////////
    if ($items['posting']['delivery_schema'] == 'FBO') {
        foreach ($items['services'] as $services) 
            { // перебираем массив services 
               $arr_article[$new_sku]['logistika']['FBO_direct'][$services['name']] = @$arr_article[$new_sku]['logistika']['FBO_direct'][$services['name']] + $services['price'];
            }
    } elseif ($items['posting']['delivery_schema'] == 'FBS') {
         foreach ($items['services'] as $services) 
            { // перебираем массив services 
                $arr_article[$new_sku]['logistika']['FBS_direct'][$services['name']] = @$arr_article[$new_sku]['logistika']['FBS_direct'][$services['name']] + $services['price'];
            }
    } else {
         foreach ($items['services'] as $services) 
            { // перебираем массив services 
                 echo "<br>******************************* НЕ НАШЛИ КУАД ПРИВЯЗАТЬ ПРЯМУЮ ЛОГИСТИКУ  *********************<br>";    
                $arr_article[$new_sku]['logistika']['XXX_direct'][$services['name']] = @$arr_article[$new_sku]['logistika']['XXX_direct'][$services['name']] + $services['price'];
            }    
    }


}

// echo "<pre>";
// print_r( $arr_article);

