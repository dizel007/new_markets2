<?php

/******************************************************************************
 * Рисуем ттаблицу
 *****************************************************************************/
echo <<<HTML
	<link rel="stylesheet" href="css/yandex_css.css">
HTML;
print_r($arr_count_item);

$sum_procent_raspredelenia_tovarov = 0;

echo <<<HTML
 <table class="prod_table">
   <tr>
 <td>Артикул</td>
 <td>Кол-во<br> продаж</td>
 <td>К перечислению<br> за товар</td>

 <td>Возвраты</td>
 <td>Комиссия <br> Яндекс</td>
 <td>Итого к оплате</td>
 <td>цена за шт</td>
 <td>Хорошая <br> цена</td>
 <td>Себест</td>
 <td>Прибыль<br> с артикула</td>
 </tr>
 
 
HTML;

foreach ($arr_items_yandex as $key => $item) {
    echo "<tr>";
    echo "<td>" . $key . "</td>";
    echo "<td>" . @$item['count_sell'] . "</td>";
    ///     Сумма выплат с ВБ до вычета 
    if (isset($item['count_sell'])) {
      if ($item['count_sell'] > 0) {
        $price_one_shtuka_na_site = round(@$item['sum_k_pererchisleniu']/@$item['count_sell'],2);
      } else {
        $price_one_shtuka_na_site = "-"; 
      }
    } else {
      $price_one_shtuka_na_site = "-"; 
    }
    echo "<td class=\"plus\">" . number_format(@$item['sum_k_pererchisleniu'], 2, ',', ' ') ."<br>".$price_one_shtuka_na_site. "</td>";
    // Авансовая оплата за товар без движения
    echo "<td class=\"plus\">" . number_format(@$item['sum_avance'], 2, ',', ' ') . "</td>";
    ///     Сумма выплат с возвратов 
    echo "<td class=\"minus\">" . number_format(@$item['sum_vozvratov'], 2, ',', ' ') . "</td>";
    ///     Сумма ЛОгистики 
    echo "<td class=\"minus\">" . number_format(@$item['sum_logistik'], 2, ',', ' ') .
        "<br>" . number_format(@(int)$item['logistika_za_shtuku'], 2, ',', ' ') . "</td>";
    ///     Сумма Комиссии ВБ
    echo "<td class=\"minus\">" . number_format(@$item['sum_voznagrazhdenie_wb'], 2, ',', ' ') . "</td>";
    ///     Сумма к выплате
    echo "<td class=\"our_many\">" . number_format(@$item['sum_nasha_viplata'], 2, ',', ' ') . "</td>";
    ///     Цена за штуку
    echo "<td>" . number_format(@$item['price_for_shtuka'], 2, ',', ' ') . "</td>";


   // Хорошая цена товара и дельта
  if ($item['delta_good_and_sell_prices'] >= 0 ) {
   echo "<td class=\"plus\">" . number_format(@$item['good_price'], 2, ',', ' ') ."<br>".$item['delta_good_and_sell_prices']. "</td>";
  } else {
    echo "<td class=\"minus\">" . number_format(@$item['good_price'], 2, ',', ' ') ."<br>".$item['delta_good_and_sell_prices']. "</td>"; 
  }


    ///     себестоимость и дельта
    if ($item['delta_v_stoimosti'] >= 0 ) {
    echo "<td class=\"plus\">" . @$item['sebes_str_item']."<br>".@$item['delta_v_stoimosti']. "</td>";
   } else {
    echo "<td class=\"minus\">" . @$item['sebes_str_item']."<br>".@$item['delta_v_stoimosti']. "</td>";
   }




    ///     Заработок с артикула 
    echo "<td class=\"our_many\"><b>" . number_format(@$item['our_pribil'], 2, ',', ' ') . "</b></td>"; // заработали на артикуле

      ///     Процент от суммы выплаты  с артикула 
      echo "<td class=\"minus\"><b>" . number_format(@$item['procent_ot_summi'], 2, ',', ' ') . "</b></td>"; // заработали на артикуле
///     Сумма штрафа для артикула (распределяем по всем)
echo "<td class=\"minus\"><b>" . number_format(@$item['summa_strafa_article'], 2, ',', ' ') . "</b></td>"; // заработали на артикуле

///     Заработок с артикула после вычета штрафа
    echo "<td class=\"our_many\"><b>" . number_format(@$item['pribil_posle_vicheta_strafa'], 2, ',', ' ') . "</b></td>"; // заработали на артикуле


}



echo "</tr>";



echo "<tr>";
echo "<td></td>";
echo "<td></td>";
echo "<td class=\"plus\"><b>" . number_format($sum_k_pererchisleniu_po_wb, 2, ',', ' ') . "</b></td>";
echo "<td class=\"plus\"><b>" . number_format($sum_avance_po_wb, 2, ',', ' ') . "</b></td>";
echo "<td class=\"minus\"><b>" . number_format($sum_vozvratov_po_wb, 2, ',', ' ') . "</b></td>";
echo "<td class=\"minus\"><b>" . number_format($sum_logistik_po_wb, 2, ',', ' ') . "</b></td>";
echo "<td class=\"minus\"><b>" . number_format($sum_voznagrazhdenie_wb_po_wb, 2, ',', ' ') . "</b></td>";
echo "<td class=\"our_many\"><b>" . number_format($sum_nasha_viplata_po_wb, 2, ',', ' ') . "</b></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td class=\"our_many\"><b>" . number_format($sum_our_pribil_po_wb, 2, ',', ' ') . "</b></td>";

echo "<td class=\"minus\"><b>".number_format($procent_all, 2, ',', ' ')." </td>";
echo "<td class=\"minus\"><b>$summa_shtrafa_raschet</td>";

echo "<td class=\"our_many\"><b>" . number_format($summa_posle_vicheta_shtrafa, 2, ',', ' ') . "</b></td>";





echo "</tr>";
////////////////////////////////////////////////////////////////
echo "<tr>";
echo "<td></td>";
echo "<td></td>";
$summa_k_perechilseniu_za_tovar = $sum_k_pererchisleniu_po_wb + $sum_avance_po_wb + $sum_brak - $sum_vozvratov_po_wb;
echo "<td class=\"plus\"><b>" . number_format($summa_k_perechilseniu_za_tovar, 2, ',', ' ') . "</b></td>";
echo "<td> <-- </td>";
echo "<td> <-- </td>";
echo "<td></td>";
echo "<td class=\"plus\"><b>Выплата с<br>учетом штрафов</td>";
// Сумма итого у оплате За вычетов штрафов / Хранение / Удержания /
$summa_itogo_k_oplate = $sum_nasha_viplata_po_wb - $sum_storage - $sum_uderzhania - $sum_shtafi_i_doplati + $sum_brak - $sum_storage_correctirovka;
echo "<td class=\"plus\"><b>" . number_format($summa_itogo_k_oplate, 2, ',', ' ') . "</b></td>";


echo "<td></td>";





echo "</tr>";




echo "</table>";
