<?php


echo '<link rel="stylesheet" href="css/see_all_sells.css">';
echo '<link rel="stylesheet" href="css/test.css">';


echo "<h2 class=\"center\">Период запроса с ($date_start) по  ($date_end)</h2>";

echo "<div class=\"table-wrapper\">";
echo "<table class=\"inventory-table\">";

echo "<thead class=\"color_orange\">";
echo "<tr>";
echo "<td>АРТИКУЛ</td>";

echo "<td>Итого</td>";
echo "<td>Маркет</td>";
// Выводим в шапке все даты
foreach ($arr_dates as $dates) {
    $timestamp = strtotime($dates);
    $date =  date("d.m", $timestamp);
    $date_day_week = date("D", $timestamp);
    echo "<td>$date <br>$date_day_week</td>";
}

echo "</tr>";
echo "</thead>";


echo "<pre>";
// print_r($arr_sells_for_print);
// print_r($arr_sells_for_print['2026-03-30']['85400-ч']);

// echo "</pre>";



foreach ($arr_article as $article) {
    // выводи мне все артикулы, а только выбранные
    $priznak_vivida_articuls = 0;
    foreach ($need_article as $need_art) {
        if ($need_art == $article) {
            $priznak_vivida_articuls = 1;
        }
    }
    if ($priznak_vivida_articuls == 0) {
        continue;
    }
    // закончили выборку выводимых артикулов 
$count_string = 8;
$count_long_table = 4 + count($arr_dates);
    echo "<tr>";
    echo "<td class = \"sticky\" rowspan = \"$count_string\">{$article}</td>";

    if (isset ($arr_sum_all_date[$article])) {
    echo "<td class = \"\" rowspan = \"$count_string\">$arr_sum_all_date[$article]</td>";
    } else {
        echo "<td class = \"\" rowspan = \"$count_string\">-</td>";
    }


    $summa_artikula_za_den = 0;
    // ПРОДАЖИ ФБО Озон ООО**********************************************
    echo "<td class = \"fbo_sell_tovari\">Озон ООО ФБО</td>";
    foreach ($arr_dates as $date) {
        if (isset($arr_sells_for_print[$date][$article]['fbo']['ozon_anmaks'])) {
            echo "<td class = \"fbo_sell_tovari\">{$arr_sells_for_print[$date][$article]['fbo']['ozon_anmaks']}</td>";
            $summa_artikula_za_den = $summa_artikula_za_den + $arr_sells_for_print[$date][$article]['fbo']['ozon_anmaks'];
        } else {
            echo "<td class = \"fbo_sell_tovari\"> - </td>";
        }
    }
    echo "</tr>";

    // ПРОДАЖИ ФБС Озон ООО**********************************************
    echo "<td class = \"fbs_sell_tovari\">Озон ООО ФБC</td>";
    foreach ($arr_dates as $date) {
        if (isset($arr_sells_for_print[$date][$article]['fbs']['ozon_anmaks'])) {
            echo "<td class = \"fbs_sell_tovari\">{$arr_sells_for_print[$date][$article]['fbs']['ozon_anmaks']}</td>";
            $summa_artikula_za_den = $summa_artikula_za_den + $arr_sells_for_print[$date][$article]['fbs']['ozon_anmaks'];

        } else {
            echo "<td class = \"fbs_sell_tovari\"> - </td>";
        }
    }
   echo "</tr>";



// ПРОДАЖИ ФБО Озон ИП**********************************************
    echo "<td class = \"fbo_sell_tovari\">Озон ИП ФБО</td>";
    foreach ($arr_dates as $date) {
        if (isset($arr_sells_for_print[$date][$article]['fbo']['ozon_ip_zel'])) {
            echo "<td class = \"fbo_sell_tovari\">{$arr_sells_for_print[$date][$article]['fbo']['ozon_ip_zel']}</td>";
            $summa_artikula_za_den = $summa_artikula_za_den + $arr_sells_for_print[$date][$article]['fbo']['ozon_ip_zel'];

        } else {
            echo "<td class = \"fbo_sell_tovari\"> - </td>";
        }
    }
    echo "</tr>";

    // ПРОДАЖИ ФБС Озон ИП **********************************************
    echo "<td class = \"fbs_sell_tovari\">Озон ИП ФБC</td>";

    foreach ($arr_dates as $date) {
        if (isset($arr_sells_for_print[$date][$article]['fbs']['ozon_ip_zel'])) {
            echo "<td class = \"fbs_sell_tovari\">{$arr_sells_for_print[$date][$article]['fbs']['ozon_ip_zel']}</td>";
            $summa_artikula_za_den = $summa_artikula_za_den + $arr_sells_for_print[$date][$article]['fbs']['ozon_ip_zel'];
             
        } else {
            echo "<td class = \"fbs_sell_tovari\"> - </td>";
        }
    }
   echo "</tr>";

    // ПРОДАЖИ ФБС ВБ ООО **********************************************
    echo "<td class = \"fbs_sell_tovari\">WB ООО</td>";
    foreach ($arr_dates as $date) {
        if (isset($arr_sells_for_print[$date][$article]['fbs']['wb_anmaks'])) {
            echo "<td class = \"fbs_sell_tovari\">{$arr_sells_for_print[$date][$article]['fbs']['wb_anmaks']}</td>";
            $summa_artikula_za_den = $summa_artikula_za_den + $arr_sells_for_print[$date][$article]['fbs']['wb_anmaks'];

        } else {
            echo "<td class = \"fbs_sell_tovari\"> - </td>";
        }
    }
   echo "</tr>";

    // ПРОДАЖИ ФБС ВБ ИП **********************************************
    echo "<td class = \"fbs_sell_tovari\">WB ИП</td>";
    foreach ($arr_dates as $date) {
        if (isset($arr_sells_for_print[$date][$article]['fbs']['wb_ip_zel'])) {
            echo "<td class = \"fbs_sell_tovari\">{$arr_sells_for_print[$date][$article]['fbs']['wb_ip_zel']}</td>";
            $summa_artikula_za_den = $summa_artikula_za_den + $arr_sells_for_print[$date][$article]['fbs']['wb_ip_zel'];

        } else {
            echo "<td class = \"fbs_sell_tovari\"> - </td>";
        }
    }
   echo "</tr>";

    // ПРОДАЖИ ЯНДЕКС **********************************************
    echo "<td class = \"fbs_sell_tovari\">Яндекс</td>";
    foreach ($arr_dates as $date) {
        if (isset($arr_sells_for_print[$date][$article]['fbs']['ya_anmaks_fbs'])) {
            echo "<td class = \"fbs_sell_tovari\">{$arr_sells_for_print[$date][$article]['fbs']['ya_anmaks_fbs']}</td>";
            $summa_artikula_za_den = $summa_artikula_za_den + $arr_sells_for_print[$date][$article]['fbs']['ya_anmaks_fbs'];

        } else {
            echo "<td class = \"fbs_sell_tovari\"> - </td>";
        }
    }
   echo "</tr>";

//// ИТОГО продажи за день

  echo "<td class = \"fbs_sell_tovari\">Итого за день</td>";
    foreach ($arr_dates as $date) {
        if (isset($arr_sells_for_print[$date][$article]['summa'])) {
            echo "<td class = \"fbs_sell_tovari\"><b>{$arr_sells_for_print[$date][$article]['summa']}</b></td>";
            
        } else {
            echo "<td class = \"fbs_sell_tovari\"> - </td>";
        }
    }
   echo "</tr>";

   

        // echo "<td class = \"fbs_sell_tovari\" colspan = \"$count_long_table\" > - </td>";
    

    echo "<tr class=\"thick_line\">";




    echo "</tr>";
}

echo "</tr>";
echo "</table>";
echo "</div>";