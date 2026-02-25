<?php 

/* **************************************************************************************************************
*********  Функция обновляния данных Она ОЗОН
************************************************************************************************************** */

function send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url ) {
 
	$ch = curl_init('https://api-seller.ozon.ru/'.$ozon_dop_url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Api-Key:' . $token,
		'Client-Id:' . $client_id, 
		'Content-Type:application/json'
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $send_data); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$res = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код

	curl_close($ch);
	
	$res = json_decode($res, true);

  
   if (intdiv($http_code,100) > 2) {
	echo     'Результат обмена : '.$http_code. "<br>";
	echo "<pre>";
    print_r($res);
	}
   
    return($res);	

}

/* **************************************************************************************************************
*********  Функция обновляния данных Она ОЗОН
************************************************************************************************************** */

function post_with_data_ozon($token_ozon, $client_id_ozon, $send_data, $ozon_dop_url ) {


	$link = 'https://api-seller.ozon.ru/'.$ozon_dop_url;

	// echo "<br>$link";
	$ch = curl_init($link);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Api-Key:' . $token_ozon,
		'Client-Id:' . $client_id_ozon, 
		'Content-Type:application/json'
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $send_data); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$res = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код

	curl_close($ch);
	
	$res = json_decode($res, true);

    if (intdiv($http_code,100) > 2) {
        echo     '<br>Результат обмена озон (с данными POST): '.$http_code. "<br>";
		echo "<pre>";
        print_r($res);
        }

    return($res);	
    }