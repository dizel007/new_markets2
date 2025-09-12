<?php


require_once '../../connect_db.php';
require_once '../include_funcs.php';
require_once 'make_1c_file.php';

/*****************************************************************************************************************
 ******  Собираем данные ГЕТ запроса 
 ******************************************************************************************************************/

$ozon_shop = $_GET['ozon_shop'];
if ($ozon_shop  == 'ozon_anmaks') {
       $token_ozon = $token_ozon;
       $client_id_ozon = $client_id_ozon;
    }
elseif ($ozon_shop  == 'ozon_ip_zel') {
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
 ******  Формируем папки для разнесения информации 
 ******************************************************************************************************************/

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

$file_name_OTLADKA = $path_excel_docs."/otladka.txt";
// вычитываем все Заказы н эту дату
$res = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, "awaiting_deliver", $dop_days_query);

// сохраняем JSON всех заказов 
$string_json_all_order = json_encode($res, JSON_UNESCAPED_UNICODE);
$temp_path_all_order = $path_excel_docs."/json_all_order.json";
file_put_contents($temp_path_all_order, $string_json_all_order);


/*****************************************************************************************************************
 ******  Формируем ссылку с параметрами для перехода 
 ******************************************************************************************************************/
$link_for_make_etikets_for_all ="wait_file.php?ozon_shop=".$ozon_shop.
                                 "&now_date_razbora=".$now_date_razbora.
                                 "&date_query_ozon=".$date_query_ozon.
                                 "&dop_days_query=".$dop_days_query.
                                 "&number_order=$number_order";
// header('Location: '.$link_for_make_etikets_for_all, true, 301);

echo "<script>window.open('$link_for_make_etikets_for_all', '_blank');</script>";


 echo <<<HTML
 <br><br>
 <a href="$link_for_make_etikets_for_all" target="_blank">Аварийный переход на формирование этикеток</a>
 <br><br>
 HTML;



die('Далее переходим на получение ПДФ этикеток');
