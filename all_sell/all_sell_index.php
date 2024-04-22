<?php
require_once '../connect_db.php';
require_once '../pdo_functions/pdo_functions.php';

require_once "../mp_functions/ozon_api_functions.php";
require_once "../mp_functions/ozon_functions.php";
require_once "../mp_functions/wb_api_functions.php";
require_once "../mp_functions/wb_functions.php";
require_once "../mp_functions/yandex_api_functions.php";
require_once "../mp_functions/yandex_functions.php";

echo '<link rel="stylesheet" href="css/sell_table.css">';

echo "<pre>";
    // die();




// Доставем информацию по складам ****** АКТИВНЫМ СКЛАДАМ ******
$sklads = select_info_about_sklads($pdo); // ОБщая Информация по складам

$arr_need_ostatok = get_min_ostatok_tovarov($pdo); // массив с утвержденным неснижаемым остатком

// Вся продаваемая номенклатура
$arr_all_nomenklatura = select_all_nomenklaturu($pdo);

// print_r($arr_all_nomenklatura);


// Названия магазинов
$wb_anmaks = 'wb_anmaks';
$wb_ip = 'wb_ip_zel';
$ozon_anmaks = 'ozon_anmaks';
$ozon_ip = 'ozon_ip_zel';
$yandex_anmaks_fbs = 'ya_anmaks_fbs';

// Формируем каталоги товаров
$wb_catalog      = get_catalog_tovarov_v_mp($wb_anmaks , $pdo);
$wbip_catalog    = get_catalog_tovarov_v_mp($wb_ip, $pdo); // фомируем каталог
$ozon_catalog    = get_catalog_tovarov_v_mp($ozon_anmaks, $pdo); // получаем озон каталог
$ozon_ip_catalog = get_catalog_tovarov_v_mp($ozon_ip, $pdo); // получаем озон каталог
$ya_fbs_catalog  = get_catalog_tovarov_v_mp($yandex_anmaks_fbs, $pdo); // получаем yandex каталог

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
$ozon_catalog = get_new_zakazi_ozon ($token_ozon, $client_id_ozon, $ozon_catalog); // цепляем продажи

//***************************** Получаем Фактические остатки с OZON *****************************
$ozon_ip_catalog = get_ostatki_ozon ($token_ozon_ip, $client_id_ozon_ip, $ozon_ip_catalog); // цепояем остатки
//   Достаем фактически заказанные товары OZON *****************************
$ozon_ip_catalog = get_new_zakazi_ozon ($token_ozon_ip, $client_id_ozon_ip, $ozon_ip_catalog); // цепляем продажи

//*****************************  получаем массив (артикул - кол-во проданного товара  *****************************

//***************************** Получаем Фактические остатки с ЯНДЕКС *****************************
$ya_fbs_catalog = get_ostatki_yandex ($yam_token, $campaignId_FBS, $ya_fbs_catalog); // цепояем остатки
//*****************************  Достаем фактически заказанные товары YANDEX *****************************
$ya_fbs_catalog = get_new_zakazi_yandex ($yam_token, $campaignId_FBS, $ya_fbs_catalog); // цепляем продажи





print_info_sell_market ($arr_all_nomenklatura, $wb_catalog, $wbip_catalog, $ozon_catalog , $ozon_ip_catalog, $ya_fbs_catalog);
// print_r($ya_fbs_catalog);
/**************************************************************************************
 ********************************** THE END
 *****************************************************************************************/
die('');

