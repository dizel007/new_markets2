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
		$item['price_old'] = $gtemp[0]['price_old'];
		$item['dis_price_old'] = $gtemp[0]['dis_price_old'];
		$item['discount_old'] = $gtemp[0]['discount_old'];
		$item['date_old'] = $gtemp[0]['date_old'];
	} 
}


// echo "<pre>";
// print_r($wb_catalog);

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
// Чтобы обновить данные на сайте ВБ, нужно чтобы либо цена либо скидка отличались
// Формируем массив для обновления с учетом отличий
// print_r($wb_catalog);
foreach ($wb_catalog as $wb_item) {
foreach ($arr_post_new as $update_item){

	if ($update_item['sku'] == $wb_item['sku']) {
// echo $wb_item['sku']."<br>";
		if(($update_item['pricenow'] != $wb_item['price_old']) || ($update_item['discountnow'] != $wb_item['discount_old'])) {
			$arr_for_update[] =$update_item; 
		}
	}

}
}
}
}


echo "<pre>";
print_r($arr_for_update);
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
		"price"=> (int)$item['pricenow'],
		"discount"=> (int)$item['discountnow']
	))
);

$link_wb = 'https://discounts-prices-api.wildberries.ru/api/v2/upload/task';
$res = light_query_with_data($token_wb, $link_wb, $data);
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