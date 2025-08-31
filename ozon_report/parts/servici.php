<?php


echo "<pre>";
// print_r($arr_nerazjbrannoe);
// echo "</pre>";


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
// print_r($items);

// СУмма по сервисами
if ($items['operation_type'] == 'MarketplaceMarketingActionCostOperation') 
    { // Услуги продвижения товаров
        $Summa_uslugi_prodvizhenia_tovara = @$Summa_uslugi_prodvizhenia_tovara  + $items['amount']; 
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
elseif ($items['operation_type'] == 'OperationMarketplaceServiceSupplyInboundSupplyShortage')
    {  //"Услуга по бронированию места и персонала для поставки с неполным составом
        $Summa_usluga_po_bronirovaniu_mesta_dla_postavku = @$Summa_usluga_po_bronirovaniu_mesta_dla_postavku  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceVolumeWeightCharacsProcessing')
    {  //Услуга по дополнительной обработке ОВХ
        $Summa_izmerenii_OVX = @$Summa_izmerenii_OVX  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationOtherElectronicServices')
    {  //Иные электронные услуги
        $Summa_electron_uslugi = @$Summa_electron_uslugi  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceSupplyInboundCargoSurplus')
    {  //Услуга по дополнительной обработке ОВХ
        $Summa_obrabotka_opoznannih_izlishkov = @$Summa_obrabotka_opoznannih_izlishkov  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceProcessingSpoilageSurplus')
    {  //Обработка брака с приемки
        $Summa_obrabotka_braka_s_priemki = @$Summa_obrabotka_braka_s_priemki  + $items['amount']; 
    }


elseif ($items['operation_type'] == 'OperationMarketplacePackageRedistribution')
    {  //Упаковка товара партнёрами
        $Summa_upakovka_tovara_partnerami = @$Summa_upakovka_tovara_partnerami  + $items['amount']; 
    }

elseif ($items['operation_type'] == 'OperationMarketplacePackageMaterialsProvision')
    {  //Обеспечение материалами для упаковки товара
        $Summa_obespechenie_materialami_dlia_upakovki = @$Summa_obespechenie_materialami_dlia_upakovki  + $items['amount']; 
    }




elseif ($items['operation_type'] == 'OperationMarketplaceSupplyAdditional')
    {  //Обработка товара в составе грузоместа на FBO
        $Summa_obrabotka_gruzomestFBO = @$Summa_obrabotka_gruzomestFBO  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'TemporaryStorage')
{  //Временное размещение товара в СЦ/ПВЗ
       $Summa_vrenennoe_razmeshenie_tovara_v_SC = @$Summa_vrenennoe_razmeshenie_tovara_v_SC  + $items['amount']; 
}
elseif ($items['operation_type'] == 'OperationMarketplaceServiceStorage')
    {  //ФБО хранение
        $Summa_hranenia_FBO = @$Summa_hranenia_FBO  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceStockDisposal')
    {  //Утилизацция
        $Summa_utilizacii_tovara = @$Summa_utilizacii_tovara  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'DisposalReasonFailedToPickupOnTime')
    {  //Утилизацция товара - не забрали в срок
        $Summa_utilizacii_tovara = @$Summa_utilizacii_tovara  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'DisposalReasonDamagedPackaging')
    {  //Утилизация товара: Повреждённые из-за упаковки
        $Summa_utilizacii_tovara_povrezhdenie_upakovki = @$Summa_utilizacii_tovara_povrezhdenie_upakovki  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceSupplyInboundCargoShortage')
    {  //УУслуга по бронированию места и персонала для поставки с неполным составом в составе ГМ
        $Summa_bronirovanie_mesta_i_personala = @$Summa_bronirovanie_mesta_i_personala  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplacePremiumSubscribtion')
    {  //Premium-подписка
        $Summa_premiaum_podpiska = @$Summa_premiaum_podpiska  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationElectronicServiceStencil')
    {  //Реклама Трафареты
             $Summa_reklami_trafareti = @$Summa_reklami_trafareti + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationGettingToTheTop')
    {  //Реклама вывод в ТОП
             $Summa_reklami_get_in_Top = @$Summa_reklami_get_in_Top + $items['amount']; 
    }




elseif ($items['operation_type'] == 'MarketplaceServiceBrandCommission')
    {  //Продвижение бренда
             $Summa_prodvizhenie_brenda = @$Summa_prodvizhenie_brenda + $items['amount']; 
    }





elseif ($items['operation_type'] == 'OperationPointsForReviews')
    {  //Баллы за отзывы
        $Summa_balli_za_otzivi = @$Summa_balli_za_otzivi + $items['amount']; 
    }
elseif ($items['operation_type'] == 'MarketplaceSaleReviewsOperation')
    {  //Приобретение отзывов на платформе
        $Summa_buy_otzivi = @$Summa_buy_otzivi  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'MarketplaceServiceItemVideoCover')
    {  //Генерация видеообложки
        $Summa_generacia_videooblozhki = @$Summa_generacia_videooblozhki  + $items['amount']; 
    }     
elseif ($items['operation_type'] == 'OperationElectronicServicesPromotionInSearch'){
       //Реклама Продвижение в поиске
            $Summa_reklami_poisk = @$Summa_reklami_poisk + $items['amount']; 
     }
elseif ($items['operation_type'] == 'DefectRateCancellation'){
     //Услуга за обработку операционных ошибок продавца: отмена
            $Summa_oshibka_obrabotki = @$Summa_oshibka_obrabotki + $items['amount']; 
        } 

elseif ($items['operation_type'] == 'DefectRateDetailed'){
     //Услуга за обработку операционных ошибок продавца: поздняя отгрузка
           $Summa_pozdniaia_otgruzka = @$Summa_pozdniaia_otgruzka + $items['amount']; 
          }   
elseif ($items['operation_type'] == 'MarketplaceServiceItemCrossdocking'){
    //Кросс-докинг
            $Summa_kross_doking = @$Summa_kross_doking + $items['amount']; 
        }  
elseif ($items['operation_type'] == 'MarketplaceSellerCorrectionOperation'){
     //Корректировки стоимости услуг
            $Summa_korrektirovka_stoimosti_uslug = @$Summa_korrektirovka_stoimosti_uslug + $items['amount']; 
         } 
elseif ($items['operation_type'] == 'OperationSubscriptionPremium'){
    //Подписка Premium
            $Summa_primiun_5000 = @$Summa_primiun_5000 + $items['amount']; 
        }  
elseif ($items['operation_type'] == 'OperationSubscriptionPremiumPlus'){
    //Подписка Premium Plus
            $Summa_primiun_plus25000 = @$Summa_primiun_plus25000 + $items['amount']; 
        }  


else {
    $Summa_neizvestnogo =  @$Summa_neizvestnogo  + $items['amount']; 
    $arr_nerazjbrannoe[] = $items; 
   
}
    
}

}
// echo "<pre>";
// print_r($arr_nerazjbrannoe);
// echo "</pre>";