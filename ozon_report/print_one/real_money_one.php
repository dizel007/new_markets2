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
    echo "<th>пп</th>";
    echo "<th>Операция</th>";
    echo "<th>Номер заказ</th>";
    echo "<th>Достав</th>";
    echo "<th>товары</th>";
    echo "<th>Кол-во<br>товара<br>(шт)</th>";
    echo "<th>Стоимость<br>товаров с <br> учётом скидок <br> продавца.<br>(руб)</th>";
    echo "<th>Итоговая<br>сумма<br>операции</th>";
    echo "<th>Комиссия озон</th>";
    echo "<th>Логистика</th>";
    echo "<th>Последняя<br>миля</th>";
    echo "<th>Обработка<br>операции</th>";
    echo "<th>Эквайринг</th>";

    echo "<th>Комиссия за продажу<br>или возврат комиссии<br>за продажу</th>";
    echo "<th>Затраты на<br>доп.услуги<br>(руб)</th>";
    echo "<th>Цена за вычетом <br>всего (руб)</th>";
    echo "<th>Желаемая цена<br>(руб)</th>";
    echo "<th>Себестоимость</th>";
    echo "<th>Заработали<br>с артикула</th>";
echo "</tr>";

$pp=0;
foreach ($arr_article as $key=>$print_item) {   
$pp++;
echo "<tr>";
echo "<td>" . $pp. "</td>";
// Тип операции 
$type_operation = '';
if (isset($print_item['SELL'])) {
    $type_operation .= 'SELL<br>';
}; 
if (isset($print_item['RETURN'])) {
    $type_operation .= 'RETURN<br>';
}; 
if (isset($print_item['ACQUIRING'])) {
    $type_operation .= 'ACQUIRING<br>';
}; 
if (isset($print_item['SERVICES'])) {
    $type_operation .= 'SERVICES<br>';
}; 
if (isset($print_item['UDERZHANIA'])) {
    $type_operation .= 'UDERZHANIA<br>';
}; 



echo "<td>" . $type_operation. "</td>";
   echo "<td>" . $key."<br>от ".$print_item['order_date']. "</td>";

// **********************  Схема доставки товара **********************************
   if (isset($print_item['delivery_schema'] )) {
    echo "<td>" . $print_item['delivery_schema'] . "</td>";

 } else {
    echo "<td>" . "" . "</td>";
 }
// ********************** выводим на экран список товаров **********************
 if (isset($print_item['items_buy'] )) {
    echo "<td>";
    foreach ($print_item['items_buy'] as $tovar_name) {   
        echo $tovar_name['c_1c_article']."<br>";;
    }
    echo "</td>";
 } else {
    echo "<td>" . "" . "</td>";
 }
// **********************  выводим на экран количество товаров в Заказе **********************
   print_one_string_in_table($print_item,  'count');

// ********************** выводим на экран Стоимость товаров с учётом скидок продавца. **********************
$summa_accruals_for_sale = 0;
  if (isset($print_item['items_buy'] )) {
    echo "<td>";
    foreach ($print_item['items_buy'] as $tovar_name) {   
        echo $tovar_name['accruals_for_sale']."<br>";
        $summa_accruals_for_sale +=$tovar_name['accruals_for_sale'];
    }
    echo "</td>";
 } else {
    echo "<td>" . "" . "</td>";
 }

   // выводим на экран Итоговая сумма операции.
  if (isset($print_item['items_buy'] )) {
    echo "<td>";
    foreach ($print_item['items_buy'] as $tovar_name) {   
        echo $tovar_name['amount']."<br>";;
    }
    echo "</td>";
 } else {
    echo "<td>" . "" . "</td>";
 }

 //  **********************  комиссия за продажу.**********************
   if (isset($print_item['items_buy'] )) {
    echo "<td>";
    foreach ($print_item['items_buy'] as $tovar_name) {  
        if ($tovar_name['accruals_for_sale'] != 0 ) {
            $procent_comissii = round((-$tovar_name['sale_commission']/$tovar_name['accruals_for_sale'])*100,1);
        } else 
        {$procent_comissii = '';}

        echo $tovar_name['sale_commission']."(".$procent_comissii."%)"."<br>";;
    }
    echo "</td>";
 } else {
    echo "<td>" . "" . "</td>";
 }
//  ********************** выводим на экран Логистика **********************
  if (isset($print_item['items_buy'] )) {
    echo "<td>";
    foreach ($print_item['items_buy'] as $tovar_name) { 
        if (isset($tovar_name['logistika'] )) {  
        echo $tovar_name['logistika']."<br>";
        }  
    }
    echo "</td>";
 } else {
    echo "<td>" . "" . "</td>";
 }

 // ********************** выводим на экран Последняя миоя **********************
   if (isset($print_item['items_buy'] )) {
    echo "<td>";
    foreach ($print_item['items_buy'] as $tovar_name) {   
        if (isset($tovar_name['last_mile'] )) {  
            echo $tovar_name['last_mile']."<br>";
            }
    }
    echo "</td>";
 } else {
    echo "<td>" . "" . "</td>";
 }

 //  ********************** выводим на экран Обработка отправлений **********************
 if (isset($print_item['items_buy'] )) {
    echo "<td>";
    foreach ($print_item['items_buy'] as $tovar_name) {   
        if (isset($tovar_name['obrabotka_otpravlenia'] )) {  
            echo $tovar_name['obrabotka_otpravlenia']."<br>";
            }
    }
    echo "</td>";
 } else {
    echo "<td>" . "" . "</td>";
 }

//  ********************** Эквайринг **********************
if (isset($print_item['amount_ecvairing'] )) {

    if ($summa_accruals_for_sale != 0 ) {
        $procent_acquiring = round((-$print_item['amount_ecvairing']/$summa_accruals_for_sale)*100,1);
    } else 
    {$procent_acquiring = '';}
    echo "<td>".$print_item['amount_ecvairing']."(".$procent_acquiring."%)"."</td>";
                       
    }
 
  else {
    echo "<td>" . "" . "</td>";
 }




//    print_two_strings_in_table($print_item, 'accruals_for_sale' , 'one_shtuka_buyer' );
//    print_one_string_in_table($print_item,  'price_minus_all_krome_dop_uslug');
//    print_one_string_in_table($print_item,  'proc_item_ot_vsey_summi');
//    print_one_string_in_table($print_item,  'dop_uslugi_each_item');
//    print_two_strings_in_table($print_item, 'real_price_minus_all' , 'real_price_minus_all_one_shtuka');
//    // Желаемая цена за товар
// if ($print_item['main_price_delta']  >= 0) {
//     $color_desired_price = 'good_desired_price';
//    }else {
//     $color_desired_price = 'bad_desired_price';
//    }
//    print_two_strings_in_table($print_item, 'main_price' , 'main_price_delta' , $color_desired_price );
// // Себестоимость
//    if ($print_item['min_price_delta']  >= 0) {
//     $color_desired_price = 'good_desired_price';
//    }else {
//     $color_desired_price = 'bad_desired_price';
//    }
//    print_two_strings_in_table($print_item, 'min_price' , 'min_price_delta' ,$color_desired_price );
// // Заработали на артикуле 
// if ($print_item['zarabotali_na_artikule']  >= 0) {
//     $color_desired_price = 'good_desired_price';
//    }else {
//     $color_desired_price = 'bad_desired_price';
//    }

//    print_one_string_in_table($print_item,  'zarabotali_na_artikule' ,$color_desired_price);

    echo "</tr>";


}

// СТРОКА ИТОГО ТАблицы
echo "<tr>"; 
// echo "<td></td>"; // арктикул
//     print_one_string_in_table($arr_sum_data, 'count');
//     print_one_string_in_table($arr_sum_data, 'accruals_for_sale');
//     print_one_string_in_table($arr_sum_data, 'price_minus_all_krome_dop_uslug');
//     print_one_string_in_table($arr_sum_data, 'proc_item_ot_vsey_summi');
//     print_one_string_in_table($arr_sum_data, 'dop_uslugi_each_item');

    // print_one_string_in_table($arr_sum_data, 'real_price_minus_all');
    echo "<td></td>"; // хорошая цена
    echo "<td></td>"; // Себестоимость
    if ($print_item['zarabotali_na_artikule']  >= 0) {
        $color_desired_price = 'good_desired_price';
       }else {
        $color_desired_price = 'bad_desired_price';
       }

    // print_one_string_in_table($arr_sum_data, 'zarabotali_na_artikule' ,$color_desired_price);
              
echo "</tr>";

echo "</table>";
