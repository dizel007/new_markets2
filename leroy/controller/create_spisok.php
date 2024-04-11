<?php

/**
 * Выводим на экран выбранные заказы 
 */

function make_spisok_sendings ($new_array_create_sends){


// echo "<pre>";
echo "<table class = \"prod_table\">";
echo "<tr>
<td>пп</td>
<td>номер отправления</td>
<td>дата заказа</td>
<td>Дата Сбора</td>

<td>цена заказа</td>
<td>арт</td>
<td>наименование</td>
<td>кол-во</td>
<td>цена за шт</td>

</tr>";
$i=1;
foreach ($new_array_create_sends as $item) {
//  print_r($item);
$count_td = count($item['products']);
$j1=0;
echo "<tr>";
echo "<td rowspan=\"$count_td\">$i</td>
<td rowspan=\"$count_td\">".$item['id']."</td>
<td rowspan=\"$count_td\">".$item['creationDate']."</td>
<td rowspan=\"$count_td\"><b>".$item['pickup']['pickupDate']."</b></td>


<td rowspan=\"$count_td\">".number_format($item['parcelPrice'],2)."</td>";
// echo "<td>";
// echo "<table>";
foreach ($item['products'] as $prods) {
    // echo "<tr>";
    $j1++;
    if ($j1 > 1) {
       echo "<tr>"; 
    }
    echo "<td>".$prods['vendorCode']."</td>";
    echo "<td>".$prods['name']."</td>";
    echo "<td>".$prods['qty']."</td>";
    echo "<td>".number_format($prods['price'],2)."</td>";
    if ($j1 >1) {
        echo "</tr>"; 
     }
    
}

// echo "</table>";
// echo "</td>";
echo "</tr>";
$i++;
}
echo "</table>";
}
