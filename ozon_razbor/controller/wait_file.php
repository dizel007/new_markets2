<?php
require_once '../../connect_db.php';
require_once '../include_funcs.php';
require_once 'make_1c_file.php';


require_once '../../pdo_functions/pdo_functions.php'; // подключаем функцию записи в Таблицу действия пользователя


/*****************************************************************************************************************
 ******  Собираем данные ГЕТ запроса 
 ******************************************************************************************************************/
 $ozon_shop = $_GET['ozon_shop']; // название нашего магазина

 if ($ozon_shop == 'ozon_anmaks') {
       $token_ozon = $token_ozon;
       $client_id_ozon = $client_id_ozon;
 
   }
       
elseif ($ozon_shop == 'ozon_ip_zel') {
       $token_ozon =  $token_ozon_ip;
       $client_id_ozon =  $client_id_ozon_ip;
 } else {
       die ('МАГАЗИН НЕ ВЫБРАН');
 }

$number_order = $_GET['number_order'];
$now_date_razbora = $_GET['now_date_razbora'];
$date_query_ozon = $_GET['date_query_ozon'];
$dop_days_query = $_GET['dop_days_query'];

/*****************************************************************************************************************
 ******  Формируем пути для файлов
 ******************************************************************************************************************/
$start_file_path = "../../!all_razbor/ozon/";
$path_excel_docs = $start_file_path.$now_date_razbora."/".$number_order."/excel_docs";
$path_etiketki = $start_file_path.$now_date_razbora."/".$number_order."/etiketki";
$file_name_OTLADKA = $path_excel_docs."/otladka.txt";


 
/*****************************************************************************************************************
 ******  Берем данные из ДЖЕСОН файла
 ******************************************************************************************************************/
$temp_path_all_order = $path_excel_docs."/json_all_order.json";
$res = json_decode(file_get_contents($temp_path_all_order),true);


/*****************************************************************************************************************
 ******  Уходим на формирование этикетоук
 ******************************************************************************************************************/

require_once "make_etikets_for_all.php";
