<?php

/***************************************************************************************************************
 ***************** GET запрос без даннхы 
 **************************************************************************************************************/
function yandex_get_query_without_data($ya_token, $ya_link){
	
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


/****************************************************************************************************************
**************************** Простой запрос на YANDEX с данными **************************************
****************************************************************************************************************/

function yandex_post_query_with_data($ya_token, $ya_link, $ya_data){
	// echo "<pre>";
	// echo "<br>$ya_link<br>";
	// echo "<br>$ya_token<br>";
	// print_r ($ya_data);

	$ch = curl_init($ya_link);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: Bearer '.$ya_token,
		'Content-Type:application/json'
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ya_data, JSON_UNESCAPED_UNICODE)); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	
	$res = curl_exec($ch);
	
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
	curl_close($ch);

	if (intdiv($http_code,100) > 2) {
		echo     'Результат обмена YANDEX (with Data): '.$http_code. "<br>";
	
		}
	
	$res = json_decode($res, true);
	
	// var_dump($res);
	// die();
	return $res;

}


/****************************************************************************************************************
****************************  ОТправка PUT с данными **************************************
****************************************************************************************************************/

function yandex_put_query_with_data($ya_token, $ya_link, $ya_data) {
    $ch = curl_init($ya_link);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer '.$ya_token,
        'Content-Type:application/json'
    ));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($ya_data, JSON_UNESCAPED_UNICODE)); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    
    $res = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
    curl_close($ch);
    
	if (intdiv($http_code,100) > 2) {
		echo     'Результат обмена PATCH: '.$http_code. "<br>";
	}

    
    $res = json_decode($res, true);
    
    return $res;
    }

