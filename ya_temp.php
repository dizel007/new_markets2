<?php
require_once "connect_db.php";

require_once "yandex_razbor/functions/functions_yandex.php";

require_once "pdo_functions/pdo_functions.php";
require_once "yandex_razbor/functions/functions.php";




$ya_token =  $arr_tokens['yandex_anmaks_fbs']['token'];
$campaignId =  $arr_tokens['yandex_anmaks_fbs']['id_market'];


$arr_all_stocks = get_all_stocks_yandex($ya_token, $campaignId);


foreach ($arr_all_stocks['result']['warehouses'][0]['offers'] as $stocks) {
    
    // $arr_stocks_by_artickle[$stocks['offerId']] = $stocks['offerId'];
    foreach ($stocks['stocks'] as $type_stock) {
        if ($type_stock['type'] == 'AVAILABLE') {
            $arr_stocks_by_artickle[$stocks['offerId']]['AVAILABLE'] = $type_stock['count'];
        } elseif ($type_stock['type'] == 'FREEZE'){
            $arr_stocks_by_artickle[$stocks['offerId']]['FREEZE'] = $type_stock['count'];
        } elseif ($type_stock['type'] == 'FIT') {
            $arr_stocks_by_artickle[$stocks['offerId']]['FIT'] = $type_stock['count'];
        }

    }
    
}

echo "<pre>";
print_r($arr_stocks_by_artickle);

die();


$ya_link = 'https://api.partner.market.yandex.ru/campaigns/'.$campaignId.'/offers/stocks';

$ya_data = array(
    "skus" => array(array(
            "sku" =>  "1282760677",
            "items" => array(array(
                    "count" => 35,
                    "updatedAt"=> "2024-04-17T12:05:01Z"
            ),
                            )
            )));

       $res = yandex_put_query_with_data($ya_token, $ya_link, $ya_data);

var_dump($res);

die('ggg');



function get_all_stocks_yandex($ya_token, $campaignId) {
    $ya_data = array (
        "withTurnover" => false,
        "archived" => false,
        "offerIds" => array()
     );
    
    $ya_link = 'https://api.partner.market.yandex.ru/campaigns/'.$campaignId.'/offers/stocks';
    
    $result = yandex_get_query_without_data($ya_token, $ya_link, $ya_data);
    
    return $result;
    }
    

        function yandex_get_query_without_data($ya_token, $ya_link, $ya_data){
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
                echo     'Результат обмена(with Data): '.$http_code. "<br>";
            
                }
            
            $res = json_decode($res, true);
            // var_dump($res);
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

