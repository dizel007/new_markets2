<?php
require_once "connect_db.php";
require_once __DIR__ . '/vendor/autoload.php'; // если это не точка входа, а отдельный скрипт


use App\Ozon\OzonClient;
use App\Ozon\Service\ProductService;
use App\Ozon\Service\FinanceService;
$token = $token_ozon_ip;
$client_id = $client_id_ozon_ip;

$ozon    = new OzonClient($token, $client_id);
$finance = new FinanceService($ozon);
$product = new ProductService($ozon);

$balance = $finance->getBalance('2026-09-01', '2026-09-30');
$tovary  = $product->getSebestoimostTovarov();


echo "<pre>";
print_r($balance);
print_r($tovary);