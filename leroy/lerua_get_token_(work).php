<?php
echo "START LEROY <br>";

// Array
// (
//     [access_token] => eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjE5NjU5IiwibmFtZSI6ItCX0LXQu9C40LfQutC-INCU0LzQuNGC0YDQuNC5IiwibG9naW4iOiJ0ZW5kZXJAYW5tYWtzLnJ1Iiwicm9sZXMiOlsibWVyY2hhbnQiLCJtcF9tYW5hZ2VyIl0sIm1lcmNoYW50X2lkIjoiMjYxOSIsIm1lcmNoYW50SWQiOiIyNjE5IiwiaWF0IjoxNjg1NTM5MjU1LCJqdGkiOiI3NTA2YjU1Zi1lYjNmLTQ1YWEtYmY5MS01MWExYzQ5YzcyNmUifQ.1SKrLVm_vio4Q5oksQSlFt4f6iqPdHUDCSc-w2xtW_g
//     [token_type] => Bearer
//     [expires_in] => 5644799
//     [refresh_token] => ce0fcf7b-2d7d-4c4b-8465-462e330f50cf
//     [scope] =>  
// )

// ID Зелизко 2619 
// $send_data = array(
//    "grant_type" => "password",
//    "username"  =>  "tender@anmaks.ru",
//    "password"  =>  "fM2a1r1H",
//    "client_id"  =>  "merchants_orchestrator",
//    "client_secret"  =>  "GRpmUaXkamR9Jtg8WR6DhK5zgk3gwWjf"

// );

$send_data = array(
   "grant_type" => "password",
   "username"  =>  "tender@anmaks.ru",
   "password"  =>  "fM2a1r1H",
   "client_id"  =>  "merchants_orchestrator",
   "client_secret"  =>  "zbpHEJ4rwVrzz9rka3KwvgyUtd8GyfDY"

);

echo "СОЗДАЛ массив <br>"; 
echo "<pre>";
   print_r($send_data);	
echo "<pre>";

$send_data_arr_js = json_encode($send_data);

echo "Перевел в Json для отправки в функцию CURL<br>"; 
echo "<pre>";
   print_r($send_data_arr_js);	
echo "<pre>";


$send_data = 'grant_type=password&username=tender@anmaks.ru&password=fM2a1r1H&client_id=merchants_orchestrator&client_secret=zbpHEJ4rwVrzz9rka3KwvgyUtd8GyfDY';

$ch = curl_init('https://api.leroymerlin.ru/marketplace/oauth/token');
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/x-www-form-urlencoded',
      'x-api-key: b1VSXCMYNYr6H3h0pBLaUczXYEATcS58'
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $send_data); 

   curl_setopt($ch, CURLOPT_POST, 1);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	
   $res = curl_exec($ch);

   $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код

	curl_close($ch);
	
	$res = json_decode($res, true);

   echo     'Результат обмена : '.$http_code. "<br>";
   echo "Ответ  с сайта  CURL<br>";  
echo "<pre>";
   print_r($res);	
echo "<pre>";

$send_data_arr_js = json_encode($send_data);

   
// echo "<pre>";
//    print_r($send_data_arr_js);	
// echo "<pre>";


   die('<br>LERUA DIE');
?>
