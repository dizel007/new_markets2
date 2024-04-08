<?php
if (isset ($arr_services)) {
foreach ($arr_services as $items) {
    $i++;
    $service_obrabotan = 0;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
        foreach ($our_item as $item) {

  ///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке
  $new_sku = change_SKU_fbo_fbs($ozon_sebest, $item['sku']);
  
  
            $arr_article[$new_sku]['name'] = $item['name'];
            $arr_article[$new_sku]['sku'] = $new_sku;
        if (($items['operation_type'] == 'OperationMarketplaceReturnStorageServiceAtThePickupPointFbs')  OR 
            ($items['operation_type'] == 'OperationMarketplaceReturnDisposalServiceFbs') )
            {
                // Начисление за хранение/утилизацию возвратов
                $arr_article[$new_sku]['count_hranenie'] = @$arr_article[$new_sku]['count_hranenie'] + 1;
                $arr_article[$new_sku]['amount_hranenie'] = @$arr_article[$new_sku]['amount_hranenie'] + $items['amount']/count($our_item);
                $service_obrabotan = 1;
            }
        }
if ($service_obrabotan == 1) {
    continue;
}
// СУмма по сервисами
if ($items['operation_type'] == 'MarketplaceMarketingActionCostOperation') 
    { // Услуги продвижения товаров
        $Summa_uslugi_prodvizhenia_tovara = @$Summa_uslugi_prodvizhenia_tovara  + $items['amount']; 
    } 
elseif ($items['operation_type'] == 'MarketplaceSaleReviewsOperation')
    {  //Приобретение отзывов на платформе
        $Summa_buy_otzivi = @$Summa_buy_otzivi  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketPlaceItemPinReview')
    {  // Закрепление отзыва
        $Summa_zakrepleneie_otzivi = @$Summa_zakrepleneie_otzivi  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceDefectRate')
    {  //Закрепление отзыва 
        $Summa_izmen_uslovi_otgruzki = @$Summa_izmen_uslovi_otgruzki  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'DefectRateShipmentDelay')
    {  //"Услуга за обработку операционных ошибок продавца: просроченная отгрузка
        $Summa_oshibok_prodavca = @$Summa_oshibok_prodavca  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceSupplyAdditional')
    {  //Обработка товара в составе грузоместа на FBO
        $Summa_obrabotka_gruzomestFBO = @$Summa_obrabotka_gruzomestFBO  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'MarketplaceServiceItemVideoCover')
    {  //Генерация видеообложки
        $Summa_generacia_videooblozhki = @$Summa_generacia_videooblozhki  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplacePremiumSubscribtion')
    {  //Premium-подписка
        $Summa_premiaum_podpiska = @$Summa_premiaum_podpiska  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceStorage')
    {  //Premium-подписка
        $Summa_hranenia_FBO = @$Summa_hranenia_FBO  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceStockDisposal')
    {  //Premium-подписка
        $Summa_utilizacii_tovara = @$Summa_utilizacii_tovara  + $items['amount']; 
    }

else {
    $Summa_neizvestnogo =  @$Summa_neizvestnogo  + $items['amount']; 
    $arr_xz_service[] = $items; 
}
    
}

}
// print_r($arr_xz_service);