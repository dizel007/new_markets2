<?php

require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";
require_once '../vendor/autoload.php';
require_once "../mp_functions/get_token_by_shop.php";
// require_once "../".__DIR__ . '/vendor/autoload.php'; // если это не точка входа, а отдельный скрипт


use App\Ozon\OzonClient;

echo "<pre>";
// print_r($arr_tokens);


// echo  "ПРОШЛИ КОННЕКТ<br>";
if (!isset($_GET['ozon_shop']) || $_GET['ozon_shop'] === '') {
    http_response_code(400);
    exit('Параметр ozon_shop обязателен');
} else {
$shop_name = $_GET['ozon_shop'];
}
// получаем токен запрашиваемого магазина
[$token , $client_id] = get_token_AND_idclient ($arr_tokens, $shop_name);
$token = '0a2679cf-74cb-43eb-b042-94997de5f748';
$client_id = '1724451';


 $ozon_fee = send_injection_on_ozon($token, $client_id, '', 'v1/finance/accrual/types' );
file_put_contents(
    'ddd.json',
    json_encode($ozon_fee, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
);
print_r($ozon_fee);