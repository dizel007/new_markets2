<?php
/**********************************************************************************************************
 *     ОДНОДНЕВНЫЙ ОТЧЕТ ПО ПРОДАЖАМ ПО ВСЕМ АРТИКУЛАМ
*********************************************************************************************************/

require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";

// Выбираем магазин 
if (!isset($_GET['ozon_shop'])) {
   $_GET['ozon_shop'] = 'ozon_anmaks';
} else {
   $ozon_shop = $_GET['ozon_shop'];
}

if ($_GET['ozon_shop'] == 'ozon_anmaks') {
       $token =  $token_ozon;
       $client_id =  $client_id_ozon;
       $name_mp_shop = 'OZON ООО АНМАКС';
       $select_ozon_anmaks = "selected";
       $select_ozon_ip = "";
   }
       
elseif ($_GET['ozon_shop'] == 'ozon_ip_zel') {
       $token =  $token_ozon_ip;
       $client_id =  $client_id_ozon_ip;
       $name_mp_shop = 'OZON ИП ЗЕЛ';
       $select_ozon_anmaks = "";
       $select_ozon_ip = "selected";
 } else {
       die ('МАГАЗИН НЕ ВЫБРАН');
 }


 // Выбираем дату

if (isset($_GET['dateQuery'])) {
   $dateQuery = $_GET['dateQuery'];
} else {
   $dateQuery = false;
}


echo <<<HTML
<head>
<link rel="stylesheet" href="css/main_table.css">

</head>
<body>

<form action="#" method="get">
<label>Магазин</label>
<select required name="ozon_shop">
   <option {$select_ozon_anmaks} value = "ozon_anmaks">OZON</option>
   <option {$select_ozon_ip} value = "ozon_ip_zel">OZON_IP</option>
</select>


<label>дата запроса</label>
<input required type="date" name = "dateQuery" value="$dateQuery">
<input type="submit"  value="START">
</form>
HTML;


if ($dateQuery == false) {
   echo "Нужно выбрать дату";
   die();
}
$yearQuery = (int) (substr($dateQuery, 0, 4));
$monthQuery = (int) substr($dateQuery, 5, 2);
$dayQuery = (int) substr($dateQuery, 8, 2);


$arr_all_nomenklatura = select_active_nomenklaturu($pdo);

// Вставляем форму для ввода
// require_once "start_form.php";

$send_data = array(
   "day" => $dayQuery,
   "month" => $monthQuery,
   "year" => $yearQuery
);
// 

$str_data_send = implode($send_data);
// echo "<pre>";
// print_r($send_data);

$priznak_all_orders = 0;
$json_data_send = json_encode($send_data);
$temp_res = send_injection_on_ozon($token, $client_id, $json_data_send, 'v1/finance/realization/by-day');


// echo "<pre>";
// print_r($temp_res['rows']);

// die();
// echo "<pre>";


if (!isset($temp_res['rows'])) {
   echo "Нет даных для выдачи";
   die();
}
// Если есть жанные, то сохраним их
$file_name_one_day_json = "_arhive_all_data_days/".$_GET['ozon_shop']."/".$yearQuery.".".$monthQuery.".".$dayQuery.".json";
file_put_contents($file_name_one_day_json, json_encode($temp_res, JSON_UNESCAPED_UNICODE));

$i=0;
foreach ($temp_res['rows'] as $item) {
   if ($item['item']['offer_id'] == "ANM.39*59") {
      $item['item']['offer_id'] = "301";
   }
   if ($item['item']['offer_id'] == "ANM.49*99") {
      $item['item']['offer_id'] = "302";
   }

// **********************************************  Продажи 
// **********************************************  Продажи 
if (isset($item['delivery_commission']['quantity'])) {
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
   // СКУ
   $arr_article[$item['item']['offer_id']]['sku'] = $item['item']['sku'];

}
// **********************************************  Возвраты ****************************************
// **********************************************  Возвраты ****************************************

elseif (isset($item['return_commission']['quantity'])) {
      $arr_article_return[$item['item']['offer_id']]['count'] = @$arr_article_return[$item['item']['offer_id']]['count']  + $item['return_commission']['quantity'];
      // сумма заказа
      $arr_article_return[$item['item']['offer_id']]['amount'] = @$arr_article_return[$item['item']['offer_id']]['amount']  + $item['return_commission']['amount'];
      // баллы за скидку
      $arr_article_return[$item['item']['offer_id']]['bonus'] = @$arr_aarr_article_returnrticle[$item['item']['offer_id']]['bonus']  + $item['return_commission']['bonus'];
      // Базовое вознаграждение Ozon
      $arr_article_return[$item['item']['offer_id']]['standard_fee'] = @$arr_article_return[$item['item']['offer_id']]['standard_fee']  + $item['return_commission']['standard_fee'];
      // Итого к начислению
      $arr_article_return[$item['item']['offer_id']]['total'] = @$arr_article_return[$item['item']['offer_id']]['total']  + $item['return_commission']['total'];
      // Выплаты по механикам лояльности партнёров: зелёные цены.
      $arr_article_return[$item['item']['offer_id']]['bank_coinvestment'] = @$arr_article_return[$item['item']['offer_id']]['bank_coinvestment']  + $item['return_commission']['bank_coinvestment'];

      // Доля комиссии за продажу по категории.
      $arr_article_return[$item['item']['offer_id']]['commission_ratio'] = $item['commission_ratio'];
      // Цена продавца с учётом скидки.
      $arr_article_return[$item['item']['offer_id']]['seller_price_per_instance'] = $item['seller_price_per_instance'];
      // СКУ
      $arr_article_return[$item['item']['offer_id']]['sku'] = $item['item']['sku'];


} else {
   echo "<br>FIND CHTOTO<br>";
}

$i++;
}





// print_r($arr_article);
// print_r($arr_article_return);

// die();
// вставляем таблицу всех продаж на выбранном озоне
if (isset($arr_article)) {
   
require_once "print/print_sell_one_day.php";
}
echo "<br><br>";
// выводим возвраты
if (isset($arr_article_return)) {
require_once "print/print_return_one_day.php";
}
echo "<br><br>";

die();
