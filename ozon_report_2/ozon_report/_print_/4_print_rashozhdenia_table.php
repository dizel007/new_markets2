<?php
// print_r($arr_for_sum_table);
// CSS цепляем
echo <<<HTML
<!-- <link rel="stylesheet" href="css/proverka_table.css"> -->

 <!-- Начинаем отрисовывать таблицу  -->
  <div class="h80procentov">
  <div class="table-container">
<h3 class = "shapka_tabla">В таблице сравниваем расчитанные поартикульно суммы с суммами из таблицы баланса озон (из-за округлений могут быть некоторые расхождения)</h3>
<table class="proverka-table">
<tr>
    <th>пп</th>
    <th>Проверяемый<br>столбец</th>
    <th>Итого<br>расчет(руб)</th>
    <th>Статья списания</th>
    <th>Сумма<br>(руб)</th>
    <th>Итого с таблицы выплат(руб)</th>
    <th>Расхождение<br>(руб)</th>
</tr>
HTML;


/*******************************************************************************************************/
/// Цена для покупателя 
/*******************************************************************************************************/
$pp=0;

$pp++;

$summa_price_for_pokupatel = $arr_for_sum_table['Продажи']['-'] + $arr_for_sum_table['Возвраты']['-'];
$rashozhdenie_price_for_pokupatel = $arr_summ['Цена для покупателя'] - $summa_price_for_pokupatel;
    ($summa_price_for_pokupatel >=0)? $color_class="positive":$color_class="negative";
echo "<tr>";
    echo "<td rowspan =\"2\">"."$pp". "</td>";
    echo "<td rowspan =\"2\">"."Цена для покупателя". "</td>";
    echo "<td class=\"center_text $color_class\" rowspan =\"2\">".number_format(round(@$arr_summ['Цена для покупателя'],0),0,'.',',') . "</td>";
    
    echo "<td>". 'Продажи'. "</td>";
    echo "<td class=\"center_text positive\">". number_format(round(@$arr_for_sum_table['Продажи']['-'],0),0,'.',',') . "</td>";
    echo "<td class=\"center_text positive\" rowspan =\"2\">".number_format(round($summa_price_for_pokupatel,0),0,'.',','). "</td>";
    echo "<td class=\"center_text\" rowspan =\"2\">".number_format(round($rashozhdenie_price_for_pokupatel,0),0,'.',','). "</td>";
echo "</tr>";

// строка возвраты 
echo "<tr>";

    echo "<td class=\"center_text\">". "Возвраты". "</td>";
    echo "<td class=\"center_text negative\">". number_format(round(@$arr_for_sum_table['Возвраты']['-'],0),0,'.',',') . "</td>";

echo "</tr>";


/*******************************************************************************************************/
// Комиссия озона
/*******************************************************************************************************/
$pp++;
echo "<tr>";
    echo "<td>"."$pp". "</td>";
    echo "<td>"."Комиссия озона". "</td>";
    echo "<td class=\"center_text negative\">".number_format(round(@$arr_summ['Комиссия озона'],0),0,'.',',') . "</td>";
    echo "<td>". 'Комиссия озона'. "</td>";
    echo "<td class=\"center_text negative\">". number_format(round(@$arr_for_sum_table['Вознаграждение Ozon']['-'],0),0,'.',',') . "</td>";
    echo "<td class=\"center_text negative\">". number_format(round(@$arr_for_sum_table['Вознаграждение Ozon']['-'],0),0,'.',',') . "</td>";
    $rashozhdenie_price_ozon_commision = $arr_summ['Комиссия озона'] - $arr_for_sum_table['Вознаграждение Ozon']['-'];
    echo "<td class=\"center_text\">".number_format(round($rashozhdenie_price_ozon_commision,0),0,'.',','). "</td>";
echo "</tr>";

/*******************************************************************************************************/
// Логистика - Услуги агентов
/*******************************************************************************************************/

