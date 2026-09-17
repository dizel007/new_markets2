<?php


/********************************************************************************************************************
 * разбераем по отправлениям все что можем выбрать
 ********************************************************************************************************************/

function razbor_POSTING_by_posting_numbers(&$arr_posting_orders,  $posting_item_array, $type_fees) {

foreach ($posting_item_array as $posting_item) {
/// Номер отправления и дата
    $unit_number = $posting_item['unit_number'];
    $date_order = $posting_item['date'];

/// Получаем номер заказа
    $order_number = implode('-', array_slice(explode('-', $unit_number), 0, 2));
///  Сехма поставки
    $delivery_schema = $posting_item['posting']['delivery_schema'];
    

   foreach ($posting_item['posting']['products'] as $products) {
       $sku = $products['sku'];

       if (isset($products['delivery']))  {
        foreach ($products['delivery']['services'] as $delivery_serveces) {
            // получает категирию трат
            $description_type = change_id_by_description_spend($type_fees, $delivery_serveces['type_id']);
            // если логистика отрицательная, то добавляем признак возврата
            if ($delivery_serveces['type_id'] == 59) {
                @$arr_posting_orders[$sku][$order_number][$unit_number]['_return_'] = 1;
            }
            // запоняем массив доставкой

            @$arr_posting_orders[$sku][$order_number][$unit_number][$description_type] += $delivery_serveces['accrued']['amount'];
            }
   //// закончили перебирать delivery
       }
       // перебираем commission 
       if (isset($products['commission']))  {

          foreach ($products['commission'] as $type_commission=> $delivery_commission) {

          if (($type_commission == 'sale_amount') OR ($type_commission == 'sale_commission') OR 
               ($type_commission == 'coinvestment') OR ($type_commission == 'bonus') ) { 
            continue;
          }
    // если есть отрицательные суммы продаж, то добавляем признак возвратта 
   if (($type_commission == 'sale_price') && ( $delivery_commission < 0)) {
              @$arr_posting_orders[$sku][$order_number][$unit_number]['_return_'] = 1;
   }
     // Изголяемся чтобы привести процент комиисстии Озона в нормальному виду
            if ($type_commission == 'commission_ratio') { 
     // вытаскиваем число из строки
            preg_match('/"([\d.]+)"/', $delivery_commission, $m);
            $num = $m[1] ?? ''; // "0.440000"
     // берём всё после точки и убираем нули
            $afterDot = explode('.', $num)[1] ?? '';
            $result_delivery_commission = $afterDot/10000; 
      // записываем обратно
                @$arr_posting_orders[$sku][$order_number][$unit_number][$type_commission] = $result_delivery_commission;
// остальное все просто переносим в заказ
            } else {
                
                @$arr_posting_orders[$sku][$order_number][$unit_number][$type_commission] += $delivery_commission['amount'];
                

            }
        }
   //// закончили перебирать commission
       }


       $arr_posting_orders[$sku][$order_number][$unit_number]['delivery_schema'] = $delivery_schema;
       $arr_posting_orders[$sku][$order_number][$unit_number]['date_order'] = $date_order;
    //    $item['is_return']   = array_key_exists('Обратная логистика', $item);

  }
 
}
// print_r($ggg);
// die();
return $arr_posting_orders;
}



/********************************************************************************************************************
 * разбераем по отправлениям все что можем выбрать
 ********************************************************************************************************************/

