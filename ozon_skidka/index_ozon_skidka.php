<?php
require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";



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

$arr_zapros_skidki = post_with_data_ozon($token, $client_id, $send_data, $ozon_dop_url );



echo "<br> Количество Заявок для согласования :". count($arr_zapros_skidki['result'])."<br>";
///////////////////////////////////////////////////////////////////////
$procent_skidki = 4;
///////////////////////////////////////////////////////////////////////

foreach ($arr_zapros_skidki['result'] as $zapros_skidki) {

    $arr_data['id'] = $zapros_skidki['id'];
    $temp_price = $zapros_skidki['base_price'];
  
    $arr_data['price'] = round($temp_price - $temp_price*$procent_skidki/100,0);

    $arr_data['min_count'] = $zapros_skidki['requested_quantity_min'];
    $arr_data['max_count'] = $zapros_skidki['requested_quantity_max'];
 

    soglasovanie_zaiavki_na_skidku($token, $client_id, $arr_data);

usleep(100000);
}


die();

function soglasovanie_zaiavki_na_skidku($token_ozon, $client_id_ozon, $arr_data) {
$send_data =  array (
    "tasks" => array (array(
        "id" => $arr_data['id'],
        "approved_price" => $arr_data['price'],
        "seller_comment" =>  "OK",
        "approved_quantity_min" => $arr_data['min_count'],
        "approved_quantity_max" => $arr_data['max_count']
    ))
);


$send_data = json_encode($send_data);
$ozon_dop_url = 'v1/actions/discounts-task/approve';
$res = post_with_data_ozon($token_ozon, $client_id_ozon, $send_data, $ozon_dop_url );
echo "<br> ********** ".$arr_data['id'] ."*****************************************************";
print_r($res);

}

