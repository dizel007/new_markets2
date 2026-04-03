<?php
// echo "<pre>";
// print_r($arr_services[44]);
// die();

if (isset ($arr_services)) {
    foreach ($arr_services as $items) {
        
       // добавляем создание массива с сервисами в которых есть номер заказа, чтобы потом по-
        //пробовать вывести их на отдельной страничке penalty tables
       if  (($items['posting']['posting_number'] != '' ) AND ((preg_match('/^\d+-\d+-\d+$/', $items['posting']['posting_number'])))) {
        $arr_services_with_post_numbers[$items['operation_type_name']][$items['posting']['posting_number']] = @$arr_services_with_post_numbers[$items['operation_type_name']][$items['posting']['posting_number']] + $items['amount'];

        
       }

        /// конец создания массива с сервисами с заказами
        $i++;
        $service_obrabotan = 0;
        // создаем массивы с сервисами и их описанием
           $arr_services_types[$items['operation_type']] = $items['operation_type_name'];
           $arr_services_types_rus[$items['operation_type_name']] = $items['operation_type_name'];

        $our_item = $items['items'];
    // перебираем список товаров в этом заказе (Там где одиночные борды. Остальные отправления мы разбиваем по 1 штуке)
            foreach ($our_item as $item) {
    ///// ТУТ мы меняет SKU ФБО на СКУ ФБС, чтобы в таблице вывести их в одной строке
                $new_sku = $item['sku'];
                $arr_article[$new_sku]['name'] = $item['name'];
                $arr_article[$new_sku]['sku'] = $new_sku;
                // если есть СКУ то привязываем трату к СКУ
            if (count($our_item) != 0)
                {
                   $arr_article[$new_sku]['services'][$items['operation_type_name']] = @$arr_article[$new_sku]['services'][$items['operation_type_name']] + 
                   $items['amount']/count($our_item);
                    $arr_sum_services_payment_with_SKU[$items['operation_type_name']] = @$arr_sum_services_payment_with_SKU[$items['operation_type_name']] + 
                    $items['amount']/count($our_item);            
                    $service_obrabotan = 1;
                }
            }
    if ($service_obrabotan == 1) { // если в сервисе есть артикул, то цепляем сервис к артикулу, сли нет, то в массив сервисов
        continue;
    }
    // print_r($items);
    // СУмма по сервисами
    $arr_sum_services_payment[$items['operation_type_name']] = @$arr_sum_services_payment[$items['operation_type_name']] + $items['amount'];
        
    }

}


// echo "***********************<br>";
// echo "<pre>";
// print_r($arr_services_with_post_numbers);
// echo "***********************<br>";
