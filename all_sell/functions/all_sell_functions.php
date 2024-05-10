<?php

/**************************************************************************************
 ****** Выводим таблицу с данными на экран ***********************
 *****************************************************************************************/
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



function print_table_with_gabariti_mp($arr_all_nomenklatura, $mp_catalog, $shop_name)  {

    // print_r($arr_all_nomenklatura);
    
echo <<<HTML
<div class="sell_mp_table">
    <h2 class ="center">Таблица продаж с габаритами для $shop_name</h2>
     
    <table class="V_mp_table">
     <thead>


     <tr>
         <th>арт</th>
         <th>продано (шт)</th>
         <th>объем в <br>литрах(шт)</th>
         <th>объем</th>
         <th>объем накопленный</th>
         <th>Кол-во паллет</th>
        
     
     </tr>
 </thead>
HTML;

$V_palleti = 1200*1000*1800/1000000;
$V_postavki = 0;
$kolvo_pallet = 0;
foreach ($mp_catalog as $item) {
if (!isset($item['sell_count'])) {
    continue;
}
echo "<tr>";
    echo "<td>".$item['main_article']."</td>";
    echo "<td>".$item['sell_count']."</td>";
    $value = round(return_obiom_tovara ($arr_all_nomenklatura, $item['main_article']),2);
    echo "<td>".$value."</td>";
    echo "<td>".$value*$item['sell_count']."</td>";
    $V_postavki += $value*$item['sell_count'];
    echo "<td>".$V_postavki."</td>";
    $kolvo_pallet = round($V_postavki/$V_palleti,2);
    echo "<td>".$kolvo_pallet."</td>";
echo "</tr>";
}


echo "</table>";
echo "</div>";
}



//// Функция возвращает обеъем товара выбранного артикула
function return_obiom_tovara ($arr_all_nomenklatura, $article) {
    $value = 0;
    foreach($arr_all_nomenklatura as $item) {
        if(mb_strtolower($item['main_article_1c']) == mb_strtolower($article)) {
            $value = $item['dlina']*$item['shirina']*$item['visota']/1000000;
            break;
        }
    }

    return $value;
}





    
// функция нахоит нужный товар в перечене номенклатуры, и возвращает его проданное количество и сумму
function find_sell_items_all ($mp_catalog , $article, $parametr_poiska )  {
        $count_item = 0;
        foreach ($mp_catalog as $mp_item) {
            // echo "<br>--".mb_strtolower($mp_item['main_article'])."*****".$article."--<br>";
            if (mb_strtolower($mp_item['main_article']) == $article) {
                 isset($mp_item['sell_count'])?$count_item = $count_item + $mp_item[$parametr_poiska]:$Z=1;
               } else  {
                $count_item = $count_item + 0;
               }
            }
    return $count_item;
}

// Функция ищет есть ли хоть один проданный товар в магазине
function find_sell_zakaz_in_mp($mp_catalog) {
    
    foreach ($mp_catalog as $item) {
        if (isset($item['sell_count'])) return true;
    }
return false;
}