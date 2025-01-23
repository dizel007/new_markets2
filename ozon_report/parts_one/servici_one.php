<?php

echo "<br> ***************************************************************** <br>";
echo "<pre>";
// print_r($arr_services);
// $kk = 0;
// foreach ($arr_services as $items) {
// if ($items['operation_type'] === 'DefectRateDetailed') {
//     $new_post_number = make_posting_number ($items['posting']['posting_number']);
//     $arr_serv_222[$new_post_number][] = $items;
// }
//     // print_r($items);
// $kk++;
// }


// print_r($arr_serv_222);


// echo "**********************+++$kk++++";

// die('ssssssssssssssssssssssssssssssss');
// echo "</pre>";


if (isset ($arr_services)) {
foreach ($arr_services as $items) {
    $i++;
    $service_obrabotan = 0;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)


$new_post_number = make_posting_number ($items['posting']['posting_number']);
$arr_article[$new_post_number]['order_date'] = $items['posting']['order_date'];
$arr_article[$new_post_number]['SERVICES'] = 'SERVICES';

$new_sku = change_SKU_fbo_fbs($ozon_sebest, $item['sku']);
$arr_article[$new_post_number]['name'] = $item['name'];
$arr_article[$new_post_number]['sku'] = $new_sku;



        foreach ($our_item as $item) {

  ///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке


        if (($items['operation_type'] == 'OperationMarketplaceReturnStorageServiceAtThePickupPointFbs')  OR 
            ($items['operation_type'] == 'OperationMarketplaceReturnDisposalServiceFbs') )
            {
                // Начисление за хранение/утилизацию возвратов
                $arr_article[$new_post_number]['count_hranenie'] = @$arr_article[$new_post_number]['count_hranenie'] + 1;
                $arr_article[$new_post_number]['amount_hranenie'] = @$arr_article[$new_post_number]['amount_hranenie'] + $items['amount']/count($our_item);
                $service_obrabotan = 1;
         } 
        }


if ($items['operation_type'] === 'DefectRateDetailed')  {
            //Услуга за обработку операционных ошибок продавца: поздняя отгрузка
                $arr_article[$new_post_number]['count_pozdniaa_otgruzka'] = @$arr_article[$new_post_number]['count_pozdniaa_otgruzka'] + 1;
                $arr_article[$new_post_number]['pozdniaa_otgruzka'] = @$arr_article[$new_post_number]['pozdniaa_otgruzka'] + $items['amount'];
                $service_obrabotan = 1;
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
elseif ($items['operation_type'] == 'OperationMarketplaceSupplyAdditional')
    {  //Обработка товара в составе грузоместа на FBO
        $Summa_obrabotka_gruzomestFBO = @$Summa_obrabotka_gruzomestFBO  + $items['amount']; 
    }













elseif ($items['operation_type'] == 'OperationMarketplaceServiceStorage')
    {  //ФБО хранение
        $Summa_hranenia_FBO = @$Summa_hranenia_FBO  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationMarketplaceServiceStockDisposal')
    {  //Утилизацция
        $Summa_utilizacii_tovara = @$Summa_utilizacii_tovara  + $items['amount']; 
    }

elseif ($items['operation_type'] == 'OperationMarketplacePremiumSubscribtion')
    {  //Premium-подписка
        $Summa_premiaum_podpiska = @$Summa_premiaum_podpiska  + $items['amount']; 
    }
elseif ($items['operation_type'] == 'OperationElectronicServiceStencil')
    {  //Реклама Трафареты
             $Summa_reklami_trafareti = @$Summa_reklami_trafareti + $items['amount']; 
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
elseif ($items['operation_type'] == 'MarketplaceServiceItemCrossdocking'){
    //Кросс-докинг
            $Summa_kross_doking = @$Summa_kross_doking + $items['amount']; 
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