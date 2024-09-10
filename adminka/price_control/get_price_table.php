<?php
$offset ="../../";
require_once $offset."connect_db.php";
require_once $offset."pdo_functions/pdo_functions.php";
require_once $offset."mp_functions/wb_api_functions.php";
require_once $offset."mp_functions/wb_functions.php";
require_once "functions.php";


/**НАСТРОЙКИ МАГАЗИНЫ ****************************************** */
if ($_GET['wb_shop'] == 'wb_ip_zel') {
	$token_wb = $token_wb_ip;
	$wb_shop = 	'wb_ip_zel';
} elseif ($_GET['wb_shop'] == 'wb_anmaks') {
	$token_wb = $token_wb;
	$wb_shop = 	'wb_anmaks';
} else {
	die('Не нашли маркет');
}



// Доставем информацию по складам ****** АКТИВНЫМ СКЛАДАМ ******
$sklads = select_info_about_sklads($pdo); // ОБщая Информация по складам
// Названия магазинов
$wb_catalog = get_wb_prices($pdo, $token_wb, $wb_shop);
// echo "<pre>";
// print_r($wb_catalog[20]);


foreach ($wb_catalog as &$item) {
	$gtemp = select_last_data_from_db($pdo, $item['sku'], $wb_shop);
	
	if (isset($gtemp[0])) {
// если нашли массив в таблице то добавляем данные в каталог
		$item['price_old_DB'] = $gtemp[0]['price_old'];
		$item['dis_price_old_DB'] = $gtemp[0]['dis_price_old'];
		$item['discount_old_DB'] = $gtemp[0]['discount_old'];
		$item['date_old_DB'] = $gtemp[0]['date_old'];

		$item['price_now_DB'] = $gtemp[0]['price_now'];
		$item['dis_price_now_DB'] = $gtemp[0]['dis_price_now'];
		$item['discount_now_DB'] = $gtemp[0]['discount_now'];
		$item['date_now_DB'] = $gtemp[0]['date_now'];
	} else { // 
// если НЕ нашли данных , то добавляем первые значения
		$data_for_input['main_article'] = $item['main_article'];
		$data_for_input['sku'] = $item['sku'];
		$data_for_input['price_now_DB'] = $item['price_now_WB'];
		$data_for_input['dis_price_now_DB'] = $item['dis_price_now_WB'];
		$data_for_input['discount_now_DB'] = $item['discount_now_WB'];
		$data_for_input['date_now_DB'] = date('Y-m-d');
		$data_for_input['pricenowWB'] = $item['price_now_WB'];
		$data_for_input['dispricenowWB'] = $item['dis_price_now_WB'];
		$data_for_input['discountnowWB'] = $item['discount_now_WB'];
		$data_for_input['date_now'] = date('Y-m-d');;
		insert_data_in_prices_table_db_wb($pdo, $wb_shop, $data_for_input);
	// вычитываем добавленные данные с БД
		$gtemp = select_last_data_from_db($pdo, $item['sku'], $wb_shop);
	// если нашли массив в таблице то добавляем данные в каталог
		if (isset($gtemp[0])) {
			$item['price_old'] = $gtemp[0]['price_old'];
			$item['dis_price_old'] = $gtemp[0]['dis_price_old'];
			$item['discount_old'] = $gtemp[0]['discount_old'];
			$item['date_old'] = $gtemp[0]['date_old'];
		}
	}
}

// echo "<pre>";
// print_r($wb_catalog);



print_table_with_prices_WB($wb_catalog, $token_wb, $wb_shop);

die();
