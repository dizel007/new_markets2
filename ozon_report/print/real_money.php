<?php 

// echo "<pre>";
// print_r($arr_all_nomenklatura);
// echo "</pre>";
// CSS цепляем
echo "<link rel=\"stylesheet\" href=\"css/main_ozon_reports.css\">";





// Начинаем отрисовывать таблицу 

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
    echo "<th>Заработали<br>с артикула</th>";
echo "</tr>";


foreach ($arr_article as $key=>$print_item) {   
$link_for_report_article = "../ozon_report_po_article/index_ozon_razbor_article.php?file_name_ozon=$file_name_ozon&dateFrom=$date_from&dateTo=$date_to&need_update=1&article=".$print_item['article'];
echo "<tr>";
   echo "<td>"." <a href =\"$link_for_report_article\" target=\"_blank\">". $print_item['article']. "</td>";
   print_one_string_in_table($print_item,  'count');
   print_two_strings_in_table($print_item, 'accruals_for_sale' , 'one_shtuka_buyer' );
   print_one_string_in_table($print_item,  'price_minus_all_krome_dop_uslug');
   print_one_string_in_table($print_item,  'proc_item_ot_vsey_summi');
   print_one_string_in_table($print_item,  'dop_uslugi_each_item');
   print_two_strings_in_table($print_item, 'real_price_minus_all' , 'real_price_minus_all_one_shtuka');
   // Желаемая цена за товар
if ($print_item['main_price_delta']  >= 0) {
    $color_desired_price = 'good_desired_price';
   }else {
    $color_desired_price = 'bad_desired_price';
   }
   print_two_strings_in_table($print_item, 'main_price' , 'main_price_delta' , $color_desired_price );
// Себестоимость
   if ($print_item['min_price_delta']  >= 0) {
    $color_desired_price = 'good_desired_price';
   }else {
    $color_desired_price = 'bad_desired_price';
   }
   print_two_strings_in_table($print_item, 'min_price' , 'min_price_delta' ,$color_desired_price );
// Заработали на артикуле 
if ($print_item['zarabotali_na_artikule']  >= 0) {
    $color_desired_price = 'good_desired_price';
   }else {
    $color_desired_price = 'bad_desired_price';
   }

   print_one_string_in_table($print_item,  'zarabotali_na_artikule' ,$color_desired_price);

    echo "</tr>";


}

// СТРОКА ИТОГО ТАблицы
echo "<tr>"; 
echo "<td></td>"; // арктикул
    print_one_string_in_table($arr_sum_data, 'count');
    print_one_string_in_table($arr_sum_data, 'accruals_for_sale');
    print_one_string_in_table($arr_sum_data, 'price_minus_all_krome_dop_uslug');
    print_one_string_in_table($arr_sum_data, 'proc_item_ot_vsey_summi');
    print_one_string_in_table($arr_sum_data, 'dop_uslugi_each_item');

    print_one_string_in_table($arr_sum_data, 'real_price_minus_all');
    echo "<td></td>"; // хорошая цена
    echo "<td></td>"; // Себестоимость
    if ($arr_sum_data['zarabotali_na_artikule']  >= 0) {
        $color_desired_price = 'good_desired_price';
       }else {
        $color_desired_price = 'bad_desired_price';
       }

    print_one_string_in_table($arr_sum_data, 'zarabotali_na_artikule' ,$color_desired_price);
              
echo "</tr>";

echo "</table>";