$arr_logistiki_for_print=[];
if (isset($arr_for_sum_table['Услуги доставки']['Прямая логистика'])) {$arr_logistiki_for_print['Услуги доставки/Прямая логистика'] = $arr_for_sum_table['Услуги доставки']['Прямая логистика'];}
if (isset($arr_for_sum_table['Услуги доставки']['Обратная логистика'])) {$arr_logistiki_for_print['Услуги доставки/Обратная логистика'] = $arr_for_sum_table['Услуги доставки']['Обратная логистика'];}
if (isset($arr_for_sum_table['Услуги доставки']['Обработка отправления Drop-off'])) {$arr_logistiki_for_print['Услуги доставки/Обработка отправления Drop-off'] = $arr_for_sum_table['Услуги доставки']['Обработка отправления Drop-off'];}
if (isset($arr_for_sum_table['Услуги агентов']['Выдача товара'])) {$arr_logistiki_for_print['Услуги агентов/Выдача товара'] = $arr_for_sum_table['Услуги агентов']['Выдача товара'];}
if (isset($arr_for_sum_table['Услуги агентов']['Доставка до места выдачи'])) {$arr_logistiki_for_print['Услуги агентов/Доставка до места выдачи'] = $arr_for_sum_table['Услуги агентов']['Доставка до места выдачи'];}
if (isset($arr_for_sum_table['Услуги агентов']['Обработка возвратов, отмен и невыкупов партнёрами'])) {$arr_logistiki_for_print['Услуги агентов/Обработка возвратов, отмен и невыкупов партнёрами'] = $arr_for_sum_table['Услуги агентов']['Обработка возвратов, отмен и невыкупов партнёрами'];}


 if (count($arr_logistiki_for_print) > 0 ) {
$pp++;
$summa_logistika = array_sum($arr_logistiki_for_print);
$rashozhdenie_logistika = round($summa_logistika  - $arr_summ['Логистика'],0);
$count_string = count($arr_logistiki_for_print);

$log_sum = number_format(round(@$arr_summ['Логистика'],0),0,'.',',') ;
echo <<<HTML
            <tr>
            <td rowspan="{$count_string}">$pp</td>
            <td rowspan="{$count_string}">Логистика</td>
            <td  class="center_text negative" rowspan="{$count_string}">$log_sum</td>
HTML;

$i = 0;

foreach ($arr_logistiki_for_print as $key=>$data_for_print){
$i++;
    echo "<td class=\"statia_rashodov\">$key</td>";
    echo "<td class=\"center_text negative\">".number_format(round($data_for_print,0),0,'.',',')."</td>";
    if ( $i == 1 ) { 
        echo     "<td class=\"center_text negative\" rowspan=\"$count_string\">".number_format(round($summa_logistika,0),0,'.',',')."</td>";
        echo     "<td class=\"center_text \" rowspan=\"$count_string\">".number_format(round($rashozhdenie_logistika,0),0,'.',',')."</td>";
    }

    echo"</tr>";
  
   }
}

/*******************************************************************************************************/
// Сервисы
/*******************************************************************************************************/

if (isset($arr_sum_services_payment_with_SKU)) {
    $pp++;
    $summa_service = array_sum(@$arr_sum_services_payment_with_SKU) ;
    $rashozhdenie_servicei = round(@$summa_service  - @$arr_summ['Сервисы'],0);
    $count_string = count(@$arr_sum_services_payment_with_SKU) ;
    $dop_rashodi_sum = number_format(round(@$arr_summ['Сервисы'],0),0,'.',',');
    echo <<<HTML
            <tr>
            <td rowspan="{$count_string}">$pp</td>
            <td rowspan="{$count_string}">Сервисы</td>
            <td class="center_text negative" rowspan="{$count_string}">$dop_rashodi_sum</td>
HTML;

$i = 0;

foreach ($arr_sum_services_payment_with_SKU as $key=>$data_for_print){
$i++;
    echo "<td class=\"statia_rashodov\">$key</td>";
    echo "<td class=\"center_text negative\">".number_format(round($data_for_print,0),0,'.',',')."</td>";
    if ( $i == 1 ) { 
        echo     "<td class=\"center_text negative\" rowspan=\"$count_string\">".number_format(round($summa_service,0),0,'.',',')."</td>";
        echo     "<td class=\"center_text\" rowspan=\"$count_string\">".number_format(round($rashozhdenie_servicei,0),0,'.',',')."</td>";
    }

    echo"</tr>";
}

}
/*******************************************************************************************************/
// эувайринг
/*******************************************************************************************************/
if (isset($arr_for_sum_table['Услуги агентов']['Эквайринг'])) {
$pp++;
echo "<tr>";
    echo "<td>"."$pp". "</td>";
    echo "<td>"."Эквайринг". "</td>";
    echo "<td class=\"center_text negative\">".number_format(round(@$arr_summ['Эквайринг'],0),0,'.',',') . "</td>";
    echo "<td>". 'Эквайринг'. "</td>";
    echo "<td class=\"center_text negative\">". number_format(round(@$arr_for_sum_table['Услуги агентов']['Эквайринг'],0),0,'.',',') . "</td>";
    echo "<td class=\"center_text negative\">". number_format(round(@$arr_for_sum_table['Услуги агентов']['Эквайринг'],0),0,'.',',') . "</td>";
    $rashozhdenie_price_aquaring = $arr_summ['Эквайринг'] - $arr_for_sum_table['Услуги агентов']['Эквайринг'];
        echo "<td class=\"center_text\">".number_format(round($rashozhdenie_price_aquaring,0),0,'.',','). "</td>";
echo "</tr>";

}


