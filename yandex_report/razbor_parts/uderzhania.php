<?php
function razbor_all_uderzania_yandex ($sheet, $type_array) {
  $summa_vseh_uderzanii = 0;
    foreach ($type_array as $key => $string_number) {
    // возвраты 
    if ($key == 'Информация об удержаниях') {
        $j = $string_number;
        $str_number = 0;
        do {
    
            $next_string =  $sheet->getCellByColumnAndRow(1, $j)->getValue();
            if ($next_string == '') {
              break;
            }

            $type_operation = $sheet->getCellByColumnAndRow(16, $j)->getValue();
            $order_price = $sheet->getCellByColumnAndRow(15, $j)->getValue();

            $arr_uderzania[$type_operation]['сумма_операций_удержания'] =  @$arr_uderzania[$type_operation]['сумма_операций_удержания'] + $order_price;
  
          $j++;
          // $str_number++;
          $summa_vseh_uderzanii += $order_price;
        } while ($next_string <> '');
      }


    }
    $arr_uderzania['summa_vseh_uderzanii'] = $summa_vseh_uderzanii;
return $arr_uderzania;
}