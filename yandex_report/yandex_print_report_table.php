<?php

/******************************************************************************
 * Рисуем ттаблицу
 *****************************************************************************/
echo <<<HTML
	<link rel="stylesheet" href="css/yandex_css.css">
HTML;
// print_r($arr_count_item);

echo <<<HTML
 <table class="prod_table">
   <tr>
 <td>Артикул</td>
 <td>Кол-во<br> продаж</td>
 <td>К перечислению<br> за товар</td>
 <td>Возвраты</td>
 <td>Комиссия <br> Яндекс</td>
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
    echo "<td class=\"plus\">" . number_format(@$item['sum_nasha_viplata'], 2, ',', ' ') ."<br>".@$item['price_for_shtuka']. "</td>";

    ///     Сумма выплат с возвратов 
    echo "<td class=\"minus\">" ."-". "</td>";

    ///     Сумма КОммиссии яндекса 
    $commission_one_item = @$item['commission']/@$item['count_sell'];
    echo "<td class=\"minus\">" . number_format(@$item['commission'], 2, ',', ' ') .
        "<br>" . number_format(@$commission_one_item, 2, ',', ' ') . "</td>";
    
      
        ///     Цена за штуку
    echo "<td>" . number_format(@$item['price_for_shtuka'], 2, ',', ' ') . "</td>";


   // Хорошая цена товара и дельта
  if ($item['delta_good_and_sell_prices'] >= 0 ) {
   echo "<td class=\"plus\">" . number_format(@$item['main_price'], 2, ',', ' ') ."<br>".$item['delta_good_and_sell_prices']. "</td>";
  } else {
    echo "<td class=\"minus\">" . number_format(@$item['main_price'], 2, ',', ' ') ."<br>".$item['delta_good_and_sell_prices']. "</td>"; 
  }


    ///     себестоимость и дельта
    if ($item['delta_v_stoimosti'] >= 0 ) {
    echo "<td class=\"plus\">" . @$item['min_price']."<br>".@$item['delta_v_stoimosti']. "</td>";
   } else {
    echo "<td class=\"minus\">" . @$item['min_price']."<br>".@$item['delta_v_stoimosti']. "</td>";
   }




    ///     Заработок с артикула 
    echo "<td class=\"our_many\"><b>" . number_format(@$item['our_pribil'], 2, ',', ' ') . "</b></td>"; // заработали на артикуле

    echo "</tr>";

    $all_sum_nasha_viplata = @$all_sum_nasha_viplata +  @$item['sum_nasha_viplata'];
    $all_sum_commission = @$all_sum_commission +  @$item['commission'];
    $all_sum_our_pribil = @$all_sum_our_pribil + @$item['our_pribil'];


}







echo "<tr>";
echo "<td></td>";
echo "<td></td>";
echo "<td class=\"plus\"><b>" . number_format($all_sum_nasha_viplata, 2, ',', ' ') . "</b></td>"; // выплата нам на счет
echo "<td></td>"; // возвраты
echo "<td class=\"minus\"><b>" . number_format($all_sum_commission, 2, ',', ' ') . "</b></td>"; // комиссия ЯМ
echo "<td></td>";
echo "<td></td>";
echo "<td></td>";
echo "<td class=\"our_many\"><b>" . number_format($all_sum_our_pribil, 2, ',', ' ') . "</b></td>";

echo "</tr>";




echo "</table>";
