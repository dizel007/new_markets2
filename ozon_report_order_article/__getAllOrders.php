
<?php
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";
require_once "functions_orders_all.php";
require_once "print_FBO_FBS_sells.php";




// доставем всю номенсклатуру
$arr_all_nomenklatura = select_active_nomenklaturu($pdo);
foreach ($arr_all_nomenklatura as $zzz) {
   $nomenclature[] =  mb_strtolower($zzz['main_article_1c']);
}
// echo "<pre>";
// print_r($nomenclature);


// Вставляем форму для ввода даты
require_once "start_form_article.php";

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

// сырые данные складываем в файл
$file_name_ozon_FBS_orders = "../!cache/". $userdata['user_login'] . "/FBS_orders_article_report.json";
$json_data_types = json_encode($orders_FBS_ooo, JSON_UNESCAPED_UNICODE);
file_put_contents($file_name_ozon_FBS_orders, $json_data_types);
$orders_our_article = make_array_with_all_orders ($orders_FBS_ooo, $select_nomenclature);


// Получаем массив ФБO заказов 
$orders_FBO_ooo = query_FBO_orders_from_ozon ($token_ozon, $client_id_ozon, $date_from, $date_to);

// сырые данные складываем в файл
$file_name_ozon_FBO_orders = "../!cache/". $userdata['user_login'] . "/FBO_orders_article_report.json";
$json_data_types = json_encode($orders_FBO_ooo, JSON_UNESCAPED_UNICODE);
file_put_contents($file_name_ozon_FBO_orders, $json_data_types);

$orders_our_article = $array = array_merge($orders_our_article, make_array_with_all_orders ($orders_FBO_ooo, $select_nomenclature));


// echo "<pre>";
// print_r($orders_our_article);
// die();

require_once "get_orders_by_article.php";