function print_info_sell_market ($arr_all_nomenklatura, $wb_catalog, $wbip_catalog, $ozon_catalog , $ozon_ip_catalog,$ya_fbs_catalog) {

    // print_r($wb_catalog);
    
echo <<<HTML

   <!-- <h2>Сводная таблица по 4-м магазинам по продажам</h2> -->
    <table class="sell_mp_table">
    <thead>
    <tr>
        <th>арт</th>
        <th>продано<br>на ВБ(шт)</th>
        <th>сумма<br>на ВБ(руб)</th>
        <th>продано<br>на ВБ ИП(шт)</th>
        <th>сумма<br>на ВБ ИП(руб)</th>
        <th>продано<br>на озон(шт)</th>
        <th>сумма<br>на ОЗОН(руб)</th>
        <th>продано<br>на озон ИП(шт)</th>
        <th>сумма<br>на ОЗОН ИП(руб)</th>
        <th>продано<br>на Яндекс(шт)</th>
        <th>сумма<br>на Яндекс(руб)</th>
        <th>продано<br>ВЕЗДЕ(шт)</th>
        <th>Сумма<br>ВЕЗДЕ(руб)</th>
        
    
    </tr>
</thead>
    
HTML;

$all_count_items_wb =0;
$all_count_items_wb_ip =0;
$all_count_items_ozon =0;
$all_count_items_ozon_ip =0;
$all_count_items_yandex_fbs = 0;

$all_summa_items_wb = 0;
$all_summa_items_wb_ip = 0;
$all_summa_items_ozon = 0;
$all_summa_items_ozon_ip = 0;

$all_summa_items_yandex_fbs = 0;


foreach ($arr_all_nomenklatura as $item_99) {
      $article = mb_strtolower($item_99['main_article_1c']);
        
        echo "<tr>";
           
            echo "<td>".$article."</td>";
// количество товара проданного на ВБ
            $count_wb_item = find_sell_items_all ($wb_catalog , $article, 'sell_count' );
            echo "<td><b>".$count_wb_item."</b></td>";
// СУММА товара проданного на ВБ
        $summa_wb_item = round((find_sell_items_all ($wb_catalog , $article, 'sell_summa' ))/100,0);
        $summa_wb_item_text = number_format($summa_wb_item , 0);
        echo "<td>".$summa_wb_item_text."</td>";

// количество товара проданного на ВБ ИП
            $count_wb_ip_item = find_sell_items_all ($wbip_catalog, $article, 'sell_count'  );
            echo "<td>".$count_wb_ip_item."</td>";
// СУММА товара проданного на ВБ
        $summa_wb_ip_item = round((find_sell_items_all ($wbip_catalog , $article, 'sell_summa' ))/100,0);
        $summa_wb_ip_item_text = number_format($summa_wb_ip_item , 0);
        echo "<td>".$summa_wb_ip_item_text."</td>";

// количество товара проданного на ОЗОН
            $count_ozon_item = find_sell_items_all ($ozon_catalog , $article, 'sell_count'  );
            echo "<td>".$count_ozon_item."</td>";

// СУММА товара проданного на ОЗОН
        $summa_ozon_item = round((find_sell_items_all ($ozon_catalog , $article, 'sell_summa' )),0);
        $summa_ozon_item_text = number_format($summa_ozon_item , 0);
        echo "<td>".$summa_ozon_item_text."</td>";

// количество товара проданного на ОЗОН ИП    
            $count_ozon_ip_item = find_sell_items_all ($ozon_ip_catalog , $article, 'sell_count'  );
            echo "<td>".$count_ozon_ip_item."</td>";
// СУММА товара проданного на ОЗОН
        $summa_ozon_ip_item = round((find_sell_items_all ($ozon_ip_catalog , $article, 'sell_summa' )),0);
        $summa_ozon_ip_item_text = number_format($summa_ozon_ip_item , 0);
        echo "<td>".$summa_ozon_ip_item_text."</td>";

// количество товара проданного на Яндекс
$count_yandex_fbs_item = find_sell_items_all ($ya_fbs_catalog , $article, 'sell_count'  );
echo "<td>".$count_yandex_fbs_item."</td>";
// СУММА товара проданного на ОЗОН
$summa_yandex_fbs_item = round((find_sell_items_all ($ya_fbs_catalog , $article, 'sell_summa' )),0);
$summa_yandex_fbs_item_text = number_format($summa_yandex_fbs_item , 0);
echo "<td>".$summa_yandex_fbs_item_text."</td>";






// количество товара проданного ВЕЗДЕ
            $summa_all_mp = $count_wb_item + $count_wb_ip_item + $count_ozon_item + $count_ozon_ip_item + $count_yandex_fbs_item;
            $summa_money_all_mp = $summa_wb_item + $summa_wb_ip_item + $summa_ozon_item + $summa_ozon_ip_item +$summa_yandex_fbs_item ;
            $summa_money_all_mp_text = number_format($summa_money_all_mp , 0);
            echo "<td>".$summa_all_mp."</td>";
            echo "<td>".$summa_money_all_mp_text."</td>";

// Подсчитываем обзие суммы
// количетсва
 $all_count_items_wb += $count_wb_item;      
 $all_count_items_wb_ip += $count_wb_ip_item;      
 $all_count_items_ozon += $count_ozon_item;      
 $all_count_items_ozon_ip += $count_ozon_ip_item;
 $all_count_items_yandex_fbs+= $count_yandex_fbs_item; 

// стоимость
 $all_summa_items_wb += $summa_wb_item;      
 $all_summa_items_wb_ip += $summa_wb_ip_item;      
 $all_summa_items_ozon += $summa_ozon_item;      
 $all_summa_items_ozon_ip += $summa_ozon_ip_item;  
 $all_summa_items_yandex_fbs+=   $summa_yandex_fbs_item;
    echo "</tr>";
}

$all_summa_items_wb_text = number_format($all_summa_items_wb , 0);       
$all_summa_items_wb_ip_text = number_format($all_summa_items_wb_ip , 0);      
$all_summa_items_ozon_text = number_format($all_summa_items_ozon , 0);      
$all_summa_items_ozon_ip_text = number_format($all_summa_items_ozon_ip , 0);
$all_summa_items_yandex_fbs_text = number_format($all_summa_items_yandex_fbs , 0);

// ПРОДАНО ВЕЗДЕ 
$all_sell_count_all_shops = 0;
$all_sell_count_all_shops = $all_count_items_wb + $all_count_items_wb_ip + $all_count_items_ozon + $all_count_items_ozon_ip
                            +  $all_count_items_yandex_fbs;

$all_sell_summa_all_shops = 0;
$all_sell_summa_all_shops = $all_summa_items_wb + $all_summa_items_wb_ip + $all_summa_items_ozon + $all_summa_items_ozon_ip  
                             +   $all_summa_items_yandex_fbs;
$all_sell_summa_all_shops_text = number_format($all_sell_summa_all_shops , 0);

echo <<<HTML
     <tr>
        <td>ИТОГО</td>
        <td>$all_count_items_wb</td>
        <td>$all_summa_items_wb_text</td>
        <td>$all_count_items_wb_ip</td>
        <td>$all_summa_items_wb_ip_text</td>
        <td>$all_count_items_ozon</td>
        <td>$all_summa_items_ozon_text </td>
        <td>$all_count_items_ozon_ip</td>
        <td>$all_summa_items_ozon_ip_text</td>

        <td>$all_count_items_yandex_fbs</td>
        <td>$all_summa_items_yandex_fbs_text</td>


        <td>$all_sell_count_all_shops</td>
        <td>$all_sell_summa_all_shops_text</td>
        
 
 </tr>
HTML;







     echo "</table>";
    }
    
// функция нахоит нужный товар в перечене номенклатуры, и возвращает его проданное количество и сумму
function find_sell_items_all ($mp_catalog , $article, $parametr_poiska )  {
        $count_item = 0;
        foreach ($mp_catalog as $mp_item) {
            // echo "<br>--".mb_strtolower($mp_item['main_article'])."*****".$article."--<br>";
            if (mb_strtolower($mp_item['main_article']) == $article) {
                 isset($mp_item['sell_count'])?$count_item = $count_item + $mp_item[$parametr_poiska]:$Z=1;
               } else  {
                $count_item = $count_item +0;
               }
            }
    return $count_item;
}


