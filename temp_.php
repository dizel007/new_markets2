<?php

require_once "connect_db.php";
require_once "pdo_functions/pdo_functions.php";
require_once "mp_functions/report_excel_file.php";
// require_once "mp_functions/yandex_api_functions.php";
// require_once "mp_functions/yandex_functions.php";




// require_once "functions/functions_yandex.php";
// require_once "functions/functions.php";

$ya_token =  get_token_yam($pdo);
$campaignId =  get_id_company_yam($pdo);
$yandex_anmaks_fbs = 'ya_anmaks_fbs';
$ya_fbs_catalog = get_catalog_tovarov_v_mp($yandex_anmaks_fbs, $pdo, 'active'); // получаем yandex каталог
$nomenclatura = select_active_nomenklaturu($pdo);


echo "<pre>";

// print_r($nomenclatura);



foreach ($nomenclatura as $nomen) {
	foreach ($ya_fbs_catalog as $ya_items) {
		if (mb_strtolower($nomen['main_article_1c']) == mb_strtolower($ya_items['main_article'])) {
			$arr_items_yandex[$nomen['main_article_1c']] = $nomen;
			$arr_items_yandex[$nomen['main_article_1c']]['sku'] = $ya_items['sku'];
		}
	}
}


// die();

$date_start = "2024-05-01";
$date_stop = "2024-05-31";


$ya_data = array(
	"dateFrom" => $date_start,
	"dateTo" =>  $date_stop,
	// "statuses" => array("DELIVERED", "RETURNED", "CANCELLED_IN_DELIVERY",  "CANCELLED_IN_PROCESSING"),
	//   "statuses"=> array ("RETURNED"),
	"statuses" => array("DELIVERED"),


	"hasCis" => 'false'
);




$next_page = '';
do {
	$ya_link =  'https://api.partner.market.yandex.ru/campaigns/' . $campaignId . '/stats/orders?page_token=' . $next_page . '&limit=200';
	$arr_result_temp = post_query_with_data($ya_token, $ya_link, $ya_data);
	if (!isset($arr_result_temp['result']['paging']['nextPageToken'])) {
		break;
	}
	$next_page = $arr_result_temp['result']['paging']['nextPageToken'];
	foreach ($arr_result_temp['result']['orders'] as $order) {
		$arr_all[] = $order;
	}
} while (isset($arr_result_temp['result']['orders'][0]));



// echo "<pre>";
// print_r($arr_all);


foreach ($arr_all as $order) {
	$order_sum_commission = 0;
	$order_sum_pays = 0;

	// перебираем каждый товар в закаказе
	foreach ($order['items'] as $key => $item) {
		$item_pays = 0;
		// перебираем только товары и начинаем формировать массив товаров 
		if (is_numeric($key)) {
			// Перебираем цены, сколько оплатил покупатель за товар , Скидка магазина и кэшбэк		
			foreach ($item['prices'] as $price) {
				$item_pays = $item_pays +  $price['total'];
				$arr_items[$order['id']]['items'][$item['shopSku']][$price['type']] = @$arr_items[$item['shopSku']][$price['type']] + $price['total'];
			}
			// сумма всех выплат по Одному SKU
			$arr_items[$order['id']]['items'][$item['shopSku']]['SUM_item_pays'] = $item_pays;
			$order_sum_pays = $order_sum_pays + $item_pays;
		}
	}


	// Перебираем КОМИССИЮ ЯНДЕКСА (Коммисия считается за весь заказ)
	foreach ($order['commissions'] as $commission) {
		$order_sum_commission = $order_sum_commission + $commission['actual'];
		$arr_items[$order['id']]['ORDERS_COMMISSIONS'][$commission['type']] = $commission['actual'];
	}
	// Цепляем сумму всех комиссий
	$arr_items[$order['id']]['ORDERS_COMMISSIONS']['SUMMA'] = $order_sum_commission;
	$arr_items[$order['id']]['ORDERS_PAYS_SUMMA'] = $order_sum_pays;
	$arr_items[$order['id']]['ONE_PROCENT_OT_PAYS_SUMMA'] = $order_sum_pays / 100;

	// перебираем каждый товар в закаказе
	foreach ($order['items'] as $key => $item) {
		$item_pays = 0;
		// перебираем только товары и начинаем формировать массив товаров 
		if (is_numeric($key)) {
			$arr_items[$order['id']]['items'][$item['shopSku']]['Orderid'] = $order['id'];
			$arr_items[$order['id']]['items'][$item['shopSku']]['status'] = $order['status'];
			$arr_items[$order['id']]['items'][$item['shopSku']]['statusUpdateDate'] = $order['statusUpdateDate'];

			$arr_items[$order['id']]['items'][$item['shopSku']]['deliveryRegion'] = $order['deliveryRegion']['name'];
			$arr_items[$order['id']]['items'][$item['shopSku']]['creationDate'] = $order['creationDate'];
			$arr_items[$order['id']]['items'][$item['shopSku']]['offerName'] = $item['offerName'];
			$arr_items[$order['id']]['items'][$item['shopSku']]['shopSku'] = $item['shopSku'];
			$arr_items[$order['id']]['items'][$item['shopSku']]['count'] = @$arr_items[$item['shopSku']]['count'] + $item['count'];
			// Рассчитываем комиисию в зависимости от суммы товара
			$arr_items[$order['id']]['items'][$item['shopSku']]['commission'] = round($order_sum_commission *
				($arr_items[$order['id']]['items'][$item['shopSku']]['SUM_item_pays'] / $arr_items[$order['id']]['ONE_PROCENT_OT_PAYS_SUMMA']) / 100, 2);
			// Рассчитываем сколько мы заработали на этом СКУ
			$arr_items[$order['id']]['items'][$item['shopSku']]['pribil_s_SKU'] = $arr_items[$order['id']]['items'][$item['shopSku']]['SUM_item_pays']  -
				$arr_items[$order['id']]['items'][$item['shopSku']]['commission'];
			// Рассчитываем сколько мы заработали на этом СКУ за 1 штуку
			$arr_items[$order['id']]['items'][$item['shopSku']]['pribil_s_SKU_za_shtuku'] = round($arr_items[$order['id']]['items'][$item['shopSku']]['pribil_s_SKU'] /
				$arr_items[$order['id']]['items'][$item['shopSku']]['count'], 2);
		}
	}
}





