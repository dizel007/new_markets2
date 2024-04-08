<?php 
foreach ($arr_orders as $items) {
    $i++;
    $our_item = $items['items'];
    @$all_summa_tovarov_ += $items['accruals_for_sale'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
    foreach ($our_item as $item) {
///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке

            $new_sku = change_SKU_fbo_fbs($ozon_sebest, $item['sku']);
        
            // echo "<br>NEW_SKU = ".$new_sku."|||| OLD SKU = ".$item['sku']."<br>";
        $arr_article[$new_sku]['name'] = $item['name'];
        $arr_article[$new_sku]['sku'] = $new_sku;
     // количество товаров в заказе 
       $arr_article[$new_sku]['count'] = @$arr_article[$new_sku]['count'] + 1;
     // Суммируем суммы операции
       $arr_article[$new_sku]['amount'] = @$arr_article[$new_sku]['amount'] + $items['amount']/count($our_item); 
     // Суммируем Комиссию за продажу     
        $arr_article[$new_sku]['sale_commission'] = @$arr_article[$new_sku]['sale_commission'] + $items['sale_commission']/count($our_item);
     // Цена для покупателя    
     $arr_article[$new_sku]['accruals_for_sale'] = @$arr_article[$new_sku]['accruals_for_sale'] + $items['accruals_for_sale']/count($our_item);

//***************************** РАЗБИВАЕМ ТОВАРЫ ПО СХЕМЕ ПОСТАВКИ ************************ */
        if ($items['posting']['delivery_schema'] == 'FBO') {
            // количество товаров в заказе 
            $arr_article[$new_sku]['countFBO'] = @$arr_article[$new_sku]['countFBO'] + 1;
            // Суммируем суммы операции
            $arr_article[$new_sku]['amountFBO'] = @$arr_article[$new_sku]['amountFBO'] + $items['amount']/count($our_item); 
       // Суммируем Комиссию за продажу     
       $arr_article[$new_sku]['sale_commissionFBO'] = @$arr_article[$new_sku]['sale_commissionFBO'] + $items['sale_commission']/count($our_item);
        } elseif ($items['posting']['delivery_schema'] == 'FBS') {
            // количество товаров в заказе 
            $arr_article[$new_sku]['countFBS'] = @$arr_article[$new_sku]['countFBS'] + 1;
            // Суммируем суммы операции
            $arr_article[$new_sku]['amountFBS'] = @$arr_article[$new_sku]['amountFBS'] + $items['amount']/count($our_item); 

               // Суммируем Комиссию за продажу     
      $arr_article[$new_sku]['sale_commissionFBS'] = @$arr_article[$new_sku]['sale_commissionFBS'] + $items['sale_commission']/count($our_item);
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



    foreach ($items['services'] as $services) { // перебираем массив services 
            if ($services['name'] == 'MarketplaceServiceItemDirectFlowLogistic') {
//логистика
                $arr_article[$new_sku]['logistika'] = @$arr_article[$new_sku]['logistika'] + $services['price']; // суммма логистики
            }
            if ($services['name'] == 'MarketplaceServiceItemDropoffSC') {
// обработка отправления
                $arr_article[$new_sku]['sborka'] = @$arr_article[$new_sku]['sborka'] + $services['price']; // суммма логистики
            }
            if ($services['name'] == 'MarketplaceServiceItemDelivToCustomer') {
//последняя миля.
                $arr_article[$new_sku]['lastMile'] = @$arr_article[$new_sku]['lastMile'] + $services['price']; // суммма логистики
            }
    }

////////////////////////////// Разбираем массив Севисы по типу поставки ФБО или ФБС //////////////////////////////////////
    if ($items['posting']['delivery_schema'] == 'FBO') {
        foreach ($items['services'] as $services) { // перебираем массив services 
            if ($services['name'] == 'MarketplaceServiceItemDirectFlowLogistic') {
    //логистика
                $arr_article[$new_sku]['logistikaFBO'] = @$arr_article[$new_sku]['logistikaFBO'] + $services['price']; // суммма логистики
            }
            if ($services['name'] == 'MarketplaceServiceItemDropoffSC') {
    // обработка отправления
                $arr_article[$new_sku]['sborkaFBO'] = @$arr_article[$new_sku]['sborkaFBO'] + $services['price']; // суммма логистики
            }
            if ($services['name'] == 'MarketplaceServiceItemDelivToCustomer') {
    //последняя миля.
                $arr_article[$new_sku]['lastMileFBO'] = @$arr_article[$new_sku]['lastMileFBO'] + $services['price']; // суммма логистики
            }
    }
    } elseif ($items['posting']['delivery_schema'] == 'FBS') {
        foreach ($items['services'] as $services) { // перебираем массив services 
            if ($services['name'] == 'MarketplaceServiceItemDirectFlowLogistic') {
    //логистика
                $arr_article[$new_sku]['logistikaFBS'] = @$arr_article[$new_sku]['logistikaFBS'] + $services['price']; // суммма логистики
            }
            if ($services['name'] == 'MarketplaceServiceItemDropoffSC') {
    // обработка отправления
                $arr_article[$new_sku]['sborkaFBS'] = @$arr_article[$new_sku]['sborkaFBS'] + $services['price']; // суммма логистики
            }
            if ($services['name'] == 'MarketplaceServiceItemDelivToCustomer') {
    //последняя миля.
                $arr_article[$new_sku]['lastMileFBS'] = @$arr_article[$new_sku]['lastMileFBS'] + $services['price']; // суммма логистики
            }
    }
    } else {
        foreach ($items['services'] as $services) { // перебираем массив services 
            if ($services['name'] == 'MarketplaceServiceItemDirectFlowLogistic') {
    //логистика
                $arr_article[$new_sku]['logistikaXXX'] = @$arr_article[$new_sku]['logistikaXXX'] + $services['price']; // суммма логистики
            }
            if ($services['name'] == 'MarketplaceServiceItemDropoffSC') {
    // обработка отправления
                $arr_article[$new_sku]['sborkaXXX'] = @$arr_article[$new_sku]['sborkaXXX'] + $services['price']; // суммма логистики
            }
            if ($services['name'] == 'MarketplaceServiceItemDelivToCustomer') {
    //последняя миля.
                $arr_article[$new_sku]['lastMileXXX'] = @$arr_article[$new_sku]['lastMileXXX'] + $services['price']; // суммма логистики
            }
    } 
}








}