function razbor_NON_ITEM_by_posting_numbers(&$arr_posting_orders, &$non_item_array, $type_fees, $sku_poisk) {

// echo "<pre>";
// print_r($non_item_array);
// die('kkk');
$i_for_delete = 0;
$summa_unset_for_orders = 0; // сумма из массива NON_ITEM которую мы смогли привязать к заказам
foreach ($non_item_array as &$non_item) {
// тип траты 
$description_type = change_id_by_description_spend($type_fees, $non_item['non_item_fee']['type_id']);

    $ok_unit_number = false;
/// Номер отправления и дата
    $date_order = $non_item['date'];
    // проверяем есть ли unit_number и соответствует ли он номеру отправления
     $ok_unit_number = array_key_exists('unit_number', $non_item)
     && preg_match('/^\d+-\d+-\d+$/', trim((string)$non_item['unit_number'])) === 1;
     if ($ok_unit_number) {
          $order_number = implode('-', array_slice(explode('-', $non_item['unit_number']), 0, 2));
          $unit_number = $non_item['unit_number'];
/// если есть номер отправления, то пробуем найти этот заказ в массиве  POSTING
// если не найдем, то добавим массив БЕЗ СКУ ---------------------
foreach ($arr_posting_orders as $sku=> &$g_orders) {
   foreach ($g_orders as $t_order_number=> &$orders) {
     if ($t_order_number ==  $order_number) {
        foreach ($orders as $t_unit_number=> &$postings) {
            if (($t_unit_number == $unit_number) AND ($sku_poisk == $sku) ){
                // добавялем статью расход в наш накопительный массив у удаляем из общего массив NON_ITEM
                // echo "$unit_number";
                   @$postings[$description_type] += $non_item['non_item_fee']['accrued']['amount'];
                   $summa_unset_for_orders += $non_item['non_item_fee']['accrued']['amount'];
                   unset($non_item_array[$i_for_delete]);
            } 
      }
     }
   }
}



     }

     $i_for_delete++;

}

return [$arr_posting_orders, $summa_unset_for_orders];
}





/********************************************************************************************************************
 * разбераем массив ITEM
 ********************************************************************************************************************/

function razbor_ITEM_by_posting_numbers(&$arr_posting_orders, &$item_array, $type_fees, $sku_poisk) {

$summa_item_for_orders = 0; // сумма из массива ITEM которую мы смогли привязать к заказам

foreach ($item_array as &$item) {
    $unit_number = $item['unit_number'];
// тип траты 
foreach ($item['item_fees'] as $fees) {
      foreach ($fees as $data_fees) {
  
        if (isset($data_fees['sku'])) {
            $sku  = $data_fees['sku'];
        } else {
          $sku  = 'NO_SKU';
        }
        // изменям код траты на название 
        $description_type = change_id_by_description_spend($type_fees, $data_fees['fees'][0]['type_id']);
        $amount_for_trat = $data_fees['fees'][0]['accrued']['amount'];

        @$categ[$sku][$description_type]++;
     }
  }
  @$arr_item_orders[$sku][$unit_number][$description_type] +=$amount_for_trat ;
}


//============================================================================================
// разбираем все траты, где есть номер отправления,
//===================================================================================


foreach ($arr_item_orders[$sku_poisk] as $unit_numer=>&$item_c) {
        $temp_1 = explode('-', $unit_numer);
        $order_number = implode('-', array_slice($temp_1, 0, 2));
//// 
    if (isset($arr_posting_orders[$sku_poisk][$order_number][$unit_numer])) {
        foreach ($item_c as $trat_j=>$amount_j) {
            $arr_posting_orders[$sku_poisk][$order_number][$unit_numer][$trat_j] = $amount_j;
            unset ($arr_item_orders[$sku_poisk][$unit_numer][$trat_j]);
            if (count($arr_item_orders[$sku_poisk][$unit_numer]) == 0) {
                unset ($arr_item_orders[$sku_poisk][$unit_numer]);

            }
        }
    }
}

//============================================================================================
// Разбираем где есть номер заказа и ровным слоем размазываем по всем заказам
//============================================================================================

foreach ($arr_item_orders[$sku_poisk] as $unit_numer=>&$item_c) {
        $temp_1 = explode('-', $unit_numer);
        $order_number = implode('-', array_slice($temp_1, 0, 2));
    if (isset($arr_posting_orders[$sku_poisk][$order_number]) ) {
        foreach ($arr_posting_orders[$sku_poisk][$order_number] as $unit_number=>$z) {
           foreach ($item_c as $trat_j=>$amount_j) {
             $arr_posting_orders[$sku_poisk][$order_number][$unit_number][$trat_j] = round ($amount_j / count($arr_posting_orders[$sku_poisk][$order_number]),2) ;
              unset ($arr_item_orders[$sku_poisk][$unit_numer][$trat_j]);
            if (count($arr_item_orders[$sku_poisk][$order_number]) == 0) {
                unset ($arr_item_orders[$sku_poisk][$order_number]);
            }
        }
      }
    }
}


/***
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 *  Добавить сумму неразобранных 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 * 
 */
// print_r($arr_items);
// print_r($categ[$sku_poisk]);
// die();
return [$arr_posting_orders, $arr_item_orders];
}