<?php
/**********************************************************************************************************
 *    ОДНОДНЕВНЫЙ ОТЧЕТ ПО ПРОДАЖАМ ПО ОДНОМУ АРТИКУЛУ
 * артикул, дату и магазин, получаем по GET запросу
*********************************************************************************************************/

require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";


$json_data_send = $_GET['json_data_send'];
$shop_name = $_GET['shop_name'];
$article = $_GET['article'];
// $token =  $token_ozon;
// $client_id =  $client_id_ozon;
// Вставляем форму для ввода
// require_once "start_form.php";
// echo "<pre>";
// print_r($send_data);

$priznak_all_orders = 0;
$json_data_send = json_encode($send_data);
$temp_res = send_injection_on_ozon($token_ozon, $client_id_ozon, $json_data_send, 'v1/finance/realization/by-day');



// echo "<pre>";
// print_r($temp_res['rows']);

// die();



if (!isset($temp_res['rows'])) {
   echo "Нет даных для выдачи";
   die();
}

foreach ($temp_res['rows'] as $item) {
   if ($item['item']['offer_id'] == "ANM.39*59") {
      $item['item']['offer_id'] = "301";
   }
   if ($item['item']['offer_id'] == "ANM.49*99") {
      $item['item']['offer_id'] = "302";
   }
// количетво товарв
$arr_article[$item['item']['offer_id']]['count'] = @$arr_article[$item['item']['offer_id']]['count']  + $item['delivery_commission']['quantity'];
// сумма заказа
$arr_article[$item['item']['offer_id']]['amount'] = @$arr_article[$item['item']['offer_id']]['amount']  + $item['delivery_commission']['amount'];
// баллы за скидку
$arr_article[$item['item']['offer_id']]['bonus'] = @$arr_article[$item['item']['offer_id']]['bonus']  + $item['delivery_commission']['bonus'];
// Базовое вознаграждение Ozon
$arr_article[$item['item']['offer_id']]['standard_fee'] = @$arr_article[$item['item']['offer_id']]['standard_fee']  + $item['delivery_commission']['standard_fee'];
// Итого к начислению
$arr_article[$item['item']['offer_id']]['total'] = @$arr_article[$item['item']['offer_id']]['total']  + $item['delivery_commission']['total'];
// Выплаты по механикам лояльности партнёров: зелёные цены.
$arr_article[$item['item']['offer_id']]['bank_coinvestment'] = @$arr_article[$item['item']['offer_id']]['bank_coinvestment']  + $item['delivery_commission']['bank_coinvestment'];

// Доля комиссии за продажу по категории.
$arr_article[$item['item']['offer_id']]['commission_ratio'] = $item['commission_ratio'];
// Цена продавца с учётом скидки.
$arr_article[$item['item']['offer_id']]['seller_price_per_instance'] = $item['seller_price_per_instance'];
}





// print_r($arr_article[6210]);
// die();
// вставляем таблицу всех продаж на выбранном озоне
require_once "print_sell_all_one_day.php";
echo "<br><br>";

die();


// вставляем таблицу всех продаж c разбивкой по городам 
require_once "print_sell_po_gorodam.php";
echo "<br><br>";