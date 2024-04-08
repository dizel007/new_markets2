<?php 

    @$count =0 ; // сумма продажи 
    @$amount =0; // сумма продажи 
    @$accruals_for_sale = 0; // сумма продажи 
      
/// СУММЫ ПО ФБО
    @$countFBO = 0; // сумма продажи 
    @$amountFBO = 0;//$item['amountFBO']; // сумма продажи 
    @$one_shtukaFBO = 0 ;// цена за 1 штуку ФБО
    @$sale_commissionFBO =0 ;// Общая стоимость 
    @$logistikaFBO = 0 ; // Общая стоимость 
    @$sborkaFBO =0; //+=$item['sborkaFBO']; // Общая стоимость 
    @$lastMileFBO =0; //+=$item['lastMileFBO']; // Общая стоимость 

    /// СУММЫ ПО ФБС
    @$countFBS =0; //+=$item['countFBS']; // сумма продажи 
    @$amountFBS =0; //+=$item['amountFBS']; // сумма продажи 
    @$one_shtukaFBS = 0; // цена за 1 штуку ФБC
    @$sale_commissionFBS +=$item['sale_commissionFBS']; // Общая стоимость 
    @$logistikaFBS =0; //+=$item['logistikaFBS']; // Общая стоимость 
    @$sborkaFBS =0; //+=$item['sborkaFBS']; // Общая стоимость 
    @$lastMileFBS =0; //+=$item['lastMileFBS']; // Общая стоимость 

echo "<table class=\"fl-table\">";

// ШАПКА ТАблицы
echo "<tr>";
    echo "<th>Артикл</th>";
    echo "<th>Кол-во<br>продано<br>(шт)</th>";
    echo "<th>Цена<br>для пок-ля<br>(руб)</th>";
    echo "<th>Сумма<br>продаж<br>(руб)</th>";
    
    echo "<th>Кол-во<br>продано<br>FBO(шт)</th>";
    echo "<th>Сумма<br>продаж<br>FBO(руб)</th>";
    echo "<th>Комиссия<br>Озон<br>FBO(руб)</th>";
    echo "<th>Логистика<br>FBO(руб)</th>";
    echo "<th>Сбор<br>FBO<br>(руб)</th>";
    echo "<th>Посл.миля<br>FBO(руб)</th>";



    echo "<th>Кол-во<br>продано<br>FBS(шт)</th>";
    echo "<th>Сумма<br>продаж<br>FBS(руб)</th>";
    echo "<th>Комиссия<br>Озон<br>FBS(руб)</th>";
    echo "<th>Логистика<br>FBS(руб)</th>";
    echo "<th>Сборка<br>FBS(руб)</th>";
    echo "<th>Посл.миля<br>FBS(руб)</th>";



echo "</tr>";


