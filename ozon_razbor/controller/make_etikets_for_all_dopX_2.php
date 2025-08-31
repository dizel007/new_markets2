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



// $date_query_ozon = $_GET['date_query_ozon'];
$number_order = $_GET['number_order'];
$dop_days_query = 0; // Всегда собираем за один день


$now_date_razbora = date('Y-m-d');
$date_query_ozon = date('Y-m-d', strtotime($now_date_razbora . ' -5 day'));
$dop_days_query = 10;


// echo "1d";
/*****************************************************************************************************************
 ******  Формируем папки для разнесения информации 
 ******************************************************************************************************************/
// $new_date = date('Y-m-d');
// $new_path = '../reports/'.$date_query_ozon."/";
$new_path = '../../!all_razbor/ozon/'.$now_date_razbora.""; // переход в новую папку 

make_new_dir_z($new_path,0); // создаем папку с датой

$number_order = $number_order.'(dop)';
$new_path = $new_path.'/'.$number_order.'/';
make_new_dir_z($new_path,0); // создаем папку с датой

$path_etiketki = $new_path.'etiketki';
make_new_dir_z($path_etiketki,0); // создаем папку с датой
$path_excel_docs = $new_path.'excel_docs';
make_new_dir_z($path_excel_docs,0); // создаем папку с датой
$path_zip_archives = $new_path.'zip_archives';
make_new_dir_z($path_zip_archives,0); // создаем папку с датой

// die('kmnfjbflkbfg');
$file_name_OTLADKA = $path_excel_docs."/otladka.txt";
// вычитываем все Заказы н эту дату
$res = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, "awaiting_deliver", $dop_days_query);

// сохраняем JSON всех заказов 
$string_json_all_order = json_encode($res, JSON_UNESCAPED_UNICODE);
$temp_path_all_order = $path_excel_docs."/json_all_order.json";
file_put_contents($temp_path_all_order, $string_json_all_order);


/*****************************************************************************************************************
 ******  Формируем JSON файл поартикульно Для формирования Листа подбора ПОТОМ
 ******************************************************************************************************************/
// $array_oben[] = (1);
// $string_json_list_podbora = json_encode($array_oben);
// $temp_path = $path_excel_docs."/json_list_podbora.json";

// file_put_contents($temp_path, $string_json_list_podbora);


// require_once "make_etikets_for_all.php";


// Формируем ссылку на перенаправление на формирование этикеток

// $link_for_make_etikets_for_all = 'make_etikets_for_all.php?ozon_shop='.$ozon_shop."&path_excel_docs=".$path_excel_docs."&number_order=".$number_order;
// $link_for_make_etikets_for_all .="&path_etiketki=".$path_etiketki;


// было до 28.08.2025
// $link_for_make_etikets_for_all = 'wait_file.php?ozon_shop='.$ozon_shop."&path_excel_docs=".$path_excel_docs."&number_order=".$number_order;
// $link_for_make_etikets_for_all .="&path_etiketki=".$path_etiketki;

$link_for_make_etikets_for_all ="wait_file.php?ozon_shop=".$ozon_shop."&date_razbora=".$now_date_razbora."&number_order=$number_order";
// header('Location: '.$link_for_make_etikets_for_all, true, 301);

echo "<script>window.open('$link_for_make_etikets_for_all', '_blank');</script>";


 echo <<<HTML
 <br><br>
 <a href="$link_for_make_etikets_for_all" target="_blank">Аварийный переход на формирование этикеток</a>
 <br><br>
 HTML;



die('Далее переходим на получение ПДФ этикеток');
