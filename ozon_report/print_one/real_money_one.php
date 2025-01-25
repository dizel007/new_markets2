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
    echo "<th>Стоимость<br>товаров с <br> учётом скидок <br> продавца</th>";
    echo "<th>Итоговая<br>сумма<br>операции</th>";
    echo "<th>Комиссия озон</th>";
    echo "<th>Логистика</th>";
    echo "<th>Последняя<br>миля</th>";
    echo "<th>Обработка<br>операции</th>";
    echo "<th>Обратная<br>логистика</th>";
    echo "<th>Обработка<br>при <br>обратной<br>логистике</th>";
    echo "<th>Поздняя<br>отгрузка</th>";
    echo "<th>Хранение<br>Утилизация</th>";
    echo "<th>Эквайринг</th>";
    echo "<th>Затраты <br>по заказу</th>";
    echo "<th>Сумма<br>всего<br>заказа для<br>покупателя</th>";
    echo "<th>Цена за<br>вычетом<br>всего</th>";

    echo "<th>Желаемая цена<br>(руб)</th>";
    echo "<th>Себестоимость</th>";
    echo "<th>Заработали<br>с артикула</th>";
echo "</tr>";

$pp=0; // номер строки в тублице
$ALL_summa_accruals_for_sale = 0;
foreach ($arr_article as $key=>$print_item) {   
$pp++;

$summa_accruals_for_sale = 0;
$zartati_po_zakazu = 0; // сумма всех затрах по заказу
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
 if (isset($print_item['items_buy'] )) { // ПРОДАННЫЕ ТОВАРЫ
    echo "<td>";
    foreach ($print_item['items_buy'] as $tovar_name) {   
        echo $tovar_name['c_1c_article']."<br>";;
    }
    echo "</td>";
 } elseif (isset($print_item['items_returns'] )) { // ТОВАРЫ ИЗ ВОЗВРАТОВ
    echo "<td>";
    foreach ($print_item['items_returns'] as $tovar_name) {   
        echo $tovar_name['c_1c_article']."<br>";;
    }
    echo "</td>";

 } else {
    echo "<td>" . "" . "</td>";
 }
// **********************  выводим на экран количество товаров в Заказе **********************

if (isset($print_item['items_buy'] )) { // ПРОДАННЫЕ ТОВАРЫ
    print_one_string_in_table($print_item,  'count');
 } elseif (isset($print_item['items_returns'] )) { // ТОВАРЫ ИЗ ВОЗВРАТОВ
    echo "<td>" . count($print_item['items_returns'] ) . "</td>";

 } else {
    echo "<td>" . "" . "</td>";
 }

   

// ********************** выводим на экран Стоимость товаров с учётом скидок продавца. **********************

  if (isset($print_item['items_buy'] )) {
    echo "<td>";
    foreach ($print_item['items_buy'] as $tovar_name) {   
        echo $tovar_name['accruals_for_sale']."<br>";
        $summa_accruals_for_sale +=$tovar_name['accruals_for_sale'];
    
    }
    $ALL_summa_accruals_for_sale += $summa_accruals_for_sale;
    echo "</td>";
 } else {
    echo "<td>" . "" . "</td>";
 }

// ********************************      выводим на экран Итоговая сумма операции. ****************************************
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
    echo "<td class= \"bad_desired_price\">";
    foreach ($print_item['items_buy'] as $tovar_name) {  
        if ($tovar_name['accruals_for_sale'] != 0 ) {
            $procent_comissii = round((-$tovar_name['sale_commission']/$tovar_name['accruals_for_sale'])*100,1);
        } else 
        {$procent_comissii = '';}

        echo $tovar_name['sale_commission']."(".$procent_comissii."%)"."<br>";
        $zartati_po_zakazu +=$tovar_name['sale_commission']; // Добавляем комиссию к Затратам 
    }
    echo "</td>";
 } else {
    echo "<td>" . "" . "</td>";
 }
