<?php

require_once "../../connect_db.php";
require_once "../../mp_functions/ozon_api_functions.php";
require_once "../../mp_functions/ozon_functions.php";
/*****************************************************************
 * Вычитываем информацию о товаре - цену / скидки и прочую хрень
 **************************************************************/
// echo "Вычитываем информацию о товаре - цену / скидки и прочую хрень"."<br>";

try {
    $pdo = new PDO('mysql:host=' . $host . ';dbname=' . $db . ';charset=utf8', $user, $password);
    $pdo->exec('SET NAMES utf8');
} catch (PDOException $e) {
    print "Has errors: " . $e->getMessage();
    die();
}

$queryString = $_SERVER['QUERY_STRING'] ?? '';

// Разобрать вручную
parse_str($queryString, $params);

// находим ID клиента
if (isset($params['ozon_shop'])) {
    $article = $params['art'];
    $product_id = $params['product_id'];
    $shop_name = $params['ozon_shop'];

    if ($shop_name == 'ozon_anmaks') {
        // ОЗОН АНМКАС
        $token  = $arr_tokens['ozon_anmaks']['token'];
        $client_id = $arr_tokens['ozon_anmaks']['id_market'];
    } elseif ($shop_name == 'ozon_ip_zel') {
        // озон ИП зел
        $client_id = $arr_tokens['ozon_ip_zel']['id_market'];
        $token = $arr_tokens['ozon_ip_zel']['token'];
    }
} else {
    die('Не нашли файл с данными');
}

// // находим время доставки за последнюю неделю 
// $ozon_dop_url = "v1/analytics/average-delivery-time/summary";
// $send_data = '';
// $average_delivery_time = post_with_data_ozon($token, $client_id, $send_data, $ozon_dop_url);

// берем информацию по данному артикулу

$ozon_dop_url = "v5/product/info/prices";
$send_data =  array(
    "cursor" => "",
    "filter" => array(
        "offer_id" => array("$article"),
        //    "visibility" => "ALL",
        //    "visibility" => "IN_SALE"
    ),
    "limit" => 1000
);
$send_data = json_encode($send_data);
$data = post_with_data_ozon($token, $client_id, $send_data, $ozon_dop_url);

//// доставем Габаритные размеры товарв и вычислям его объем
$data_by_article = get_dimensions_from_ozon_by_article($token, $client_id, $article);

if (isset($data_by_article['result'][0])) {
    $volume = $data_by_article['result'][0]['height'] *
        $data_by_article['result'][0]['depth'] *
        $data_by_article['result'][0]['width'] / 1000000;
} else {

    echo "<br> Не удалось получить данные о размере с сайта озон";
}

// Запрашиваем массив из БД для этого объема с доставкой ФБС из Москвы
$sql = "SELECT claster_get, cost_norm FROM ozon_logistika_price
        WHERE min_litr <= :volume AND max_litr >= :volume 
        AND claster_send=:claster_send ORDER BY cost_norm DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':volume' => $volume,
    ':claster_send' => 'Москва, МО и Дальние регионы'
]);
$data_by_our_volme = $stmt->fetchAll(PDO::FETCH_ASSOC);



// готовим данные для таблички 

$acquiring = $data['items'][0]['acquiring'];
$sales_percent_fbs = $data['items'][0]['commissions']['sales_percent_fbs'];
$marketing_seller_price = $data['items'][0]['price']['marketing_seller_price'];
$net_price = $data['items'][0]['price']['net_price'];
$fbs_deliv_to_customer_amount = $data['items'][0]['commissions']['fbs_deliv_to_customer_amount'];
$fbs_first_mile_max_amount = $data['items'][0]['commissions']['fbs_first_mile_max_amount'];

// рассчетные данные
$commissionFBS = $marketing_seller_price * $sales_percent_fbs / 100;
$dop_sbori_krome_logistiki = $acquiring + $fbs_deliv_to_customer_amount + $fbs_first_mile_max_amount;

$cost_krome_logistiki = $marketing_seller_price - $commissionFBS - $dop_sbori_krome_logistiki ;


/// Добавляем в массив данные по затратам

foreach ($data_by_our_volme as &$item_log) {
$item_log['s_logistikoi'] = $cost_krome_logistiki - $item_log['cost_norm'];
$item_log['profit'] = $item_log['s_logistikoi'] - $net_price;
}

//   echo "<pre>";  
//   print_r($data_by_our_volme);
// die();

// $sql = "SELECT DISTINCT claster_send FROM ozon_logistika_price";
// $stmt = $pdo->query($sql);

// while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//     $claster_send[] = $row['claster_send'];
// }



// print_r($claster_send);


// echo "<pre>";
// print_r($data_by_article);
// die();



require_once "print_fbs_table.php";
