<?php
$offset = "../";
require_once $offset."connect_db.php";
require_once $offset."pdo_functions/pdo_functions.php";
require_once $offset."mp_functions/yandex_api_functions.php";
require_once $offset."mp_functions/yandex_functions.php";
require_once "functions/functions_yandex.php";

require_once "functions/functions.php";

//  $ya_token =  get_token_yam($pdo);
//  $campaignId =  get_id_company_yam($pdo);


    // Яндекс ООО склад FBS
    $ya_token =  $arr_tokens['ya_anmaks_fbs']['token'];
    $campaignId =  $arr_tokens['ya_anmaks_fbs']['id_market'];
    // print_r($campaignId);

// die();
    if (isset($_GET['select_date'])) {
        $need_date_temp = $_GET['select_date'];
        $need_date = date('d-m-Y' , strtotime($need_date_temp)); 
    
    }else {
        // $date_query_ozon =''; 
        $need_date_temp = date('Y-m-d'); 
        $need_date = date('d-m-Y' , strtotime($need_date_temp)); 
    }
    
    // echo $need_date."<br>"; 
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
 
    $return_arrays = make_array_sell_items ($arr_all_new_orders , $need_date);


isset($return_arrays['arr_mass_orders'])?$arr_mass_orders = $return_arrays['arr_mass_orders']:$x=0;
isset($return_arrays['arr_mass_one_date_orders'])?$arr_mass_one_date_orders = $return_arrays['arr_mass_one_date_orders']:$x=0;
isset($return_arrays['arr_all_items'])?$arr_all_items = $return_arrays['arr_all_items']:$x=0;

    
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
    <a href="razbiavaem_po_gruzomestam.php?select_date=$need_date_temp">разбить товары по грузоместам</a>
    
    HTML;  
}

} else {
    echo "Нет данных для вывода";
}

