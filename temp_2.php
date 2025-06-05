<?php
$offset = "";
require_once $offset . "connect_db.php";
require_once $offset . "mp_functions/ozon_api_functions.php";


/**
 * ПОЛУЧАЕМ список товаров проданных за определенный месяц, с учестом возвратов
 */

  $ozon_dop_url = "v2/finance/realization";
   $send_data = '{"month": 4,"year": 2025}';

$result_array = post_with_data_ozon($token_ozon, $client_id_ozon, $send_data, $ozon_dop_url ) ;
  
  echo "<pre>";

  print_r($result_array['result']['rows'][0]);
  // print_r($result_array['result']['rows']);
$summa_realization = 0;
$summa_bank_coinvestment = 0;
$summa_bonus = 0;
$summa_standard_fee =0;
$summa_total = 0;
$summa_return_amount=0;
$summa_return_bank_coinvestment =0;
$summa_return_bonus =0;
$summa_return_standard_fee =0;
$summa_return_total =0;


foreach ($result_array['result']['rows'] as $sell_item) {
// артикл
    $arr_summ[$sell_item['item']['sku']]['article'] = $sell_item['item']['offer_id']; 
// ску
    $arr_summ[$sell_item['item']['sku']]['sku'] = $sell_item['item']['sku']; // ску
// Реализованно на сумму 
if (isset($sell_item['delivery_commission']['quantity'])) {
    $arr_summ[$sell_item['item']['sku']]['amount'] = @$arr_summ[$sell_item['item']['sku']]['amount'] + $sell_item['delivery_commission']['amount'];
// количетво товаров
    $arr_summ[$sell_item['item']['sku']]['quantity'] = @$arr_summ[$sell_item['item']['sku']]['quantity'] + $sell_item['delivery_commission']['quantity'];
// Выплаты по механикам лояльности партнёров
  $arr_summ[$sell_item['item']['sku']]['bank_coinvestment'] = @$arr_summ[$sell_item['item']['sku']]['bank_coinvestment'] + $sell_item['delivery_commission']['bank_coinvestment'];
// Баллы за скидки
$arr_summ[$sell_item['item']['sku']]['bonus'] = @$arr_summ[$sell_item['item']['sku']]['bonus'] + $sell_item['delivery_commission']['bonus'];
// Базовое вознаграждение Ozon
$arr_summ[$sell_item['item']['sku']]['standard_fee'] = @$arr_summ[$sell_item['item']['sku']]['standard_fee'] + $sell_item['delivery_commission']['standard_fee'];
// Итого к начислению
$arr_summ[$sell_item['item']['sku']]['total'] = @$arr_summ[$sell_item['item']['sku']]['total'] + $sell_item['delivery_commission']['total'];

// сумма при продажк
    $summa_realization += $sell_item['delivery_commission']['amount'];
    $summa_bank_coinvestment+= $sell_item['delivery_commission']['bank_coinvestment'];
    $summa_bonus +=$sell_item['delivery_commission']['bonus'];
    $summa_standard_fee +=$sell_item['delivery_commission']['standard_fee'];
    $summa_total += $sell_item['delivery_commission']['total'];

  }
/// RETURNS 
if (isset($sell_item['return_commission']['quantity'])) {
  $arr_summ[$sell_item['item']['sku']]['return_quantity'] = @$arr_summ[$sell_item['item']['sku']]['return_quantity'] + $sell_item['return_commission']['quantity'];
// Возвращено на сумму 
  $arr_summ[$sell_item['item']['sku']]['return_amount'] = @$arr_summ[$sell_item['item']['sku']]['return_amount'] + $sell_item['return_commission']['amount'];
// ВОЗВРАТНЫЕ Выплаты по механикам лояльности партнёров
$arr_summ[$sell_item['item']['sku']]['return_bank_coinvestment'] = @$arr_summ[$sell_item['item']['sku']]['return_bank_coinvestment'] + $sell_item['return_commission']['bank_coinvestment'];
// Баллы за скидки
$arr_summ[$sell_item['item']['sku']]['return_bonus'] = @$arr_summ[$sell_item['item']['sku']]['return_bonus'] + $sell_item['return_commission']['bonus'];
// Базовое вознаграждение Ozon
$arr_summ[$sell_item['item']['sku']]['return_standard_fee'] = @$arr_summ[$sell_item['item']['sku']]['return_standard_fee'] + $sell_item['return_commission']['standard_fee'];
// Итого к начислению
$arr_summ[$sell_item['item']['sku']]['return_total'] = @$arr_summ[$sell_item['item']['sku']]['return_total'] + $sell_item['return_commission']['total'];


// суммы вовзратов
  $summa_return_amount += $sell_item['return_commission']['amount'];
  $summa_return_bank_coinvestment+= $sell_item['return_commission']['bank_coinvestment'];
  $summa_return_bonus +=$sell_item['return_commission']['bonus'];
  $summa_return_standard_fee +=$sell_item['return_commission']['standard_fee'];
  $summa_return_total += $sell_item['return_commission']['total'];
}


// //delivery_commission
if (($sell_item['rowNumber'] == 161)) {
  print_r($sell_item);
}

  


}



echo "<br>Реализовано на сумму = ". number_format($summa_realization, 2, ",", ".");
echo "<br>Выплаты по механикам лояльности партнёров = ".number_format($summa_bank_coinvestment, 2, ",", ".");
echo "<br>Баллы за скидки = ".number_format($summa_bonus, 2, ",", ".");
echo "<br>Базовое вознаграждение Ozon = ".number_format($summa_standard_fee, 2, ",", ".");
echo "<br>Итого к начислению = ".number_format($summa_total, 2, ",", ".");
echo "<br>Возвращено на сумму  = ".number_format($summa_return_amount, 2, ",", ".");
echo "<br>Возвраты Выплаты по механикам лояльности партнёров = ".number_format($summa_return_bank_coinvestment, 2, ",", ".");
echo "<br>Возвраты Баллы за скидки = ".number_format($summa_return_bonus, 2, ",", ".");
echo "<br>Возвраты Базовое вознаграждение Ozon = ".number_format($summa_return_standard_fee, 2, ",", ".");
echo "<br>Возвраты Итого возвращено = ".number_format($summa_return_total, 2, ",", ".");


echo "<br>";

  print_r($arr_summ);
