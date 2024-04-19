<?php

require_once "pdo_functions/pdo_functions.php";

require_once "mp_functions/yandex_api_functions.php";
require_once "mp_functions/yandex_functions.php";




require_once "functions/functions_yandex.php";
require_once "functions/functions.php";

 $ya_token =  get_token_yam($pdo);
 $campaignId =  get_id_company_yam($pdo);

    // print_r($ya_token_info);


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
echo "<pre>";
// print_r($arr_all_new_orders);


if  (isset($arr_all_new_orders)) {




 
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

