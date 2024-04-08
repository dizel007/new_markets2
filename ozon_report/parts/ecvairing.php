<?php 

foreach ($arr_other as $items) {
    $i++;
    $our_item = $items['items'];
// перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)

// [MarketplaceRedistributionOfAcquiringOperation] => Оплата эквайринга
// [OperationClaim] => Начисления по претензиям

if ($items['operation_type'] == 'MarketplaceRedistributionOfAcquiringOperation') //Оплата эквайринга
    { 
        foreach ($our_item as $item) 
            {

 ///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке
        $new_sku = change_SKU_fbo_fbs($ozon_sebest, $item['sku']);

                $arr_article[$new_sku]['name'] = $item['name'];
                $arr_article[$new_sku]['sku'] = $new_sku;
           // количество товаров в заказе, Эквайринг
                $arr_article[$new_sku]['count_ecvairing'] = @$arr_article[$new_sku]['count_ecvairing'] + 1;
                $arr_article[$new_sku]['amount_ecvairing'] = @$arr_article[$new_sku]['amount_ecvairing'] + round($items['amount']/count($our_item),2);
            }
    }
// СУмма претензий (ОНа не привязана к артикулу) /Начисления по претензиям
if ($items['operation_type'] == 'OperationClaim') 
    {
        $Summa_pretensii = @$Summa_pretensii  + $items['amount']; // сумма начислений по претензиям
    }
    
}