//  ********************** выводим на экран Логистика **********************
  if (isset($print_item['items_buy'] )) {
    echo "<td class= \"bad_desired_price\">";
    foreach ($print_item['items_buy'] as $tovar_name) {  // Прямая Логистика при доставке (УДАЧНАЯ ПРОДАЖА)
        if (isset($tovar_name['logistika'] )) {  
        echo $tovar_name['logistika']."<br>";
        $zartati_po_zakazu +=$tovar_name['logistika']; // Добавляем логистику
        }  
    }
    echo "</td>";
 }  elseif (isset($print_item['items_returns'] )) {
    echo "<td class= \"bad_desired_price\">";
    foreach ($print_item['items_returns'] as $tovar_name) { // Прямая логистика при возврате (ВОЗВРАТ ТОВАРА)
        if (isset($tovar_name['logistika_vozvrat'] )) {  
        echo $tovar_name['logistika_vozvrat']."<br>";
        $zartati_po_zakazu +=$tovar_name['logistika_vozvrat']; // Добавляем логистику
        }  
    }
    echo "</td>";
} else {
    echo "<td>" . "" . "</td>";
 }

 // ********************** выводим на экран Последняя миоя **********************
   if (isset($print_item['items_buy'] )) {
    echo "<td class= \"bad_desired_price\">";
    foreach ($print_item['items_buy'] as $tovar_name) {   
        if (isset($tovar_name['last_mile'] )) {  
            echo $tovar_name['last_mile']."<br>";
            $zartati_po_zakazu +=$tovar_name['last_mile']; // Добавляем последнюю милю к затратам
            }
    }
    echo "</td>";
 } else {
    echo "<td>" . "" . "</td>";
 }

 //  ********************** выводим на экран Обработка отправлений **********************
 if (isset($print_item['items_buy'] )) {
    echo "<td class= \"bad_desired_price\">";
    foreach ($print_item['items_buy'] as $tovar_name) {   
        if (isset($tovar_name['obrabotka_otpravlenia'] )) {  
            echo $tovar_name['obrabotka_otpravlenia']."<br>";
            $zartati_po_zakazu +=$tovar_name['obrabotka_otpravlenia']; // Добавляем обработку операуии к затратам
            }
    }
    echo "</td>";
 }  elseif (isset($print_item['items_returns'] )) {
    echo "<td class= \"bad_desired_price\">";
    foreach ($print_item['items_returns'] as $tovar_name) { // Обработка отправлний при возврате (ВОЗВРАТ ТОВАРА)
        if (isset($tovar_name['obrabotka_otpravlenii_v_SC'] )) {  
        echo $tovar_name['obrabotka_otpravlenii_v_SC']."<br>";
        $zartati_po_zakazu +=$tovar_name['obrabotka_otpravlenii_v_SC']; // Добавляем обработку операуии к затратам
        }  
    }
    echo "</td>";
}else {
    echo "<td>" . "" . "</td>";
 }

 //  ********************** ОбРАТНАЯ ЛОГИСТИКА ПРИ ВОЗВРАТЫ **********************
 if (isset($print_item['items_returns'] )) {
    echo "<td class= \"bad_desired_price\">";
      foreach ($print_item['items_returns'] as $tovar_name) { // Обработка отправлний при возврате (ВОЗВРАТ ТОВАРА)
        if (isset($tovar_name['back_logistika_vozvrat'] )) {  
        echo $tovar_name['back_logistika_vozvrat']."<br>";
        $zartati_po_zakazu +=$tovar_name['back_logistika_vozvrat']; // Добавляем обратную логистику к затратам

        }  
    }
    echo "</td>";
}else {
    echo "<td>" . "" . "</td>";
 }

  //  ********************** ОБРАБОТКА ЗАКАЗА ПРИ ОбРАТНой ЛОГИСТИКе ПРИ ВОЗВРАТЫ **********************
  if (isset($print_item['items_returns'] )) {
    echo "<td class= \"bad_desired_price\">";
      foreach ($print_item['items_returns'] as $tovar_name) { // Обработка отправлний при возврате (ВОЗВРАТ ТОВАРА)
        if (isset($tovar_name['return_obrabotka'] )) {  
        echo $tovar_name['return_obrabotka']."<br>";
        $zartati_po_zakazu +=$tovar_name['return_obrabotka']; // Добавляем Обработка отправлний логистику к затратам

        }  
    }
    echo "</td>";
}else {
    echo "<td>" . "" . "</td>";
 }   


  //  ********************** ПОЗДНЯЯ ОТГРУЗКА**********************
  if (isset($print_item['pozdniaa_otgruzka'] )) {
    echo "<td class= \"bad_desired_price\">".$print_item['pozdniaa_otgruzka']."</td>";
    $zartati_po_zakazu +=$print_item['pozdniaa_otgruzka']; // Добавляем эквайринг к затратам
}else {
    echo "<td>" . "" . "</td>";
 }   

  //  ********************** Хранение Утилизация  **********************
  if (isset($print_item['amount_hranenie'] )) {
    echo "<td class= \"bad_desired_price\">".$print_item['amount_hranenie']."</td>";
    $zartati_po_zakazu +=$print_item['amount_hranenie']; // Добавляем эквайринг к затратам
}else {
    echo "<td>" . "" . "</td>";
 }   







//  ********************** Эквайринг **********************
if (isset($print_item['amount_ecvairing'] )) {

    if ($summa_accruals_for_sale != 0 ) {
        $procent_acquiring = round((-$print_item['amount_ecvairing']/$summa_accruals_for_sale)*100,1);
    } else 
    {$procent_acquiring = '';}
    echo "<td class= \"bad_desired_price\">".round($print_item['amount_ecvairing'],2)."(".$procent_acquiring."%)"."</td>";
    $zartati_po_zakazu +=$print_item['amount_ecvairing']; // Добавляем эквайринг к затратам
                  
    }
 
  else {
    echo "<td>" . "" . "</td>";
 }
// ******************************    Все затраты по заказу *****************************
 echo "<td class= \"bad_desired_price\">" . "$zartati_po_zakazu" . "</td>";

 // ******************************    СУММА ЗАКАЗА ДЛЯ ПОКУПАТЕЛЯ *****************************
 if ($summa_accruals_for_sale > 0) {
 echo "<td class= \"neutral_desired_price\">" . "$summa_accruals_for_sale" . "</td>";
 } else {
    echo "<td>" . "---" . "</td>"; 
 }

 // ******************************    СКОЛЬКО ЗАРАБОТАЛИ НА ЗАКАЗЕ *****************************
 $skolko_zarabotali_na_zakaze = $summa_accruals_for_sale + $zartati_po_zakazu;
 if ($skolko_zarabotali_na_zakaze >= 0) {
    echo "<td class= \"good_desired_price\">" . "$skolko_zarabotali_na_zakaze" . "</td>";
    } else {
    echo "<td class= \"bad_desired_price\">" . "$skolko_zarabotali_na_zakaze" . "</td>";
    }
   



} //////////////////////////////// КОНЕЦ ЦИКЛА



echo "</tr>";

echo "</table>";


echo "hhhhhhhhhhhhhhhhhhhhh =  $ALL_summa_accruals_for_sale";