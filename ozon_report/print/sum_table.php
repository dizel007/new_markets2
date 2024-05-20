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


foreach ($arr_article as $key=>$item) {
    
    $article = get_article_by_sku_fbs($ozon_sebest, $key); // получаем артикл по СКУ
    
   /// ОБЩИЕ СУММЫ 
    @$count +=$item['count']; // количеств проданных товарв продажи 
    @$accruals_for_sale +=$item['accruals_for_sale']; // сумма продажи 
    

    @$amount_bez_equaring = $item['amount'] + $item['amount_ecvairing']; // сумма к выплате (уже без эквайринг) 
    @$amount +=$amount_bez_equaring; // сумма к вылате 
    
    
  
    @$one_shtuka = round($amount_bez_equaring/$item['count'],2); // цена за штуку нам в карман (минус эквайринг)
    @$one_shtuka_buyer = round($item['accruals_for_sale']/$item['count'],2); // цена за штуку для покупателя



    @$sale_commission +=$item['sale_commission']; // КОММИСИЯ ОЗОН
    @$logistika +=$item['logistika']; // Общая стоимость 
    @$sborka +=$item['sborka']; // Общая стоимость 
    @$lastMile +=$item['lastMile']; // Общая стоимость 
   

    @$amount_hranenie +=$item['amount_hranenie']; // общая стоимость хранения 
    @$amount_ecvairing +=$item['amount_ecvairing']; // Общая стоимость эквайринга
    @$compensation += $item['compensation'] ; // Общая стоимость недовлажений
    @$amount_vozrat +=$item['amount_vozrat']; // Общая стоимость возвратов
    


    echo "<tr>";

        // if (isset($item['name'])){echo "<td>".$item['name']."</td>";}else{echo "<td>"."</td>";}
        if (isset($article)){
            echo "<td><b>".$article."</b></td>";
            } else {
            echo "<td>"."</td>";
        }
        if (isset($item['count'])){
            $arr_report_items[$article]['count_sell']  = $item['count']; //// массив всех данных 
            echo "<td>".$item['count']."</td>";
        } else { 
            $arr_report_items[$article]['count_sell']  = ""; //// массив всех данных 
            echo "<td>"."</td>"; 
        }
// ценя для покупателья
        if (isset($item['amount'])){
            $arr_report_items[$article]['buyer_price']  = $item['accruals_for_sale']; //// массив всех данных 
            $arr_report_items[$article]['buyer_price_za_shtuku']  = $one_shtuka_buyer; //// массив всех данных 

            echo "<td>".$item['accruals_for_sale']."<br>".$one_shtuka_buyer."</td>";
         } else {
            $arr_report_items[$article]['buyer_price']  = ""; //// массив всех данных 
            $arr_report_items[$article]['buyer_price_za_shtuku']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        } 
 // сумма продаж      
        if (isset($item['amount'])){
            $arr_report_items[$article]['amount_bez_equaring']  = $amount_bez_equaring; //// массив всех данных 
            $arr_report_items[$article]['amount_bez_equaring_za_shtuku']  = $one_shtuka; //// массив всех данных 
            echo "<td>".$amount_bez_equaring."<br>".$one_shtuka."</td>";
        } else {
            $arr_report_items[$article]['amount_bez_equaring']  = ""; //// массив всех данных 
            $arr_report_items[$article]['amount_bez_equaring_za_shtuku']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        }
//Комиссия Озон        
        if (isset($item['sale_commission'])){
            $arr_report_items[$article]['sale_commission']  = $item['sale_commission']; //// массив всех данных 
            echo "<td>".$item['sale_commission']."</td>";
        } else { 
            $arr_report_items[$article]['sale_commission']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        }
// Логистика
        if (isset($item['logistika'])){
            $arr_report_items[$article]['logistika']  = $item['logistika']; //// массив всех данных 
            echo "<td>".$item['logistika']."</td>";
        } else {
            $arr_report_items[$article]['logistika']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        }
// обратная логистика 
        if (isset($item['back_logistika'])){
            $arr_report_items[$article]['back_logistika']  = $item['back_logistika']; //// массив всех данных 
            echo "<td>".$item['back_logistika']."</td>";
        } else {
            $arr_report_items[$article]['back_logistika']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        }
// Сборка
        if (isset($item['sborka'])){
            $arr_report_items[$article]['sborka']  = $item['sborka']; //// массив всех данных 
            echo "<td>".$item['sborka']."</td>";
        } else { 
            $arr_report_items[$article]['sborka']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        }
// ОБратна сборка
        if (isset($item['back_sborka'])){
            $arr_report_items[$article]['back_sborka']  = $item['back_sborka']; //// массив всех данных 
            echo "<td>".$item['back_sborka']."</td>";
        } else {
            $arr_report_items[$article]['back_sborka']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        }

// ОБратная обработка
        if (isset($item['return_obrabotka'])){
            $arr_report_items[$article]['return_obrabotka']  = $item['return_obrabotka']; //// массив всех данных 
            echo "<td>".$item['return_obrabotka']."</td>";
        } else {
            $arr_report_items[$article]['return_obrabotka']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        }

// Последняя Миля
        if (isset($item['lastMile'])){
            $arr_report_items[$article]['lastMile']  = $item['lastMile']; //// массив всех данных 
            echo "<td>".$item['lastMile']."</td>";
        } else {
            $arr_report_items[$article]['lastMile']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        }

        if (isset($item['amount_hranenie'])){
            $arr_report_items[$article]['amount_hranenie']  = $item['amount_hranenie']; //// массив всех данных 
            echo "<td>".$item['amount_hranenie']."</td>";
        } else {
            $arr_report_items[$article]['amount_hranenie']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        }
        if (isset($item['compensation'])){
            $arr_report_items[$article]['compensation']  = $item['compensation']; //// массив всех данных 
            echo "<td>".$item['compensation']."</td>";
        } else { 
            $arr_report_items[$article]['compensation']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        }
        if (isset($item['amount_ecvairing'])){
            $arr_report_items[$article]['amount_ecvairing']  = $item['amount_ecvairing']; //// массив всех данных 
            echo "<td>".$item['amount_ecvairing']."</td>";
        } else { 
            $arr_report_items[$article]['amount_ecvairing']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        }
        if (isset($item['count_vozvrat'])){
            $arr_report_items[$article]['count_vozvrat']  = $item['count_vozvrat']; //// массив всех данных 
            echo "<td>".$item['count_vozvrat']."</td>";
        } else {
            $arr_report_items[$article]['count_vozvrat']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        }
        if (isset($item['amount_vozrat'])){
            $arr_report_items[$article]['amount_vozrat']  = $item['amount_vozrat']; //// массив всех данных 
            echo "<td>".$item['amount_vozrat']."</td>";
        } else {
            $arr_report_items[$article]['amount_vozrat']  = ""; //// массив всех данных 
            echo "<td>"."</td>";
        }


    echo "</tr>";


}

