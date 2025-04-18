<?php
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/

require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";



$arr_all_nomenklatura = select_active_nomenklaturu($pdo);

// Вставляем форму для ввода
require_once "start_form.php";

$send_data = array("dir"=> "ASC",
                 "filter"=> array("since" => $date_from."T00:00:00.000Z",
                                "status" => "",
                                   "to" =>  $date_to."T23:59:59.000Z"
                ),
"limit" =>  1000,
"offset" =>  0,
"translit" => true,
"with" => array(
"analytics_data" => true,
"financial_data" => true
)
);

// echo "<pre>";
// print_r($send_data);

$priznak_all_orders = 0;
$i=0;
do {
   $json_data_send = json_encode($send_data);
   $temp_res = send_injection_on_ozon($token, $client_id, $json_data_send, 'v2/posting/fbo/list');
// Записываем все продажи в массив 
        foreach ($temp_res['result'] as $temp_item) {
            $res[] = $temp_item;
        }
    
  
   if (isset($temp_res["result"][999])) {
    $send_data["offset"] = $send_data["offset"]  + 1000; 
   } else {
    $priznak_all_orders = 1;
   }
$i++;
} while ($priznak_all_orders == 0);


if (!isset($res)) {
   echo "Нет даных для выдачи";
   die();
}

foreach ($res as $item) {
$arr_article[$item['products'][0]['offer_id']]['count'] = @$arr_article[$item['products'][0]['offer_id']]['count']  + 1;
$arr_article[$item['products'][0]['offer_id']]['price'] = @$arr_article[$item['products'][0]['offer_id']]['price']  + $item['products'][0]['price'];
$arr_warehouse[$item['products'][0]['offer_id']][$item['analytics_data']['warehouse_name']] = 
@$arr_warehouse[$item['products'][0]['offer_id']][$item['analytics_data']['warehouse_name']] + 1;
}




// echo "<pre>";
// print_r($arr_warehouse);

// вставляем таблицу всех продаж на выбранном озоне
require_once "print_all_sells.php";
echo "<br><br>";
// вставляем таблицу всех продаж c разбивкой по городам 
require_once "print_sell_po_gorodam.php";
echo "<br><br>";