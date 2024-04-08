<?php
/**********************************************************************************************************
 *     ***************    Разбиваем массив по типу операции
*********************************************************************************************************/

foreach ($prod_array as $items) {
    foreach ($items as $item) {
    //    $nnnnn[$item['operation_type']] = $item['operation_type'];

    $index_name = $item['services'];  
    // Доставка и обработка возврата, отмены, невыкупа   
        if ($item['type'] == 'orders') { 
            $arr_orders[] = $item; // формируем массив 
            foreach ($index_name as $index) {
                $new_name = $index['name'];
                // $arr_index_job['orders'][$new_name] = @$arr_index_job['orders'][$new_name]  + $index['price'];
            }
    
        
        }     

// Доставка и обработка возврата, отмены, невыкупа
        elseif ($item['type'] == 'returns') {
            $arr_returns[] = $item;
            foreach ($index_name as $index) {
                $new_name = $index['name'];
                // $arr_index_job['returns'][$new_name] = @$arr_index_job['returns'][$new_name]  + $index['price'];
            }
        } 



// эквайринг ;претензиям
        elseif ($item['type'] == 'other') { 
            $arr_new[] = $item;
            foreach ($index_name as $index) {
                $new_name = $index['name'];
                // $arr_index_job['other'][$new_name] = @$arr_index_job['other'][$new_name] + $index['price'];
            }
        
        } 

//продвижения товаров ;хранение/утилизацию ...... SERVICES **************************************
elseif ($item['type'] == 'services') { 
    
    $arr_services[] = $item;
    $operation_type_name = $item['operation_type_name'];
    // $arr_index_dop['services'][$operation_type_name] = @$arr_index_job['services'][$operation_type_name]  + $item['amount'];


} 
// Если есть неучтенка то сюда
else {
    
            $arr_index_job['XXX'] = $item;
        }
    }
}

echo "<pre>";
// print_r($prod_array) ;
echo "<br>-*************************arr_index_dop*************************<br>";

// print_r($arr_index_dop) ;
echo "<br>-************************arr_index_job**************************<br>";
// print_r($arr_index_job) ;
echo "<br>-******************arr_services********************************<br>";
// print_r($arr_services) ;
// die();