// СТРОКА ИТОГО ТАблицы
echo "<tr>";
    echo "<td></td>"; // Наименование
    echo "<td>$count</td>"; // Количество
    echo "<td>$accruals_for_sale</td>"; // общая сумма
    echo "<td>$amount</td>"; // общая сумма
    if (isset($sale_commission)){echo "<td>".$sale_commission."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий


    if (isset($logistika)){echo "<td>".$logistika."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий
    if (isset($summa_obratnoy_logistik)){echo "<td>".$summa_obratnoy_logistik."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий
    
 // сумма Сборки   
    if (isset($sborka)){echo "<td>".$sborka."</td>";}else{echo "<td>"."</td>";} 
// сумма обратной Сборки
    if (isset($back_sborka)){echo "<td>".$back_sborka."</td>";}else{echo "<td>"."</td>";} 
// сумма обратной обработки
if (isset($return_obrabotka)){echo "<td>".$return_obrabotka."</td>";}else{echo "<td>"."</td>";} 

    

    if (isset($lastMile)){echo "<td>".$lastMile."</td>";}else{echo "<td>"."</td>";} // сумма коммиссий


    if (isset($amount_hranenie)){echo "<td>".$amount_hranenie."</td>";}else{echo "<td>"."</td>";} // сумма хранения
    if (isset($compensation)){echo "<td>".$compensation."</td>";}else{echo "<td>"."</td>";} // сумма эквайринга
    if (isset($amount_ecvairing)){echo "<td>".$amount_ecvairing."</td>";}else{echo "<td>"."</td>";} // сумма эквайринга
    echo "<td></td>";
    if (isset($amount_vozrat)){echo "<td>".$amount_vozrat."</td>";}else{echo "<td>"."</td>";} // сумма возвратов



echo "</tr>";

echo "</table>";


// echo "<pre>";
// print_r($arr_report_items);