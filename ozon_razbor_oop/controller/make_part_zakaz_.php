<?php
/**
 *   суть работы такая же как и make_all_zakaz.php 
 *   только здесь мы получаем не все отправления а только выбранное количество
 *  (один артикул  или часть товаров одного артикула)
 *  
 */

require_once '../../connect_db.php';
require_once '../include_funcs.php';
require_once 'make_1c_file.php';
require_once "../../mp_functions/get_token_by_shop.php"; 
require_once '../functions/part_function.php';

require_once '../../vendor/autoload.php';
use App\Ozon\OzonClient;
use App\Ozon\Service\OrderService;



// echo  "ПРОШЛИ КОННЕКТ<br>";
if (!isset($_POST['ozon_shop']) || $_POST['ozon_shop'] === '') {
    http_response_code(400);
    exit('Параметр ozon_shop обязателен');
} else {
$shop_name = $_POST['ozon_shop'];
}
// получаем токен запрашиваемого магазина
[$token , $client_id] = get_token_AND_idclient ($arr_tokens, $shop_name);


$number_order = trim($_POST['number_order'] ?? '');
if (empty($number_order)) die('Номер заказа не передан');

$now_date_razbora = $_POST['now_date_razbora'];
$date_query_ozon  = $_POST['date_query_ozon'];
$dop_days_query   = $_POST['dop_days_query'];

/// формируем список артикла и количества товаров, который нужно собрать

$array_with_select_article = [];
foreach ($_POST['selected'] as $select_article=> $select) {
    $array_with_select_article[$select_article] = $_POST['new_qty'][$select_article];
}


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

$file_name_OTLADKA = $path_excel_docs."/otladka.txt";

/****************************************************************************************************************
// **********************    сохраняем данные с POST запроса в JSON файд
****************************************************************************************************************/
  $string_json_post_data = json_encode($_POST, JSON_UNESCAPED_UNICODE);
  $temp_path_all_order = $path_excel_docs."/".$number_order ."_json_post_data.json";
  file_put_contents($temp_path_all_order, $string_json_post_data);



$startTime = microtime(true);
$text_otladka = $startTime." "."Вычитываем все заказы в состоянии awaiting_packaging"."\n\n";
file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);



/****************************************************************************************************************
// вычитываем все Заказы в состоянии ОЖИДАЮТ ОТГУЗКИ *******************************************
 *****************************************************************************************************************/

$ozon   = new OzonClient($token, $client_id);
$orders = new OrderService($ozon);

$ArrayOrders = $orders->getUnfulfilledFbs($date_query_ozon, 'awaiting_packaging', $dop_days_query);
 
 /****************************************************************************************************************
 * // сохраняем JSON всех заказов 
 ****************************************************************************************************************/ 
$startTime = microtime(true);
$text_otladka = $startTime." "."Сохраняем все заказы в файл ".$number_order ."_json_awaiting_packaging.json"."\n\n";
file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);


  $string_json_awaiting_packaging = json_encode($ArrayOrders, JSON_UNESCAPED_UNICODE);
  $temp_path_awaiting_packaging = $path_excel_docs."/".$number_order ."_json_awaiting_packaging.json";
  file_put_contents($temp_path_awaiting_packaging, $string_json_awaiting_packaging);


$number_buyer_number_zakaza ='';
// ***********************************************************************************************************
// Из полученного массива формируем массив заказов в которых есть выбранных товары
// формируем массив заказов где только выбранный товар без еще каких либо товаров
// ***********************************************************************************************************
$arr_for_zakaz=[];
   foreach ($ArrayOrders as $posts) {
      // print_r ($posts);
      // следующим условием выбираем только заказы с одним типом товаров 
            if (count($posts['products']) == 1 ){
            foreach ($posts['products'] as $prods) 
            {
             // оследующим условием оставляем только выбранные нами товары
                if (isset ($array_with_select_article[$prods['offer_id']])) {
                    preg_match_all('/\d+/', $posts['posting_number'], $matches);
                    $firstTwoNumbers = array_slice($matches[0], 0, 2);
                    $number_buyer_number_zakaza = implode('-', $firstTwoNumbers);

                    $arr_for_zakaz[$number_buyer_number_zakaza]['posting_number'] = $posts['posting_number'];
                    $arr_for_zakaz[$number_buyer_number_zakaza]['order_number'] = $posts['order_number'];
                    $arr_for_zakaz[$number_buyer_number_zakaza]['shipment_date'] = substr($posts['shipment_date'],0,10);
                    $arr_for_zakaz[$number_buyer_number_zakaza]['products'][$prods['offer_id']]['sku'] = $prods['sku'];
                    $arr_for_zakaz[$number_buyer_number_zakaza]['products'][$prods['offer_id']]['name'] = $prods['name'];
                    $arr_for_zakaz[$number_buyer_number_zakaza]['products'][$prods['offer_id']]['quantity'] = $prods['quantity'];
                    // $i++;
                } 
              }
       } else {
        // сюда складываем заказы где несколько товаров
        $arr_with_some_items_position[] = $posts; // пока этот массив не трогаем забиваем на него
       }
   }


// print_r($arr_for_zakaz);


