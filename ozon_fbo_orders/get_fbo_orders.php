<?php
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/

require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";
require_once "print_sell_po_gorodam.php";
require_once "print_article_from_clusters.php";

$arr_all_nomenklatura = select_active_nomenklaturu($pdo);
foreach ($arr_all_nomenklatura as $zzz) {
   $arr_poriadkovii_number[mb_strtolower($zzz['main_article_1c'])] = $zzz['number_in_spisok'];
}

// echo "<pre>";
// print_r($arr_poriadkovii_number);


// die();
// Вставляем форму для ввода
require_once "start_form.php";

$send_data = array("dir"=> "ASC",
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

// echo "<pre>";
// print_r($send_data);
// Получаем массив продаж и сортируем по количеству и месту
$arr_ooo = make_array_for_print ($token_anmaks, $client_id_anmaks,$send_data);
$arr_article_ooo = $arr_ooo['art'];
$arr_warehouse_ooo = $arr_ooo['warehouse'];
$arr_for_art_report = $arr_ooo['warehouse_name'];

// Получаем массив продаж и сортируем по количеству и месту для второй организации
$arr_ip = make_array_for_print ($token_ip_zel, $client_id_ip_zel,$send_data);
$arr_article_ip = $arr_ip['art'];
$arr_warehouse_ip = $arr_ip['warehouse'];


// формируем перечень артикулов которые были проданы
foreach ($arr_article_ooo as $key=>$z) {
   $art_ar[$key] = $key;
   $art_ar_ooo[$key] = $key;
}
foreach ($arr_article_ip as $key=>$z) {
   $art_ar[$key] = $key;
   $art_ar_ip[$key] = $key;
}

// Привем массив артикулов в порядок (согласно порядковому нормеру)

foreach ($arr_poriadkovii_number as $key=>$z) {
  if (isset($art_ar[$key])){ $arr_sort_ar[$key] = $z;}
  if (isset($art_ar_ooo[$key])){ $arr_sort_ar_ooo[$key] = $z;}
  if (isset($art_ar_ip[$key])){ $arr_sort_ar_ip[$key] = $z;}
}

// Сортировка по возрастанию с сохранением ключей
asort($arr_sort_ar);
asort($arr_sort_ar_ooo);
asort($arr_sort_ar_ip);


// вставляем таблицу всех продаж на выбранном озоне
require_once "print_all_sells.php";
echo "<br><br>";
// вставляем таблицу всех продаж c разбивкой по городам 

print_sell_po_gorodam ($arr_warehouse_ooo , $arr_sort_ar_ooo);
echo "<br><br>";
print_sell_po_gorodam ($arr_warehouse_ip , $arr_sort_ar_ip);

echo "<br><br>";

 print_article_from_clusters ($arr_for_art_report , $arr_sort_ar_ooo) ;
echo "<br><br>";





function make_array_for_print ($token, $client_id, $send_data_array) {
       $json_data_send = json_encode($send_data_array, JSON_UNESCAPED_UNICODE)  ;  

// запустили запрос на озона
do {
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

// echo "<pre>";
// print_r($ArrayOrders[0]);
// die();


if (!isset($ArrayOrders)) {
   echo "Нет даных для выдачи";
   die();
}

// формируем массив продаж по ФБО
foreach ($ArrayOrders as $item) {
   $article = mb_strtolower($item['products'][0]['offer_id']);
   $arr_article[$article]['count'] = @$arr_article[  $article]['count']  + 1;
   $arr_article[$article]['price'] = @$arr_article[$article]['price']  + $item['products'][0]['price']['amount'];
   $arr_warehouse[$article][$item['analytics_data']['warehouse_name']] = 
   @$arr_warehouse[$article][$item['analytics_data']['warehouse_name']] + 1;
}

// формируем массив продаж по артикулам
foreach ($ArrayOrders as $item) {
   $article = mb_strtolower($item['products'][0]['offer_id']); // артикул товара
   $warehouse_name = $item['financial_data']['cluster_from']; // склад отгрудки
   $arr_article_awre[$article][$warehouse_name][] = $item;
   
}





$arr['art'] = $arr_article;
$arr['warehouse'] = $arr_warehouse;
$arr['warehouse_name'] = $arr_article_awre;

return $arr;
}


/*********************************************************************************************
 * 
 ***********************************************************************************************/