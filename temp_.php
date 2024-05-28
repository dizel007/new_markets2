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


$ya_data = '{
    "dateFrom": "2024-04-01",
    "dateTo": "2024-04-30",
    "statuses": [
        "DELIVERED"
    ],
    "hasCis": false
}';


$ya_data = array ("dateFrom" => "2024-04-01",
			      "dateTo" =>  "2024-04-30",
				  "statuses"=> array ("DELIVERED" ,"RETURNED", "CANCELLED_IN_DELIVERY" ,  "CANCELLED_IN_PROCESSING" ),
				//   "statuses"=> array ("RETURNED"),
				//   "statuses"=> array ("DELIVERED"),


				  "hasCis" => 'false');




$next_page = '';
do {
    $ya_link =  'https://api.partner.market.yandex.ru/campaigns/'.$campaignId.'/stats/orders?page_token='.$next_page.'&limit=200';
    $arr_result_temp = post_query_with_data($ya_token, $ya_link, $ya_data);
    if (!isset($arr_result_temp['result']['paging']['nextPageToken'])) {
        break;
    }
    $next_page = $arr_result_temp['result']['paging']['nextPageToken'];
        foreach ($arr_result_temp['result']['orders'] as $order) {
            $arr_all[]=$order;
        }
    

} while (isset($arr_result_temp['result']['orders'][0]));

echo "<pre>";
print_r($arr_all[0]);

foreach ($arr_all as $order) {
	$order_sum_commission =0;

// перебираем каждый товар в закаказе
	foreach ($order['items'] as $key=>$item) {
	// перебираем только товары и начинаем формировать массив товаров 
		if (is_numeric($key)) {
			$arr_items[$item['shopSku']]['offerName'] = $item['offerName'];
			$arr_items[$item['shopSku']]['shopSku'] = $item['shopSku'];
			$arr_items[$item['shopSku']]['count'] = @$arr_items[$item['shopSku']]['count'] + $item['count'];
		// Перебираем цены, сколько оплатил покупатель за товар , Скидка магазина и кэшбэк		
			foreach ($item['prices'] as $price) {
				$arr_items[$item['shopSku']][$price['type']] = @$arr_items[$item['shopSku']][$price['type']] + $price['total']/$item['count'];	
			}

	}
		}
		
	
	// Перебираем КОМИССИЮ ЯНДЕКСА (Коммисия считается за весь заказ)
	foreach ($order['commissions'] as $commission) {
		$order_sum_commission = $order_sum_commission + $commission['actual'];	
	}


break;
}
echo "<br>".$order_sum_commission."<br>";
print_r($arr_items);
/****************************************************************************************************************
**************************** Простой запрос на YANDEX с данными **************************************
****************************************************************************************************************/

function post_query_with_data($ya_token, $ya_link, $ya_data){
	$ch = curl_init($ya_link);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: Bearer '.$ya_token,
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

	if (intdiv($http_code,100) > 2) {
		echo     'Результат обмена(with Data): '.$http_code. "<br>";
	
		}
	
	$res = json_decode($res, true);
	// var_dump($res);
	return $res;

}


$jjj = 'https://api.partner.market.yandex.ru/reports/united-marketplace-services/generate';