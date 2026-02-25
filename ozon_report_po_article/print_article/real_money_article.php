<?php



echo "<link rel=\"stylesheet\" href=\"css/main_ozon_reports.css\">";

// Начинаем отрисовывать таблицу 

echo "<table class=\"real_money fl-table\">";

// ШАПКА ТАблицы
echo "<tr>";
echo "<th>пп</th>";
echo "<th>Опер-я</th>";
echo "<th>Артикул</th>";
echo "<th>Достав</th>";
echo "<th>Склад</th>";
echo "<th>№ заказа</th>";
echo "<th>Ст-ть<br>товаров <br>в кабинете</th>";

echo "<th>Комиссия<br>озон</th>";
echo "<th>Лог-ка</th>";
echo "<th>Обр<br>лог-ка</th>";
echo "<th>Поздняя<br>отгрузка</th>";
echo "<th>Продвиж.<br>брэнда</th>";

echo "<th>Эк-г</th>";
echo "<th>Затраты <br>по заказу</th>";

echo "<th>Цена за<br>вычетом<br>всего</th>";
echo "<th>Себ-ть</th>";
echo "<th>Зар-ли<br>с зак-а</th>";
echo "</tr>";

$pp = 0; // номер строки в тублице
$ALL_summa_accruals_for_sale = 0;
$ALL_summa_amount = 0;
$ALL_summa_sale_commission = 0;

$ALL_summa_obraboka_otpavlenii = 0;
$ALL_summa_logistika = 0;
$ALL_summa_last_mile = 0;
$ALL_summa_our_pribil_s_zakaza = 0;
// echo "<pre>";
foreach ($one_tovar_reestr as $key => $type_logistik) {

   foreach ($type_logistik as $key_logistik => $all_item) {
    foreach ($all_item as $print_item) {
        echo "<tr>";
        $pp++;   
   
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


    // костыль когда нет стоимости логистики 
 if (!isset($print_item['logistika'])) {
    $print_item['logistika'] = 0;
 }

     // костыль когда нет стоимости логистики 
 if (!isset($print_item['sale_commission'])) {
    $print_item['sale_commission'] = 0;
 }


 // костыль окончен
    echo "<td>" . $pp . "</td>";
    echo "<td>" . $type_operation . "</td>";
    echo "<td>" . $key . "</td>";
    echo "<td>" . $key_logistik . "</td>";
    echo "<td>" .$print_item['warehouse_name'] . "</td>";

    echo "<td>" . $print_item['post_number_gruzomesto']."<br>". $print_item['order_date'].  "</td>";

// Стоимость товара в кабинете
    echo "<td>" . $print_item['accruals_for_sale'] . "</td>";
// КОмиссия озона
if ($print_item['accruals_for_sale'] != 0) {
$procent_ozon = round (-$print_item['sale_commission'] / ($print_item['accruals_for_sale']/100) , 1);
} else {
   $procent_ozon = 0; 
}
    echo "<td class= \"bad_desired_price\">" . $print_item['sale_commission']."($procent_ozon%)" . "</td>";
    echo "<td class= \"bad_desired_price\">" . $print_item['logistika'] . "</td>";

// Обратная логистика
if (isset( $print_item['logistika_vozvrat'] )) {
    echo "<td class= \"bad_desired_price\">" . $print_item['logistika_vozvrat'] . "</td>";
}else  {
    echo "<td>" . "0" . "</td>";
}

// Поздняя отгрузка
if (isset($print_item['pozdniaa_otgruzka'])) {
     echo "<td class= \"bad_desired_price\">" .  number_format($print_item['pozdniaa_otgruzka'], 2, '.', ' ') . "</td>";
} else {
    echo "<td>" . "0" . "</td>";   
}

// Поздняя отгрузка
if (isset($print_item['prodvizenie_branda'])) {
     echo "<td class= \"bad_desired_price\">" .  number_format($print_item['prodvizenie_branda'], 2, '.', ' ')  . "</td>";
} else {
    echo "<td>" . "-" . "</td>";   
}



 // Эквайринг

 if (isset($print_item['acquiring'])) {
    echo "<td class= \"bad_desired_price\">" . $print_item['acquiring'] . "</td>";
} else {
    echo "<td>" . "" . "</td>";
}

 // Затраты по заказу
 $zartati_po_zakazu = round(@$print_item['sale_commission'] + @$print_item['logistika'] + 
                      @$print_item['logistika_vozvrat'] + @$print_item['pozdniaa_otgruzka'] + @$print_item['prodvizenie_branda'] + 
                      @$print_item['acquiring'],2);

  echo "<td>" .  $zartati_po_zakazu . "</td>";

// Цена за вычетом всего 
$price_with_all_commisions = $print_item['accruals_for_sale'] +  $zartati_po_zakazu;

    if ($price_with_all_commisions >= 0) {
        echo "<td class= \"good_desired_price\">"."$price_with_all_commisions" . "</td>";
    } else {
        echo "<td class= \"bad_desired_price\">" . "$price_with_all_commisions" . "</td>";
   }

// Себесьлимость и расчет относительно ее


echo "<td class= \"\">" . $print_item['min_price']. "</td>";

// Заработали на артикула

if ($print_item['accruals_for_sale'] == 0) {
    $our_pribil_with_min_price = round ($price_with_all_commisions,2) ;
} else {
    $our_pribil_with_min_price = round ($price_with_all_commisions - $print_item['min_price'],2);
}


if ($our_pribil_with_min_price >= 0) {
    echo "<td class= \"good_desired_price\">" . "$our_pribil_with_min_price" . "</td>";
} else {
    echo "<td class= \"bad_desired_price\">" .  "$our_pribil_with_min_price" . "</td>";
}
    echo "</tr>";


    if ($pp > 2000) die('<br>ppppp10');
    }
  }


}

//  die('vvvvvvvvvvvv');


echo "</tr>";

echo "</table>";
