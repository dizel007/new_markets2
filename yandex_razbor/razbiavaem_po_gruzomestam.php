<?php 
require_once ("../connect_db.php"); // подключение к БД
require_once "../pdo_functions/pdo_functions.php";


require_once '../libs/PDFMerger/PDFMerger.php';


// require_once "token/ya_tok.php";
// require_once "token/ya_const.php";
require_once "functions/functions_yandex.php";
require_once "functions/functions.php";


// Получаем токены ЯМ
$ya_token =  get_token_yam($pdo);
$campaignId =  get_id_company_yam($pdo);

// echo "<pre>";

// получаем даты на которую нужно разобрать заказы по грузоместам
if  (isset($_GET['select_date'])) {
    $need_date_temp = $_GET['select_date'];
    $need_date = date('d-m-Y' , strtotime($need_date_temp)); 
    // echo $need_date."<br>"; 
} else {
    echo "<br>NET DATE DIE<br>";
    die('die without date');
}
 
 $arr_all_new_orders = get_new_orders($ya_token, $campaignId);

   
// выбираем из массива только заказы на выбранную дату
foreach ($arr_all_new_orders['orders'] as $order) { // перебираем все новые заказы
       $need_ship_date = $order['delivery']['shipments'][0]['shipmentDate'];
             if ($need_date == $need_ship_date)  {    /// выбор даты дня отгрузки
              $new_arr_select_date[] = $order;
            }
    
    }

echo "* ЗАКАЗЫ ДЛЯ РАЗБИВКИ ПО ГРУЗОМЕСТАМ *";


foreach ($new_arr_select_date as $order) { // перебираем все новые заказы

    $orderId = $order['id']; // ID  выбранного заказа

    echo "<br>* НОМЕР ЗАКАЗА = <b>$orderId </b> *";
        // print_r($items);
        $res[] = razbivaev_zakazi_po_gruzomestam ($ya_token, $campaignId, $orderId, $order['items']);

}
echo "<h3> Количество заказов в поставке =  ".count($new_arr_select_date)."</h3>";

////////////////////////////////////////////////////////////////////////////////////////////
foreach ($arr_all_new_orders['orders'] as $order) { // перебираем все новые заказы
    
    $orderId = $order['id']; // ID  выбранного заказа
    $item_number = 0; // порядквый номер товаров, если их несколько
    $need_ship_date = $order['delivery']['shipments'][$item_number]['shipmentDate'];
    $id_shipment = $order['delivery']['shipments'][$item_number]['id'];
  
        if ($need_date == $need_ship_date)  {    /// выбор даты дня отгрузки
          
            foreach ($order['items'] as $items) { // перебираем все товары из выбранного заказа
               unset ($items['subsidies']);
                $arr_all_items[] = $items;
            }
        }

}

// Выаодим Тбалицу
if (isset($arr_all_items)) {
    print_table_with_orders ($arr_all_items, $need_date_temp);
    /// переход на разбивку заказа
echo <<<HTML

<form action="poluchenie_yarlikov.php" method= "get">
    <label>Дата сбора заказа</label>
    <input required type="date" name="select_date" value=$need_date_temp readonly>
    <label>Введите номер заказа</label>
    <input required type="text" name="order_number" value="">
    <br><br>
    <input type="submit" value="Получить Ярлыки">
</form>

    
HTML;  
}

// print_r($res);
