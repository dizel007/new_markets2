<?php

$summa_count_ooo = 0;
$summa_price_ooo = 0;
$summa_count_ip = 0;
$summa_price_ip = 0;
echo '<link rel="stylesheet" href="css/sell_table.css">';
echo "<table class=\"sell_mp_table\">";

echo "<thead>";
echo "<tr>";
echo "<th class=\"red_windows\">Артикул</th>"; 
echo "<th>Количество<br>ООО</th>"; 
echo "<th>Стоимость<br>ООО</th>"; 
echo "<th class=\"blue_windows\">Количество<br>ИП</th>"; 
echo "<th class=\"blue_windows\">Стоимость<br>ИП</th>"; 

echo "</tr>";

echo "</thead>";

foreach ($arr_sort_ar as $atricle=>$number) {

  
echo "<tr>";
echo "<td>{$atricle}</td>";
if (isset($arr_article_ooo[$atricle])) {
            echo "<td>{$arr_article_ooo[$atricle]['count']}</td>";  
            $summa = number_format($arr_article_ooo[$atricle]['price'],0);
            echo "<td>{$summa}</td>"; 
            $summa_count_ooo += $arr_article_ooo[$atricle]['count'];
            $summa_price_ooo += $arr_article_ooo[$atricle]['price'];
    } 
         else {
echo "<td>  --  </td>"; 
echo "<td>  --  </td>"; 
    }
if (isset($arr_article_ip[$atricle])) {
            echo "<td>{$arr_article_ip[$atricle]['count']}</td>";  
            $summa = number_format($arr_article_ip[$atricle]['price'],0);
            echo "<td>{$summa}</td>"; 
            $summa_count_ip += $arr_article_ip[$atricle]['count'];
            $summa_price_ip += $arr_article_ip[$atricle]['price']; 
 
        
}  else {
    echo "<td>  --  </td>"; 
echo "<td>  --  </td>"; 
}
 echo "</tr>";

}
echo "<tr>";
echo "<td>ИТОГО</td>"; 
echo "<td><b>$summa_count_ooo</b></td>"; 
$summa_price_ooo = number_format($summa_price_ooo,0);
echo "<td><b>$summa_price_ooo</b></td>"; 
echo "<td><b>$summa_count_ip</b></td>"; 
$summa_price_ip = number_format($summa_price_ip,0);
echo "<td><b>$summa_price_ip</b></td>"; 
echo "</tr>";

echo "</table>";