/*******************************************************************************************************/
// Дополнительные расходы
/*******************************************************************************************************/

if ((isset ($arr_sum_services_payment)) OR (isset ($arr_for_compensation_copy_for_check_table)))  {
$pp++;
   $summa_dop_rashodi = 0;
   $count_string = 0;
    if (isset ($arr_sum_services_payment)){
        $summa_dop_rashodi += array_sum($arr_sum_services_payment);
        $count_string += count(@$arr_sum_services_payment);
    }
    if (isset ($arr_for_compensation_copy_for_check_table)){ 
        $summa_dop_rashodi += array_sum($arr_for_compensation_copy_for_check_table);
            $count_string += count(@$arr_for_compensation_copy_for_check_table);
    }


$rashozhdenie_dop_rashodi = round($summa_dop_rashodi  - @$arr_summ['Сумма распределения доп.услуг'],0);

$dop_rashodi_sum = number_format(round(@$arr_summ['Сумма распределения доп.услуг'],0),0,'.',',') ;  

echo <<<HTML
            <tr>
            <td rowspan="{$count_string}">$pp</td>
            <td rowspan="{$count_string}">Дополнительные расходы</td>
            <td  class="center_text negative" rowspan="{$count_string}">$dop_rashodi_sum</td>
HTML;

$i = 0;
if (isset($arr_sum_services_payment)) {

    foreach ($arr_sum_services_payment as $key=>$data_for_print){
    $i++;
        echo "<td class=\"statia_rashodov\">$key</td>";
        echo "<td class=\"center_text negative\">".number_format(round($data_for_print,0),0,'.',',')."</td>";
        if ( $i == 1 ) { 
            echo     "<td class=\"center_text negative\" rowspan=\"$count_string\">".number_format(round($summa_dop_rashodi,0),0,'.',',')."</td>";
            echo     "<td class=\"center_text\" rowspan=\"$count_string\">".number_format(round($rashozhdenie_dop_rashodi,0),0,'.',',')."</td>";
        }

        echo"</tr>";
    }
}
if (isset($arr_for_compensation_copy_for_check_table)) {
    
    foreach ($arr_for_compensation_copy_for_check_table as $key=>$data_for_print){
    $i++;
        echo "<td class=\"statia_rashodov\">$key</td>";
        echo "<td class=\"center_text negative\">".number_format(round($data_for_print,0),0,'.',',')."</td>";
        if ( $i == 1 ) { 
            echo     "<td class=\"center_text negative\" rowspan=\"$count_string\">".round($summa_dop_rashodi,0)."</td>";
            echo     "<td class=\"center_text\" rowspan=\"$count_string\">".round($rashozhdenie_dop_rashodi,0)."</td>";
        }

        echo"</tr>";

    }

}
}
echo "</table>";
echo "</div>";
echo "</div>";
/*******************************************************************************************************/
/*******************************************************************************************************/
/*******************************************************************************************************/





