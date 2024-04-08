<?php

/********************************************************************************************************
 * ******************** Вычитываем и выводи заказы для ВБ
 ********************************************************************************************************/

$raw_arr_orders = get_all_new_zakaz($token_wb_orders); // получили массив новых отправлений


if (isset($raw_arr_orders['orders'][0])) {
  // массив новых отправлений собранный по артикулу
  $full_price = 0;
  foreach ($raw_arr_orders['orders'] as $orders) {
    $new_arr_orders[$orders['article']][] = $orders;
    $full_price = $full_price + $orders['convertedPrice'] / 100;
  }
  $middle_price = 1;
  $all_count = count($raw_arr_orders['orders']);
  foreach ($new_arr_orders as $key => $orders) {
    $raw_price = 0;

    foreach ($orders as $order) {
      $raw_price = $raw_price + $order['convertedPrice'];
    }
    $middle_price = number_format(($raw_price / count($new_arr_orders[$key])) / 100, 2);
    $sum_arr_article[make_right_articl($key)] = array(
      'count' => count($new_arr_orders[$key]),
      'price' => $middle_price
    );
  }

  $full_zakaz_wb_price = number_format($full_price, 2);


  echo <<<HTML

<div class = "table-wrapper">
<table class = "fl-table">
<thead>
  <tr>
    <th colspan ="3" >$shop_name </th>
  </tr>
</thead>
<tbody>
<td><b>Количество заказов :<br> $all_count </b></td>
<td><b>Сумма заказов :<br> $full_zakaz_wb_price </b></td>
  <td >
      <form action="wb_new_razbor/start_new_supplies.php" method="post">
        <label for="wb">Введите номер заказа из 1С</label>
        <input hidden type="text" name="token" value="$token_wb_orders">
        <input hidden type="text" name="wb_path" value="ooo">
        <input required type="number" name="Zakaz1cNumber" value="">
      <input type="submit" value="СОБРАТЬ">
      </form>    

  </td>

 
</tbody>
</table>
</div>

HTML;


  show_orders($sum_arr_article);


  unset($raw_arr_orders);
  unset($new_arr_orders);
  unset($sum_arr_article);
} else {
echo <<<HTML

  <div class = "table-wrapper">
  <table class = "fl-table ">
     <thead>
        <tr>
          <th colspan ="3" >$shop_name</th>
    </tr>
     </thead>
    <tbody>
      <td colspan ="3" ><b>НЕТ ЗАКАЗОВ</b></td>
    </tbody>
    </table>
  </div>
  
HTML;
}



