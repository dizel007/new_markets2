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
echo "<th>Обр.Логистика<br>(включена в лог)</th>";
echo "<th>Сборка<br>(руб)</th>";
echo "<th>Обр.Сборка<br>(руб)</th>";
echo "<th>Обр.Обработка<br>(руб)</th>";
echo "<th>Посл.миля<br>(руб)</th>";
echo "<th>Хранение<br>утилизация<br>(руб)</th>";
echo "<th>Удерж<br>за недовл<br>(руб)</th>";
echo "<th>Эквайринг<br>(руб)</th>";
echo "<th>Возвраты<br>(шт)</th>";
echo "<th>Возвраты<br>(руб)</th>";



echo "</tr>";

foreach ($arr_article as $print_item) {
    echo "<tr>";
        echo "<td>" . $print_item['article']. "</td>";
        print_one_string_in_table($print_item, 'count');
        print_two_strings_in_table($print_item, 'accruals_for_sale' , 'one_shtuka_buyer');
        print_two_strings_in_table($print_item, 'amount_bez_equaring' , 'one_shtuka');
        print_one_string_in_table($print_item, 'sale_commission');
        print_one_string_in_table($print_item, 'logistika');
        print_one_string_in_table($print_item, 'back_logistika_vozvrat');
        print_one_string_in_table($print_item, 'sborka');
        print_one_string_in_table($print_item, 'back_sborka');
        print_one_string_in_table($print_item, 'return_obrabotka');
        print_one_string_in_table($print_item, 'lastMile');
        print_one_string_in_table($print_item, 'amount_hranenie');
        print_one_string_in_table($print_item, 'compensation');
        print_one_string_in_table($print_item, 'amount_ecvairing');
        print_one_string_in_table($print_item, 'get_vozvrat_count');
        print_one_string_in_table($print_item, 'get_vozvrat_amount');
    echo "</tr>";
}

/// печатаем суммы в конце таблицы

echo "<tr>"; 
echo "<td></td>"; // арктикул
    print_one_string_in_table($arr_sum_data, 'count');
    print_one_string_in_table($arr_sum_data, 'accruals_for_sale');
    print_one_string_in_table($arr_sum_data, 'amount_bez_equaring');
    print_one_string_in_table($arr_sum_data, 'sale_commission');
    print_one_string_in_table($arr_sum_data, 'logistika');
    print_one_string_in_table($arr_sum_data, 'back_logistika_vozvrat');
    print_one_string_in_table($arr_sum_data, 'sborka');
    print_one_string_in_table($arr_sum_data, 'back_sborka');
    print_one_string_in_table($arr_sum_data, 'return_obrabotka');
    print_one_string_in_table($arr_sum_data, 'lastMile');
    print_one_string_in_table($arr_sum_data, 'amount_hranenie');
    print_one_string_in_table($arr_sum_data, 'compensation');
    print_one_string_in_table($arr_sum_data, 'amount_ecvairing');
    print_one_string_in_table($arr_sum_data, 'get_vozvrat_count');
    print_one_string_in_table($arr_sum_data, 'get_vozvrat_amount');
      
echo "</tr>";


echo "</table>";


