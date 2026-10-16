
<?php
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";
require_once "functions_orders_fbo_fbs.php";





// доставем всю номенсклатуру
$arr_all_nomenklatura = select_active_nomenklaturu($pdo);
foreach ($arr_all_nomenklatura as $zzz) {
   $arr_poriadkovii_number[mb_strtolower($zzz['main_article_1c'])] = $zzz['number_in_spisok'];
}

// Вставляем форму для ввода даты
require_once "start_form.php";

//// Проверяем и есть нет папки, то создаем ее, для каждого пользователя своя папка в !cache
 
$dir_for_cache = "../!cache/".$userdata['user_login']."/";
if (!is_dir($dir_for_cache)) {
    if (mkdir($dir_for_cache, 0755, true)) {
        echo "Папка создана.";
    } else {
        echo "Не удалось создать папку.";
    }
}


/***************************************************************************************************************
 * Заказы для ООО Анм
 **************************************************************************************************************/
// Получаем массив ФБС заказов 
$orders_FBS_ooo = query_FBS_orders_from_ozon ($token_ozon, $client_id_ozon, $date_from, $date_to);
// сохраняем файл на диске, чтобы потом детелаьно ковырятся в нем и не запрашивать новый
// file_put_contents($dir_for_cache."json_fbsOrders_".$shop_name.".json", json_encode($orders_FBS_ooo, JSON_UNESCAPED_UNICODE));
// $orders_FBS_article_ooo = make_array_with_all_FBS_FBO_orders ($orders_FBS_ooo);





// die();

// echo "<pre>";
// print_r($orders_FBS_ooo[0]);

foreach ($orders_FBS_ooo as $FBS_order) {

    $str = $FBS_order['in_process_at'];
    $dt = new DateTime($str);
    $dt->setTimezone(new DateTimeZone('Europe/Moscow'));
    $date = $dt->format('Y-m-d');   // 2026-10-16
    $time = $dt->format('H:i:s');   // 00:38:25


    $hour = (int) $dt->format('H');
$cluster_to = $FBS_order['financial_data']['cluster_to'];
    if ($hour >= 0 && $hour < 12) {
        $array_date_time[$date]['0-12'] = @$array_date_time[$date]['0-12'] + 1 ;
    // } elseif ($hour >= 40 && $hour < 46) {
        // $array_date_time[$date]['8-16'] = @$array_date_time[$date]['8-16'] + 1 ;
    } else {
        $array_date_time[$date]['12-24'] = @$array_date_time[$date]['12-24'] + 1 ;
    }


}



require_once "print_FBS_sells_days.php";
