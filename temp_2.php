<?php

require_once "connect_db.php";
require_once "pdo_functions/pdo_functions.php";
require_once "mp_functions/wb_api_functions.php";
require_once "mp_functions/wb_functions.php";


// Доставем информацию по складам ****** АКТИВНЫМ СКЛАДАМ ******
$sklads = select_info_about_sklads($pdo); // ОБщая Информация по складам
// Названия магазинов
$wb_anmaks = 'wb_anmaks';
$wb_ip = 'wb_ip_zel';

$wb_catalog = get_wb_prices($pdo, $token_wb, $wb_anmaks);

echo "<pre>";
// print_r($wb_catalog);




foreach ($wb_catalog as &$item) {
	$gtemp = select_last_data_from_db($pdo, $item['sku'], $wb_anmaks);

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
		insert_data_in_prices_tabla_db($pdo, $wb_anmaks, $data_for_input);
	// вычитываем добавленные данные с БД
		$gtemp = select_last_data_from_db($pdo, $item['sku'], $wb_anmaks);
	// если нашли массив в таблице то добавляем данные в каталог
		if (isset($gtemp[0])) {
			$item['price_old'] = $gtemp[0]['price_old'];
			$item['dis_price_old'] = $gtemp[0]['dis_price_old'];
			$item['discount_old'] = $gtemp[0]['discount_old'];
			$item['date_old'] = $gtemp[0]['date_old'];
		}
	}
}


print_r($wb_catalog[0]);

print_table_with_prices($wb_catalog);


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

/************************************************************************************************
 ******  Вычитываем из БД самую свежую цену ************************************************
 ************************************************************************************************/
function print_table_with_prices($wb_catalog){


 echo "<link rel=\"stylesheet\" href=\"adminka/css/main_table.css\">";
 echo "<form action=\"\" method=\"post\">";
   
 echo "<table class=\"prods_table\">";


 echo "<tr  class=\"rovnay_table_shapka\">";
	 echo "<td>Артикул МП</td>";
	 echo "<td>Цена с БД<br></td>";
	 echo "<td>Скидка<br> Цена <br>с БД</td>";
	 echo "<td>Скидка<br></td>";
	 echo "<td>Дата в БД<br></td>";
	 echo "<td>Цена с <br>сайта<br>Upd</td>";
	 echo "<td>СкидЦена с ВБ<br>Upd</td>";
	 echo "<td>Скидка<br> с ВБ<br>Upd</td>";
	 echo "<td>Сумма %</td>";
 echo "</tr>";

foreach ($wb_catalog as $item) {


// Проверяем одинаковые ли цена на сайт и в БД
$delta_prices = $item['dis_price_old'] -  $item['dis_price_now'];

	($item['dis_price_old'] <> $item['dis_price_now'])? $bolshe100 = 'bolshe100': $bolshe100 = '' ;

	 
	 echo "<tr class=\"rovnay_table  $bolshe100 \">";

	 echo "<td>".$item['main_article']."</td>";

	 $name_for_update = $item['main_article'];
// данные из БД
echo  "<td>".$item['price_old']."</td>";
echo  "<td>".$item['dis_price_old']."</td>";
echo  "<td>".$item['discount_old']."</td>";
echo  "<td>".$item['date_old']."</td>";

/// данные с сайта ВБ
echo  "<td><input class=\"text-field__input future_ostatok\" type=\"number\" name=\"_mp_ozon_ip_zel_$name_for_update\" value=".$item['price_now']."></td>";
echo  "<td>".$item['dis_price_now']."</td>";
// заблокированный товар иили нет
echo  "<td><input class=\"text-field__input future_ostatok\" type=\"number\" name=\"_mp_ya_anmaks_fbs_$name_for_update\" value=".$item['discount_now']."></td>";


/// СУмма распределния товаров во всех МП
echo  "<td>".$delta_prices."</td>";

echo "</tr>";
	 
 }
 echo "</table>";
 echo "<input class=\"btn\" type=\"submit\" value=\"ОБНОВИТЬ ДАННЫЕ\">";

}