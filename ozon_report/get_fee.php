<?php

require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";
require_once '../vendor/autoload.php';
require_once "../mp_functions/get_token_by_shop.php";
// require_once "../".__DIR__ . '/vendor/autoload.php'; // если это не точка входа, а отдельный скрипт


use App\Ozon\OzonClient;

// echo "<pre>";
// print_r($arr_tokens);

$shop_name = 'ozon_ip_zel';

// получаем токен запрашиваемого магазина
[$token , $client_id] = get_token_AND_idclient ($arr_tokens, $shop_name);




 $ozon_fee = send_injection_on_ozon($token, $client_id, '', 'v1/finance/accrual/types' );
file_put_contents(
    'types_spend_.json',
    json_encode($ozon_fee, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);
print_r($ozon_fee);