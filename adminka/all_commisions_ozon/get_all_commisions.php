<?php
//Получить информацию о цене товара
//https://docs.ozon.ru/api/seller/?userid=36019959&utm_campaign=MPCOM-19042-2&utm_mcp_vid=cy76hf1vcgxt2n92409g&utm_medium=email&utm_mid=6669145302112474624&utm_source=mcp&utm_term=4501%3A020250121#operation/ProductAPI_GetProductInfoPrices

//https://api-seller.ozon.ru/v5/product/info/prices



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



$ozon_catalog    = get_catalog_tovarov_v_mp($ozon_shop , $pdo, 'active'); // получаем озон каталог

// формируем массиd для запроса цены ********************
foreach ($ozon_catalog as $tovars) {
$arr_article[] = $tovars['mp_article'];
$arr_id_ozon[] = $tovars['product_id'];
}

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
print_r($ozcatalog['items'][0]);
// die();
// print_r($arr_article);



echo <<<HTML
<h1 class="text-center">Таблица корректировка цен для : $ozon_shop</h1>
<link rel="stylesheet" href="css/table_inf_about_items.css">

<table class="info_about_items">
<tr>
    <th>product_id</th>
    <th>Арт</th>
    <th>Объемовес</th>
    <th>эквайринг</th>

    <th>Последняя миля (FBO)</th>
    <th>Магистраль до (FBO). МАкс</th>
    <th>Магистраль до (FBO). мин</th>
    <th>Комиссия за возврат и отмену (FBO).</th>
    <th>Последняя миля (FBS).</th>
    <th>Магистраль до (FBS). Макс</th>
    <th>Магистраль до (FBS). Мин</th>
    <th>Максимальная комиссия за обработку отправления (FBS)</th>
    <th>Минимальная комиссия за обработку отправления (FBS)</th>
    <th>Комиссия за возврат и отмену, обработка отправления (FBS)</th>
    <th>Процент комиссии за продажу (FBO)</th>
    <th>Процент комиссии за продажу (FBS)</th>

    <th>true, если авто-применение акций у товара включено.</th>
    <th>Цена на товар с учётом всех акций, которая будет указана на витрине Ozon.</th>
    <th>Цена на товар с учётом акций продавца.</th>
    <th>Минимальная цена товара после применения всех скидок.</th>
    <th>Цена до учёта скидок. На карточке товара отображается зачёркнутой.</th>
    <th>Цена товара с учётом скидок — это значение показывается на карточке товара.</th>
    <th>Цена поставщика.</th>
    <th>Ставка НДС для товара.</th>



<tr>




HTML;

 // https://www.ozon.ru/product/233029725/

foreach ($ozcatalog['items'] as $items) {

// ищем продукт id 


foreach ($ozon_catalog as $item_catalog) {
  if ($items['product_id'] == $item_catalog['product_id']) {
    $product = $item_catalog['sku'];
    break;
  }
}



echo <<<HTML
<tr>
    <td><a  href ="https://www.ozon.ru/product/{$product}/" target="_blank">{$items['product_id']}</a></td>
    <td>{$items['offer_id']}</td>
    <td>{$items['volume_weight']}</td>
    <td>{$items['acquiring']}</td>

    <td>{$items['commissions']['fbo_deliv_to_customer_amount']}</td>
    <td>{$items['commissions']['fbo_direct_flow_trans_max_amount']}</td>
    <td>{$items['commissions']['fbo_direct_flow_trans_min_amount']}</td>
    <td>{$items['commissions']['fbo_return_flow_amount']}</td>
    <td>{$items['commissions']['fbs_deliv_to_customer_amount']}</td>
    <td>{$items['commissions']['fbs_direct_flow_trans_min_amount']}</td>
    <td>{$items['commissions']['fbs_direct_flow_trans_max_amount']}</td>
    <td>{$items['commissions']['fbs_first_mile_max_amount']}</td>
    <td>{$items['commissions']['fbs_first_mile_min_amount']}</td>
    <td>{$items['commissions']['fbs_return_flow_amount']}</td>
    <td>{$items['commissions']['sales_percent_fbo']}</td>
    <td>{$items['commissions']['sales_percent_fbs']}</td>
    

    <td>{$items['price']['auto_action_enabled']}</td>
    <td>{$items['price']['marketing_price']}</td>
    <td>{$items['price']['marketing_seller_price']}</td>
    <td>{$items['price']['min_price']}</td>
    <td>{$items['price']['old_price']}</td>
    <td>{$items['price']['price']}</td>
    <td>{$items['price']['retail_price']}</td>
    <td>{$items['price']['vat']}</td>

    

</tr>
HTML;
}


echo "</table>";
die();