foreach ($arr_article as $key=>$item) {
    $article = get_article_by_sku_fbs($ozon_sebest, $key); // получаем артикл по СКУ


   /// ОБЩИЕ СУММЫ 
   @$count +=$item['count']; // количество проданнызз товаров
   
   @$amount_bez_equaring = $item['amount'] + $item['amount_ecvairing']; // сумма к выплате (уже без эквайринг) 
   @$amount +=$amount_bez_equaring; // сумма к вылате 

//    @$amount +=$item['amount']; // сумма продажи
   if (isset($item['count'])) {
   @$one_item_equaring =  -$item['amount_ecvairing'] / $item['count']; // Эквайринг за одну елиницу товара
   } else {
    @$one_item_equaring=0;
   }
   @$accruals_for_sale +=$item['accruals_for_sale']; // сумма продажи 
   
   
//    @$one_shtuka = round($item['amount']/$item['count'],2); // цена за штуку нам в карман (минус эквайринг)
   @$one_shtuka = round($amount_bez_equaring/$item['count'],2); // цена за штуку нам в карман (минус эквайринг)


   @$one_shtuka_buyer = round($item['accruals_for_sale']/$item['count'],2); // цена за штуку для покупателя
      
/// СУММЫ ПО ФБО***************************************************************************************************
    @$countFBO +=$item['countFBO']; // сумма продажи 

    @$amountFBO_bez_equaring = round($item['amountFBO'] - $item['countFBO'] * $one_item_equaring, 2); // сумма продажи 
    @$amountFBO += $amountFBO_bez_equaring; // сумма продажи 


    // @$amountFBO +=$item['amountFBO']; // сумма продажи 
    @$one_shtukaFBO = round($amountFBO_bez_equaring/$item['countFBO'],2); // цена за 1 штуку ФБО


    @$sale_commissionFBO +=$item['sale_commissionFBO']; // Общая стоимость 
    @$logistikaFBO +=$item['logistikaFBO']; // Общая стоимость 
    @$sborkaFBO +=$item['sborkaFBO']; // Общая стоимость 
    @$lastMileFBO +=$item['lastMileFBO']; // Общая стоимость 

    /// СУММЫ ПО ФБС*******************************************************************************************

    @$countFBS +=$item['countFBS']; // сумма продажи 
    
    // @$amountFBS +=$item['amountFBS']; // сумма продажи 

    @$amountFBS_bez_equaring = round($item['amountFBS'] - $item['countFBS'] * $one_item_equaring ,2); // сумма продажи 
    @$amountFBS +=$amountFBS_bez_equaring; // сумма продажи 
    @$one_shtukaFBS = round($amountFBS_bez_equaring/$item['countFBS'],2); // цена за 1 штуку ФБC
    
    @$sale_commissionFBS +=$item['sale_commissionFBS']; // Общая стоимость 
    @$logistikaFBS +=$item['logistikaFBS']; // Общая стоимость 
    @$sborkaFBS +=$item['sborkaFBS']; // Общая стоимость 
    @$lastMileFBS +=$item['lastMileFBS']; // Общая стоимость 


   
    echo "<tr>";

        // if (isset($item['name'])){echo "<td>".$item['name']."</td>";}else{echo "<td>"."</td>";}
        if (isset($article)){echo "<td><b>".$article."</b></td>";}else{echo "<td>"."</td>";}
        if (isset($item['count'])){echo "<td>".$item['count']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['amount'])){echo "<td>".$item['accruals_for_sale']."<br>".$one_shtuka_buyer."</td>";}else{echo "<td>"."</td>";} // ценя для покупателья
        if (isset($item['amount'])){echo "<td>".$amount_bez_equaring."<br>".$one_shtuka."</td>";}else{echo "<td>"."</td>";}
       
/// FBO 

        if (isset($item['countFBO'])){echo "<td>".$item['countFBO']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['amountFBO'])){echo "<td>".$amountFBO_bez_equaring."<br>".$one_shtukaFBO."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['sale_commissionFBO'])){echo "<td>".$item['sale_commissionFBO']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['logistikaFBO'])){echo "<td>".$item['logistikaFBO']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['sborkaFBO'])){echo "<td>".$item['sborkaFBO']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['lastMileFBO'])){echo "<td>".$item['lastMileFBO']."</td>";}else{echo "<td>"."</td>";}

/// FBS
        if (isset($item['countFBS'])){echo "<td>".$item['countFBS']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['amountFBS'])){echo "<td>".$amountFBS_bez_equaring."<br>".$one_shtukaFBS."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['sale_commissionFBS'])){echo "<td>".$item['sale_commissionFBS']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['logistikaFBS'])){echo "<td>".$item['logistikaFBS']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['sborkaFBS'])){echo "<td>".$item['sborkaFBS']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['lastMileFBS'])){echo "<td>".$item['lastMileFBS']."</td>";}else{echo "<td>"."</td>";}




    echo "</tr>";


}

// СТРОКА ИТОГО ТАблицы
echo "<tr>";
echo "<td></td>"; // Наименование
echo "<td>$count</td>"; // Количество
echo "<td>$accruals_for_sale</td>"; // общая сумма
echo "<td>$amount</td>"; // общая сумма
    
   

    if (isset($countFBO)){echo "<td>".$countFBO."</td>";}else{echo "<td>"."</td>";} // Количество
    if (isset($amountFBO)){echo "<td>".$amountFBO."</td>";}else{echo "<td>"."</td>";} // общая сумма
    if (isset($sale_commissionFBO)){echo "<td>".$sale_commissionFBO."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий
    if (isset($logistikaFBO)){echo "<td>".$logistikaFBO."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий
    if (isset($sborkaFBO)){echo "<td>".$sborkaFBO."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий
    if (isset($lastMileFBO)){echo "<td>".$lastMileFBO."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий


    
    echo "<td>$countFBS</td>"; // Количество
    if (isset($amountFBS)){echo "<td>".$amountFBS."</td>";}else{echo "<td>"."</td>";} // общая сумма
    if (isset($sale_commissionFBS)){echo "<td>".$sale_commissionFBS."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий
    if (isset($logistikaFBS)){echo "<td>".$logistikaFBS."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий
    if (isset($sborkaFBS)){echo "<td>".$sborkaFBS."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий
    if (isset($lastMileFBS)){echo "<td>".$lastMileFBS."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий




echo "</tr>";

echo "</table>";

