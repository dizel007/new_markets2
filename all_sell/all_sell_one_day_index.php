<?php
require_once '../connect_db.php';
require_once '../pdo_functions/pdo_functions.php';

require_once "../mp_functions/ozon_api_functions.php";
require_once "../mp_functions/ozon_functions.php";
require_once "../mp_functions/wb_api_functions.php";
require_once "../mp_functions/wb_functions.php";
require_once "../mp_functions/yandex_api_functions.php";
require_once "../mp_functions/yandex_functions.php";
require_once "functions/all_sell_functions.php";

echo '<link rel="stylesheet" href="css/sell_table.css">';

// echo "<pre>";
    // die();


echo <<<HTML
    <!-- <link rel="stylesheet" href="css/main_ozon.css"> -->
HTML;
    
    if (isset($_GET['date_sbora'])) {
        $date_sbora = $_GET['date_sbora'];  
    
    }else {
        // $date_query_ozon =''; 
        $date_sbora = date('Y-m-d'); 
    
    }
    
echo <<<HTML
    <h1>Все товары проданные на : $date_sbora</h1>
    <hr>
    <div>
        <form method="get" action="#">
        <input  required type="date" name="date_sbora" value="$date_sbora">
        <input type="submit" value="Найти заказы на выбранную дату">
       
    </div>
    </form>    
    <hr>
HTML;
    

// Доставем информацию по складам ****** АКТИВНЫМ СКЛАДАМ ******
$sklads = select_info_about_sklads($pdo); // ОБщая Информация по складам

$arr_need_ostatok = get_min_ostatok_tovarov($pdo); // массив с утвержденным неснижаемым остатком

// Вся продаваемая номенклатура
// $arr_all_nomenklatura = select_all_nomenklaturu($pdo);
$arr_all_nomenklatura = select_active_nomenklaturu($pdo);

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

/******************************      Получаем Фактические остатки с ВБ *****************************/
$wb_catalog = get_ostatki_wb ($token_wb, $wb_catalog, $sklads[$wb_anmaks ]['warehouseId']);
//    Достаем фактически заказанные товары  *****************************
$wb_catalog = get_new_zakazi_wb ($token_wb, $wb_catalog);

/* *****************************      Получаем Фактические остатки с ВБ ИП *****************************/
$wbip_catalog = get_ostatki_wb ($token_wb_ip, $wbip_catalog, $sklads[$wb_ip]['warehouseId']); // цепляем остатки 
//    Достаем фактически заказанные товары  WB IP *****************************
$wbip_catalog = get_new_zakazi_wb ($token_wb_ip, $wbip_catalog);

//***************************** Получаем Фактические остатки с OZON *****************************
$ozon_catalog = get_ostatki_ozon ($token_ozon, $client_id_ozon, $ozon_catalog); // цепояем остатки
//   Достаем фактически заказанные товары OZON *****************************
$ozon_catalog = get_new_zakazi_ozon_one_date ($token_ozon, $client_id_ozon, $ozon_catalog, $date_sbora); // цепляем продажи

//***************************** Получаем Фактические остатки с OZON *****************************
$ozon_ip_catalog = get_ostatki_ozon ($token_ozon_ip, $client_id_ozon_ip, $ozon_ip_catalog); // цепояем остатки
//   Достаем фактически заказанные товары OZON *****************************
$ozon_ip_catalog = get_new_zakazi_ozon_one_date ($token_ozon_ip, $client_id_ozon_ip, $ozon_ip_catalog, $date_sbora); // цепляем продажи

//*****************************  получаем массив (артикул - кол-во проданного товара  *****************************

//***************************** Получаем Фактические остатки с ЯНДЕКС *****************************
$ya_fbs_catalog = get_ostatki_yandex ($yam_token, $campaignId_FBS, $ya_fbs_catalog); // цепояем остатки
//*****************************  Достаем фактически заказанные товары YANDEX *****************************
$ya_fbs_catalog = get_new_zakazi_yandex_one_date ($yam_token, $campaignId_FBS, $ya_fbs_catalog, $date_sbora); // цепляем продажи





print_info_sell_market ($arr_all_nomenklatura, $wb_catalog, $wbip_catalog, $ozon_catalog , $ozon_ip_catalog, $ya_fbs_catalog);
// print_r($ya_fbs_catalog);

echo "<hr><hr><hr>";

/// Выводим на экран таблицы с габаритами проданнных товаров
if (find_sell_zakaz_in_mp($wb_catalog)) {print_table_with_gabariti_mp($arr_all_nomenklatura, $wb_catalog, $wb_anmaks );};
if (find_sell_zakaz_in_mp($wbip_catalog)) {print_table_with_gabariti_mp($arr_all_nomenklatura, $wbip_catalog, $wb_ip );};
if (find_sell_zakaz_in_mp($ozon_catalog)) {print_table_with_gabariti_mp($arr_all_nomenklatura, $ozon_catalog, $ozon_anmaks );};
if (find_sell_zakaz_in_mp($ozon_ip_catalog)) {print_table_with_gabariti_mp($arr_all_nomenklatura, $ozon_ip_catalog, $ozon_ip );};
if (find_sell_zakaz_in_mp($ya_fbs_catalog)) {print_table_with_gabariti_mp($arr_all_nomenklatura, $ya_fbs_catalog, $yandex_anmaks_fbs );};









/**************************************************************************************
 ********************************** THE END **********************************************
 *****************************************************************************************/
die('');

