<?php
$offset = "../../";
require_once $offset . "connect_db.php";
require_once $offset . "pdo_functions/pdo_functions.php";
require_once $offset . "mp_functions/ozon_api_functions.php";
require_once $offset . "mp_functions/ozon_functions.php";
// require_once "functions.php";


// /**НАСТРОЙКИ МАГАЗИНЫ ****************************************** */

// $ozon_shop = $_GET['shop_name']; // Какаой магазин дернуди
$ozon_shop = 'ozon_anmaks';

if ($ozon_shop == 'ozon_anmaks') {
	// ОЗОН АНМКАС
	$client_id = $arr_tokens['ozon_anmaks']['id_market'];
	$token_ozon = $arr_tokens['ozon_anmaks']['token'];
  } elseif ($ozon_shop == 'ozon_ip_zel') {
	// озон ИП зел
	$client_id = $arr_tokens['ozon_ip_zel']['id_market'];
	$token_ozon = $arr_tokens['ozon_ip_zel']['token'];
  } else {
	  echo "Не нашли маркет" ;
	  die();
  }


// echo "<pre>";

$ozon_catalog    = get_catalog_tovarov_v_mp($ozon_shop , $pdo, 'active'); // получаем озон каталог

// формируем массиd для запроса цены ********************
foreach ($ozon_catalog as $tovars) {
$arr_article[] = $tovars['mp_article'];
$arr_id_ozon[] = $tovars['product_id'];
}

// $ozon_dop_url = "v4/product/info/prices";
$ozon_dop_url = "v5/product/info/prices";

$send_data = array(
	"filter" => array(
		"offer_id" => $arr_article,
		"product_id" => $arr_id_ozon,
		"visibility" =>  "ALL"
	),
	"limit" => 100
);

$send_data = json_encode($send_data);


// непосредственный запрос цен
$ozcatalog = post_with_data_ozon($token_ozon, $client_id, $send_data, $ozon_dop_url);

echo "<pre>";
print_r($ozcatalog['items'][0] );
// die();
// print_r($arr_article);


unset($items);
foreach ($ozcatalog['items'] as $items) {
	$new_ozon_catalog_from_site[$items['offer_id']]['product_id'] =  $items['product_id']; // ску товара
	$new_ozon_catalog_from_site[$items['offer_id']]['offer_id'] =  $items['offer_id']; // артикул товара

    $new_ozon_catalog_from_site[$items['offer_id']]['volume_weight'] =  $items['volume_weight']; // объемовес
    $new_ozon_catalog_from_site[$items['offer_id']]['acquiring'] =  $items['acquiring']; // эквайринг'

// Комиссии для логистику
    $new_ozon_catalog_from_site[$items['offer_id']]['fbo_deliv_to_customer_amount'] =      $items['commissions']['fbo_deliv_to_customer_amount']; // Последняя миля (FBO).
    $new_ozon_catalog_from_site[$items['offer_id']]['fbo_direct_flow_trans_max_amount'] =  $items['commissions']['fbo_direct_flow_trans_max_amount']; // Магистраль до (FBO). МАкс
    $new_ozon_catalog_from_site[$items['offer_id']]['fbo_direct_flow_trans_min_amount'] =  $items['commissions']['fbo_direct_flow_trans_min_amount']; // Магистраль до (FBO). мин
    $new_ozon_catalog_from_site[$items['offer_id']]['fbo_return_flow_amount'] =            $items['commissions']['fbo_return_flow_amount']; // Комиссия за возврат и отмену (FBO).
    $new_ozon_catalog_from_site[$items['offer_id']]['fbs_deliv_to_customer_amount'] =      $items['commissions']['fbs_deliv_to_customer_amount']; //  Последняя миля (FBS).
    $new_ozon_catalog_from_site[$items['offer_id']]['fbs_direct_flow_trans_min_amount'] =  $items['commissions']['fbs_direct_flow_trans_min_amount']; // Магистраль до (FBS). Макс
    $new_ozon_catalog_from_site[$items['offer_id']]['fbs_direct_flow_trans_max_amount'] =  $items['commissions']['fbs_direct_flow_trans_max_amount']; // Магистраль до (FBS). Мин
    $new_ozon_catalog_from_site[$items['offer_id']]['fbs_first_mile_max_amount'] =         $items['commissions']['fbs_first_mile_max_amount']; // Максимальная комиссия за обработку отправления (FBS).
    $new_ozon_catalog_from_site[$items['offer_id']]['fbs_first_mile_min_amount'] =         $items['commissions']['fbs_first_mile_min_amount']; // Минимальная комиссия за обработку отправления (FBS).
    $new_ozon_catalog_from_site[$items['offer_id']]['fbs_return_flow_amount'] =            $items['commissions']['fbs_return_flow_amount']; // Комиссия за возврат и отмену, обработка отправления (FBS).
    $new_ozon_catalog_from_site[$items['offer_id']]['sales_percent_fbo'] =                 $items['commissions']['sales_percent_fbo']; // Процент комиссии за продажу (FBO).
    $new_ozon_catalog_from_site[$items['offer_id']]['sales_percent_fbs'] =                 $items['commissions']['sales_percent_fbs']; // Процент комиссии за продажу (FBS).
// Цены 

    $new_ozon_catalog_from_site[$items['offer_id']]['auto_action_enabled'] =    $items['price']['auto_action_enabled']; // true, если автоприменение акций у товара включено.
    $new_ozon_catalog_from_site[$items['offer_id']]['marketing_price'] =        $items['price']['marketing_price']; // Цена на товар с учётом всех акций, которая будет указана на витрине Ozon.
    $new_ozon_catalog_from_site[$items['offer_id']]['marketing_seller_price'] = $items['price']['marketing_seller_price']; // Цена на товар с учётом акций продавца.
    $new_ozon_catalog_from_site[$items['offer_id']]['min_price'] =              $items['price']['min_price']; // Минимальная цена товара после применения всех скидок.
    $new_ozon_catalog_from_site[$items['offer_id']]['old_price'] =              $items['price']['old_price']; // Цена до учёта скидок. На карточке товара отображается зачёркнутой.
    $new_ozon_catalog_from_site[$items['offer_id']]['price'] =                     $items['price']['price']; // Цена товара с учётом скидок — это значение показывается на карточке товара.
    $new_ozon_catalog_from_site[$items['offer_id']]['retail_price'] =           $items['price']['retail_price']; // Цена поставщика.
    $new_ozon_catalog_from_site[$items['offer_id']]['vat'] =                    $items['price']['vat']; // Ставка НДС для товара.

// Индексы цен












	$new_ozon_catalog_from_site[$items['offer_id']]['marketing_seller_price'] =  $items['price']['marketing_seller_price'];
	$new_ozon_catalog_from_site[$items['offer_id']]['marketing_price'] =  $items['price']['marketing_price'];
	$new_ozon_catalog_from_site[$items['offer_id']]['price'] =  $items['price']['price'];
	// $new_ozon_catalog_from_site[$items['offer_id']]['min_price'] =  $items['price']['min_price'];

}




print_r($new_ozon_catalog_from_site);

die();
