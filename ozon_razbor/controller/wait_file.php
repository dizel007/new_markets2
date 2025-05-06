<?php
require_once '../../connect_db.php';
require_once '../include_funcs.php';
require_once 'make_1c_file.php';


require_once '../../pdo_functions/pdo_functions.php'; // подключаем функцию записи в Таблицу действия пользователя




$ozon_shop = $_GET['ozon_shop'];
if ($_GET['ozon_shop'] == 'ozon_anmaks') {
       $token_ozon = $token_ozon;
       $client_id_ozon = $client_id_ozon;
 
   }
       
elseif ($_GET['ozon_shop'] == 'ozon_ip_zel') {
    //    echo "<br>Выбран магазин ИП Зел<br>";
       $token_ozon =  $token_ozon_ip;
       $client_id_ozon =  $client_id_ozon_ip;
 } else {
       die ('МАГАЗИН НЕ ВЫБРАН');
 }




// Формируем дополнительные переменные после разделения файла

 $path_excel_docs = $_GET['path_excel_docs'];
 $number_order = $_GET['number_order'];
 $path_etiketki = $_GET['path_etiketki'];
 $now_date_razbora = date('Y-m-d');
 $date_query_ozon = date('Y-m-d', strtotime($now_date_razbora . ' -5 day'));
 $dop_days_query = 10;
 $file_name_OTLADKA = $path_excel_docs."/otladka.txt";


 

// сохраняем JSON всех заказов 
$temp_path_all_order = $path_excel_docs."/json_all_order.json";
$res = json_decode(file_get_contents($temp_path_all_order),true);


// echo "Подождите пока формируются этикетки ..." ;

require_once "make_etikets_for_all.php";
