<?php


function razbor_all_tranzactions_yandex ($sheet, $type_array) {




    foreach ($type_array as $key => $string_number) {
        // перебераем прямые продажи
    
        // продажи 
        if ($key == 'Информация о начислениях') {
          $j = $string_number;
          $str_number = 0;
          do {
    
            $next_string =  $sheet->getCellByColumnAndRow(1, $j)->getValue();
            if ($next_string == '') {
              break;
            }
            $order_number =  mb_strtolower($sheet->getCellByColumnAndRow(7, $j)->getValue());
            $article = mb_strtolower($sheet->getCellByColumnAndRow(12, $j)->getValue());
            $type_operation = $sheet->getCellByColumnAndRow(13, $j)->getValue();
            $count_sell = $sheet->getCellByColumnAndRow(14, $j)->getValue();
            $order_price = $sheet->getCellByColumnAndRow(15, $j)->getValue();
            
            //41280145410
            // если есть артикул, то цепляем его
            // echo "**<$article>** <br>";
  
              if ($count_sell <> '') {
                  // Данные по продаже
                  $type_operation = "Продажа_покупателю";
                
                  $arr_nachilslenia[$order_number][$article]['Кол-во товаров'] = $count_sell;
                  $arr_nachilslenia[$order_number][$article][$type_operation] = @$arr_nachilslenia[$order_number][$article][$type_operation] + $order_price;
                  $arr_nachilslenia[$order_number][$article]['сумма_операций'] = @$arr_nachilslenia[$order_number][$article]['сумма_операций'] + $order_price;
              } else {
                  // Всякие коммиссии
                  $arr_nachilslenia[$order_number][$article][$type_operation] = @$arr_nachilslenia[$order_number][$article][$type_operation] +  $order_price;
                  $arr_nachilslenia[$order_number][$article]['сумма_операций'] = @$arr_nachilslenia[$order_number][$article]['сумма_операций'] + $order_price;
              } 
              // Всякие премии за акции ( они без артикула)
   

  


            $j++;
            $str_number++;
          } while ($next_string <> '');
        
        }

       
       
      }

return $arr_nachilslenia;
}