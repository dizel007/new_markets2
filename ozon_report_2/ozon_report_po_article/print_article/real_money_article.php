<?php


// foreach ($one_sku_in_reestr as $key=>&$one_string_data) {
//     if (!isset($one_string_data['type']['RETURN'])) {
//         unset($one_sku_in_reestr[$key]);
//     }

// }

// print_r($arr_article);
// die();
///////////// выше удлаить ///////////////////

echo "<link rel=\"stylesheet\" href=\"css/main_ozon_reports.css\">";

// Начинаем отрисовывать таблицу 

echo "<table class=\"real_money fl-table\">";

// ШАПКА ТАблицы
echo <<<HTML
<tr>
<th>пп</th>
<th>Опер-я</th>
<th>Артикул</th>
<th>SKU</th>
<th>Дата заказа</th>
<th>Тип<br>отгрузки</th>
<th>Цена в ЛК</th>
<th>Комиссия<br>озон</th>
<th>Прям/обрат<br>логистика</th>
<th>На р/с</th>
<th>Эквайринг<br>Сервисы</th>
<th>Доп.Услуги<br>Штрафы</th>
<th>Итого на р/с<br>Себест-ть</th>
<th>Итого<br>на р/с</th>



</tr>

HTML;
$pp = 0; // номер строки в тублице

// echo "<pre>";
foreach ($arr_article as $key => $one_string_data) {

   echo "<tr>";
        $pp++;   
// пункт по порядку
    echo "<td>" . $pp . "</td>";
 /// тип операции
    echo "<td>"; 

        foreach ($one_string_data['type_operation'] as $type_key=>$type) {
                echo "$type<br>";
       }

    echo "</td>";
    echo "<td class=\"td_logistika\">" . $one_string_data['name'] . "</td>";
    echo "<td>" .  $mp_article."<hr>" .$one_string_data['sku'] . "</td>";
    
// номер и дата заказа
        $link_for_post_delivery_finans_info = 'https://seller.ozon.ru/app/finances/accruals?search='.$one_string_data['post_number_gruzomesto'].'&tab=ACCRUALS_DETAILS';
    echo "<td>"."<a href=\"$link_for_post_delivery_finans_info\" target=\"_blank\">".$one_string_data['post_number_gruzomesto'] . "</a><hr>". $one_string_data['order_date'] . "</td>";
// схема доставки 
    echo "<td>";
    if ($one_string_data['delivery_schema'] == "FBS") {
         echo "<a href=\"https://seller.ozon.ru/app/postings/fbs?tab=delivering&postingDetails=".$one_string_data['post_number_gruzomesto']."\" target=\"_blank\">".
     $one_string_data['delivery_schema'] . "</a>";
     } elseif ($one_string_data['delivery_schema'] == "FBO") {
         echo "<a href=\"https://seller.ozon.ru/app/postings/fbo/".$one_string_data['post_number_gruzomesto']."\" target=\"_blank\">".$one_string_data['delivery_schema'] . "</a>";;
     }
        
     if (isset($one_string_data['ino'])) {echo " (ino)";} // цепляем метку иностранного товара
     if (isset($one_string_data['proc_rekl'])) {echo " (рекл)"; } // цепляем метку иностранного товара

    echo "</td>";
// Цена в личном кабинете
    echo "<td>" . $one_string_data['accruals_for_sale'] . "</td>";
    $summa_accruals_for_sale = @$summa_accruals_for_sale + $one_string_data['accruals_for_sale'];

// Комиссия озон
    echo "<td  class= \"bad_desired_price\">" . $one_string_data['sale_commission']." (".$one_string_data['ozon_procent_commition']."%)" . "</td>";
     $summa_sale_commission = @$summa_sale_commission + $one_string_data['sale_commission'];

// Прямая логистика
    echo "<td class= \"bad_desired_price\">";
    echo $one_string_data['logistika_direct']." (". $one_string_data['procent_stoimosti_logistika_direct'] . "%)";
    echo "<hr>";
    echo $one_string_data['logistika_return'] ;

    echo "</td>";

// Цена на р/с
    echo "<td>" . $one_string_data['amount'] . "</td>";
    $summa_amount = @$summa_amount + $one_string_data['amount'];
// Эквайринг Штрафы
    echo "<td class= \"bad_desired_price\">";
    echo $one_string_data['acquiring'];
    echo "<hr>";
    echo $one_string_data['penalty'];
    echo "</td>";
// Допр услугуи
    echo "<td class= \"bad_desired_price\">";
    echo $one_string_data['dop_uslugi'] ;
    echo "<hr>";
    echo $one_string_data['services_summa'] ;
    echo "</td>";




    // Итого на р/с

    echo "<td>";
    echo "<p class= \"\" >".$one_string_data['amount_na_rs'].
    "<hr>" .$article_sebest."</p>";
    echo "</td>";


// Прибыль
($one_string_data['pribil'] >0)?$amount_flag = 'good_desired_price':$amount_flag = 'bad_desired_price';
    echo "<td>";
    echo "<p class= \"$amount_flag\" >".$one_string_data['pribil']."</p>";
    echo "</td>";


echo "</tr>";

}


echo <<<HTML
<tr>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
HTML;


// цена в ЛК кабинете
echo "<td>".$arr_sum_article_data['accruals_for_sale']."</td>";
// комиссия озоон
echo "<td class= \"bad_desired_price\">".$arr_sum_article_data['sale_commission']."</td>";

//логистика прям обр
$sum_logisk_direct_return = round($arr_sum_article_data['logistika_direct'] + $arr_sum_article_data['logistika_return'],0);
echo "<td class= \"bad_desired_price\">".$arr_sum_article_data['logistika_direct']." | ".$arr_sum_article_data['logistika_return'].
     "<hr>". $sum_logisk_direct_return."</td>";
// amount
echo "<td>".$arr_sum_article_data['amount']."</td>";
// эквайринг  + сервисы
$sum_acquiring_services_summa = round($arr_sum_article_data['acquiring'] + $arr_sum_article_data['services_summa'],0);
echo "<td class= \"bad_desired_price\">".$arr_sum_article_data['acquiring']." | ".$arr_sum_article_data['services_summa'].
"<hr>". $sum_acquiring_services_summa."</td>";
// допрасходы

$sum_dopi_penalty = round($arr_sum_article_data['dop_uslugi'],0);
echo "<td class= \"bad_desired_price\">".$arr_sum_article_data['dop_uslugi']." | ".$arr_sum_article_data['penalty'].
"<hr>". $sum_dopi_penalty."</td>";

// на р/с за вычетом всего
echo "<td>".$arr_sum_article_data['amount_na_rs']."</td>";


echo "<td>".$arr_sum_article_data['pribil']."</td>";

// echo "<td>".$arr_sum_article_data['sale_commission']."</td>";
// echo "<td>".$arr_sum_article_data['sale_commission']."</td>";

// $arr_sum_article_data['acquiring'] = @$arr_sum_article_data['acquiring'] +  round($one_tovar['acquiring'],0);
// $arr_sum_article_data['penalty'] = @$arr_sum_article_data['penalty'] +  round($one_tovar['penalty'],0);


echo "</tr>";


echo "</table>";



