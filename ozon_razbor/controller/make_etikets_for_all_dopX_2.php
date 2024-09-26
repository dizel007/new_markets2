<?php


require_once '../../connect_db.php';
require_once '../include_funcs.php';
require_once 'make_1c_file.php';




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



$date_query_ozon = $_GET['date_query_ozon'];
$number_order = $_GET['number_order'];
$dop_days_query = 0; // Всегда собираем за один день

// echo "1d";
/*****************************************************************************************************************
 ******  Формируем папки для разнесения информации 
 ******************************************************************************************************************/
// $new_date = date('Y-m-d');
// $new_path = '../reports/'.$date_query_ozon."/";
$new_path = '../../!all_razbor/ozon/'.$date_query_ozon.""; // переход в новую папку 

make_new_dir_z($new_path,0); // создаем папку с датой

$new_path = $new_path.'/'.$number_order.'(dop)'.'/';
make_new_dir_z($new_path,0); // создаем папку с датой

$path_etiketki = $new_path.'etiketki';
make_new_dir_z($path_etiketki,0); // создаем папку с датой
$path_excel_docs = $new_path.'excel_docs';
make_new_dir_z($path_excel_docs,0); // создаем папку с датой
$path_zip_archives = $new_path.'zip_archives';
make_new_dir_z($path_zip_archives,0); // создаем папку с датой

// die('kmnfjbflkbfg');

// вычитываем все Заказы н эту дату
$res = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, "awaiting_packaging", $dop_days_query);

// сохраняем JSON всех заказов 
$string_json_all_order = json_encode($res);
$temp_path_all_order = $path_excel_docs."/json_all_order.json";
file_put_contents($temp_path_all_order, $string_json_all_order);


// echo "************************************************************************************************<br>";
// die();



/*****************************************************************************************************************
 ******  Формируем JSON файл поартикульно Для формирования Листа подбора ПОТОМ
 ******************************************************************************************************************/
$array_oben[] = (1);
$string_json_list_podbora = json_encode($array_oben);
$temp_path = $path_excel_docs."/json_list_podbora.json";

file_put_contents($temp_path, $string_json_list_podbora);

/*****************************************************************************************************************
 *****************************  Формируем штрих кода / 1с файл и лист подбора
 ******************************************************************************************************************/
// echo "2d";
require_once "make_etikets_for_all.php";




/*****************************************************************************************************************
 ******  Формируем папки для разнесения информации 
 ******************************************************************************************************************/
// // $new_date = date('Y-m-d');
// $new_path = '../../!all_razbor/ozon/'.$date_query_ozon."";

// make_new_dir_z($new_path,0); // создаем папку с датой

// $new_path = $new_path.'/'.$number_order.'(dop)'.'/';
// make_new_dir_z($new_path,0); // создаем папку с датой

// $path_etiketki = $new_path.'etiketki';
// make_new_dir_z($path_etiketki,0); // создаем папку с датой
// $path_excel_docs = $new_path.'excel_docs';
// make_new_dir_z($path_excel_docs,0); // создаем папку с датой
// $path_zip_archives = $new_path.'zip_archives';
// make_new_dir_z($path_zip_archives,0); // создаем папку с датой


die('ОТПРАВИЛИ МНОГО ЗАКАЗОВ');
