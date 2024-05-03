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


// $token_ozon = $_GET['token_ozon'];
// $client_id_ozon = $_GET['client_id_ozon'];
// // echo $client_id_ozon."<br>";
// echo $token_ozon."<br>";


$date_query_ozon = $_GET['date_query_ozon'];
$number_order = $_GET['number_order'];
$dop_days_query = 0; // Всегда собираем за один день



// die('kmnfjbflkbfg');

/*****************************************************************************************************************
 ******  Формируем папки для разнесения информации 
 ******************************************************************************************************************/
// $new_date = date('Y-m-d');
$new_path = '../reports/'.$date_query_ozon."/";
make_new_dir_z($new_path,0); // создаем папку с датой

$new_path = $new_path.'/'.$number_order.'/';
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
foreach ($arr_for_zakaz as $one_post) {
    set_time_limit(0);
    // echo "<br>==/ Следующий заказ /==";
    $result = make_packeges_for_one_post($token_ozon, $client_id_ozon,$one_post);
    usleep(10000);
    $array_list_podbora[] = $result['list_podbora'];
    $array_oben[] = $result['obmen'];
    // print_r($result['obmen']);

}

// echo "<pre>";
// print_r($array_oben);

echo "************************************************************************************************<br>";
// die();



/*****************************************************************************************************************
 ******  Формируем JSON файл поартикульно Для формирования Листа подбора ПОТОМ
 ******************************************************************************************************************/
$string_json_list_podbora = json_encode($array_oben);
$temp_path = $path_excel_docs."/json_list_podbora.json";

file_put_contents($temp_path, $string_json_list_podbora);

/*****************************************************************************************************************
 *****************************  Формируем штрих кода / 1с файл и лист подбора
 ******************************************************************************************************************/

require_once "make_etikets_for_all.php";



die('ОТПРАВИЛИ МНОГО ЗАКАЗОВ');
