<?php 
// echo "<pre>";
// print_r($arr_other);

foreach ($arr_other as $items) {
    $i++;
    $our_item = $items['items'];
   $new_post_number = make_posting_number ($items['posting']['posting_number']);
      // техническая инфа 
        $arr_article[$new_post_number]['operation_id'][] = $items['operation_id'];
        
        
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
     
        $arr_article[$new_post_number]['ACQUIRING'] = 'ACQUIRING';
        $arr_article[$new_post_number]['order_date'] = $items['posting']['order_date'];

 


// [MarketplaceRedistributionOfAcquiringOperation] => Оплата эквайринга
// [OperationClaim] => Начисления по претензиям

if ($items['operation_type'] == 'MarketplaceRedistributionOfAcquiringOperation') //Оплата эквайринга
    { 
                $arr_article[$new_post_number]['amount_ecvairing'] = @$arr_article[$new_post_number]['amount_ecvairing'] + $items['amount'];
    }
// СУмма претензий (ОНа не привязана к артикулу) /Начисления по претензиям
elseif ($items['operation_type'] == 'OperationClaim') 
        {
            $arr_article[$new_post_number]['shraf_po_pretenziam'] = @$arr_article[$new_post_number]['shraf_po_pretenziam'] +  $items['amount'];
        }
else {
        $arr_nerazjbrannoe[]=$items;
    }
    
}


// echo "<pre>";
// print_r($arr_nerazjbrannoe);