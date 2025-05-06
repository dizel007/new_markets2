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
// $dop_days_query = 0; // Всегда собираем за один день

$now_date_razbora = date('Y-m-d');
$date_query_ozon = date('Y-m-d', strtotime($now_date_razbora . ' -5 day'));
$dop_days_query = 10;







/*****************************************************************************************************************
 ******  Формируем папки для разнесения информации 
 ******************************************************************************************************************/
// $new_date = date('Y-m-d');
// $new_path = '../reports/'.$date_query_ozon."";
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

// вычитываем все Заказы в состоянии ОЖИДАЮТ ОТГУЗКИ *******************************************
$res = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, "awaiting_packaging", $dop_days_query);

// сохраняем JSON всех заказов 
$string_json_all_order = json_encode($res, JSON_UNESCAPED_UNICODE);
$temp_path_all_order = $path_excel_docs."/json_all_order.json";
file_put_contents($temp_path_all_order, $string_json_all_order);

$file_name_OTLADKA = $path_excel_docs."/otladka.txt";

$i=0;
// Из полученного массива формируем массив данных, с которым убодно будет отправлять заказы на сборку
// также тут формируем массив    $array_art   для создания Заказа в 1С.
   foreach ($res['result']['postings'] as $posts) {
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
    // echo "<pre>";
    // echo "<br>==/ Количество заказов /==". count($arr_for_zakaz);
// отсюда начинаем отсчитывавать время выполенния скрипта
$startTime = microtime(true);

$text_otladka = $startTime." "."Начали перебор этикеток"."\n";
file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);


// echo "Время начала скрипта : { $startTime} <br>"; 

set_time_limit(0);

foreach ($arr_for_zakaz as $one_post) {
    $result = make_packeges_for_one_post_2($token_ozon, $client_id_ozon,$one_post);
    usleep(120); // 

    $realTime = microtime(true);
    $text_otladka = $realTime." "."Разбиваем заказы по одному отправлению "."\n";
    file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);

    // $array_list_podbora[] = $result['list_podbora'];
    // $array_oben[] = $result['obmen'];
    // print_r($result['obmen']);

}


/*****************************************************************************************************************
 ******  Формируем JSON файл поартикульно Для формирования Листа подбора ПОТОМ
 ******************************************************************************************************************/
// $string_json_list_podbora = json_encode($array_oben);
// $temp_path = $path_excel_docs."/json_list_podbora.json";

// file_put_contents($temp_path, $string_json_list_podbora);

/*****************************************************************************************************************
 *****************************  Формируем штрих кода / 1с файл и лист подбора
 ******************************************************************************************************************/

// require_once "make_etikets_for_all.php";



$link_for_make_etikets_for_all = 'wait_file.php?ozon_shop='.$ozon_shop."&path_excel_docs=".$path_excel_docs."&number_order=".$number_order;
$link_for_make_etikets_for_all .="&path_etiketki=".$path_etiketki;


// header('Location: '.$link_for_make_etikets_for_all, true, 301);

echo "<script>window.open('$link_for_make_etikets_for_all', '_blank');</script>";


 echo <<<HTML
 <br><br>
 <a href="$link_for_make_etikets_for_all" target="_blank">Аварийный переход на формирование этикеток</a>
 <br><br>
 HTML;



die('Далее тпереходим на получение ПДФ этикеток');

