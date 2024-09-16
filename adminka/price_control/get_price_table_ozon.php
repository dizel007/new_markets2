<?php
$offset = "../../";
require_once $offset . "connect_db.php";
require_once $offset . "pdo_functions/pdo_functions.php";
require_once $offset . "mp_functions/ozon_api_functions.php";
require_once $offset . "mp_functions/ozon_functions.php";
require_once "functions.php";


// /**НАСТРОЙКИ МАГАЗИНЫ ****************************************** */
// озон ИП зел
$ozon_shop = 'ozon_anmaks';
// $ozon_shop = 'ozon_ip_zel';

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

  echo "Не $ozon_shop маркет" ;

echo "<pre>";

$ozon_catalog    = get_catalog_tovarov_v_mp($ozon_shop , $pdo, 'active'); // получаем озон каталог




// наxодим ID товаров озона 

// $ozon_dop_url = "v2/product/info";
// foreach ($ozon_catalog as &$items) {
// 	$send_data = array(
// "offer_id" => "",
// "product_id" => 0,
// "sku" => $items['sku']);
// $send_data = json_encode($send_data);
// $ozcatalog = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $send_data, $ozon_dop_url);
// $items['id_ozon'] = $ozcatalog['result']['id'];
// }

// file_put_contents('1.json', json_encode($ozon_catalog, JSON_UNESCAPED_UNICODE));
// echo "<pre>";
// print_r($ozon_catalog);

// $ozon_catalog = json_decode(file_get_contents('1.json'), true);


// формируем массиd для запроса цены ********************
foreach ($ozon_catalog as $tovars) {
$arr_article[] = $tovars['mp_article'];
$arr_id_ozon[] = $tovars['product_id'];
}

$ozon_dop_url = "v4/product/info/prices";
$send_data = array(
	"filter" => array(
		"offer_id" => $arr_article,
		"product_id" => $arr_id_ozon,
		"visibility" =>  "ALL"
	),
	"last_id" => "",
	"limit" => 100
);
$send_data = json_encode($send_data);


// непосредственный запрос цен
$ozcatalog = post_with_data_ozon($token_ozon, $client_id, $send_data, $ozon_dop_url);

// print_r($ozcatalog );
// print_r($arr_article);


unset($items);
foreach ($ozcatalog['result']['items'] as $items) {
	$new_ozon_catalog_from_site[$items['offer_id']]['product_id'] =  $items['product_id'];
	$new_ozon_catalog_from_site[$items['offer_id']]['offer_id'] =  $items['offer_id'];
	$new_ozon_catalog_from_site[$items['offer_id']]['marketing_seller_price'] =  $items['price']['marketing_seller_price'];
	$new_ozon_catalog_from_site[$items['offer_id']]['marketing_price'] =  $items['price']['marketing_price'];
	$new_ozon_catalog_from_site[$items['offer_id']]['price'] =  $items['price']['price'];
	// $new_ozon_catalog_from_site[$items['offer_id']]['min_price'] =  $items['price']['min_price'];

}

///////  ***************   Добавляем цены с наш каталог озон*********************************
//********************************************************************************************
foreach ($ozon_catalog as &$ozon_item) {
	foreach ($new_ozon_catalog_from_site as $item_query) {
	 if ($ozon_item['product_id'] == $item_query['product_id']) {
		 $ozon_item['price_now_ozon'] =  round($item_query['price'],0);
		 $ozon_item['price_na_mp_ozon'] =  round($item_query['marketing_price'],0);
		 $ozon_item['price_seller_na_mp_ozon'] =  round($item_query['marketing_seller_price'],0);
	 }

	}
}


//***************************************************************************************************** 
//**********  Вычитываем данные с БД если есть, если нет , то добавляем в БД данные ******************* 
//***************************************************************************************************** 
unset($item);
foreach ($ozon_catalog as &$item) {
	$gtemp = select_last_data_from_db($pdo, $item['sku'], $ozon_shop);
	
	if (isset($gtemp[0])) {
// если нашли массив в таблице то добавляем данные в каталог
		$item['price_old_DB'] = $gtemp[0]['price_old'];
		$item['dis_price_old_DB'] = $gtemp[0]['dis_price_old'];
		$item['price_na_mp_old_DB'] = $gtemp[0]['price_na_mp_old'];
		$item['date_old_DB'] = $gtemp[0]['date_old'];
		$item['price_seller_na_mp_old_DB'] = $gtemp[0]['price_seller_na_mp_old'];
		

		$item['price_now_DB'] = $gtemp[0]['price_now'];
		$item['dis_price_now_DB'] = $gtemp[0]['dis_price_now'];
		$item['price_na_mp_ozon_DB'] = $gtemp[0]['price_na_mp_ozon'];
		$item['price_seller_na_mp_ozon_DB'] = $gtemp[0]['price_seller_na_mp_ozon'];
		
		$item['date_now_DB'] = $gtemp[0]['date_now'];
	} else { // 
// если НЕ нашли данных , то добавляем первые значения
		$data_for_input['main_article'] = $item['main_article'];
		$data_for_input['sku'] = $item['sku'];
		$data_for_input['product_id'] = $item['product_id'];


/// new data
		$data_for_input['price_now_DB']            = round($item['price_now_ozon'],0);
		$data_for_input['dis_price_now_DB']        = 0;
		$data_for_input['price_na_mp_old']         = round($item['price_na_mp_ozon'],0);
		$data_for_input['price_seller_na_mp_old']  = round($item['price_seller_na_mp_ozon'],0);
		$data_for_input['date_now_DB']             = date('Y-m-d');
		$data_for_input['pricenow_OZON']           = round($item['price_now_ozon'],0);
		$data_for_input['dispricenow_OZON']        = 0;
		$data_for_input['price_na_mp_ozon']        = round($item['price_na_mp_ozon'],0);
		$data_for_input['price_seller_na_mp_ozon'] = round($item['price_seller_na_mp_ozon'],0);
		$data_for_input['date_now'] = date('Y-m-d');;
		
		insert_data_in_prices_table_db_OZON($pdo, $ozon_shop, $data_for_input);
	// вычитываем добавленные данные с БД
		$gtemp = select_last_data_from_db($pdo, $item['sku'], $ozon_shop);
	// если нашли массив в таблице то добавляем данные в каталог
		if (isset($gtemp[0])) {
			$item['price_old'] = $gtemp[0]['price_old'];
			$item['price_na_mp_old'] = $gtemp[0]['price_na_mp_old'];
			$item['price_seller_na_mp_old'] = $gtemp[0]['price_seller_na_mp_old'];
			$item['date_old'] = $gtemp[0]['date_old'];
		}
	}
}



print_r($ozon_catalog[0]);
print_table_with_prices_OZON($ozon_catalog, $ozon_shop);
die();
