<?php

/***************************************************************
 *  Тут отрисовываем таблицу с неразобранными статьями затрат
 *****************************************************************/
require_once '../../vendor/autoload.php';
require_once "../../main_info.php";

// отправляем письмо с данными которых у нас нет
$body_Email = json_encode($alarm_index_array, JSON_UNESCAPED_UNICODE);



// Отправляем себе письмо

try {
    send_many_emails('dizel007@yandex.ru', 'TradeStorm новые статьи затрат', $body_Email, $mail_for_send_letter, $mail_pass);
} catch (Exception $e) {
    echo "Произошла ошибка: " . $e->getMessage();
}





// CSS цепляем
echo "<link rel=\"stylesheet\" href=\"css/alarm_table.css\">";

// Начинаем отрисовывать таблицу 
echo <<<HTML
<div class="table-container">
<table class="not_find_data_table">
<tr class ="">
    <th >пп</th>
    <th>Тип массива</th>

    <th>Индекс списания</th>
    <th>Сумма(руб)</th>
    <th>Общая сумма</th>
    
 
</tr>
HTML;

$string_munber=0;
foreach ($alarm_index_array as $key=>$data_for_print){
    $string_munber++;
    $count_string = count($data_for_print);
    echo <<<HTML
            <tr>
            <td rowspan="{$count_string}">$string_munber</td>
            <td rowspan="{$count_string}">$key</td>
           
HTML;
$i=0;
$summa_stati = 0;
    foreach ($data_for_print as $name_trata => $summa_trat)
        {
        $i++;      
    echo "<td class=\"statia_rashodov\">$name_trata</td>";
    echo "<td>".number_format(round($summa_trat,0),0,'.',',')."</td>";
    $summa_stati = $summa_stati + $summa_trat;
    if ( $i == 1 ) { 
        echo     "<td rowspan=\"$count_string\">".number_format(round($summa_stati,0),0,'.',',')."</td>";
    }
        echo "</tr>";
    
  
   }
 }



echo "<tr>";
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td>ИТОГО НЕ РАЗОБРАНО :</td>";
echo "<td>". number_format(round($summa_ne_naidennih_statei,0),0,'.',',')."</td>";
echo "</tr>";

echo "</table>";
echo "</div>";
// die();



