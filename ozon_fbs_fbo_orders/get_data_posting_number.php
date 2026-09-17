<?php
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";
// require_once "functions_orders_fbo_fbs.php";


// доставем всю номенсклатуру
$arr_all_nomenklatura = select_active_nomenklaturu($pdo);
foreach ($arr_all_nomenklatura as $zzz) {
   $arr_poriadkovii_number[mb_strtolower($zzz['main_article_1c'])] = $zzz['number_in_spisok'];
}
//// Проверяем и есть нет папки, то создаем ее, для каждого пользователя своя папка в !cache
 
$dir_for_cache = "../!cache/".$userdata['user_login']."/";


// echo "<pre>";

$shop_name = $_GET['shopname'];


$delivery_schema = $_GET['delivery_schema'] ?? '';

$schemaLower = strtolower($delivery_schema);

$need_posting_number = $_GET['posting_number'] ?? '';

// print_r($delivery_schema);

if ($schemaLower === 'fbs') {
    // FBS
    $filePathFBS = $dir_for_cache."json_fbsOrders_".$shop_name.".json";
    $ordersFBS = json_decode(file_get_contents($filePathFBS),true);


    // выбиираем товары только с нашим артикулом из ФБС
foreach ($ordersFBS as $itemFbs) {
         $posting_number = mb_strtolower($itemFbs['posting_number']);
         if ($need_posting_number == $posting_number) {
            $work_article_array[] = $itemFbs;
            break;
     }
}
unset($itemFbs);
unset($product);


} elseif ($schemaLower === 'fbo') {
       // FBO
$filePathFBO = $dir_for_cache."json_fboOrders_".$shop_name.".json";
$ordersFB0 = json_decode(file_get_contents($filePathFBO),true);
// выбиираем товары только с нашим артикулом из ФБO

foreach ($ordersFB0 as $itemFbo) {
         $posting_number = mb_strtolower($itemFbo['posting_number']);
         if ($need_posting_number === $posting_number) {
            $work_article_array[] = $itemFbo;
            break;
     }
}

unset($itemFbo);
unset($product);
} else {
    echo "<br>Ошибка в поиске схемы доставки<br>";
    die();
}



$i = 0;
echo "<pre>";
print_r($work_article_array);
