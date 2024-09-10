<?php
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";

require_once "functions_skidka.php";

$ozon_shop = $_GET['ozon_shop'];
if ($_GET['ozon_shop'] == 'ozon_anmaks') {
       $token =  $token_ozon;
       $client_id =  $client_id_ozon;
 
   }
       
elseif ($_GET['ozon_shop'] == 'ozon_ip_zel') {
       $token =  $token_ozon_ip;
       $client_id =  $client_id_ozon_ip;
 } else {
       die ('МАГАЗИН НЕ ВЫБРАН');
 }






echo "<pre>";
// озон ИП зел
$client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
$token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];

$ozon_dop_url = 'v1/actions/discounts-task/list';

$send_data = '{
    "status": "NEW",
    "page": 1,
    "limit": 50
    }';


///////////////////////////////////////////////////////////////////////
$procent_skidki = 4;
///////////////////////////////////////////////////////////////////////
for ($procent_skidki = 4; $procent_skidki <= 7; $procent_skidki++) {
    echo "<br> *********** СКИДКА  =  $procent_skidki ПРОЦЕНТОВ **************************** <br>";
    $arr_zapros_skidki = post_with_data_ozon($token, $client_id, $send_data, $ozon_dop_url );
    echo "<br> Количество Заявок для согласования :". count($arr_zapros_skidki['result'])."<br>";
    
    $success_result_discount = perebor_skidok($token, $client_id, $arr_zapros_skidki, $procent_skidki);
     
    if ($success_result_discount == 1) {
        $procent_skidki --;
    } else {
        break ;
    }
echo  "<br> ЕСТЬ ЛИ УДАЧНЫЕ СКИДКИ  : = ". $success_result_discount."<br>";
}
die();
/************************************
 *  КОНЕЦ СКРИПТА  ***
 ***************************/




