<?php
/**********************************************************************************************************
 *     ОДНОДНЕВНЫЙ ОТЧЕТ ПО ПРОДАЖАМ ПО ВСЕМ АРТИКУЛАМ
*********************************************************************************************************/

require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";



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
<select required name="ozon">
   <option value = "1">OZON</option>
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


// $client_id =  $client_id_ozon;

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
echo "<pre>";
print_r($send_data);

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
$i=0;
foreach ($temp_res['rows'] as $item) {
   if ($item['item']['offer_id'] == "ANM.39*59") {
      $item['item']['offer_id'] = "301";
   }
   if ($item['item']['offer_id'] == "ANM.49*99") {
      $item['item']['offer_id'] = "302";
   }

   echo "$i fffffffffffffffffff<br>";
if (($i == 38) OR ($i==60)){
   print_r($item);
   $i++;
   continue;
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

$i++;
}





// print_r($arr_article[6210]);
// die();
// вставляем таблицу всех продаж на выбранном озоне
require_once "print/print_sell_all_one_day.php";
echo "<br><br>";

die();
