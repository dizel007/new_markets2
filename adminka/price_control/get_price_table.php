<?php
$offset ="../../";
require_once $offset."connect_db.php";
require_once $offset."pdo_functions/pdo_functions.php";
require_once $offset."mp_functions/wb_api_functions.php";
require_once $offset."mp_functions/wb_functions.php";
require_once "functions.php";

$wb_anmaks = 'wb_anmaks';
$wb_ip = 'wb_ip_zel';
/**НАСТРОЙКИ МАГАЗИНЫ ****************************************** */

$token_wb = $token_wb_ip;
$wb_shop = $wb_ip;

// Доставем информацию по складам ****** АКТИВНЫМ СКЛАДАМ ******
$sklads = select_info_about_sklads($pdo); // ОБщая Информация по складам
// Названия магазинов
$wb_catalog = get_wb_prices($pdo, $token_wb, $wb_shop);
// echo "<pre>";
// print_r($wb_catalog);

foreach ($wb_catalog as &$item) {
	$gtemp = select_last_data_from_db($pdo, $item['sku'], $wb_shop);

	if (isset($gtemp[0])) {
// если нашли массив в таблице то добавляем данные в каталог
		$item['price_old'] = $gtemp[0]['price_old'];
		$item['dis_price_old'] = $gtemp[0]['dis_price_old'];
		$item['discount_old'] = $gtemp[0]['discount_old'];
		$item['date_old'] = $gtemp[0]['date_old'];
	} else { // 
// если НЕ нашли данных , то добавляем первые значения
		$data_for_input['main_article'] = $item['main_article'];
		$data_for_input['sku'] = $item['sku'];
		$data_for_input['price_old'] = $item['price_now'];
		$data_for_input['dis_price_old'] = $item['dis_price_now'];
		$data_for_input['discount_old'] = $item['discount_now'];
		$data_for_input['date_old'] = date('Y-m-d');
		$data_for_input['price_now'] = $item['price_now'];
		$data_for_input['dis_price_now'] = $item['dis_price_now'];
		$data_for_input['discount_now'] = $item['discount_now'];
		$data_for_input['date_now'] = date('Y-m-d');;
		insert_data_in_prices_tabla_db($pdo, $wb_shop, $data_for_input);
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


// print_r($wb_catalog[0]);

print_table_with_prices($wb_catalog, $token_wb, $wb_shop);

die('END SCRIPT');
/************************************************************************************************
 * *************** Получаем каталог их БД и берем цены с сайта ВБ *******************************
 ************************************************************************************************/
function get_wb_prices($pdo, $token_wb, $shop_name)
{
	// Получаем из БД каталог ВБ (для )
	$wb_catalog = get_catalog_tovarov_v_mp($shop_name, $pdo, 'active');

	// Достаем с ВБ фактические цены  
	$link_wb = "https://discounts-prices-api.wildberries.ru/api/v2/list/goods/filter?limit=100";
	$res = light_query_without_data($token_wb, $link_wb);
	// Цепляем эти цены к нашему каталогу
	foreach ($res['data']['listGoods'] as $item) {
		foreach ($wb_catalog as &$our_item) {

			if ($our_item['sku'] == $item['nmID']) {
				$our_item['price_now'] = $item['sizes'][0]['price'];
				$our_item['dis_price_now'] = $item['sizes'][0]['discountedPrice'];
				$our_item['discount_now'] = $item['discount'];
				$our_item['date_now'] = date('Y-m-d H:i:s');
			}
		}
	}
	// Достаем последние цены с нашей базы


	return $wb_catalog;
}

/************************************************************************************************
 ******  Вставляем новую строку в БД ************************************************
 ************************************************************************************************/

function insert_data_in_prices_tabla_db($pdo, $shop_name, $data_for_input)
{

	$article = $data_for_input['main_article'];
	$sku = $data_for_input['sku'];
	$price_old = $data_for_input['price_old'];
	$dis_price_old = $data_for_input['dis_price_old'];
	$discount_old = $data_for_input['discount_old'];
	$date_old = $data_for_input['date_old'];
	$price_now = $data_for_input['price_now'];
	$dis_price_now = $data_for_input['dis_price_now'];
	$discount_now = $data_for_input['discount_now'];
	$date_now = $data_for_input['date_now'];
	$date_stamp = date('Y-m-d H:i:s');


	$stmt  = $pdo->prepare("INSERT INTO `mp_prices` (shop_name, sku, article, price_old, dis_price_old, discount_old, date_old, 
												price_now, dis_price_now, discount_now, date_now, date_stamp)
                                        VALUES (:shop_name, :sku, :article, :price_old, :dis_price_old, :discount_old, :date_old, 
												:price_now, :dis_price_now, :discount_now, :date_now, :date_stamp)");

	$stmt->bindParam(':shop_name', $shop_name);
	$stmt->bindParam(':sku', $sku);
	$stmt->bindParam(':article', $article);
	$stmt->bindParam(':price_old', $price_old);
	$stmt->bindParam(':dis_price_old', $dis_price_old);
	$stmt->bindParam(':discount_old', $discount_old);
	$stmt->bindParam(':date_old', $date_old);
	$stmt->bindParam(':price_now', $price_now);
	$stmt->bindParam(':dis_price_now', $dis_price_now);
	$stmt->bindParam(':discount_now', $discount_now);
	$stmt->bindParam(':date_now', $date_now);
	$stmt->bindParam(':date_stamp', $date_stamp);


	if (!$stmt->execute()) {
		print_r($stmt->ErrorInfo());
		die("<br>Померли на вводе нового пользователя");
	}
}


/************************************************************************************************
 ******  Вычитываем из БД самую свежую цену ************************************************
 ************************************************************************************************/
function select_last_data_from_db($pdo, $sku, $shop_name)
{
	$stmt = $pdo->prepare("SELECT * FROM `mp_prices` WHERE `sku` = '$sku' AND `shop_name` = '$shop_name' ORDER BY `date_stamp` DESC LIMIT 1");
	$stmt->execute([]);
	$tovar_table_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $tovar_table_data;
}

// /************************************************************************************************
//  ******  Обновляем цену и скидку на товар на сайте ВБ и в БД ************************************************
//  ************************************************************************************************/
// function update_prices_and_discount_inWB_and_inDB_work($token_wb)
// {

	
// 	$arr_data['sku'] = (int)$_GET['sku'];
// 	$arr_data['price_now'] = (int)$_GET['price_now'];
// 	$arr_data['discount_now'] = (int)$_GET['discount_now'];

// $data= array("data"=> array(array(
// 	"nmID" => $arr_data['sku'],
// 	"price"=> $arr_data['price_now'],
// 	"discount"=> $arr_data['discount_now']
// ))

// );

// $link_wb = 'https://discounts-prices-api.wildberries.ru/api/v2/upload/task';
// $res = light_query_with_data($token_wb, $link_wb, $data);


// print_r($res);
// }