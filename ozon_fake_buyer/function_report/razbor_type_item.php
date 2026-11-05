<?php

/********************************************************************************************************************
 * разбераем массив данных с катгорией POSTING
 ********************************************************************************************************************/

function razbor_POSTING_category($posting_item_array, $type_fees) {

foreach ($posting_item_array as $posting_item) {
   foreach ($posting_item['posting']['products'] as $products) {
    //    die();
       if (isset($products['delivery']))  {
        foreach ($products['delivery']['services'] as $delivery_serveces) {
            /// обратная логистика (добавляем возвратный товар) 
             if ($delivery_serveces['type_id'] == 59)  {
              $array_fake_buyers[] = $posting_item;
              break 1;
             }
        }
   //// закончили перебирать delivery
       }

   // перебираем commission 
       if (isset($products['commission']))  {
          foreach ($products['commission'] as $type_commission=> $delivery_commission) {
            // добавляем еще возвраты если 
            if ($type_commission == 'commission_ratio') {
                continue;
            }

            if (($type_commission == 'sale_price') && ( $delivery_commission < 0)) {
              $array_fake_buyers[] = $posting_item;
              break 1;
             }


        }
   //// закончили перебирать commission
       }


  }
 
}

return $array_fake_buyers;
}
