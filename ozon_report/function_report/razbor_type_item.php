<?php

/********************************************************************************************************************
 * разбераем массив данных с катгорией ITEM
 ********************************************************************************************************************/

function razbor_ITEM_category(&$sum_array, $item_array, $type_fees) {

foreach ($item_array as $item) {
        // print_r($item);
    foreach ($item['item_fees'] as $fees) {
          foreach ($fees as $data_fees) {
        // изменям код траты на название 
        $description_type = change_id_by_description_spend($type_fees, $data_fees['fees'][0]['type_id']);
        // симмируем все траты     
       @$sum_array[$data_fees['sku']][$description_type] += $data_fees['fees'][0]['accrued']['amount'];
         }
    }
}
return $sum_array;
}

/********************************************************************************************************************
 * разбераем массив данных с катгорией NON_ITEM
 ********************************************************************************************************************/

function razbor_NON_ITEM_category(&$sum_array, $non_item_array, $type_fees) {

foreach ($non_item_array as $non_item) {
        // print_r($item);
  
        // изменям код траты на название 
        $description_type = change_id_by_description_spend($type_fees, $non_item['non_item_fee']['type_id']);
        // симмируем все траты     
       @$sum_array['NO_SKU'][$description_type] += $non_item['non_item_fee']['accrued']['amount'];
  
  
}
return $sum_array;
}



/********************************************************************************************************************
 * разбераем массив данных с катгорией POSTING
 ********************************************************************************************************************/

function razbor_POSTING_category(&$sum_array, $posting_item_array, $type_fees) {

foreach ($posting_item_array as $posting_item) {
    $unit_number = $posting_item['unit_number'];
   foreach ($posting_item['posting']['products'] as $products) {
       $sku = $products['sku'];
       // перебираем delivery 
    //    print_r($posting_item);
    //    echo count($posting_item['posting']['products']);
    //    print_r($products);
    //    die();
       if (isset($products['delivery']))  {
        //   @$sum_array[$sku]['delivery_summa'] += $products['delivery']['total_accrued']['amount'];
        foreach ($products['delivery']['services'] as $delivery_serveces) {
            $description_type = change_id_by_description_spend($type_fees, $delivery_serveces['type_id']);
            @$arr_status [$description_type] ++;

             /// прямая логистика (добавляем проданный товар)
            if ($delivery_serveces['type_id'] == 32) {
              @$sum_array[$sku]['count_direct'] ++; 

            }
            /// обратная логистика (добавляем возвратный товар) 
             elseif ($delivery_serveces['type_id'] == 59)  {
              
              @$sum_array[$sku]['count_return'] ++;
            }

            @$sum_array[$sku][$description_type] += $delivery_serveces['accrued']['amount'];
           
        }
   //// закончили перебирать delivery
       }
       // перебираем commission 
       if (isset($products['commission']))  {
          foreach ($products['commission'] as $type_commission=> $delivery_commission) {
            // добавляем еще возвраты если 


            if ($type_commission == 'commission_ratio') {
                continue;
            // @$sum_array[$sku][$type_commission] = $delivery_commission;
            } else {
                @$sum_array[$sku][$type_commission] += $delivery_commission['amount'];
            }
        }
   //// закончили перебирать commission
       }


  }
 
}
// print_r($arr_status);
return $sum_array;
}



/********************************************************************************************************************
 * меняем id статьи затрат на наименование
 ********************************************************************************************************************/

function change_id_by_description_spend($type_fees, $type_id) {
  $description_type ="sssssssssssssssssssssss";
            foreach($type_fees['accrual_types'] as $accrual_types) {
                if ($type_id == $accrual_types['id']) {
                    // $description_type = $accrual_types['name'];
                    $description_type = $accrual_types['description'];
                    break;
                }
            }
return $description_type;
}