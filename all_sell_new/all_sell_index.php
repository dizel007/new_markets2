<?php
$offset = "../";
require_once $offset.'connect_db.php';
require_once $offset.'pdo_functions/pdo_functions.php';

require_once $offset."mp_functions/ozon_api_functions.php";
require_once $offset."mp_functions/ozon_functions.php";
require_once $offset."mp_functions/wb_api_functions.php";
require_once $offset."mp_functions/wb_functions.php";
require_once $offset."mp_functions/yandex_api_functions.php";
require_once $offset."mp_functions/yandex_functions.php";

require_once "functions/all_sell_functions.php";


require_once '../vendor/autoload.php';
use App\Ozon\OzonClient;
use App\Ozon\Service\OrderService;
use App\Yandex\YandexClient;
use App\Yandex\Service\YandexOrderService;



echo '<link rel="stylesheet" href="css/sell_table.css">';

echo "<pre>";
    // die();
 insert_in_table_user_action($pdo, $userdata['user_login'] , 'OPEN_SELL_TABLE');



// Доставем информацию по складам ****** АКТИВНЫМ СКЛАДАМ ******
$sklads = select_info_about_sklads($pdo); // ОБщая Информация по складам

$arr_need_ostatok = get_min_ostatok_tovarov($pdo); // массив с утвержденным неснижаемым остатком

// Вся продаваемая номенклатура
// $arr_all_nomenklatura = select_all_nomenklaturu($pdo);
$arr_all_nomenklatura = select_active_nomenklaturu($pdo);

// echo "<ore>";
// print_r($arr_all_nomenklatura);


// Названия магазинов
$wb_anmaks = 'wb_anmaks';
$wb_ip = 'wb_ip_zel';
$ozon_anmaks = 'ozon_anmaks';
$ozon_ip = 'ozon_ip_zel';
$yandex_anmaks_fbs = 'ya_anmaks_fbs';

// Формируем каталоги товаров
$wb_catalog      = get_catalog_tovarov_v_mp($wb_anmaks ,        $pdo, 'all');
$wbip_catalog    = get_catalog_tovarov_v_mp($wb_ip,             $pdo, 'all'); // фомируем каталог
$ozon_catalog    = get_catalog_tovarov_v_mp($ozon_anmaks,       $pdo ,'all'); // получаем озон каталог
$ozon_ip_catalog = get_catalog_tovarov_v_mp($ozon_ip,           $pdo, 'all'); // получаем озон каталог
$ya_fbs_catalog  = get_catalog_tovarov_v_mp($yandex_anmaks_fbs, $pdo, 'all'); // получаем yandex каталог

// Формируем массив в номенклатурой, с учетом того, что один товар можнт продаваться под разным артикулом на Маркете


// Формируем массив в номенклатурой, с учетом того, что один товар можнт продаваться под разным артикулом на Маркете

/* *****************************      Получаем Фактические остатки с ВБ *****************************/
// $wb_catalog = get_ostatki_wb ($token_wb, $wb_catalog, $sklads[$wb_anmaks ]['warehouseId']);
//*****************************      Достаем фактически заказанные товары  *****************************
$wb_catalog = get_new_zakazi_wb ($token_wb, $wb_catalog);

/* *****************************      Получаем Фактические остатки с ВБ ИП *****************************/
// $wbip_catalog = get_ostatki_wb ($token_wb_ip, $wbip_catalog, $sklads[$wb_ip]['warehouseId']); // цепляем остатки 
//*****************************      Достаем фактически заказанные товары  WB IP *****************************
$wbip_catalog = get_new_zakazi_wb ($token_wb_ip, $wbip_catalog);

//***************************** Получаем Фактические остатки с OZON *****************************
// $ozon_catalog = get_ostatki_ozon ($token_ozon, $client_id_ozon, $ozon_catalog); // цепояем остатки

//*****************************  Достаем фактически заказанные товары OZON *****************************
$date_query_ozon = date('Y-m-d', strtotime('-7 day', strtotime(date('Y-m-d')))); // начальную датк на 4 дня раньше берем
$dop_days_query = 14; // захватывает 14 дней после сегодняшней даты

