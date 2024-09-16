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

echo "<pre>";
print_r($arr_post_new);


update_prices_on_ozon($token_ozon, $client_id);
die();



// Формируем массив в БД 
foreach ($wb_catalog as $wb_data) {
	foreach ($arr_for_update as $update_date_z) {
		if ($update_date_z['sku'] == $wb_data['sku']) {

			$wb_data['pricenowWB']=$update_date_z['pricenowWB'];
			$wb_data['discountnowWB']=$update_date_z['discountnowWB'];
			$wb_data['dispricenowWB']=$update_date_z['dispricenowWB'];
			
			$arr_for_db[]=$wb_data;

		}
	}


}


// print_r($arr_for_db);
// Вставляем новую строку в БД с обновленными ценами
foreach ($arr_for_db as $data_for_input) {
	insert_data_in_prices_table_db_wb($pdo, $wb_shop, $data_for_input);
	}

// обновляем данные на ВБ 
update_prices_and_discount_inWB_and_inDB($token_wb, $arr_for_update);
// print_r($arr_post_new);
sleep(3);
header('Location: get_price_table.php?wb_shop='.$wb_shop, true, 301);
exit();



function update_prices_on_ozon($token, $client_id) {

$send_data = '
	{
	"prices": [
	{
	"auto_action_enabled": "UNKNOWN",
	"currency_code": "RUB",
	"min_price": "",
	"offer_id": "",
	"old_price": "",
	"price": "842",
	"price_strategy_enabled": "UNKNOWN",
	"product_id": 56476066
	}
	]
	}
}';

$ozon_dop_url = "v1/product/import/prices";
$res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url );

print_r($res);


}