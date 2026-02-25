<?php

echo "<pre>";
if (isset($arr_services)) {
    foreach ($arr_services as $items) {
        $i++;
        $service_obrabotan = 0;
        $our_item = $items['items'];
     
        
        
       // перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
  // В эТОМ ИФЕ перебираем элементы массива, где мы можем преципить СЕРВИСЫ к номеру заказа
  //
        if (($items['operation_type'] == 'OperationMarketplaceReturnStorageServiceAtThePickupPointFbs')  OR
            ($items['operation_type'] == 'OperationMarketplaceReturnDisposalServiceFbs'))
         {
            // Начисление за хранение/утилизацию возвратов
          $new_post_number = make_posting_number($items['posting']['posting_number']);
           // техническая инфа 
            $arr_article[$new_post_number]['operation_id'][] = $items['operation_id'];
            $arr_article[$new_post_number]['order_date'] = $items['posting']['order_date'];
            $arr_article[$new_post_number]['SERVICES'] = 'SERVICES';
            $arr_article[$new_post_number]['amount_hranenie'] = @$arr_article[$new_post_number]['amount_hranenie'] + $items['amount'];
            $service_obrabotan = 1;
        } elseif (($items['operation_type'] === 'DefectRateDetailed') OR ($items['operation_type'] === 'DefectRateCancellation'))  {
            // Услуга за обработку операционных ошибок продавца: поздняя отгрузка
            // ИЛИ 
            // Услуга за обработку операционных ошибок продавца: поздняя отгрузка - отмена начисления
           
            $new_post_number = make_posting_number($items['posting']['posting_number']);
                // техническая инфа 
            $arr_article[$new_post_number]['operation_id'][] = $items['operation_id'];
            $arr_article[$new_post_number]['order_date'] = $items['posting']['order_date'];
            $arr_article[$new_post_number]['SERVICES'] = 'SERVICES';
            $arr_article[$new_post_number]['pozdniaa_otgruzka'] = @$arr_article[$new_post_number]['pozdniaa_otgruzka'] + $items['amount'];
            $service_obrabotan = 1;
        } elseif ($items['operation_type'] === 'MarketplaceServiceBrandCommission'){
            // Услуга за обработку операционных ошибок продавца: поздняя отгрузка - отмена начисления
            $new_post_number = make_posting_number($items['posting']['posting_number']);
                // техническая инфа 
            $arr_article[$new_post_number]['operation_id'][] = $items['operation_id'];
            $arr_article[$new_post_number]['order_date'] = $items['posting']['order_date'];
            $arr_article[$new_post_number]['SERVICES'] = 'SERVICES';
            $arr_article[$new_post_number]['prodvizenie_branda'] = @$arr_article[$new_post_number]['prodvizenie_branda'] + $items['amount'];
            $service_obrabotan = 1;

        }
        if ($service_obrabotan == 1) {
            continue;
        }



        // print_r($items);

        // СУмма по сервисами
// СУмма по сервисами
if ($items['operation_type'] == 'MarketplaceMarketingActionCostOperation') 
    { // Услуги продвижения товаров
        $arr_services_without_postNumber['Услуги продвижения товаров'] = 
            @$arr_services_without_postNumber['Услуги продвижения товаров'] + $items['amount'];
        // $Summa_uslugi_prodvizhenia_tovara = @$Summa_uslugi_prodvizhenia_tovara  + $items['amount']; 
    } 
elseif ($items['operation_type'] == 'OperationMarketPlaceItemPinReview')
    {  // Закрепление отзыва
        $arr_services_without_postNumber['Закрепление отзыва'] = 
            @$arr_services_without_postNumber['Закрепление отзыва'] + + $items['amount'];
        // $Summa_zakrepleneie_otzivi = @$Summa_zakrepleneie_otzivi  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceDefectRate')
    {  //Изменение условий отгрузки  
        $arr_services_without_postNumber['Изменение условий отгрузки'] = 
       @$arr_services_without_postNumber['Изменение условий отгрузки'] + $items['amount'];

        // $Summa_izmen_uslovi_otgruzki = @$Summa_izmen_uslovi_otgruzki  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'DefectRateShipmentDelay')
    {  //Услуга за обработку операционных ошибок продавца: просроченная отгрузка
        $arr_services_without_postNumber['Услуга за обработку операционных ошибок продавца: просроченная отгрузка'] = 
            @$arr_services_without_postNumber['Услуга за обработку операционных ошибок продавца: просроченная отгрузка'] + $items['amount'];
        // $Summa_oshibok_prodavca = @$Summa_oshibok_prodavca  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceSupplyInboundSupplyShortage')
    {  //Услуга по бронированию места и персонала для поставки с неполным составом
        $arr_services_without_postNumber['Услуга по бронированию места и персонала для поставки с неполным составом'] = 
       @$arr_services_without_postNumber['Услуга по бронированию места и персонала для поставки с неполным составом'] + $items['amount'];
        // $Summa_usluga_po_bronirovaniu_mesta_dla_postavku = @$Summa_usluga_po_bronirovaniu_mesta_dla_postavku  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceVolumeWeightCharacsProcessing')
    {  //Услуга по дополнительной обработке ОВХ
        $arr_services_without_postNumber['Услуга по дополнительной обработке ОВХ'] = 
        @$arr_services_without_postNumber['Услуга по дополнительной обработке ОВХ'] + $items['amount'];
        // $Summa_izmerenii_OVX = @$Summa_izmerenii_OVX  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceSupplyInboundCargoSurplus')
    {  //Услуга по дополнительной обработке товаров без номера заказа
        $arr_services_without_postNumber['Услуга по дополнительной обработке товаров без номера заказа'] = 
       @$arr_services_without_postNumber['Услуга по дополнительной обработке товаров без номера заказа'] + $items['amount'];

        // $Summa_obrabotka_opoznannih_izlishkov = @$Summa_obrabotka_opoznannih_izlishkov  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceProcessingSpoilageSurplus')
    {  //Обработка брака с приемки
        $arr_services_without_postNumber['Обработка брака с приемки'] = 
        @$arr_services_without_postNumber['Обработка брака с приемки'] + $items['amount'];

        // $Summa_obrabotka_braka_s_priemki = @$Summa_obrabotka_braka_s_priemki  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceSupplyAdditional')
    {  //Обработка товара в составе грузоместа на FBO
        $arr_services_without_postNumber['Обработка товара в составе грузоместа на FBO'] = 
        @$arr_services_without_postNumber['Обработка товара в составе грузоместа на FBO'] + $items['amount'];

        // $Summa_obrabotka_gruzomestFBO = @$Summa_obrabotka_gruzomestFBO  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'TemporaryStorage')
{  //Временное размещение товара в СЦ/ПВЗ
    $arr_services_without_postNumber['Временное размещение товара в СЦ/ПВЗ'] = 
    @$arr_services_without_postNumber['Временное размещение товара в СЦ/ПВЗ'] + $items['amount'];

    //    $Summa_vrenennoe_razmeshenie_tovara_v_SC = @$Summa_vrenennoe_razmeshenie_tovara_v_SC  + $items['amount']; 
}
elseif ($items['operation_type'] == 'OperationMarketplaceServiceStorage')
    {  //ФБО хранение
        $arr_services_without_postNumber['ФБО хранение'] = 
        @$arr_services_without_postNumber['ФБО хранение'] + $items['amount'];

        // $Summa_hranenia_FBO = @$Summa_hranenia_FBO  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceStockDisposal')
    {  //Утилизацция
                $arr_services_without_postNumber['Утилизацция'] = 
        @$arr_services_without_postNumber['Утилизацция'] + $items['amount'];

        
        $Summa_utilizacii_tovara = @$Summa_utilizacii_tovara  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'DisposalReasonFailedToPickupOnTime')
    {  //Утилизацция товара - не забрали в срок
         $arr_services_without_postNumber['Утилизацция товара - не забрали в срок'] = 
        @$arr_services_without_postNumber['Утилизацция товара - не забрали в срок'] + $items['amount'];

        // $Summa_utilizacii_tovara = @$Summa_utilizacii_tovara  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'DisposalReasonDamagedPackaging')
    {  //Утилизация товара: Повреждённые из-за упаковки
         $arr_services_without_postNumber['Утилизация товара: Повреждённые из-за упаковки'] = 
        @$arr_services_without_postNumber['Утилизация товара: Повреждённые из-за упаковки'] + $items['amount'];

        // $Summa_utilizacii_tovara_povrezhdenie_upakovki = @$Summa_utilizacii_tovara_povrezhdenie_upakovki  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceSupplyInboundCargoShortage')
    {  //УУслуга по бронированию места и персонала для поставки с неполным составом в составе ГМ
        $arr_services_without_postNumber['Услуга по бронированию места и персонала для поставки с неполным составом в составе ГМ'] = 
        @$arr_services_without_postNumber['Услуга по бронированию места и персонала для поставки с неполным составом в составе ГМ'] + $items['amount'];

        // $Summa_bronirovanie_mesta_i_personala = @$Summa_bronirovanie_mesta_i_personala  + $items['amount']; 
    }

elseif ($items['operation_type'] == 'OperationElectronicServiceStencil')
    {  //Реклама Трафареты
         $arr_services_without_postNumber['Реклама Трафареты'] = 
        @$arr_services_without_postNumber['Реклама Трафареты'] + $items['amount'];

            //  $Summa_reklami_trafareti = @$Summa_reklami_trafareti + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationGettingToTheTop')
    {  //Реклама вывод в ТОП
         $arr_services_without_postNumber['Реклама вывод в ТОП'] = 
        @$arr_services_without_postNumber['Реклама вывод в ТОП'] + $items['amount'];

            //  $Summa_reklami_get_in_Top = @$Summa_reklami_get_in_Top + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationPointsForReviews')
    {  //Баллы за отзывы
         $arr_services_without_postNumber['Баллы за отзывы'] = 
        @$arr_services_without_postNumber['Баллы за отзывы'] + $items['amount'];

        // $Summa_balli_za_otzivi = @$Summa_balli_za_otzivi + $items['amount']; 
    }
elseif ($items['operation_type'] == 'MarketplaceSaleReviewsOperation')
    {  //Приобретение отзывов на платформе
         $arr_services_without_postNumber['Приобретение отзывов на платформе'] = 
        @$arr_services_without_postNumber['Приобретение отзывов на платформе'] + $items['amount'];

        // $Summa_buy_otzivi = @$Summa_buy_otzivi  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'MarketplaceServiceItemVideoCover')
    {  //Генерация видеообложки
         $arr_services_without_postNumber['Генерация видеообложки'] = 
        @$arr_services_without_postNumber['Генерация видеообложки'] + $items['amount'];

        // $Summa_generacia_videooblozhki = @$Summa_generacia_videooblozhki  + $items['amount']; 
    }     
elseif ($items['operation_type'] == 'OperationElectronicServicesPromotionInSearch'){
       //Реклама Продвижение в поиске
               $arr_services_without_postNumber['Реклама Продвижение в поиске'] = 
        @$arr_services_without_postNumber['Реклама Продвижение в поиске'] + $items['amount'];

            // $Summa_reklami_poisk = @$Summa_reklami_poisk + $items['amount']; 
     }




elseif ($items['operation_type'] == 'OperationMarketplaceCostPerClick'){
       //Оплата за клик
               $arr_services_without_postNumber['Оплата за клик'] = 
        @$arr_services_without_postNumber['Оплата за клик'] + $items['amount'];

            // $Summa_reklami_poisk = @$Summa_reklami_poisk + $items['amount']; 
     }

elseif ($items['operation_type'] == 'OperationOtherElectronicServices'){
       //Иные электронные услуги
        $arr_services_without_postNumber['Иные электронные услуги'] = 
        @$arr_services_without_postNumber['Иные электронные услуги'] + $items['amount'];

            // $Summa_reklami_poisk = @$Summa_reklami_poisk + $items['amount']; 
     }

elseif ($items['operation_type'] == 'MarketplaceServiceItemCrossdocking'){
    //Кросс-докинг
            $arr_services_without_postNumber['Кросс-докинг'] = 
        @$arr_services_without_postNumber['Кросс-докинг'] + $items['amount'];

            // $Summa_kross_doking = @$Summa_kross_doking + $items['amount']; 
        }  
elseif ($items['operation_type'] == 'OperationSubscriptionPremium'){
    //Подписка Premium
    $arr_services_without_postNumber['Подписка Premium'] = 
    @$arr_services_without_postNumber['Подписка Premium'] + $items['amount'];

            // $Summa_primiun_5000 = @$Summa_primiun_5000 + $items['amount']; 
        }  
elseif ($items['operation_type'] == 'OperationSubscriptionPremiumPlus'){
    //Подписка Premium Plus
    $arr_services_without_postNumber['Подписка Premium Plus'] = 
    @$arr_services_without_postNumber['Подписка Premium Plus'] + $items['amount'];

            // $Summa_primiun_plus25000 = @$Summa_primiun_plus25000 + $items['amount']; 
        }  
elseif ($items['operation_type'] == 'OperationMarketplacePremiumSubscribtion')
        {  //Premium-подписка
             $arr_services_without_postNumber['Premium-подписка'] = 
            @$arr_services_without_postNumber['Premium-подписка'] + $items['amount'];
    
            // $Summa_premiaum_podpiska = @$Summa_premiaum_podpiska  + $items['amount']; 
        }

else {
    $Summa_neizvestnogo =  @$Summa_neizvestnogo  + $items['amount']; 
    $arr_nerazjbrannoe[] = $items; 
   
}
    }
}


if (isset($arr_nerazjbrannoe)) {
echo "<br> ЕСТЬ неразобранные сервисыы <br>";    
}
// echo "<pre>";
// print_r($arr_nerazjbrannoe);
// echo "</pre>";


// foreach ($arr_nerazjbrannoe as $popo) {
//     $jojo[$popo['operation_type_name']]= $popo['operation_type_name'];

// }
// echo "<br> ЕСТЬ неразобранныvvvе сервисыы <br>"; 
// echo "<pre>";
// print_r($jojo);
// echo "</pre>";