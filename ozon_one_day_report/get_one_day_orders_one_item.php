<?php
/**********************************************************************************************************
 *    ОДНОДНЕВНЫЙ ОТЧЕТ ПО ПРОДАЖАМ ПО ОДНОМУ АРТИКУЛУ
 * артикул, дату и магазин, получаем по GET запросу
*********************************************************************************************************/

require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";


$date_query = $_GET['date'];
$ozon_shop = $_GET['ozon_shop'];
$article = $_GET['article'];
// $token =  $token_ozon;
// $client_id =  $client_id_ozon;
// Вставляем форму для ввода
// require_once "start_form.php";
// echo "<pre>";
// print_r($send_data);




// $priznak_all_orders = 0;
// $json_data_send = json_encode($send_data);
// $temp_res = send_injection_on_ozon($token_ozon, $client_id_ozon, $json_data_send, 'v1/finance/realization/by-day');

$json_data_send = file_get_contents("../!one_day_report/".$ozon_shop."/".$date_query.".json");
$temp_res =json_decode($json_data_send, true );



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

   if (mb_strtolower($item['item']['offer_id']) == $article ) {
$arr_one_article[] = $item;
   }
}

// echo "<pre>";
// print_r($arr_one_article);


$summa_count = 0;
$summa_price = 0;

if (isset($arr_one_article)) {
echo '<link rel="stylesheet" href="css/sell_table.css">';
echo "<table class=\"sell_mp_table\">";

echo "<thead>";
echo "<tr>";
echo "<th>Артикул</th>"; 
echo "<th>Количество</th>"; 
echo "<th>сумма заказа</th>"; 

echo "<th>баллы за скидку</th>"; 
echo "<th>вознаграждение<br>Ozon</th>"; 
echo "<th>Итого к начислению</th>"; 
echo "<th>Выплаты по <br>механикам лояльности<br> партнёров:<br>зелёные цены.</th>"; 
echo "<th>Доля комиссии<br> за продажу<br>по категории</th>"; 
echo "<th>Цена продавца 1 шт<br>с учётом скидки.</th>"; 

echo "</tr>";
echo "</thead>";



    foreach ($arr_one_article as $item) {
      echo "<tr>";
      echo "<td>{$item['item']['offer_id']}</td>";
      echo "<td>{$item['delivery_commission']['quantity']}</td>";
      echo "<td>{$item['delivery_commission']['amount']}</td>";
      echo "<td>{$item['delivery_commission']['bonus']}</td>";
      echo "<td>{$item['delivery_commission']['standard_fee']}</td>";
      echo "<td>{$item['delivery_commission']['total']}</td>";
      echo "<td>{$item['delivery_commission']['bank_coinvestment']}</td>";
            $commission_ratio = $item['commission_ratio']*100;
            echo "<td>{$commission_ratio}</td>";   
            echo "<td>{$item['seller_price_per_instance']}</td>";   
         
            $summa_count += $item['delivery_commission']['quantity'];
            $summa_price += $item['delivery_commission']['amount'];
echo "</tr>";
               } 




echo "<tr>";
echo "<td>ИТОГО</td>"; 
echo "<td>$summa_count</td>"; 
$summa_price = number_format($summa_price,0);
echo "<td>$summa_price</td>"; 
echo "<td>-</td>"; 
echo "<td>-</td>"; 
echo "<td>-</td>"; 
echo "<td>-</td>"; 
echo "<td>-</td>"; 
echo "<td>-</td>"; 
echo "</tr>";

echo "</table>";
} else {
   echo "Нет данных для вывода";
}