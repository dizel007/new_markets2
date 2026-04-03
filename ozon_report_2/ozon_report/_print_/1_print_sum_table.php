<?php
// echo "<h1><a href=\"https://seller.ozon.ru/app/finances/balance?tab=IncomesExpenses\" target=\"_blank\"> Ссылка на выплаты озона </a></h1>";
// Начинаем отрисовывать таблицу 
echo <<<HTML
<div class="h80procentov">
<div class="table-container">
 
<h3 class = "shapka_tabla"> Данная таблица сформирована с запрошенных данных и привидена к виду таблицы БАЛАНС на Озоне  <a href="https://seller.ozon.ru/app/finances/balance?tab=IncomesExpenses" target="_blank" class="link-btn">Ссылка Озон Выплаты</a> </h3>

<table class="financial-table">
<tr>
    <th>пп</th>
    <th>Продажи</th>
    <th>Итого (руб)</th>
    <th>Статья списания</th>
    <th>Сумма(руб)</th>
 </tr>
HTML;
$string_number=0;
foreach ($arr_for_sum_table as $key=>$data_for_print){

    // если сумма по статье НЕ РАВНО нулю то делаем строку
    if (array_sum($arr_for_sum_table[$key])!=0 ) {
    
    $string_number++;
    ($string_number % 2)?$string_number_color = "even_color": $string_number_color="oder_color";

    $count_string = count($data_for_print);
    $summ_info = number_format(round(array_sum($arr_for_sum_table[$key]),0),0,'.',',');
    ($summ_info >=0)? $color_class="positive":$color_class="negative";
    echo <<<HTML
        <tr class="" >
            <td class ="$string_number_color"  rowspan="{$count_string}">$string_number</td>
            <td class ="$string_number_color big_text" rowspan="{$count_string}">$key</td>
            <td class ="center_text $string_number_color $color_class" rowspan="{$count_string}">$summ_info</td>
           
HTML;
// $i=0;
    foreach ($data_for_print as $name_trata => $summa_trat)
        {
    //  $i++;       
    // если есть массив с номерами заказов по этим статьям затрат, то сделаем ссылку на это строку
    if (isset($arr_services_with_post_numbers[$name_trata])) {
        $a_url = 'penalty tables?expenses='.$name_trata;
            echo "<td class=\"$string_number_color subcategory\"><a href=\"$a_url\" target=\"_blank\">$name_trata</a></td>";
    } else {
            echo "<td class=\"$string_number_color subcategory\">$name_trata</td>";
    }

            ($summa_trat >=0)? $color_class="positive":$color_class="negative";

            echo "<td class =\"center_text $string_number_color $color_class\">".number_format(round($summa_trat,0),0,'.',',')."</td>";

        echo "</tr>";
      }
 }
}

// Красим нижнюю строчку "К начислению"
$string_number++;
($string_number % 2)?$string_number_color = "even_color": $string_number_color="oder_color";
($summa_k_nachisleniu >=0)? $color_class="positive":$color_class="negative";
$text_summa_k_nachisleniu = number_format(round($summa_k_nachisleniu,0),0,'.',',');
echo <<<HTML
<tr>
<td class="$string_number_color"></td>
<td class="$string_number_color big_text">К начислению </td>
<td class="$string_number_color"></td>
<td class="$string_number_color"></td>
<td class="center_text $string_number_color $color_class big_text">$text_summa_k_nachisleniu </td>
</tr>

</table>
</div>
</div>

HTML;




