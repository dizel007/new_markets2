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
$data_by_our_volme_fbo = $data_by_our_volme;

/*******************************************************************************************************
******************************  готовим данные для таблички FBS
*******************************************************************************************************/
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

/*******************************************************************************************************
******************************  готовим данные для таблички FBO
*******************************************************************************************************/
// временка
$array_clasters_fbo = [
'Оренбург' => 8,
'Пермь'=> 8,
'Омск' => 8,
'Саратов' =>8,
'Москва, МО и Дальние регионы' => 8,
'Санкт-Петербург и СЗО' => 8,
'Самара' => 8,
'Ярославль' => 0,
'Махачкала' => 8,
'Невинномысск' => 8,
'Красноярск' => 0,
'Краснодар' => 8,
'Тверь' => 8,
'Воронеж' => 4,
'Екатеринбург' => 8,
'Уфа' => 8,
'Тюмень' => 8,
'Ростов' => 0,
'Казань' => 8,
'Калининград' => 8,
'Новосибирск' => 0,
'Дальний Восток' => 8,
'Алматы' => 6,
'Астана' => 6,
'Беларусь' => 6,
'Армения' => 0,
'Азербайджан' => 0,
'Грузия' => 0,
'Кыргызстан' => 0,
'Узбекистан' => 0,
];

// у н ас товар в Мск
$array_clasters_fbo['Москва, МО и Дальние регионы'] = 0;

$sales_percent_fbo = $data['items'][0]['commissions']['sales_percent_fbo'];
$fbo_deliv_to_customer_amount = $data['items'][0]['commissions']['fbo_deliv_to_customer_amount'];
// рассчетные данные
$commissionFBO = $marketing_seller_price * $sales_percent_fbo / 100;
$dop_sbori_krome_logistiki_fbo = $acquiring + $fbo_deliv_to_customer_amount;
$cost_krome_logistiki_fbo = $marketing_seller_price - $commissionFBO - $dop_sbori_krome_logistiki_fbo ;



foreach ($data_by_our_volme_fbo as &$item_log) {
    $item_log['proc_k_dop_cost_k_logistike'] = $array_clasters_fbo[$item_log['claster_get']];
    $item_log['dop_cost_k_logistike'] = $array_clasters_fbo[$item_log['claster_get']] * $marketing_seller_price/100;
    $item_log['s_logistikoi'] = $cost_krome_logistiki_fbo - $item_log['cost_norm'] - $item_log['dop_cost_k_logistike'];
    $item_log['profit'] = $item_log['s_logistikoi'] - $net_price;
}


$profits = array_column($data_by_our_volme_fbo, 'profit');

// Сортируем массив по убыванию profit (сохраняя ключи)
array_multisort($profits, SORT_ASC, $data_by_our_volme_fbo);


//   echo "<pre>";  
    // print_r($data_by_our_volme);

//   print_r($data_by_our_volme_fbo);
// die();

// $sql = "SELECT DISTINCT claster_send FROM ozon_logistika_price";
// $stmt = $pdo->query($sql);

// while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
//     $claster_send[] = $row['claster_send'];
// }



// print_r($claster_send);


// echo "<pre>";
// print_r($data_by_our_volme_fbo);
// die();



require_once "print_fbs_fbo_table.php";