// ***********************************************************************************************************
// ***********************************************************************************************************
// сортируем массивы где ТОЛЬКО наш товар, без каких либо дополнительных товаров 
// Типа приоритетные заказы для разбора
// ***********************************************************************************************************
$prioritet_arr_for_zakaz=[];
foreach ($arr_for_zakaz as $number_buyer_number_zakaza=> $products_zz) {
  if (count($products_zz['products']) == 1 ){
    $prioritet_arr_for_zakaz[$number_buyer_number_zakaza] = $products_zz;
    
  }
}

// сформируем массив НОМЕР ЗАКАЗА - количество товаров, для алгоритма РЮКЗАК
$arr_for_rukzak =[];
foreach ($prioritet_arr_for_zakaz as $number_buyer_number_zakaza=> $products_zz) {
  foreach ($products_zz['products'] as $article => $product) {
    $arr_for_rukzak[$article][] = array ('id' => $number_buyer_number_zakaza, 'target_qty' => $product['quantity']);
  }
}


// отправляем массив в алгоритм рюкзак 
// получчаем наиболее полный состав выбранных артикулов (для отправки на сборку)
$return_rukzak =[];
foreach ($arr_for_rukzak as $article=> $order) {
  $return_rukzak[$article] = knapsackSelectOrders($order, $array_with_select_article[$article]);
}


/// теперь выберем те заказы которые предложил алгоритм Рюкзак и их уже отправим на сборку 
$array_for_rabor =[];
foreach ($return_rukzak as $article => $selected_orders ) {
    foreach ($selected_orders['selected_ids'] as $select_order) {
       $array_for_rabor[$select_order] = $prioritet_arr_for_zakaz[$select_order];
    }
}

// echo "<pre>";
// print_r($arr_for_zakaz);
// print_r($array_for_rabor);
// echo "<br> *******************************";
// echo "коливо элементов = ".count($array_for_rabor)."<br>";


//=============================================================================================================
// Сохраяем только те заказы, которые будем собирать, 
// тут нет МИКС заказов, только заказы с одним типом товарв
// И только с той номенклатурой, либо меньше !!!!
//=============================================================================================================
$string_json_all_order = json_encode($array_for_rabor, JSON_UNESCAPED_UNICODE);
$temp_path_all_order = $path_excel_docs."/json_all_order.json";
file_put_contents($temp_path_all_order, $string_json_all_order);


// print_r($arr_for_rukzak);
// die('ssssssssssssssssssssssss');
 
/////////////// Приводим массив в удобный вид  //////////////////////////////////////////////////////////
if (count($array_for_rabor) == 0) {
    echo "<br><h2> Нет массива данных на дату <b>[".$date_query_ozon."]</b> в состоянии <b>[ОЖИДАЮТ СБОРКИ]</b> DIE </h2><br>";
    die();
}

$startTime = microtime(true);
$text_otladka = $startTime." "."Сохраняем заказы для разбора в файл ".$number_order ."_json_order_for_razbor.json"."\n\n";
file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);



  $string_json_order_for_razbor = json_encode($array_for_rabor, JSON_UNESCAPED_UNICODE);
  $temp_path_order_for_razbor = $path_excel_docs."/".$number_order ."_json_order_for_razbor.json";
  file_put_contents($temp_path_order_for_razbor, $string_json_order_for_razbor);


  // die('ddddd');

// если есть Заказы на ОЗОН, то перебираем все отправления по одному и формируем JSON для отправки в ОЗОН

// отсюда начинаем отсчитывавать время выполенния скрипта

$startTime = microtime(true);
$text_otladka = $startTime." "."Начали перебор этикеток"."\n";
file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);


// echo "Время начала скрипта : { $startTime} <br>"; 
// die('kkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkk');
set_time_limit(0);
//// РАзбиваем каждое отправление на единичное отправление и переводим в статус awaiting_deliver
foreach ($array_for_rabor as $one_post) {
  echo "<br> ************************ one_post *******************************  ";
  print_r($one_post);
    $result[] = make_packeges_for_one_post_2($token, $client_id, $one_post);
    usleep(120); // 

    $realTime = microtime(true);
    $text_otladka = $realTime." "."Разбиваем заказы по одному отправлению {$one_post["posting_number"]}"."\n";
    file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);

}


// echo "<br>";
// echo "************************** result ПОСЛЕ РАЗБИТИЯ ПО ОТПРАВЛЕНИЯМ **************************************";

// print_r($result);

  $string_json_order_for_razbor = json_encode($result, JSON_UNESCAPED_UNICODE);
  $temp_path_order_for_razbor = $path_excel_docs."/".$number_order ."_json_result_razbivka_po_otpravlenie.json";
  file_put_contents($temp_path_order_for_razbor, $string_json_order_for_razbor);


// die('dddddddddddddddd');
// было до 28.08.2025

$link_for_make_etikets_for_all ="wait_file.php?ozon_shop=".$shop_name.
                               "&now_date_razbora=".$now_date_razbora.
                               "&date_query_ozon=".$date_query_ozon.
                               "&dop_days_query=".$dop_days_query.
                               "&number_order=".$number_order;



// echo "<script>window.open('$link_for_make_etikets_for_all', '_blank');</script>";

 echo <<<HTML
 <a href="$link_for_make_etikets_for_all" target="_blank">Аварийный переход на формирование этикеток</a>

 HTML;



die('Далее тпереходим на получение ПДФ этикеток');

