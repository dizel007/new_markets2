<?php 
$arr_all_nomenklatura = select_all_nomenklaturu($pdo);
// echo "<pre>";
// print_r($arr_all_nomenklatura);
// echo "</pre>";
// CSS цепляем
echo "<link rel=\"stylesheet\" href=\"css/main_ozon_reports.css\">";



echo "<table class=\"real_money fl-table\">";

// ШАПКА ТАблицы
echo "<tr>";
    echo "<th>Артикл</th>";
    echo "<th>Кол-во<br>продано<br>(шт)</th>";
    echo "<th>Цена<br>для пок-ля<br>(руб)</th>";
    echo "<th>Сумма<br>продаж<br>(руб)</th>";
    echo "<th>% от общей<br>суммы продаж<br>(руб)</th>";
    echo "<th>Затраты на<br>доп.услуги<br>(руб)</th>";
    echo "<th>Цена за вычетом <br>всего (руб)</th>";
    echo "<th>Желаемая цена<br>(руб)</th>";
    echo "<th>Себестоимость</th>";
    echo "<th>Дельта с одной штуки<br>(руб)</th>";

    echo "<th>Заработали<br>с артикула</th>";
echo "</tr>";


foreach ($arr_article as $key=>$item) {
    $desired_price = 0; // Обнуляем желаемую цену
    $need_up_price = 0; // ОБнуляем дельту между ценой за штуку и желаемой ценой
   

    $article = get_article_by_sku_fbs($ozon_sebest, $key); // получаем артикл по СКУ
   /// ОБЩИЕ СУММЫ 
   
   $article_1C =  get_main_article_by_sku_fbs($ozon_sebest, $key);
    

    @$amount_bez_equaring = $item['amount'] + $item['amount_ecvairing']; // сумма к выплате (уже без эквайринг) 
  
    
    
  
    @$one_shtuka = round($amount_bez_equaring/$item['count'],2); // цена за штуку нам в карман (минус эквайринг)
    @$one_shtuka_buyer = round($item['accruals_for_sale']/$item['count'],2); // цена за штуку для покупателя



    $one_proc_ot_vsey_summi = round($amount/100,2);
    $proc_item_ot_vsey_summi = round($amount_bez_equaring/$one_proc_ot_vsey_summi,2);
    $summa_procentov_for_control = @$summa_procentov_for_control +  $proc_item_ot_vsey_summi ;
 
// Распределяем сумму дополнительных услуг в процентоном соотношении
   if ($proc_item_ot_vsey_summi > 0.1)  {
        $dop_uslugi_each_item = round(($dop_uslugi/100*$proc_item_ot_vsey_summi),2);
    }else {
        $dop_uslugi_each_item = 0;
    }

// сумма всех доп. услуг
    $summa_dop_uslug_temp = @$summa_dop_uslug_temp + $dop_uslugi_each_item ; 

// Наша цена за вычетом всех услуг
    $our_real_price_all_article  = $amount_bez_equaring + $dop_uslugi_each_item;
    if (isset($item['count']))  {
        $our_real_price_all_article_one_shtuka = round($our_real_price_all_article / $item['count'] ,2);
    }else {
        $our_real_price_all_article_one_shtuka = 0;
    }

    




    echo "<tr>";

        // if (isset($item['name'])){echo "<td>".$item['name']."</td>";}else{echo "<td>"."</td>";}
        if (isset($article)){echo "<td><b>".$article."</b></td>";}else{echo "<td>"."</td>";}
        if (isset($item['count'])){echo "<td>".$item['count']."</td>";}else{echo "<td>"."</td>";}
        if (isset($item['amount'])){echo "<td>".$item['accruals_for_sale']."<br>".$one_shtuka_buyer."</td>";}else{echo "<td>"."</td>";} // ценя для покупателья
       
        if (isset($item['amount'])){echo "<td>".$amount_bez_equaring."<br>".$one_shtuka."</td>";}else{echo "<td>"."</td>";}
  // // Процент проданных товаров от общей суммы 
        if (isset( $proc_item_ot_vsey_summi)){echo "<td>". $proc_item_ot_vsey_summi."</td>";}else{echo "<td>"."</td>";}
// // Распределение дополнительных услуг
        if (isset($dop_uslugi_each_item)){echo "<td>".$dop_uslugi_each_item."</td>";}else{echo "<td>"."</td>";}
// // Наша реальная цена без дополнительных
if (isset($our_real_price_all_article)){echo "<td>".$our_real_price_all_article."<br>".$our_real_price_all_article_one_shtuka."</td>";}else{echo "<td>"."</td>";}
// сумма которую мы долждны получить 
$summa_k_perevodu = @$summa_k_perevodu + $our_real_price_all_article;


/// Ищем себестоимость и желаемую цену товара
$priznak_min_price = 0;
$min_price = 0;
foreach ($arr_all_nomenklatura as $nomenclatura){

if (mb_strtolower($nomenclatura['main_article_1c']) ==  mb_strtolower($article_1C)) {
    $min_price = $nomenclatura['min_price'];
    $desired_price = $nomenclatura['main_price']; // желаемая цена товара
   
      $priznak_min_price = 1;
      
break;
    }     
} 

// Желаемая цена за товар
if ($desired_price <= $our_real_price_all_article_one_shtuka) {
    $color_desired_price = 'good_desired_price';
    $need_up_price= round($our_real_price_all_article_one_shtuka - $desired_price,0);
   }else {
    $color_desired_price = 'bad_desired_price';
    $need_up_price=round($desired_price - $our_real_price_all_article_one_shtuka,0);
   }

if (isset($desired_price)){echo "<td class=\"$color_desired_price\">".$desired_price."<br>".$need_up_price."</td>";}else{echo "<td>"."</td>";}


// Если не нашли себестоимость товара , то Себестоимость делаем равной цене продажи
if ($priznak_min_price <> 1) {
    $min_price = $our_real_price_all_article_one_shtuka;
     echo "<td>**нет СБС**</td>";
} else {
    echo "<td>". $min_price."</td>";
}

// // Дельта за одну штуку
if (isset($item['count']))  {
    $delta_for_one_temp = round($our_real_price_all_article_one_shtuka - $min_price,2);
}else {
    $delta_for_one_temp = 0;
}
if (isset($delta_for_one_temp)){echo "<td>".$delta_for_one_temp."</td>";}else{echo "<td>"."</td>";}



// Заработали на одном артикуле
if (isset($item['count']))  {
    $money_for_one_article = $delta_for_one_temp * $item['count'];
}else {
    $money_for_one_article = 0;
}
if (isset($money_for_one_article)){echo "<td>".$money_for_one_article."</td>";}else{echo "<td>"."</td>";}
// сумма за все артикулы
$sum_zarabotali = @$sum_zarabotali + $money_for_one_article;





    echo "</tr>";


}

// СТРОКА ИТОГО ТАблицы
echo "<tr>";
    echo "<td></td>"; // Наименование
    echo "<td>$count</td>"; // Количество
    echo "<td>$accruals_for_sale</td>"; // общая сумма
    echo "<td>$amount</td>"; // общая сумма
    echo "<td>".$summa_procentov_for_control."</td>"; // общая сумма


    if (isset($summa_dop_uslug_temp)){echo "<td>".$summa_dop_uslug_temp."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий

    echo "<td>$summa_k_perevodu</td>"; // Наименование
    echo "<td></td>";
    echo "<td></td>"; 
    echo "<td></td>"; 
    echo "<td>$sum_zarabotali</td>"; // общая сумма
    



echo "</tr>";

echo "</table>";
