<?php 
// echo "<pre>";
// print_r($arr_type_items_WITH_POSTING_NUMBER['other']);
// die();
foreach ($arr_type_items_WITH_POSTING_NUMBER['other'] as $items) {
    // print_r($items);
    $i_other++;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)

if ($items['operation_type'] == 'MarketplaceRedistributionOfAcquiringOperation') //Оплата эквайринга
    { 
        // foreach ($our_item as $item) 
            // {
        //        $new_posting_number = $items['posting']['posting_number']; // ставим сюда нормер заказа

        //        $arr_article[$new_posting_number]['name'] = $item['name'];
        //        $arr_article[$new_posting_number]['sku'] = $item['sku'];
        //    // количество товаров в заказе, Эквайринг
        //         $arr_article[$new_posting_number]['count_ecvairing'] = @$arr_article[$new_posting_number]['count_ecvairing'] + 1;
        //         $arr_article[$new_posting_number]['amount_ecvairing'] = @$arr_article[$new_posting_number]['amount_ecvairing'] + round($items['amount']/count($our_item),2);
            // }
    }
// СУмма претензий (ОНа не привязана к артикулу) /Начисления по претензиям
elseif ($items['operation_type'] == 'OperationClaim') 
        {
            // $Summa_pretensii = @$Summa_pretensii  + $items['amount']; // сумма начислений по претензиям
            // $arr_article[$new_posting_number]['shraf_po_pretenziam'] = @$arr_article[$new_posting_number]['shraf_po_pretenziam'] + $items['amount'];
        }
/******************************************************************************************
// Подписка Premium Pro (процент)
// ************************************   ALARM *********************************************
// хоть Подписка Premium Pro (процент) и относится к SERVICE , озон запихал ее в раздел OTHER ****
// Но при распределении все равно запихнем это в сервисы ( так будет правильнее)
*/
elseif ($items['operation_type'] == 'PremiumMembership') 
        {
            // echo "<pre>";
            // print_r($items);

            $new_posting_number = $items['posting']['posting_number']; // ставим сюда нормер заказа
            $arr_article[$new_posting_number]['services'][$items['operation_type_name']] = @$arr_article[$new_posting_number]['services'][$items['operation_type_name']] +  
                   $items['amount']/count($our_item);
            $arr_article[$new_posting_number]['proc_rekl'] = 'Pro_rekl';  
            
        }
        
else {
        $arr_nerazjbrannoe['ecvairing'][]=$items;
    }
    
}


// echo "<pre>";
// print_r($arr_nerazjbrannoe);

// print_r($arr_article);