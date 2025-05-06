<?php
function razbor_all_vozvrati_yandex ($sheet, $type_array) {
$summa_vseh_vozvratov = 0;
    foreach ($type_array as $key => $string_number) {
    // возвраты 
    if ($key == 'Информация о возвратах и компенсациях покупателям') {
        $j = $string_number;
        // $str_number = 0;
        do {
    
            $next_string =  $sheet->getCellByColumnAndRow(1, $j)->getValue();
            if ($next_string == '') {
              break;
            }

            $type_operation = mb_strtolower($sheet->getCellByColumnAndRow(12, $j)->getValue());
            $count_return_items = $sheet->getCellByColumnAndRow(14, $j)->getValue();
            $order_price = $sheet->getCellByColumnAndRow(15, $j)->getValue();
            $dop_type = $sheet->getCellByColumnAndRow(16, $j)->getValue();

            $arr_vozvrati[$type_operation]['сумма_операций'] = @$arr_vozvrati[$type_operation]['сумма_операций']  + $order_price;
            $arr_vozvrati[$type_operation]['доп_описание'] = $dop_type;
            $arr_vozvrati[$type_operation]['кол-во возвратов'] = $count_return_items;
  
  
          $j++;
          // $str_number++;
          $summa_vseh_vozvratov += $order_price;
        } while ($next_string <> '');
      }


    }
$arr_vozvrati['summa_vseh_vozvratov'] = $summa_vseh_vozvratov;
return $arr_vozvrati;
}