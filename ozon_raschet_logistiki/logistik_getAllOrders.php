
<?php
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";
require_once "print_FBO_FBS_sells.php";


set_time_limit(300);
ini_set('memory_limit', '1024M');
// Вставляем форму для ввода даты
require_once "start_form.php";

//// Проверяем и есть нет папки, то создаем ее, для каждого пользователя своя папка в !cache
 
$dir_for_cache = "../!cache/".$userdata['user_login']."/ozon_fbs_fbo_clusters/";
if (!is_dir($dir_for_cache)) {
    if (mkdir($dir_for_cache, 0755, true)) {
        echo "Папка создана.";
    } else {
        echo "Не удалось создать папку.";
    }
}

$filename_fbs_orders = $dir_for_cache."json_fbs_Orders_".$shop_name.".json";
$filename_fbo_orders = $dir_for_cache."json_fbo_Orders_".$shop_name.".json";



/***************************************************************************************************************
 * Заказы для ООО Анм
 **************************************************************************************************************/

//==============================================================
// Получаем массив ФБС заказов 
//==============================================================
$orders_FBS_ooo = query_FBS_orders_from_ozon ($token_ozon, $client_id_ozon, $date_from, $date_to);
// сохраняем файл на диске, чтобы потом детелаьно ковырятся в нем и не запрашивать новый
file_put_contents($filename_fbs_orders, json_encode($orders_FBS_ooo, JSON_UNESCAPED_UNICODE));
$orders_FBS_article_ooo = make_array_with_all_FBS_FBO_orders ($orders_FBS_ooo);
unset($orders_FBS_ooo);
//==============================================================
// Получаем массив ФБO заказов 
//==============================================================
$orders_FBO_ooo = query_FBO_orders_from_ozon ($token_ozon, $client_id_ozon, $date_from, $date_to);
// сохраняем файл на диске, чтобы потом детелаьно ковырятся в нем и не запрашивать новый
file_put_contents($filename_fbo_orders,json_encode($orders_FBO_ooo, JSON_UNESCAPED_UNICODE));
$orders_FBO_article_ooo = make_array_with_all_FBS_FBO_orders ($orders_FBO_ooo);
unset($orders_FBO_ooo);
//==============================================================
// формируем перечень артикулов которые были проданы
//==============================================================

foreach ($orders_FBS_article_ooo as $key=>$z) {
   $art_ar_ooo[$key] = $key;
 }
foreach ($orders_FBO_article_ooo as $key=>$z) {
   $art_ar_ooo[$key] = $key;
}
// Привем массив артикулов в порядок (согласно порядковому нормеру)
// foreach ($arr_poriadkovii_number as $key=>$number_position) {
//   if (isset($art_ar_ooo[$key])){ $arr_sort_ar_ooo[$key] = $number_position;}
// }

// Сортировка по возрастанию с сохранением ключей
$arr_sort_ar_ooo = $art_ar_ooo;
asort($arr_sort_ar_ooo);

// echo "<pre>";
// print_r($arr_poriadkovii_number);
// print_r($arr_sort_ar_ooo);
// die();

print_sum_table($shop_name ,$arr_sort_ar_ooo, $orders_FBS_article_ooo, $orders_FBO_article_ooo);




/*****************************************************************************************************
 *   Запрашиваем перечень заказов ФБС за выбранные даты
 *****************************************************************************************************/
function query_FBS_orders_from_ozon ($token, $client_id, $date_from, $date_to) {

$send_data_array = array("dir"=> "ASC",
                 "filter"=> array("since" => $date_from."T00:00:00.000Z",
                                 "to" =>  $date_to."T23:59:59.000Z",

                                "statuses" => ['awaiting_registration', 'acceptance_in_progress', 'awaiting_packaging', 'awaiting_deliver',
                                'delivering', 'delivered' , 'driver_pickup' , 'cancelled', 'awaiting_approve',
                                'arbitration' , 'client_arbitration', 'not_accepted' ],


                                                
                                 ),
                  "limit" =>  100,
                  "cursor" =>  '',
                  "sort_dir" => "ASC",
                  "translit" => false,
                  "with" => array(
                  "analytics_data" => true,
                  "legal_info" => true,
                  "financial_data" => true
                  )
);

       $json_data_send = json_encode($send_data_array, JSON_UNESCAPED_UNICODE)  ;  

// запустили запрос на озона
do {
    sleep(0.5);
    $res = send_injection_on_ozon($token, $client_id, $json_data_send, 'v4/posting/fbs/list' );
    $summ_data[]=$res['postings']; // положили первый массив и смотрим нужно ли еще искать
    $send_data_array['cursor'] = $res['cursor'];
    $json_data_send = json_encode($send_data_array, JSON_UNESCAPED_UNICODE)  ;  
} while ($res['has_next']);

// делаем сплошной массив с заказами
foreach ($summ_data as $oneQueryData) {
    foreach ($oneQueryData as $oneData) {
        $ArrayOrders[]=$oneData;
    }
}

if (!isset($ArrayOrders)) {
   echo "Нет даных для выдачи";
   die();
}


return $ArrayOrders;
}


/*****************************************************************************************************
 *   Формируем массив для вывода все заказанных товаров
 *****************************************************************************************************/

function make_array_with_all_FBS_FBO_orders ($data_array) {

   // формируем массив продаж по ФБО
   foreach ($data_array as $item) {
      $article = mb_strtolower($item['products'][0]['offer_id']);
      $arr_article[$article]['count'] = @$arr_article[$article]['count']  + $item['products'][0]['quantity'];
      $arr_article[$article]['price'] = @$arr_article[$article]['price']  + $item['products'][0]['price']['amount'];
      
   }
return $arr_article;
}

/*****************************************************************************************************
 *   Запрашиваем перечень заказов ФБO за выбранные даты
 *****************************************************************************************************/
function query_FBO_orders_from_ozon ($token, $client_id, $date_from, $date_to) {

$send_data_array = array("dir"=> "ASC",
                 "filter"=> array("since" => $date_from."T00:00:00.000Z",
                                "status" => "",
                                   "to" =>  $date_to."T23:59:59.000Z"
                ),
                     "limit" =>  100,
                     "cursor" =>  '',
                     "offset" =>  0,
                     "sort_dir" => "ASC",
                     "translit" => false,
                     "with" => array(
                     "analytics_data" => true,
                     "legal_info" => true,
                     "financial_data" => true
)
);

$json_data_send = json_encode($send_data_array, JSON_UNESCAPED_UNICODE)  ;  

// запустили запрос на озона
do {
    sleep(0.5);
    $res = send_injection_on_ozon($token, $client_id, $json_data_send, 'v3/posting/fbo/list' );
    $summ_data[]=$res['postings']; // положили первый массив и смотрим нужно ли еще искать
    $send_data_array['cursor'] = $res['cursor'];
    $json_data_send = json_encode($send_data_array, JSON_UNESCAPED_UNICODE)  ;  
} while ($res['has_next']);

// делаем сплошной массив с заказами
foreach ($summ_data as $oneQueryData) {
    foreach ($oneQueryData as $oneData) {
        $ArrayOrders[]=$oneData;
    }
}

if (!isset($ArrayOrders)) {
   return false;
}


return $ArrayOrders;
}

