<?php
$offset ="../../";
require_once $offset."connect_db.php";
require_once $offset."pdo_functions/pdo_functions.php";
require_once $offset."mp_functions/wb_api_functions.php";
require_once $offset."mp_functions/wb_functions.php";


// $wb_anmaks = 'wb_anmaks';
// $wb_ip = 'wb_ip_zel';
/**НАСТРОЙКИ МАГАЗИНЫ ****************************************** */
$token_wb = $_POST['token_wb'];
unset($_POST['token_wb']);
$wb_shop = $_POST['wb_shop'];
unset($_POST['wb_shop']);

$wb_catalog = get_wb_prices($pdo, $token_wb, $wb_shop);
// Подтягиваем значения из БД СКЬЮЭЛ
foreach ($wb_catalog as &$item) {
	$gtemp = select_last_data_from_db($pdo, $item['sku'], $wb_shop);
	if (isset($gtemp[0])) {
// если нашли массив в таблице то добавляем данные в каталог
		$item['price_now_DB'] = $gtemp[0]['price_now'];
		$item['dis_price_now_DB'] = $gtemp[0]['dis_price_now'];
		$item['discount_now_DB'] = $gtemp[0]['discount_now'];
		$item['date_now_DB'] = $gtemp[0]['date_now'];
	} 
}


if (isset($_POST['type_question'])) {
	if ($_POST['type_question'] == "discount_update") {
        unset($_POST['type_question']);
// собираем массив из ПОСТ массива
foreach ($_POST as $key=>$value) {
    // echo "$key"."<br>";
    $cifra_key = preg_replace("/[^,.0-9]/", '', $key);
    $key = str_replace('_','',$key); // убираем черточки
    $arr_post[$cifra_key][preg_replace('/[0-9]+/', '', $key)] =  $value;
    
}


// фомриуем массив для обновления данных (только то что нужно обновить)
foreach ($arr_post as $item) {
   if (isset($item['needupdate']))
    $arr_post_new[$item['sku']]=  $item;
  
}


if (!isset($arr_post_new)) {
	die('НЕТ ДАННЫХ ДЛЯ ОБНОВЛЕНИЯ');
}



// Чтобы обновить данные на сайте ВБ, нужно чтобы либо цена либо скидка отличались
// Формируем массив для обновления с учетом отличий
foreach ($wb_catalog as $wb_item) {
foreach ($arr_post_new as $update_item){
	if ($update_item['sku'] == $wb_item['sku']) {
		if(($update_item['pricenowWB'] != $wb_item['price_now_DB']) || ($update_item['discountnowWB'] != $wb_item['discount_now_DB'])) {
			$arr_for_update[] =$update_item; 
		}
	}
}
}
}
}

if (!isset($arr_for_update)) {
	die('ДАННЫЕ ДЛЯ ОБНОВЛЕНИЯ СОВПАДАЮТ С ДАННЫМИ НА САЙТЕ ВБ');
}


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

// die();
// print_r($wb_catalog[0]);
echo "<br>**************************";

// print_r($arr_for_db);
// Вставляем новую строку в БД с обновленными ценами
foreach ($arr_for_db as $data_for_input) {
	insert_data_in_prices_table_db($pdo, $wb_shop, $data_for_input);
	}
die('kkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkk');
// обновляем данные на ВБ 
update_prices_and_discount_inWB_and_inDB($token_wb, $arr_for_update);
// print_r($arr_post_new);



/************************************************************************************************
 ******  Обновляем цену и скидку на товар на сайте ВБ и в БД ************************************************
 ************************************************************************************************/
function update_prices_and_discount_inWB_and_inDB($token_wb, $arr_for_update)
{
foreach ($arr_for_update as $item) {
	$data = array("data"=> array(array(
		"nmID" => (int)$item['sku'],
		"price"=> (int)$item['pricenowWB'],
		"discount"=> (int)$item['discountnowWB']
	))
);

$link_wb = 'https://discounts-prices-api.wildberries.ru/api/v2/upload/task';
$res = light_query_with_data($token_wb, $link_wb, $data);
// print_r($res);
usleep(200);
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

 function insert_data_in_prices_table_db($pdo, $shop_name, $data_for_input)
 {

	 $article = $data_for_input['main_article'];
	 $sku = $data_for_input['sku'];
	 $price_old = $data_for_input['price_now_DB'];
	 $dis_price_old = $data_for_input['dis_price_now_DB'];
	 $discount_old = $data_for_input['discount_now_DB'];
	 $date_old = $data_for_input['date_now_DB'];
	 $price_now = $data_for_input['pricenowWB'];
	 $dis_price_now = $data_for_input['dispricenowWB'];
	 $discount_now = $data_for_input['discountnowWB'];
	 $date_now =  date('Y-m-d');
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
	 return $stmt;
 }
 