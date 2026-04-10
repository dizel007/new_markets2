<?php 
// echo "<pre>";
// print_r($arr_other);

foreach ($arr_other as $items) {
    // print_r($items);
    $i++;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
    // echo "<br>jjjjjjjjjggggggggggggggggggggggggggggggggggggggggggggjjjjjjjjjjjjjjjjjjj<br>";

if ($items['operation_type'] == 'MarketplaceRedistributionOfAcquiringOperation') //Оплата эквайринга
    { 
        foreach ($our_item as $item) 
            {
               $new_sku =  $item['sku'];
               $arr_article[$new_sku]['name'] = $item['name'];
               $arr_article[$new_sku]['sku'] = $new_sku;
           // количество товаров в заказе, Эквайринг
               $arr_article[$new_sku]['count_ecvairing'] = @$arr_article[$new_sku]['count_ecvairing'] + 1;
               $arr_article[$new_sku]['amount_ecvairing'] = @$arr_article[$new_sku]['amount_ecvairing'] + round($items['amount']/count($our_item),2);
            }
    }
// СУмма претензий (ОНа не привязана к артикулу) /Начисления по претензиям
elseif ($items['operation_type'] == 'OperationClaim') 
        {
            $Summa_pretensii = @$Summa_pretensii  + $items['amount']; // сумма начислений по претензиям
            $arr_article[$new_sku]['shraf_po_pretenziam'] = @$arr_article[$new_sku]['shraf_po_pretenziam'] + $items['amount'];
        }

/******************************************************************************************
// Подписка Premium Pro (процент)
// ************************************   ALARM *********************************************
// хоть Подписка Premium Pro (процент) и относится к SERVICE , озон запихал ее в раздел OTHER ****
// Но при распределении все равно запихнем это в сервисы ( так будет правильнее)
*/
elseif ($items['operation_type'] == 'PremiumMembership') 
   {

            foreach ($our_item as $item) 
            {
               $new_sku =  $item['sku'];
               $arr_article[$new_sku]['name'] = $item['name'];
               $arr_article[$new_sku]['sku'] = $new_sku;
            }

          $Summa_procent_za_premiumPro = @$Summa_procent_za_premiumPro  +  $items['amount']/count($our_item); // сумма начислений по претензиям
            // Считаем 
        $arr_article[$new_sku]['services'][$items['operation_type_name']] = @$arr_article[$new_sku]['services'][$items['operation_type_name']] + 
                $items['amount']/count($our_item);
        $arr_sum_services_payment_with_SKU[$items['operation_type_name']] = @$arr_sum_services_payment_with_SKU[$items['operation_type_name']] + 
                    $items['amount']/count($our_item);
            // $arr_article[$new_sku]['procent_za_premiumPro'] = @$arr_article[$new_sku]['procent_za_premiumPro'] + $items['amount'];
   }




else {
        $arr_nerazjbrannoe['ecvairing'][]=$items;
    }
    
}


// echo "<pre>";
// print_r($arr_nerazjbrannoe);