<?php
/**
 * Тут будем пробовать строить систему восстановления работы программы при сбоях
 */
 //********************************************************************************************************************************* */
 // функция созжает маркерный файл, что сборка началась
function create_marker_recover_file($new_path) {
 $file_recovery = 'not_ready_supply.xxx';
 if(!is_file($new_path.'/recovery/'.$file_recovery)){
     $contents = 'NOT READY FOR SUPPLY';           // Some simple example content.
     file_put_contents($new_path.'/recovery/'.$file_recovery, $contents);          // Save our content to the file.
 }
}

 //********************************************************************************************************************************* */
 // функция проверяет есть ли маркерный файл
 function check_marker_recover_file($new_path) {
    $file_recovery = 'not_ready_supply.xxx';
    if(is_file($new_path.'/recovery/'.$file_recovery)){
        return 1;
    }
    return 0;
   }

 //********************************************************************************************************************************* */
 // функция удаляет маркерный файл, что сборка закончилась
 function delete_marker_recover_file($path_recovery) {
    $file_recovery = 'not_ready_supply.xxx';
    unlink($path_recovery.'/'.$file_recovery);
   }
   
 //********************************************************************************************************************************* */
// функция проверяет наличие Заказа в Поставке
// Если заказ в поставке, то вернется 0, если нет то 1
 function test_find_order_in_supply ($token_wb, $orderId, $supplyId) {
    // $supplyId = 'WB-GI-53892210';
    // $orderId = '962057195';
    usleep(10000); // 10ms pause
    $link_wb = 'https://suppliers-api.wildberries.ru/api/v3/supplies/'.$supplyId.'/orders';
    $res =  light_query_without_data($token_wb, $link_wb);
    // echo "<pre>";
    // print_r($res['orders']);
    
    if (!isset($res)) {
        output_print_comment("<b>СБОЙ</b> При запросе заказа в поставке ничего не вернулось в ответ"); // Вывод коммент-я на экран

        $res =  light_query_without_data($token_wb, $link_wb); // повторный запрос // если первый ничего не прошел 
    }
 foreach ($res['orders'] as $temp_orders) 
  {
        if ($orderId == $temp_orders['id']) {
            output_print_comment("Заказ: $orderId в Поставке: $supplyId (УСПЕШНО)"); // Вывод коммент-я на экран
            return 0;
        }
        
  }
  output_print_comment("<b>(СБОЙ)</b> Заказа: $orderId НЕТ в Поставке: $supplyId"); // Вывод коммент-я на экран
   return 1;

 }

 //********************************************************************************************************************************* */
// функия формирования файла с заказами и номером поставки
function make_recovery_json_orders_file($path_recovery, $orderId, $supplyId, $article) {
    echo "<br> Функция записи в файл восстновления Заказ: $orderId ; Поставка :$supplyId";
    $temp_path = $path_recovery."/".$supplyId;
    make_new_dir_z($temp_path,0); // создаем папку с номером заказа

    // $article =  make_rigth_file_name($article);
    // $article =  make_right_articl($article);
    file_put_contents($temp_path."/article.txt", $article);
// Если существует файл поставки, то открываем его 
    if (file_exists($temp_path."/".$supplyId.".txt")) {
        $str_file = file_get_contents($temp_path."/".$supplyId.".txt");
        $arr_file = json_decode($str_file);
        $sigh_order=0;

 //  перебираем все заказы из файла
        foreach ($arr_file as $order) {
            if ($order == $orderId) {
                $sigh_order=1;
            }
        }
        if ($sigh_order == 0) {
            $arr_file[] = $orderId; // добавляем заказ в поставку
            $filedata_json = json_encode($arr_file, JSON_UNESCAPED_UNICODE);
            file_put_contents($temp_path."/".$supplyId.".txt", $filedata_json); // добавляем данные в файл с накопительным итогом
        } else { // если заказ есть в поставке, то не пишем его туда
            $arr_orderId = $arr_file; // сохраняем старые заказы
            $filedata_json = json_encode($arr_orderId, JSON_UNESCAPED_UNICODE);
            file_put_contents($temp_path."/".$supplyId.".txt", $filedata_json); // добавляем данные в файл с накопительным итогом
        }

    } else { // если файл не существует , то пишем первый заказ
        $arr_orderId[] = $orderId; // добавляем в файл первый заказ
        $filedata_json = json_encode($arr_orderId, JSON_UNESCAPED_UNICODE);
        file_put_contents($temp_path."/".$supplyId.".txt", $filedata_json); // добавляем данные в файл с накопительным итогом 
    }
    

}
