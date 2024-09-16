<?php
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";

require_once "functions_skidka.php";

$ozon_shop = $_GET['ozon_shop'];
if ($_GET['ozon_shop'] == 'ozon_anmaks') {
    $token =  $token_ozon;
    $client_id =  $client_id_ozon;
} elseif ($_GET['ozon_shop'] == 'ozon_ip_zel') {
    $token =  $token_ozon_ip;
    $client_id =  $client_id_ozon_ip;
} else {
    die('МАГАЗИН НЕ ВЫБРАН');
}






echo "<pre>";
// озон ИП зел
$client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
$token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];

$ozon_dop_url = 'v1/actions/discounts-task/list';







/// перебираем скидку ////////////////////////////////////////////////////////////////
for ($procent_skidki = 4; $procent_skidki <= 7; $procent_skidki++) {
    $page_number = 1;


    echo "<br> * ПРОЦЕНТ СКИДКИ = ".$procent_skidki ."*";
    // Формируем массив со всеми заявками на скидку
do {
    unset($arr_zapros_skidki); 
    $send_data_arr = array(
        "status" => "NEW",
        "page"   =>  $page_number,
        "limit"  => 50
    );
    $send_data = json_encode($send_data_arr);
    $arr_zapros_skidki = post_with_data_ozon($token, $client_id, $send_data, $ozon_dop_url);
    // перебираем массив с заявками на скидку и формируем новый общий массив со всеми стараницами запроса.
    foreach ($arr_zapros_skidki['result'] as $items) {
        $arr_all_skidki[] = $items; // фновый массив со всеми скидками
    }
    unset($items);
    $count_zapros_skidki = count($arr_zapros_skidki['result']);
    $page_number++; // увеличиваем номер старницы 
    
} while ($count_zapros_skidki <> 0);


echo "<br> Конец формирования массива. Запросов на скидку = ". count($arr_all_skidki) ."<br>";

    perebor_skidok($token, $client_id, $arr_all_skidki, $procent_skidki);
    unset($arr_all_skidki);  // Удаляем массив скидок и будем создавать новый
    echo "<br><br>";
    

}
die('<br>vvv');
/************************************
 *  КОНЕЦ СКРИПТА  ***
 ***************************/