$ozon_anmaks_obj = new OzonClient($token_ozon, $client_id_ozon);
$orders_anmaks = new OrderService($ozon_anmaks_obj);
$ArrayOrders_anmaks = $orders_anmaks->getUnfulfilledFbs($date_query_ozon, 'awaiting_packaging', $dop_days_query);
$ozon_catalog = get_new_zakazi_ozon_new ($ozon_catalog, $ArrayOrders_anmaks ); // цепляем продажи


//***************************** Получаем Фактические остатки с OZON ИП *****************************
// $ozon_ip_catalog = get_ostatki_ozon ($token_ozon_ip, $client_id_ozon_ip, $ozon_ip_catalog); // цепояем остатки

//*****************************  Достаем фактически заказанные товары OZON *****************************
$ozon_ip_obj   = new OzonClient($token_ozon_ip, $client_id_ozon_ip);
$orders_ip = new OrderService($ozon_ip_obj);
$ArrayOrders_ip = $orders_ip->getUnfulfilledFbs($date_query_ozon, 'awaiting_packaging', $dop_days_query);
$ozon_ip_catalog = get_new_zakazi_ozon_new ($ozon_ip_catalog, $ArrayOrders_ip ); // цепляем продажи


//***************************** Получаем Фактические остатки с ЯНДЕКС *****************************
$ya_fbs_catalog = get_ostatki_yandex ($yam_token, $campaignId_FBS, $ya_fbs_catalog); // цепояем остатки
//*****************************  Достаем фактически заказанные товары YANDEX *****************************

// echo ($campaignId_FBS);
// die('cc');
$yandex_obj = new YandexClient($yam_token, $campaignId_FBS);
// находим business_id для нашего магазинаа

$campaigns = $yandex_obj->yandex_post('v2/campaigns');
foreach ($campaigns['campaigns'] as $campaign) {
    if ($campaignId_FBS == $campaign['id']) {
        $business_id = $campaign['business']['id'];
        break;
    }

}

$orders_yandex = new YandexOrderService ($yandex_obj, $campaignId_FBS, $business_id);

//=========================================================================================================================
// запрашваем все заказы со статусом PROCESSING -- STARTED //
//=========================================================================================================================

$arr_all_new_orders =$orders_yandex->YandexgetUnfulfilledFby(date('Y-m-d'),  'PROCESSING',  'STARTED');



$ya_fbs_catalog = get_new_zakazi_yandex_new ($ya_fbs_catalog,  $arr_all_new_orders);



// echo "<pre>";
// print_r ($wb_catalog);
// die();


print_info_sell_market ($arr_all_nomenklatura, $wb_catalog, $wbip_catalog, $ozon_catalog , $ozon_ip_catalog, $ya_fbs_catalog);

// print_r($ya_fbs_catalog);
/**************************************************************************************
 ********************************** THE END **********************************************
 *****************************************************************************************/

 // Формируем каталог проданных товаров по магащинам
$summ_sell_arr[$wb_anmaks] = add_sells_in_sum_array ($wb_catalog) ;
$summ_sell_arr[$wb_ip] = add_sells_in_sum_array ($wbip_catalog) ;
$summ_sell_arr[$ozon_anmaks] = add_sells_in_sum_array ($ozon_catalog) ;
$summ_sell_arr[$ozon_ip] = add_sells_in_sum_array ($ozon_ip_catalog) ;
$summ_sell_arr[$yandex_anmaks_fbs] = add_sells_in_sum_array ($ya_fbs_catalog) ;

echo "<pre>";
print_r ($summ_sell_arr);
// die();
$send_data = json_encode($summ_sell_arr,  JSON_HEX_APOS | JSON_HEX_QUOT);
echo "<br>";


echo <<<HTML

<form method="POST" action="fbs_sell_in_db.php">
    <input type="hidden" name="send_data" value=$send_data>
    
    <button type="submit">Отправить Данные в БД(ТОЛЬКО ПЕРЕД РАЗБОРОМ)</button>
</form>
HTML;

die('');


function add_sells_in_sum_array ($mp_catalog) {
   foreach ($mp_catalog as $select_shop) {
    if (isset( $select_shop['sell_count'])){
        $sell_arr[mb_strtolower($select_shop['main_article'])] = @$sell_arr[mb_strtolower($select_shop['main_article'])] + $select_shop['sell_count'];
    }
} 

return $sell_arr ?? false;
}