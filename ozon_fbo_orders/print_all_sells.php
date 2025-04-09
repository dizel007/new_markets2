<?php

$summa_count = 0;
$summa_price = 0;
echo '<link rel="stylesheet" href="css/sell_table.css">';
echo "<table class=\"sell_mp_table\">";

echo "<thead>";
echo "<tr>";
echo "<th>Артикул</th>"; 
echo "<th>Количество</th>"; 
echo "<th>Стоимость</th>"; 
echo "</tr>";
echo "</thead>";
foreach ($arr_all_nomenklatura as $nomenklatura) {
echo "<tr>";
echo "<td>{$nomenklatura['main_article_1c']}</td>";
    foreach ($arr_article as $key => $item) {
        $find_article = 0;
        if (mb_strtolower($key) == mb_strtolower($nomenklatura['main_article_1c'])) {
            $find_article = 1;
            echo "<td>{$item['count']}</td>";  
            $summa = number_format($item['price'],0);
            echo "<td>{$summa}</td>"; 
            $summa_count += $item['count'];
            $summa_price += $item['price'];

            break;
        } 


    }

    if ($find_article == 0) {
        echo "<td>-</td>"; 
        echo "<td>-</td>"; 
    }
echo "</tr>";
}
echo "<tr>";
echo "<td>ИТОГО</td>"; 
echo "<td>$summa_count</td>"; 
$summa_price = number_format($summa_price,0);
echo "<td>$summa_price</td>"; 
echo "</tr>";

echo "</table>";