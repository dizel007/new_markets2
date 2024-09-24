<?php
$offset ="../../";
require_once $offset."connect_db.php";
require_once $offset."pdo_functions/pdo_functions.php";
require_once $offset."mp_functions/wb_api_functions.php";
require_once $offset."mp_functions/wb_functions.php";
require_once "functions.php";


/**НАСТРОЙКИ МАГАЗИНЫ ****************************************** */
$token_wb = $_POST['token_wb'];
unset($_POST['token_wb']);
$wb_shop = $_POST['wb_shop'];
unset($_POST['wb_shop']);

$wb_catalog = get_wb_prices($pdo, $token_wb, $wb_shop);
// Подтягиваем значения из БД СКЬЮЭЛ
foreach ($wb_catalog as &$item) {
	// echo "<br>".$item['sku']."<br>";
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
foreach ($arr_post as $item_post) {
   if (isset($item_post['needupdate']))
    $arr_post_new[$item_post['sku']]=  $item_post;
  
}


if (!isset($arr_post_new)) {
	die('НЕТ ДАННЫХ ДЛЯ ОБНОВЛЕНИЯ');
}


// echo"<pre>";
// print_r($arr_post_new);
// print_r($wb_catalog);


// Чтобы обновить данные на сайте ВБ, нужно чтобы либо цена либо скидка отличались
// Формируем массив для обновления с учетом отличий
foreach ($wb_catalog as $wb_item) {
foreach ($arr_post_new as $update_item){
	if ($update_item['sku'] == $wb_item['sku']) {
		if(($update_item['pricenowWB'] != $wb_item['price_now_WB']) || ($update_item['discountnowWB'] != $wb_item['discount_now_WB'])) {
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


// print_r($arr_for_db);
// Вставляем новую строку в БД с обновленными ценами
foreach ($arr_for_db as $data_for_input) {
	insert_data_in_prices_table_db_wb($pdo, $wb_shop, $data_for_input);
	}

// обновляем данные на ВБ 
update_prices_and_discount_inWB_and_inDB($token_wb, $arr_for_update);
// print_r($arr_post_new);
sleep(3);
header('Location: get_price_table_wb.php?shop_name='.$wb_shop, true, 301);
exit();

