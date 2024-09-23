<?php
$offset ="../../";
require_once $offset."connect_db.php";
require_once $offset."pdo_functions/pdo_functions.php";
require_once $offset."mp_functions/ozon_api_functions.php";
require_once $offset."mp_functions/ozon_functions.php";
require_once "functions.php";


/**НАСТРОЙКИ МАГАЗИНЫ ****************************************** */

$ozon_shop = $_POST['ozon_shop'];
unset($_POST['ozon_shop']);

if ($ozon_shop == 'ozon_anmaks') {
  // ОЗОН АНМКАС
  $client_id = $arr_tokens['ozon_anmaks']['id_market'];
  $token_ozon = $arr_tokens['ozon_anmaks']['token'];
} elseif ($ozon_shop == 'ozon_ip_zel') {
  // озон ИП зел
  $client_id = $arr_tokens['ozon_ip_zel']['id_market'];
  $token_ozon = $arr_tokens['ozon_ip_zel']['token'];
} else {
	echo "Не нашли маркет" ;
	die();
}


// Проверка что наш запрашиваемый массив (незер не нужна)
if (isset($_POST['type_question'])) {
	if ($_POST['type_question'] == "discount_update") {
        unset($_POST['type_question']);
// собираем массив из ПОСТ массива
foreach ($_POST as $key=>$value) {
    // echo "$key"."<br>";
    $cifra_key = preg_replace("/[^,.0-9]/", '', $key);
    // $key = str_replace('_','',$key); // убираем черточки
    $arr_post[$cifra_key][preg_replace('/[0-9]+/', '', $key)] =  $value;
    
}




// фомриуем массив для обновления данных (только то что нужно обновить)
foreach ($arr_post as $item_post) {
   if (isset($item_post['need_update']))
    $arr_post_new[$item_post['sku']]=  $item_post;
  
}


if (!isset($arr_post_new)) {
	die('НЕТ ДАННЫХ ДЛЯ ОБНОВЛЕНИЯ');
}

}
}

// echo "<pre>";
// print_r($arr_post_new);

foreach ($arr_post_new as $item_need_price_update) {
	$array_for_update['price_seller_na_mp_ozon'] =  $item_need_price_update['price_seller_na_mp_ozon'];
	$array_for_update['product_id'] =  $item_need_price_update['product_id'];

	update_prices_on_ozon($token_ozon, $client_id, $array_for_update);
}



sleep(2);
header('Location: get_price_table_ozon.php?wb_shop='.$ozon_shop, true, 301);
exit();



function update_prices_on_ozon($token, $client_id, $array_for_update) {
$new_price = $array_for_update['price_seller_na_mp_ozon'];
$product_id = $array_for_update['product_id'];

$send_data = '
	{
	"prices": [
	{
	"auto_action_enabled": "UNKNOWN",
	"currency_code": "RUB",
	"min_price": "",
	"offer_id": "",
	"old_price": "",
	"price": "'.$new_price.'",
	"price_strategy_enabled": "UNKNOWN",
	"product_id": '.$product_id.'
	}
	]
	}
}';

$ozon_dop_url = "v1/product/import/prices";
$res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url );

sleep(1);
print_r($res);


}