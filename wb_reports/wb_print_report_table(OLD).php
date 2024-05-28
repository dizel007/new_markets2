<?php
/******************************************************************************
* Рисуем ттаблицу
 *****************************************************************************/
$sum_procent_raspredelenia_tovarov = 0;

 echo <<<HTML
 <table class="prod_table">
   <tr>
 <td>Артикул</td>
 <td>Кол-во<br> продаж</td>
 <td>К перечислению<br> за товар</td>
 <td>Авансовая <br>оплата</td>
 
 
 <td>Возвраты</td>
 <td>Стоимость <br> логистки (/шт)</td>
 <td>Комиссия ВБ</td>
 <td>Итого к оплате</td>
 <td>цена за шт</td>
 <td>Себест</td>
 <td>Дельта</td>
 <td>Прибыль<br> с артикула</td>
  </tr>
 
 
 HTML;
 
 $sebestoimos      = select_all_nomenklaturu($pdo);
 // print_r($wb_catalog);
 // $sebestoimos = get_sebestiomost_wb ();
 // print_r($sebestoimos);
  foreach ($arr_key as $key){
 // Находим себестоимость товара
     foreach ($sebestoimos as $sebes_item) {
         $right_key = mb_strtolower(make_right_articl($key));
         $right_atricle = mb_strtolower($sebes_item['main_article_1c']);
         // echo "$right_key  и $right_atricle"."<br>";
         if ($right_atricle ==  $right_key) {
            $sebes_str_item = $sebes_item['min_price'] ;
         //    echo "**************************** $right_key  и $right_atricle"."<br>";
            break;
         } else {
             $sebes_str_item = 0;
         }
        }
 
      echo "<tr>";
         echo "<td>".$key."</td>";
         echo "<td>".@$arr_count[$key]."</td>";
 ///     Сумма выплат с ВБ до вычета 
 echo "<td class=\"plus\">".number_format(@$arr_sum_k_pererchisleniu[$key],2, ',', ' ')."</td>";
 
 // Авансовая оплата за товар без движения
 echo "<td class=\"plus\">".number_format(@$arr_sum_avance[$key],2, ',', ' ')."</td>"; 
 
 
 
 ///     Сумма выплат с возвратов 
 echo "<td class=\"minus\">".number_format(@$arr_sum_vozvratov[$key],2, ',', ' ')."</td>";
 
 ///     Сумма ЛОгистики 
 if (isset($arr_count[$key])){
 $logistika_za_shtuku = @$arr_sum_logistik[$key]/@$arr_count[$key];
  echo "<td class=\"minus\">".number_format(@$arr_sum_logistik[$key],2, ',', ' ').
                             "<br>".number_format(@$logistika_za_shtuku,2, ',', ' ')."</td>";
 } else {
     echo "<td class=\"minus\">".number_format(@$arr_sum_logistik[$key],2, ',', ' ').
                             "<br>"."-"."</td>";
 }
 
 ///     Сумма Комиссии ВБ
 echo "<td class=\"minus\">".number_format(@$arr_sum_voznagrazhdenie_wb[$key],2, ',', ' ')."</td>";
 
 
 ///     Сумма к выплате
 $temp[$key] =  @$arr_sum_k_pererchisleniu[$key] - @$arr_sum_vozvratov[$key] + @$arr_sum_avance[$key] +  
 @$arr_sum_brak[$key] - @$arr_sum_logistik[$key] ;
 $sum_nasha_viplata = $sum_nasha_viplata + $temp[$key];
 
 echo "<td class=\"our_many\">".number_format(@$temp[$key],2, ',', ' ')."</td>";  
 if ((isset($arr_count[$key]) && ($arr_count[$key]) <> 0)) {
 $price_for_shtuka = @$temp[$key]/@$arr_count[$key];
 } else {
     $price_for_shtuka = 0;
 }
 ///     Цена за штуку
 echo "<td>".number_format($price_for_shtuka,2, ',', ' ')."</td>"; // цена за штукту
 
 ///     себестоимость
 echo"<td class=\"plus\">"."$sebes_str_item"."</td>"; // себестоимость
 
 ///     Разница в стоимости
 if ((isset($arr_count[$key]) && ($arr_count[$key]) <> 0)) { // если количество проданного товара не равно Нулю то считаем дельту
 $temp_delta = ($price_for_shtuka - $sebes_str_item);
 } else {
     $temp_delta = 0;
 }
 
 echo"<td class=\"plus\">".number_format($temp_delta,2, ',', ' ')."</td>"; // дельта
 $our_pribil  = $temp_delta * @$arr_count[$key];
 
 $sum_our_pribil = @$sum_our_pribil + $our_pribil; // Наша заработок по всем артикулам
 
 ///     Заработок с артикула 
 echo"<td class=\"our_many\"><b>".number_format($our_pribil,2, ',', ' ')."</b></td>"; // заработали на артикуле
/// процент распределения суммы товара от общей суммы
$procent_each_item = ($sum_nasha_viplata)/100;
$sum_procent_raspredelenia_tovarov = +$procent_each_item;
echo"<td>$procent_each_item</td>";
 echo "</tr>";
 
 }
 
 echo"<tr>";
 echo"<td></td>";
 echo"<td></td>";
 echo"<td class=\"plus\"><b>".number_format($sum_k_pererchisleniu,2, ',', ' ')."</b></td>";
 echo"<td class=\"plus\"><b>".number_format($sum_avance,2, ',', ' ')."</b></td>";
 echo"<td class=\"minus\"><b>".number_format($sum_vozvratov,2, ',', ' ')."</b></td>";
 
 echo"<td class=\"minus\"><b>".number_format($sum_logistiki,2, ',', ' ')."</b></td>";
 
 echo"<td class=\"minus\"><b>".number_format($sum_voznagrazhdenie_wb,2, ',', ' ')."</b></td>";
 echo"<td class=\"our_many\"><b>".number_format($sum_nasha_viplata,2, ',', ' ')."</b></td>";
 echo"<td></td>";
 echo"<td></td>";
 echo"<td></td>";
 echo"<td class=\"our_many\"><b>".number_format($sum_our_pribil,2, ',', ' ')."</b></td>";
 echo "</tr>";
 
 //////////////////////////////////////////////////////////////////////////////////////////////////////////////

 echo"<tr>";
 echo"<td></td>";
 echo"<td></td>";
 $summa_k_perechilseniu_za_tovar = $sum_k_pererchisleniu + $sum_avance + $sum_brak - $sum_vozvratov;
 echo"<td class=\"plus\"><b>".number_format($summa_k_perechilseniu_za_tovar,2, ',', ' ')."</b></td>";
 echo"<td> <-- </td>";
 echo"<td> <-- </td>";
 echo"<td></td>";
 echo"<td></td>";
 // Сумма итого у оплате За вычетов штрафов / Хранение / Удержания /
 $summa_itogo_k_oplate = $sum_nasha_viplata - $sum_storage - $sum_uderzhania - $sum_shtafi_i_doplati + $sum_brak - $sum_storage_correctirovka;
 echo"<td class=\"plus\"><b>".number_format($summa_itogo_k_oplate,2, ',', ' ')."</b></td>";
 echo"<td></td>";
 echo"<td></td>";
 echo"<td></td>";
 echo"<td></td>";
 //// процент распределения штрафа
 $procent_raspredelenia = $sum_nasha_viplata/100;
 $procent_each_item = 0;
 echo"<td></td>";



 $summa_shrafa = -($sum_storage - $sum_uderzhania - $sum_shtafi_i_doplati + $sum_brak - $sum_storage_correctirovka);

 echo"<td> $summa_shrafa</td>";


 echo"<td>$procent_raspredelenia</td>";
 echo"<td>$sum_procent_raspredelenia_tovarov</td>";
 echo "</tr>";
 
 
 
 
 echo "</table>";
 
 