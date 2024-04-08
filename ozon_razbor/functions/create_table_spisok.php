<?php
/**
 * Выводим на экран выбранные заказы 
 */

 function make_spisok_sendings_ozon ($new_array_create_sends){


    // echo "<pre>";
    

    echo "<table class = \"prod_table\">";
    echo "<tr>
    <td>пп</td>
    <td>номер отправления</td>
    <td>shipment_date</td>
    <td>status</td>
    

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
    <td rowspan=\"$count_td\">".$item['posting_number']."</td>
    <td rowspan=\"$count_td\">".$item['shipment_date']."</td>
    <td rowspan=\"$count_td\">".$item['status']."</td>";

    // echo "<td>";
    // echo "<table>";
    foreach ($item['products'] as $prods) {
        // echo "<tr>";
        $j1++;
        if ($j1 > 1) {
           echo "<tr>"; 
        }
        echo "<td>".$prods['offer_id']."</td>";
        echo "<td>".$prods['name']."</td>";
        echo "<td>".$prods['quantity']."</td>";
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


/**
 * Выводим список для 1С какого товара сколько купили
 */
function make_spisok_sendings_ozon_1С ($array_prods){


   // echo "<pre>";
   echo "<table class = \"prod_table_2\">";
   echo "<tr>
   <td>пп</td>
   <td>Артикул</td>
   <td>Кол-во</td>
   <td>Наименование</td>
   <td>Цена за шт</td>
   
   </tr>";
   $i=1;
   foreach ($array_prods as $key=>$item) {
   //  print_r($item);

   echo "<tr>";
   echo "
   <td>$i</td>
   <td>".$key."</td>
   <td>".$item['quantity']."</td>
   <td>".$item['name']."</td>
   <td>".number_format($item['price'],2)."</td>";

   echo "</tr>";
   $i++;
   }
   echo "</table>";
}