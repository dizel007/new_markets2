<?php 

// CSS цепляем
echo "<link rel=\"stylesheet\" href=\"css/main_ozon_reports.css\">";



echo "<table class=\"fl-table\">";

// ШАПКА ТАблицы
echo "<tr>";
    // echo "<th style=\"width:10%\">Наименование</th>";
    echo "<th>Артикл</th>";
    echo "<th>Кол-во<br>продано<br>(шт)</th>";
    echo "<th>Цена<br>для пок-ля<br>(руб)</th>";
    echo "<th>Сумма<br>продаж<br>(руб)</th>";
    echo "<th>Комиссия<br>Озон<br>(руб)</th>";
    echo "<th>Логистика<br>(руб)</th>";
    echo "<th>Сборка<br>(руб)</th>";
    echo "<th>Посл.миля<br>(руб)</th>";
    echo "<th>Хранение<br>утилизация<br>(руб)</th>";
    echo "<th>Удерж<br>за недовл<br>(руб)</th>";
    echo "<th>Эквайринг<br>(руб)</th>";
    echo "<th>Возвраты<br>(шт)</th>";
    echo "<th>Возвраты<br>(руб)</th>";



echo "</tr>";


foreach ($arr_article as $key=>$item) {
    
    $article = get_article_by_sku_fbs($ozon_sebest, $key); // получаем артикл по СКУ

   /// ОБЩИЕ СУММЫ 
    @$count +=$item['count']; // количеств проданных товарв продажи 
    @$accruals_for_sale +=$item['accruals_for_sale']; // сумма продажи 
    

    @$amount_bez_equaring = $item['amount'] + $item['amount_ecvairing']; // сумма к выплате (уже без эквайринг) 
    @$amount +=$amount_bez_equaring; // сумма к вылате 
    
    
  
    @$one_shtuka = round($amount_bez_equaring/$item['count'],2); // цена за штуку нам в карман (минус эквайринг)
    @$one_shtuka_buyer = round($item['accruals_for_sale']/$item['count'],2); // цена за штуку для покупателя



    @$sale_commission +=$item['sale_commission']; // Общая стоимость 
    @$logistika +=$item['logistika']; // Общая стоимость 
    @$sborka +=$item['sborka']; // Общая стоимость 
    @$lastMile +=$item['lastMile']; // Общая стоимость 
   

    @$amount_hranenie +=$item['amount_hranenie']; // общая стоимость хранения 
    @$amount_ecvairing +=$item['amount_ecvairing']; // Общая стоимость эквайринга
    @$compensation += $item['compensation'] ; // Общая стоимость недовлажений
    @$amount_vozrat +=$item['amount_vozrat']; // Общая стоимость возвратов
    


    echo "<tr>";

        // if (isset($item['name'])){echo "<td>".$item['name']."</td>";}else{echo "<td>"."</td>";}
        if (isset($article)){echo "<td><b>".$article."</b></td>";}else{echo "<td>"."</td>";}
        if (isset($item['count'])){echo "<td>".$item['count']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['amount'])){echo "<td>".$item['accruals_for_sale']."<br>".$one_shtuka_buyer."</td>";}else{echo "<td>"."</td>";} // ценя для покупателья
       
        if (isset($item['amount'])){echo "<td>".$amount_bez_equaring."<br>".$one_shtuka."</td>";}else{echo "<td>"."</td>";}
       
        if (isset($item['sale_commission'])){echo "<td>".$item['sale_commission']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['logistika'])){echo "<td>".$item['logistika']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['sborka'])){echo "<td>".$item['sborka']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['lastMile'])){echo "<td>".$item['lastMile']."</td>";}else{echo "<td>"."</td>";}

        if (isset($item['amount_hranenie'])){echo "<td>".$item['amount_hranenie']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['compensation'])){echo "<td>".$item['compensation']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['amount_ecvairing'])){echo "<td>".$item['amount_ecvairing']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['count_vozvrat'])){echo "<td>".$item['count_vozvrat']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['amount_vozrat'])){echo "<td>".$item['amount_vozrat']."</td>";}else{echo "<td>"."</td>";}


    echo "</tr>";


}

// СТРОКА ИТОГО ТАблицы
echo "<tr>";
    echo "<td></td>"; // Наименование
    echo "<td>$count</td>"; // Количество
    echo "<td>$accruals_for_sale</td>"; // общая сумма
    echo "<td>$amount</td>"; // общая сумма
    if (isset($sale_commission)){echo "<td>".$sale_commission."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий
    if (isset($logistika)){echo "<td>".$logistika."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий
    if (isset($sborka)){echo "<td>".$sborka."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий
    if (isset($lastMile)){echo "<td>".$lastMile."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий


    if (isset($amount_hranenie)){echo "<td>".$amount_hranenie."</td>";}else{echo "<td>"."</td>";} // сумма хранения
    if (isset($compensation)){echo "<td>".$compensation."</td>";}else{echo "<td>"."</td>";} // сумма эквайринга
    if (isset($amount_ecvairing)){echo "<td>".$amount_ecvairing."</td>";}else{echo "<td>"."</td>";} // сумма эквайринга
    echo "<td></td>";
    if (isset($amount_vozrat)){echo "<td>".$amount_vozrat."</td>";}else{echo "<td>"."</td>";} // сумма возвратов



echo "</tr>";

echo "</table>";
