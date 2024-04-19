<?PHP
/**
 * Функция выводит таблицу по магазану
 */


 function print_info_about_market ($arr_all_nomenklatura, $wb_catalog, $wbip_catalog, $ozon_catalog , $ozon_ip_catalog, $yandex_anmaks_fbs) {

// print_r($wb_catalog);

    echo <<<HTML

<hr>
<h1>Сводная таблица по 4-м магазинам по продажам</h1>
<table class="prods_table">

<tr>
    <td>пп</td>
    <td>арт</td>
    <td>продано на ВБ</td>
    <td>продано на ВБ ИП</td>
    <td>продано на озон</td>
    <td>продано на озон ИП</td>
    <td>продано на Яндекс</td>
    <td>продано ВЕЗДЕ</td>
    

</tr>

HTML;

    foreach ($arr_all_nomenklatura as $item_99) {
        $article = mb_strtolower($item_99['main_article_1c']);
    
    echo "<tr>";
        echo "<td>".""."</td>";
        echo "<td>".$article."</td>";

        $count_wb_item = find_sell_items ($wb_catalog , $article );
        echo "<td>".$count_wb_item."</td>";

        $count_wb_ip_item = find_sell_items ($wbip_catalog , $article );
        echo "<td>".$count_wb_ip_item."</td>";

        $count_ozon_item = find_sell_items ($ozon_catalog , $article );
        echo "<td>".$count_ozon_item."</td>";

        $count_ozon_ip_item = find_sell_items ($ozon_ip_catalog , $article );
        echo "<td>".$count_ozon_ip_item."</td>";

        $count_yandex_fbs_item = find_sell_items ($yandex_anmaks_fbs , $article );
        echo "<td>".$count_yandex_fbs_item."</td>";

        $summa_all_mp = $count_wb_item + $count_wb_ip_item + $count_ozon_item + $count_ozon_ip_item + $count_yandex_fbs_item;
        echo "<td>".$summa_all_mp."</td>";
   
    echo "</tr>";
 }

 echo "</table>";
}




function find_sell_items ($mp_catalog , $article )  {
    $count_item = 0;
    foreach ($mp_catalog as $mp_item) {
        // echo "<br>--".mb_strtolower($mp_item['main_article'])."*****".$article."--<br>";
        if (mb_strtolower($mp_item['main_article']) == $article) {
             isset($mp_item['sell_count'])?$count_item = $count_item + $mp_item['sell_count']:$Z=1;
           } else  {
            $count_item = $count_item +0;
           }
        }
return $count_item;
}


function find_sell_yandex_items ($mp_catalog , $article )  {
    print_r($mp_catalog);
    $count_item = 0;
    foreach ($mp_catalog as $mp_item) {
        // echo "<br>--".mb_strtolower($mp_item['main_article'])."*****".$article."--<br>";
        if (mb_strtolower($mp_item['main_article']) == $article) {
             isset($mp_item['sell_count'])?$count_item = $count_item + $mp_item['sell_count']:$Z=1;
           } else  {
            $count_item = $count_item +0;
           }
        }
return $count_item;
}
