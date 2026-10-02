<?php

require_once '../../connect_db.php';
require_once '../include_funcs.php';
require_once 'make_1c_file.php';
require_once "../../mp_functions/get_token_by_shop.php"; 

require_once '../../vendor/autoload.php';
use App\Ozon\OzonClient;
use App\Ozon\Service\OrderService;



// echo  "ПРОШЛИ КОННЕКТ<br>";
if (!isset($_GET['ozon_shop']) || $_GET['ozon_shop'] === '') {
    http_response_code(400);
    exit('Параметр ozon_shop обязателен');
} else {
$shop_name = $_GET['ozon_shop'];
}
// получаем токен запрашиваемого магазина
[$token , $client_id] = get_token_AND_idclient ($arr_tokens, $shop_name);


$number_order = $_GET['number_order'];
$now_date_razbora = $_GET['now_date_razbora'];
$date_query_ozon = $_GET['date_query_ozon'];
$dop_days_query = $_GET['dop_days_query'];





/*****************************************************************************************************************
 ******  Формируем папки для разнесения информации 
 ******************************************************************************************************************/

$new_path = '../../!all_razbor/ozon/'.$now_date_razbora.""; // переход в новую папку 
make_new_dir_z($new_path,0); // создаем папку с датой

$new_path = $new_path.'/'.$number_order.'/';
make_new_dir_z($new_path,0); // создаем папку с датой

$path_etiketki = $new_path.'etiketki';
make_new_dir_z($path_etiketki,0); // создаем папку с датой
$path_excel_docs = $new_path.'excel_docs';
make_new_dir_z($path_excel_docs,0); // создаем папку с датой
$path_zip_archives = $new_path.'zip_archives';
make_new_dir_z($path_zip_archives,0); // создаем папку с датой

// ****************************************************************************************************************
// Запрашиваем данные по отправлениям за несколько дней
// ****************************************************************************************************************

$ozon   = new OzonClient($token, $client_id);
$orders = new OrderService($ozon);

$ArrayOrders = $orders->getUnfulfilledFbs($date_query_ozon, 'awaiting_packaging', $dop_days_query);
 

// die();
// сохраняем JSON всех заказов 
$string_json_all_order = json_encode($ArrayOrders, JSON_UNESCAPED_UNICODE);
$temp_path_all_order = $path_excel_docs."/json_all_order.json";
file_put_contents($temp_path_all_order, $string_json_all_order);

$file_name_OTLADKA = $path_excel_docs."/otladka.txt";

$i=0;
// Из полученного массива формируем массив данных, с которым убодно будет отправлять заказы на сборку
// также тут формируем массив    $array_art   для создания Заказа в 1С.
   foreach ($ArrayOrders as $posts) {
        $arr_for_zakaz[$i]['posting_number'] = $posts['posting_number'];
        $arr_for_zakaz[$i]['shipment_date'] = substr($posts['shipment_date'],0,10);
                  
            foreach ($posts['products'] as $prods) 
            {
              $arr_for_zakaz[$i]['products'][$prods['offer_id']]['sku'] = $prods['sku'];
              $arr_for_zakaz[$i]['products'][$prods['offer_id']]['name'] = $prods['name'];
              $arr_for_zakaz[$i]['products'][$prods['offer_id']]['quantity'] = $prods['quantity'];
             }

    $i++;
   }

if (!isset($arr_for_zakaz)) {
    echo "<br><h2> Нет массива данных на дату <b>[".$date_query_ozon."]</b> в состоянии <b>[ОЖИДАЮТ СБОРКИ]</b> DIE </h2><br>";
    die();
}
// если есть Заказы на ОЗОН, то перебираем все отправления по одному и формируем JSON для отправки в ОЗОН

// отсюда начинаем отсчитывавать время выполенния скрипта

$startTime = microtime(true);
$text_otladka = $startTime." "."Начали перебор этикеток"."\n";
file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);


// echo "Время начала скрипта : { $startTime} <br>"; 

set_time_limit(0);
//// РАзбиваем каждое отправление на единичное отправление и переводим в статус awaiting_deliver
foreach ($arr_for_zakaz as $one_post) {
    $result = make_packeges_for_one_post_2($token, $client_id, $one_post);
    usleep(120); // 

    $realTime = microtime(true);
    $text_otladka = $realTime." "."Разбиваем заказы по одному отправлению {$one_post["posting_number"]}"."\n";
    file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);

    // $array_list_podbora[] = $result['list_podbora'];
    // $array_oben[] = $result['obmen'];
    // print_r($result['obmen']);

}


// было до 28.08.2025
// $link_for_make_etikets_for_all = 'wait_file.php?ozon_shop='.$ozon_shop."&path_excel_docs=".$path_excel_docs."&number_order=".$number_order;
// $link_for_make_etikets_for_all .="&path_etiketki=".$path_etiketki;

// $link_for_make_etikets_for_all ="wait_file.php?ozon_shop=".$ozon_shop."&date_razbora=".$now_date_razbora."&number_order=$number_order";
// header('Location: '.$link_for_make_etikets_for_all, true, 301);

$link_for_make_etikets_for_all ="wait_file.php?ozon_shop=".$shop_name.
                                 "&now_date_razbora=".$now_date_razbora.
                                 "&date_query_ozon=".$date_query_ozon.
                                 "&dop_days_query=".$dop_days_query.
                                 "&number_order=$number_order";


sleep(4);
echo "<script>window.open('$link_for_make_etikets_for_all', '_blank');</script>";


 echo <<<HTML
 <br><br>
 <a href="$link_for_make_etikets_for_all" target="_blank">Аварийный переход на формирование этикеток</a>
 <br><br>
 HTML;



die('Далее тпереходим на получение ПДФ этикеток');

