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
 </tr>
 
 
HTML;

foreach ($arr_article_data as $key => $item) {
    echo "<tr>";
    echo "<td>" . $key . "</td>";
    echo "<td>" . @$item['Кол-во товаров'] . "</td>";
    ///     Сумма выплат с ВБ до вычета 
    $sum_count_sell = @$sum_count_sell + @$item['Кол-во товаров'];
    $sum_nasha_viplata = @$sum_nasha_viplata + @$item['сумма_операций'];
    $sum_raspred_komissii = @$sum_raspred_komissii + @$item['сумма_удержания_прочие'];
    $sum_k_pererchisleniu = @$sum_k_pererchisleniu + @$item['сумма_за_артикул_после_всех_вычитов'];
    $sum_proc_raspred = @$sum_proc_raspred + $item['процент_от_суммы'];      
// цена продажи товара
    echo "<td class=\"\">" . number_format(@$item['сумма_операций'], 2, ',', ' '). "</td>";
// процент распредегты
     echo "<td class=\"\">" . number_format(@$item['процент_от_суммы'], 2, ',', ' ') . "</td>";
// Сумма удержаний
     echo "<td class=\"minus\">" . number_format(@$item['сумма_удержания_прочие'], 2, ',', ' '). "</td>";
// Сумма выплат нам за артикул
    echo "<td class=\"plus\">" . number_format(@$item['сумма_за_артикул_после_всех_вычитов'], 2, ',', ' '). "</td>";
// Получили за 1 шуткук
    echo "<td class=\"\">" . number_format(@$item['получили_за_штуку'], 2, ',', ' ') ."</td>";


 ///   Находим хорую цену, себестоимость и дельту между выплотой и ними 

 foreach ($nomenclatura as $odin_tovar) {
  if (mb_strtolower($key) == mb_strtolower($odin_tovar['main_article_1c'])) {
      $main_price = $odin_tovar['main_price'];
      $min_price = $odin_tovar['min_price'];
      $delta_main_price = $item['получили_за_штуку'] - $main_price;
      $delta_min_price = $item['получили_за_штуку'] - $min_price;
    break;
  } else {
    $main_price = 0;
    $min_price = 0;
    $delta_main_price = 0;
    $delta_min_price = 0;
  }


 }



// Хорошая цена товара и дельта


      if ($delta_main_price >= 0 ) {
        echo "<td class=\"plus\">" . number_format( $main_price, 2, ',', ' ') ."<br>".number_format($delta_main_price, 2, ',', ' '). "</td>";
      } else {
        echo "<td class=\"minus\">" . number_format( $main_price, 2, ',', ' ') ."<br>".number_format($delta_main_price, 2, ',', ' '). "</td>";
      }

   
    ///     себестоимость и дельта
    if ($delta_min_price >= 0 ) {
      echo "<td class=\"plus\">" . $min_price."<br>".number_format($delta_min_price, 2, ',', ' '). "</td>";
     } else {
      echo "<td class=\"minus\">" . $min_price."<br>".number_format($delta_min_price, 2, ',', ' '). "</td>";
     }


     // Прибыль с артикула 
     if (isset($item['Кол-во товаров'])) {
     $pribil_s_one_article = $delta_min_price * $item['Кол-во товаров'];
     } else {
      $pribil_s_one_article = 0;
     }

      ///     себестоимость и дельта
    if ($pribil_s_one_article >= 0 ) {
      echo "<td class=\"plus\">" . number_format($pribil_s_one_article, 2, ',', ' '). "</td>";
     } else {
      echo "<td class=\"minus\">" . number_format($pribil_s_one_artivle, 2, ',', ' ')."</td>";
     }
     $sum_pribil_s_one_article = @$sum_pribil_s_one_article +@$pribil_s_one_article;
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

if ($sum_pribil_s_one_article >= 0 ) {
  echo "<td class=\"plus\"><b>" . number_format($sum_pribil_s_one_article, 2, ',', ' ') . "</b></td>";
 } else {
  echo "<td class=\"minus\"><b>" . number_format($sum_pribil_s_one_article, 2, ',', ' ') ."</b></td>";
 }





echo "</tr>";

echo "</table>";
