<?php
/********************************
 * Функции по взаимодействию с сайтом ВБ чреез апи
 *******************************/

 /****************************************************************************************************************
****************************  Простой запрос на ВБ без данных **************************************
****************************************************************************************************************/
function light_query_without_data($token_wb, $link_wb){
	$ch = curl_init($link_wb);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization:' . $token_wb,
		'Content-Type:application/json'
	));
	// curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE)); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	
	$res = curl_exec($ch);
	
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
	curl_close($ch);
	if (intdiv($http_code,100) > 2) {
		echo     'Результат обмена (without Data): '.$http_code. "<br>";
	
		}
	
	$res = json_decode($res, true);
	
	return $res;
	}

/****************************************************************************************************************
**************************** Простой запрос на ВБ  с данными **************************************
****************************************************************************************************************/

function light_query_with_data($token_wb, $link_wb, $data){
	$ch = curl_init($link_wb);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization:' . $token_wb,
		'Content-Type:application/json'
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE)); 
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

/****************************************************************************************************************
****************************  ОТправка PATCH на ВБ  с данными **************************************
****************************************************************************************************************/

function patch_query_with_data($token_wb, $link_wb, $data) {
$ch = curl_init($link_wb);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Authorization:' . $token_wb,
	'Content-Type:application/json'
));
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE)); 
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_HEADER, false);

$res = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
curl_close($ch);

echo     'Результат обмена PATCH: '.$http_code. "<br>";
$res = json_decode($res, true);

return $res;
}

/****************************************************************************************************************
****************************  ОТправка PATCH на ВБ  с данными **************************************
****************************************************************************************************************/

function wb_put_query_with_data($token_wb, $link_wb, $data) { // обновляем остатки товаров на ВБ

	$ch = curl_init($link_wb);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization:' . $token_wb,
        'Content-Type:application/json'
    ));
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data)); 
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    
    $res = curl_exec($ch);
    
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
    curl_close($ch);
    
    if (intdiv($http_code,100) > 2) {
    echo     'Результат обмена вб: '.$http_code. "<br>";
    }
    $res = json_decode($res, true);
    return $res;
  }