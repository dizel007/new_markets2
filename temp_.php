<?php

require_once "connect_db.php";
// require_once "pdo_functions/pdo_functions.php";

// require_once "mp_functions/yandex_api_functions.php";
// require_once "mp_functions/yandex_functions.php";




// require_once "functions/functions_yandex.php";
// require_once "functions/functions.php";

$ya_token =  get_token_yam($pdo);
$campaignId =  get_id_company_yam($pdo);


echo "<pre>";


// $ya_data = '{
//     "dateFrom": "2024-05-01",
//     "dateTo": "2024-05-30",
//     "statuses": [
//         "DELIVERED"
//     ],
//     "hasCis": false
// }';


$ya_data = array(
	"dateFrom" => "2024-04-01",
	"dateTo" =>  "2024-04-30",
	// "statuses" => array("DELIVERED", "RETURNED", "CANCELLED_IN_DELIVERY",  "CANCELLED_IN_PROCESSING"),
	//   "statuses"=> array ("RETURNED"),
	  "statuses"=> array ("DELIVERED"),


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

echo "<pre>";
print_r($arr_all[0]);

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
	$arr_items[$order['id']]['ONE_PROCENT_OT_PAYS_SUMMA'] = $order_sum_pays/100;
	
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
				($arr_items[$order['id']]['items'][$item['shopSku']]['SUM_item_pays']/$arr_items[$order['id']]['ONE_PROCENT_OT_PAYS_SUMMA']) /100,2);
		// Рассчитываем сколько мы заработали на этом СКУ
			$arr_items[$order['id']]['items'][$item['shopSku']]['pribil_s_SKU'] = $arr_items[$order['id']]['items'][$item['shopSku']]['SUM_item_pays']  - 
				$arr_items[$order['id']]['items'][$item['shopSku']]['commission'] ;
		// Рассчитываем сколько мы заработали на этом СКУ за 1 штуку
			$arr_items[$order['id']]['items'][$item['shopSku']]['pribil_s_SKU_za_shtuku'] = round($arr_items[$order['id']]['items'][$item['shopSku']]['pribil_s_SKU'] /
				$arr_items[$order['id']]['items'][$item['shopSku']]['count'] ,2) ;
			}
		
	}


	// break;
}
// echo "<br>" . $order_sum_commission . "<br>";
print_r($arr_items['445867476']);
// die();

echo "<table>";
echo "<tr>";
echo "<td>"."пп"."</td>";
echo "<td>"."Заказ"."</td>";
echo "<td>"."Дата заказа"."</td>";
echo "<td>"."Город"."</td>";
echo "<td>"."Артикул"."</td>";
echo "<td>"."Кол-во"."</td>";
echo "<td>"."Цена <br> покупателя"."</td>";
echo "<td>"."Цена <br> покупателя за шт"."</td>";

echo "<td>"."Комиссия"."</td>";
echo "<td>"."Маржа"."</td>";





echo "</tr>";

$i=1;
foreach ($arr_items as $order_number=> $orders) {
	

	foreach ($orders['items'] as $key_articke=> $item) {
		// echo "<tr>";
		// echo "<td>".$i."</td>";
		// echo "<td>".$order_number."</td>";
		// echo "<td>".$item['creationDate']."</td>";


		// echo "<td>".$item['deliveryRegion']."</td>";
		// echo "<td>".$key_articke."</td>";
		// echo "<td>".$item['count']."</td>";
		// echo "<td>".$item['SUM_item_pays']."</td>";
		// $price_one_stuka = round ($item['SUM_item_pays'] / $item['count'],2);
		// echo "<td>".$price_one_stuka."</td>";

		// echo "<td>".$item['commission']."</td>";
		// echo "<td>".$item['pribil_s_SKU']."</td>";
		// echo "<td>".$item['pribil_s_SKU_za_shtuku']."</td>";



$arr_razbor_article[mb_strtolower($key_articke)][] = $item['pribil_s_SKU_za_shtuku'];

		echo "</tr>";
	}
	

	$i++;
}
echo "</table>";

// print_r($arr_razbor_article);

foreach ($arr_razbor_article as $key=>$bitems) {
	sort($bitems);
	// print_r($bitems);



	echo "<br>*************************** $key **************************************<br>";

	$arr_g[$key] =$bitems;

}
print_r($arr_g);

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
