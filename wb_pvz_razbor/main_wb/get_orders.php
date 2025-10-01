<?php
// require_once "..\get_zakaz_by_check_date.php";
require_once "print_wb_table.php";

// дата на которую нуэно собрать заказы (ПОКА ВРУЧНУЮ ИЗМЕНЯЕТСЯ В ФУНКЦИИ)
 if (isset($_GET['date_sbora_zakaza'])) {
  $date_orders_select = $_GET['date_sbora_zakaza'];
  
} else {
  $date_orders_select = '';
 
}

if ($date_orders_select <> '') {
  $text_about_date = "Заказы созданные : $date_orders_select";
} else {
  $text_about_date = "Заказы созданные ЗА ВСЕ ВРЕМЯ";
}
/********************************************************************************************************
 * ******************** Вычитываем и выводи заказы для ВБ
 ********************************************************************************************************/
$raw_arr_orders = select_order_by_check_date($token_wb_orders, $date_orders_select) ;

// echo "<pre>";
// print_r($raw_arr_orders);
// die(); 

print_wb_order_table($shop_name, $date_orders_select , $raw_arr_orders, $text_about_date , $token_wb_orders) ;

// if ($find_old_orders) {
//   echo "<h2>Есть просроченные заказы</h2>";
// }
