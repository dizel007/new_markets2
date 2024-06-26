<?php

/******************************************************************************
 * Рисуем ттаблицу
 *****************************************************************************/
$sum_procent_raspredelenia_tovarov = 0;
echo <<<HTML
 <link rel="stylesheet" href="css/main_table.css">
HTML;


echo <<<HTML
 <table class="prod_table">
   <tr>
 <td>Артикул</td>
 <td>Кол-во<br> продаж</td>
 <td>К перечислению<br> за товар</td>
 <td>% распред</td>
 <td>Комиссия <br> Яндекса</td>
 <td>К нам на счет </td>
 <td>На счет за 1 шт</td>

 <td>Хорошая <br> цена</td>
 <td>Себест</td>
 <td>Прибыль<br> с артикула</td>

  <td>Прибыль </td>
 </tr>
 
 
HTML;

foreach ($arr_with_key as $key => $item) {
    echo "<tr>";
    echo "<td>" . $key . "</td>";
    echo "<td>" . @$item['count_sell'] . "</td>";
    ///     Сумма выплат с ВБ до вычета 
    $sum_count_sell = @$sum_count_sell + @$item['count_sell'];

    $sum_nasha_viplata = @$sum_nasha_viplata + @$item['sum_nasha_viplata'];
    $sum_raspred_komissii = @$sum_raspred_komissii + @$item['raspred_komissii'];
    $sum_k_pererchisleniu = @$sum_k_pererchisleniu + @$item['sum_k_pererchisleniu'];


// К перечислению за товар

    if (isset($item['count_sell'])) {
      if ($item['count_sell'] > 0) {
        $price_one_shtuka_na_site = round(@$item['sum_k_pererchisleniu']/@$item['count_sell'],2);
      } else {
        $price_one_shtuka_na_site = "-"; 
      }
    } else {
      $price_one_shtuka_na_site = "-"; 
    }




         

    echo "<td class=\"\">" . number_format(@$item['sum_nasha_viplata'], 2, ',', ' '). "</td>";

      // процент распредегты
      echo "<td class=\"\">" . number_format(@$item['proc_raspred'], 2, ',', ' ') . "</td>";
      $sum_proc_raspred = @$sum_proc_raspred + $item['proc_raspred'];

    echo "<td class=\"minus\">" . number_format(@$item['raspred_komissii'], 2, ',', ' '). "</td>";

    echo "<td class=\"plus\">" . number_format(@$item['sum_k_pererchisleniu'], 2, ',', ' '). "</td>";

    
    
    echo "<td class=\"\">" . $price_one_shtuka_na_site ."</td>";

// Хорошая цена товара и дельта
      if ($item['delta_good_and_sell_prices'] >= 0 ) {
        echo "<td class=\"plus\">" . number_format(@$item['main_price'], 2, ',', ' ') ."<br>".number_format(@$item['delta_good_and_sell_prices'], 2, ',', ' '). "</td>";
      } else {
        echo "<td class=\"minus\">" . number_format(@$item['main_price'], 2, ',', ' ') ."<br>".number_format(@$item['delta_good_and_sell_prices'], 2, ',', ' '). "</td>";
      }

   
    ///     себестоимость и дельта
    if ($item['delta_v_stoimosti'] >= 0 ) {
      echo "<td class=\"plus\">" . @$item['sebes_str_item']."<br>".number_format(@$item['delta_v_stoimosti'], 2, ',', ' '). "</td>";
     } else {
      echo "<td class=\"minus\">" . @$item['sebes_str_item']."<br>".number_format(@$item['delta_v_stoimosti'], 2, ',', ' '). "</td>";
     }


     // Прибыль с артикула 
    echo "<td class=\"our_many\">" . number_format(@$item['delta_v_stoimosti'], 2, ',', ' ') . "</td>";

    ///     Заработок с артикула 
    $our_pribil = @$item['delta_v_stoimosti'] * @$item['count_sell'];
$sum_our_pribil = @$sum_our_pribil  +   $our_pribil;
    echo "<td class=\"our_many\"><b>" . number_format( $our_pribil, 2, ',', ' ') . "</b></td>"; // заработали на артикуле

 
    echo "</tr>";
}







echo "<tr>";
echo "<td></td>";
echo "<td class=\"plus\"><b>" . number_format($sum_count_sell, 2, ',', ' ') . "</b></td>";

echo "<td class=\"plus\"><b>" . number_format($sum_nasha_viplata, 2, ',', ' ') . "</b></td>";
echo "<td>$sum_proc_raspred</td>";
echo "<td class=\"minus\"><b>" . number_format($sum_raspred_komissii, 2, ',', ' ') . "</b></td>";
echo "<td class=\"plus\"><b>" . number_format($sum_k_pererchisleniu, 2, ',', ' ') . "</b></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";

echo "<td class=\"our_many\"><b>" . number_format($sum_our_pribil, 2, ',', ' ') . "</b></td>";

echo "</tr>";

////////////////////////////////////////////////////////////////
// echo "<tr>";
// echo "<td></td>";
// echo "<td></td>";
// $summa_k_perechilseniu_za_tovar = $sum_k_pererchisleniu_po_wb + $sum_avance_po_wb + $sum_brak - $sum_vozvratov_po_wb;
// echo "<td class=\"plus\"><b>" . number_format($summa_k_perechilseniu_za_tovar, 2, ',', ' ') . "</b></td>";
// echo "<td> <-- </td>";
// echo "<td> <-- </td>";
// echo "<td></td>";
// echo "<td class=\"plus\"><b>Выплата с<br>учетом штрафов</td>";
// // Сумма итого у оплате За вычетов штрафов / Хранение / Удержания /
// $summa_itogo_k_oplate = $sum_nasha_viplata_po_wb - $sum_storage - $sum_uderzhania - $sum_shtafi_i_doplati + $sum_brak - $sum_storage_correctirovka;
// echo "<td class=\"plus\"><b>" . number_format($summa_itogo_k_oplate, 2, ',', ' ') . "</b></td>";


// echo "<td></td>";





// echo "</tr>";




echo "</table>";
