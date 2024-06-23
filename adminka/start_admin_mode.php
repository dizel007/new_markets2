<?php


require_once("../connect_db.php"); // подключение к БД

require_once("../pdo_functions/pdo_functions.php");  // 


$nomenclatura = select_all_nomenklaturu($pdo);
echo "<pre>";




$stmt = $pdo->prepare("SELECT * FROM `ostatki_po_skladam` WHERE `active_tovar` = 1 ");
$stmt->execute([]);
$tovar_table_data = $stmt->fetchAll(PDO::FETCH_ASSOC);

// print_r($tovar_table_data[0]);
// print_r($nomenclatura[0]);

foreach ($nomenclatura as $item) {
   
    foreach ($tovar_table_data as &$item_2) {

        if ($item['main_article_1c'] == $item_2['main_article_1c']) {
        $item_2['number_in_spisok'] = $item['number_in_spisok'] ;
        continue;
            
        }


}


}

// сортируем товар  по номерному порядку 
array_multisort(array_column($tovar_table_data, 'number_in_spisok'), SORT_ASC, $tovar_table_data);


// echo "<pre>";
// print_r($tovar_table_data);


echo <<<HTML
 <link rel="stylesheet" href="css/main_table.css">
HTML;

echo "<form action=\"update_new_ostatki.php\" method=\"post\">";
   
    echo "<table class=\"prods_table\">";


    echo "<tr  class=\"rovnay_table_shapka\">";
        echo "<td>Артикул МП</td>";
        echo "<td>ВБ ООО<br>Upd</td>";
        echo "<td>ВБ ИП<br>Upd</td>";
        echo "<td>Озон ООО<br>Upd</td>";
        echo "<td>ОЗОН ИП<br>Upd</td>";
        echo "<td>ЯМ ООО<br>Upd</td>";
        echo "<td>БЛОК<br>Upd</td>";
    echo "</tr>";

foreach ($tovar_table_data as $item) {


// Проверяем сумму процентов распределния товаров во всех магазинах
        $summa100procentov = $item['wb_anmaks'] +$item['wb_ip_zel'] +$item['ozon_anmaks']+$item['ozon_ip_zel']+$item['ya_anmaks_fbs'];
// если больше 100% то подкрашиваем цветмо
       ($summa100procentov > 100)? $bolshe100 = 'bolshe100': $bolshe100 = '' ;

        
        echo "<tr class=\"rovnay_table  $bolshe100 \">";

        echo "<td>".$item['main_article_1c']."</td>";

        $name_for_update = $item['main_article_1c'];
//  WB OOO ANM
echo  "<td><input class=\"text-field__input future_ostatok\" type=\"number\" name=\"_mp_wb_anmaks_$name_for_update\" value=".$item['wb_anmaks']."></td>";
///  WB IP ZEL
echo  "<td><input class=\"text-field__input future_ostatok\" type=\"number\" name=\"_mp_wb_ip_zel_$name_for_update\" value=".$item['wb_ip_zel']."></td>";
/// OZON OOO ANM
echo  "<td><input class=\"text-field__input future_ostatok\" type=\"number\" name=\"_mp_ozon_anmaks_$name_for_update\" value=".$item['ozon_anmaks']."></td>";
/// OZON IP ZEL
echo  "<td><input class=\"text-field__input future_ostatok\" type=\"number\" name=\"_mp_ozon_ip_zel_$name_for_update\" value=".$item['ozon_ip_zel']."></td>";
/// YABNEX OOO ANM
echo  "<td><input class=\"text-field__input future_ostatok\" type=\"number\" name=\"_mp_ya_anmaks_fbs_$name_for_update\" value=".$item['ya_anmaks_fbs']."></td>";

// заблокированный товар иили нет
if ($item['block_tovar']  == 1) {
               
    echo  "<td><input type=\"checkbox\" checked name=\"_mp_block_$name_for_update\"> </td>";
    } else {
      echo  "<td><input type=\"checkbox\" name=\"_mp_block_$name_for_update\" > </td>";
 }

        echo "</tr>";
        
    }
    echo "</table>";
    echo "<input class=\"btn\" type=\"submit\" value=\"ОБНОВИТЬ ДАННЫЕ\">";
