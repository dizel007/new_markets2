<?php
require_once '../../connect_db.php';
require_once '../include_funcs.php';
require_once 'make_1c_file.php';
require_once '../../pdo_functions/pdo_functions.php'; // подключаем функцию записи в Таблицу действия пользователя

require_once "../../mp_functions/get_token_by_shop.php"; 
require_once '../../vendor/autoload.php';


/*****************************************************************************************************************
 ******  Собираем данные ГЕТ запроса 
 ******************************************************************************************************************/

// echo  "ПРОШЛИ КОННЕКТ<br>";
if (!isset($_GET['ozon_shop']) || $_GET['ozon_shop'] === '') {
    http_response_code(400);
    exit('Параметр ozon_shop обязателен');
} else {
$ozon_shop = $_GET['ozon_shop'];
}

// получаем токен запрашиваемого магазина
[$token , $client_id] = get_token_AND_idclient ($arr_tokens, $ozon_shop);



$number_order = $_GET['number_order'];
$now_date_razbora = $_GET['now_date_razbora'];
$date_query_ozon = $_GET['date_query_ozon'];
$dop_days_query =  $_GET['dop_days_query'];

/*****************************************************************************************************************
 ******  Формируем пути для файлов
 ******************************************************************************************************************/
$start_file_path = "../../!all_razbor/ozon/";
$path_excel_docs = $start_file_path.$now_date_razbora."/".$number_order."/excel_docs";
$path_etiketki = $start_file_path.$now_date_razbora."/".$number_order."/etiketki";


$file_name_OTLADKA = $path_excel_docs."/otladka.txt";
$startTime = microtime(true);
$text_otladka = $startTime." ".""."*************************** Перешли в файл ожидания "."\n";
file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);

/*****************************************************************************************************************
 ******  Берем данные из ДЖЕСОН файла
 ******************************************************************************************************************/
$temp_path_all_order = $path_excel_docs."/json_all_order.json";
$ArrayOrders = json_decode(file_get_contents($temp_path_all_order),true);


/*****************************************************************************************************************
 ******  Уходим на формирование этикетоук
 ******************************************************************************************************************/

$startTime = microtime(true);
$text_otladka = $startTime." "."Уходим в файл makeer_etikets "."\n";
file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);




require_once "make_etikets_for_all.php";
