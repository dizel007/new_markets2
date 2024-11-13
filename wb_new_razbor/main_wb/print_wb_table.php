<?php 
/****************************************************************************************
 *  РИСУЕМ форму по выводу всех заказов
 ***************************************************************************************/
function print_wb_order_table($shop_name, $date_orders_select , $raw_arr_orders, $text_about_date , $token_wb_orders) {
echo <<<HTML
<div class = "table-wrapper">
<table class = "fl-table">
<thead>
  <tr>
    <th class="big_text" colspan ="3" >$shop_name </th>
  </tr>
</thead>
<tbody>
  <td >
      <form action="#" method="get">
        <label>Введите дату СОЗДАНИЯ ЗАКАЗА </label>
    <div id="up_input" class="LockOff"> 
      <input type="date" name="date_sbora_zakaza" value="$date_orders_select">
      <input type="submit" value="НАЙТИ ЗАКАЗЫ НА ВЫБРАННУЮ ДАТУ">
      </form>    
</div>
  </td>

 
</tbody>
</table>
</div>
HTML;





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
<tr>
    <th colspan ="3" class="big_text" >$text_about_date</th>
 </tr>

<td><b>Количество заказов :<br> $all_count </b></td>
<td ><b>Сумма заказов :<br> $full_zakaz_wb_price </b></td>
  <td >
      <form action="start_new_supplies.php" method="post">
        <label for="wb">Введите номер заказа из 1С</label>
        <input hidden type="text" name="token" value="$token_wb_orders">
        <input hidden type="date" name="date_sbora_zakaza" value="$date_orders_select">
        <input hidden type="text" name="wb_path" value="ooo">

        <!--  БЛОК который пропаадет после нажатия кнопки -->
        <div id="down_input" class="LockOff">
          <input required type="number" name="Zakaz1cNumber" value="">
          <input type="submit" value="СОБРАТЬ"  onclick="alerting()">
        </div>

        <!--  БЛОК который появляется после нажатия кнопки -->
        <div id="OnLock_textLockPane" class="LockOn">
             Обрабатываем запрос.........
        </div> 
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

}



/****************************************************************************************
 *  РИСУЕМ форму по выводу всех заказов
 ***************************************************************************************/
function print_wb_old_order_table($shop_name, $raw_arr_orders) {
  
        
    
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
          <td colspan ="3" ><b>НЕТ ПРОСРОЧЕННЫХ ЗАКАЗОВ</b></td>
        </tbody>
        </table>
      </div>
      
    HTML;
    }
    
    }