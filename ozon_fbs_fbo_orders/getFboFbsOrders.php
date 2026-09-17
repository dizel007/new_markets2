
<?php
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";
require_once "functions_orders_fbo_fbs.php";
require_once "print_FBO_FBS_sells.php";




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
file_put_contents($dir_for_cache."json_fbsOrders_".$shop_name.".json", json_encode($orders_FBS_ooo, JSON_UNESCAPED_UNICODE));
$orders_FBS_article_ooo = make_array_with_all_FBS_FBO_orders ($orders_FBS_ooo);

// echo "<pre>";
// print_r($orders_FBS_article_ooo);
// die();

// Получаем массив ФБO заказов 
$orders_FBO_ooo = query_FBO_orders_from_ozon ($token_ozon, $client_id_ozon, $date_from, $date_to);
// сохраняем файл на диске, чтобы потом детелаьно ковырятся в нем и не запрашивать новый
file_put_contents($dir_for_cache."json_fboOrders_".$shop_name.".json",json_encode($orders_FBO_ooo, JSON_UNESCAPED_UNICODE));


$orders_FBO_article_ooo = make_array_with_all_FBS_FBO_orders ($orders_FBO_ooo);
// формируем перечень артикулов которые были проданы
foreach ($orders_FBS_article_ooo as $key=>$z) {
   $art_ar_ooo[$key] = $key;
 }
foreach ($orders_FBO_article_ooo as $key=>$z) {
   $art_ar_ooo[$key] = $key;
}
// Привем массив артикулов в порядок (согласно порядковому нормеру)
foreach ($arr_poriadkovii_number as $key=>$number_position) {
  if (isset($art_ar_ooo[$key])){ $arr_sort_ar_ooo[$key] = $number_position;}
}

// Сортировка по возрастанию с сохранением ключей
asort($arr_sort_ar_ooo);

// echo "<pre>";
// print_r($arr_poriadkovii_number);
// print_r($arr_sort_ar_ooo);
// die();

print_sum_table($shop_name ,$arr_sort_ar_ooo, $orders_FBS_article_ooo, $orders_FBO_article_ooo);