// die();



// Формируем массив продаж
foreach ($arr_items as $order_number => $orders) {

	foreach ($orders['items'] as $key_articke => $item) {
		$arr_razbor_article[mb_strtolower($key_articke)][] = $item;
		$arr_sum_item[mb_strtolower($key_articke)] = @$arr_sum_item[mb_strtolower($key_articke)] + $item['pribil_s_SKU'];
		$arr_count_item[mb_strtolower($key_articke)] = @$arr_count_item[mb_strtolower($key_articke)] + $item['count'];
		$arr_sum_buyer_item[mb_strtolower($key_articke)] = @$arr_sum_buyer_item[mb_strtolower($key_articke)] + $item['BUYER'];
	
	}
}



// die();
echo <<<HTML
	<link rel="stylesheet" href="temp_css.css">
HTML;
print_r($arr_count_item);

// print_r($arr_items_yandex);



foreach ($arr_items_yandex as $ya_key => &$ya_item) 
{
	
	
	if (!isset($arr_count_item[mb_strtolower($ya_item['sku'])])) {
		print_r($ya_item);
		echo "*************************** DELETE ***************************";
		unset($arr_items_yandex[$ya_key]);
		continue;
	}

	foreach ($arr_razbor_article as $key => $items) 
	{
		 // пропускам 

		foreach ($items as $item) {

			// print_r($item);
				$key = mb_strtolower($key);
			// die();
			if (mb_strtolower($ya_item['sku']) == mb_strtolower($item['shopSku'])) {


				// Наша цена
				$ya_item['BUYER'] =  $item['BUYER'];
				// Скидка маркетплэйса
				
				if (isset($item['MARKETPLACE'])) {$ya_item['MARKETPLACE'] =  $item['MARKETPLACE'];} 
				// Кэшбэк
				if (isset($item['CASHBACK'])) {$ya_item['CASHBACK'] =  $item['CASHBACK'];}
				
				// Цена для покупателя со всеми скидками
				$ya_item['Price_for_buyer'] =  $item['BUYER'];
				
				// количество проданного
				$ya_item['count_sell'] =  $arr_count_item[$key];
				// сумма проданного
				$ya_item['sum_nasha_viplata'] =  $arr_sum_item[$key];
				// себестоимость 
				$ya_item['sebes_str_item'] =  $ya_item['min_price'];
				// цена продажи за одну штуку
				$ya_item['price_for_shtuka'] =  round($arr_sum_item[$key] / $arr_count_item[$key], 2);
				// дельта от себестоимости
				$ya_item['delta_v_stoimosti'] =  round(($ya_item['price_for_shtuka'] - $ya_item['min_price']), 2);
				// дельта от хорошей цены
				$ya_item['good_delta'] =  round(($ya_item['price_for_shtuka'] - $ya_item['main_price']), 2);
				// наша прибыль
				$ya_item['our_pribil'] =  round(($ya_item['delta_v_stoimosti'] * $ya_item['count_sell']), 2);
				// габариты 
				$ya_item['gabariti'] =  $ya_item['dlina'] . "x" . $ya_item['shirina'] . "x" . $ya_item['visota'];
				// цена на маркете 
				$ya_item['sum_k_pererchisleniu_za_shtuku'] =  round($arr_sum_buyer_item[$key] / $arr_count_item[$key], 2);

				$ya_item['pribil_posle_vicheta_strafa'] =  $ya_item['our_pribil'];
			}
		}
		// сумма штрафа 
		$ya_item['summa_strafa_article'] =  0;
	}
}





// print_r($arr_items_yandex);
// die();

$link = report_mp_make_excel_file_morzha($arr_items_yandex, $yandex_anmaks_fbs, $date_start, $date_stop);

/****************************************************************************************************************
 **************************** Простой запрос на YANDEX с данными **************************************
 ****************************************************************************************************************/

function post_query_with_data($ya_token, $ya_link, $ya_data)
{
	$ch = curl_init($ya_link);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: Bearer ' . $ya_token,
		'Content-Type:application/json'
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ya_data, JSON_UNESCAPED_UNICODE));
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $ya_data); 

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);

	$res = curl_exec($ch);

	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
	curl_close($ch);

	if (intdiv($http_code, 100) > 2) {
		echo     'Результат обмена(with Data): ' . $http_code . "<br>";
	}

	$res = json_decode($res, true);
	// var_dump($res);
	return $res;
}


$jjj = 'https://api.partner.market.yandex.ru/reports/united-marketplace-services/generate';
