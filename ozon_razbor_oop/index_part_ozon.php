<?php
require_once "../connect_db.php";
// require_once "main_ozon/header.php";
require_once 'include_funcs.php';
require_once "../mp_functions/get_token_by_shop.php";   ///

require_once '../vendor/autoload.php';
use App\Ozon\OzonClient;
use App\Ozon\Service\OrderService;



// echo  "ПРОШЛИ КОННЕКТ<br>";
if (!isset($_GET['ozon_shop']) || $_GET['ozon_shop'] === '') {
    http_response_code(400);
    exit('Параметр ozon_shop обязателен');
} else {
$shop_name = $_GET['ozon_shop'];
}
// получаем токен запрашиваемого магазина
[$token , $client_id] = get_token_AND_idclient ($arr_tokens, $shop_name);


/*****************************************************************************************************************
 *****   НАстраиваем дату начала сбора и количество дней 
 **************************************************************************************************************** */
$now_date_razbora = date('Y-m-d');
$date_query_ozon = date('Y-m-d', strtotime($now_date_razbora . ' -15 day'));
$dop_days_query = 20;

// echo "<h1> НОВЫЙ РАЗБОР ТОВАРОВ С РАЗБИВКОЙ ТОВАРОВ НА ОТПРАВЛЕНИЯ</h1>";
// ****************************************************************************************************************
// Запрашиваем данные по отправлениям за несколько дней
// ****************************************************************************************************************

$ozon   = new OzonClient($token, $client_id);
$orders = new OrderService($ozon);

$ArrayOrders = $orders->getUnfulfilledFbs($date_query_ozon, 'awaiting_packaging', $dop_days_query);

// echo "<pre>";
// print_r($ArrayOrders[0]);
// die();


// awaiting_packaging — ожидает упаковки,
// awaiting_deliver — ожидает отгрузки,


// Извлекаем товары
$items = [];
$arr_sum_items['quantity'] = 0;
$arr_sum_items['price'] = 0;
if (!empty($ArrayOrders)) {
    foreach ($ArrayOrders as $posting) {
        foreach ($posting['products'] as $product) {
            $art = $product['offer_id'];
            if (!isset($items[$art])) {
                $items[$art] = [
                    'name'     => $product['name'],
                    'price'    => $product['price']['amount'],
                    'quantity' => 0
                ];
            }
            $items[$art]['quantity'] += $product['quantity'];
            $items[$art]['price'] += $product['price']['amount'];
            $arr_sum_items['quantity'] += $product['quantity']; 
            $arr_sum_items['price'] += $product['price']['amount']; 
        }
    }
}


// echo "<pre>";
// print_r($items);
// die();


// Подключаем шаблон и передаём ему данные
$viewData = [
    'shopName'   => $shop_name,
    'dateQuery'  => $date_query_ozon,
    'dopDays'    => $dop_days_query,
    'nowDate'    => $now_date_razbora,
    'items'      => $items,
    'formAction' => 'controller/make_part_zakaz_.php'
];

require __DIR__ . '/templates/partial_razbor_form.php';