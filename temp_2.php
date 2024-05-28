<?php

require_once "connect_db.php";
// require_once "pdo_functions/pdo_functions.php";

// require_once "mp_functions/yandex_api_functions.php";
// require_once "mp_functions/yandex_functions.php";




// require_once "functions/functions_yandex.php";
// require_once "functions/functions.php";

 $ya_token =  get_token_yam($pdo);
  echo $campaignId =  get_id_company_yam($pdo);


echo "<pre>";


$ya_data = '{
    "businessId": 789064,
    "dateFrom": "2024-04-01",
    "dateTo": "2024-04-30",
    "placementPrograms": [
        "FBS"
    ],
    "inns": [
        "7727830864"
    ],
    "campaignIds": [
        22076999
    ]
}';



    $ya_link =  'https://api.partner.market.yandex.ru/reports/united-marketplace-services/generate?format=FILE';
    $arr_result_temp = post_query_with_data($ya_token, $ya_link, $ya_data);

echo "<pre>";
print_r($arr_result_temp);
sleep(2);
$reportId = $arr_result_temp['result']['reportId'];


$link = 'https://api.partner.market.yandex.ru/reports/info/'.$reportId;

$res = yandex_get333_query_without_data($ya_token, $link);
print_r($res);
/****************************************************************************************************************
**************************** Простой запрос на YANDEX с данными **************************************
****************************************************************************************************************/

function post_query_with_data($ya_token, $ya_link, $ya_data){
	$ch = curl_init($ya_link);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: Bearer '.$ya_token,
		'Content-Type:application/json'
	));
	// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ya_data, JSON_UNESCAPED_UNICODE)); 
	curl_setopt($ch, CURLOPT_POSTFIELDS, $ya_data); 

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


function yandex_get333_query_without_data($ya_token, $ya_link){
	
	$ch = curl_init($ya_link); // ИНФОРМАЦИЯ О ЗАКАЗАХ FBS
    

	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: Bearer '.$ya_token,
		'Content-Type:application/json'
	));
	// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE)); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	
	$res = curl_exec($ch);
	
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
	curl_close($ch);
	if (($http_code != 200) && ($http_code != 201) && ($http_code != 204)) {
		echo     '<br> Результат обмена (SELECT without Data): '.$http_code;
	}
	$res = json_decode($res, true);
	
	return $res;
	}
