<?php

function get_access_token_reklama_ozon() {
require_once "tokens.php";
$data = [
    "client_id"     => $client_id, 
    "client_secret" => $client_secret, 
    "grant_type"    => "client_credentials"
];

$data = json_encode($data);

	$link = 'https://api-performance.ozon.ru/api/client/token';

	$ch = curl_init($link);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type:application/json',
        'Accept: application/json'
	));

	curl_setopt($ch, CURLOPT_POSTFIELDS, $data); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$res = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код

	curl_close($ch);
		$res = json_decode($res, true);
   
        // echo     '<br>Результат обмена озон (с данными): '.$http_code. "<br>";
    
    return($res['access_token']);	
    }