<?php

require_once "pdo_functions/pdo_functions.php";

require_once "mp_functions/yandex_api_functions.php";
require_once "mp_functions/yandex_functions.php";




require_once "functions/functions_yandex.php";
require_once "functions/functions.php";

 $ya_token =  get_token_yam($pdo);
 $campaignId =  get_id_company_yam($pdo);

    // print_r($campaignId);


    if (isset($_GET['select_date'])) {
        $need_date_temp = $_GET['select_date'];
        $need_date = date('d-m-Y' , strtotime($need_date_temp)); 
    
    }else {
        // $date_query_ozon =''; 
        $need_date_temp = date('Y-m-d'); 
        $need_date = date('d-m-Y' , strtotime($need_date_temp)); 
    }
    
    echo $need_date."<br>"; 
    echo <<<HTML
    <form action="#" method= "get">
        <input required type="date" name="select_date" value="$need_date_temp">
        <input hidden type="text" name="transition" value="31">
        <input type="submit" value="Показать заказы на эту дату">
    </form>
    
    
    HTML;




$arr_all_new_orders = get_new_orders($ya_token, $campaignId);
// echo "<pre>";
// print_r($arr_all_new_orders);

// die();
if  (isset($arr_all_new_orders)) {
 
foreach ($arr_all_new_orders['orders'] as $order) { // перебираем все новые заказы
    
// формируем массиов товаров по заказам 
$arr_mass_orders[$order['id']]['data'] = $order['items'];
$arr_mass_orders[$order['id']]['date_delivery'] = $order['delivery']['shipments'][0]['shipmentDate'];


// формируем массиов товаров общим переченем

    $orderId = $order['id']; // ID  выбранного заказа
    $item_number = 0; // порядквый номер товаров, если их несколько
    $need_ship_date = $order['delivery']['shipments'][$item_number]['shipmentDate'];
    $id_shipment = $order['delivery']['shipments'][$item_number]['id'];
  
        if ($need_date == $need_ship_date)  {    /// выбор даты дня отгрузки

// формируем массиов товаров по заказам 
$arr_mass_one_date_orders[$order['id']]['data'] = $order['items'];
$arr_mass_one_date_orders[$order['id']]['date_delivery'] = $order['delivery']['shipments'][0]['shipmentDate'];


          
            foreach ($order['items'] as $items) { // перебираем все товары из выбранного заказа
               unset ($items['subsidies']);
                $arr_all_items[] = $items;
            }
        }

}


/// Выводим все заказы на все даты 
print_table_with_ALL_orders ($arr_mass_orders, '');


/// Выводим все на выбранную дату
if (isset ($arr_mass_one_date_orders)) {
print_table_with_ALL_orders ($arr_mass_one_date_orders, 'для отгрузки на дату: '.$need_date);
} else {
 echo "<h2>Нет заказов на дату : $need_date</h2>";
}

/// Выводим все заказы на выбранные даты
if (isset($arr_all_items)) {
    print_table_with_orders ($arr_all_items, $need_date_temp);
    /// переход на разбивку заказа
    echo <<<HTML
    <a href="yandex_razbor/razbiavaem_po_gruzomestam.php?select_date=$need_date_temp">разбить товары по грузоместам</a>
    
    HTML;  
}

} else {
    echo "Нет данных для вывода";
}

