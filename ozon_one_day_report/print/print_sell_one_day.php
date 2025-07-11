<?php

$summa_count = 0;
$summa_price = 0;
echo '<link rel="stylesheet" href="css/sell_table.css">';
echo "<table class=\"sell_mp_table\">";

echo "<thead>";
echo "<tr>";
echo "<th>Артикул</th>"; 
echo "<th>Количество</th>"; 
echo "<th>сумма заказа</th>"; 

echo "<th>баллы за скидку</th>"; 
echo "<th>вознаграждение<br>Ozon</th>"; 
echo "<th>Итого к начислению</th>"; 
echo "<th>Выплаты по <br>механикам лояльности<br> партнёров:<br>зелёные цены.</th>"; 
echo "<th>Доля комиссии<br> за продажу<br>по категории</th>"; 
echo "<th>Цена продавца 1 шт<br>с учётом скидки.</th>"; 

echo "</tr>";
echo "</thead>";
foreach ($arr_all_nomenklatura as $nomenklatura) {
echo "<tr>";

echo "<td><a href=\"?json_data_send=$str_data_send\">{$nomenklatura['main_article_1c']}</a></td>";
    foreach ($arr_article as $key => $item) {
        $find_article = 0;
        if (mb_strtolower($key) == mb_strtolower($nomenklatura['main_article_1c'])) {
            $find_article = 1;
            echo "<td>{$item['count']}</td>"; 
            echo "<td>{$item['amount']}</td>";  
            echo "<td>{$item['bonus']}</td>";  
            echo "<td>{$item['standard_fee']}</td>";  
            echo "<td>{$item['total']}</td>";
            
            echo "<td>{$item['bank_coinvestment']}</td>"; 
              $commission_ratio = $item['commission_ratio']*100;
            echo "<td>{$commission_ratio}</td>";   
            echo "<td>{$item['seller_price_per_instance']}</td>";   
         
            $summa_count += $item['count'];
            $summa_price += $item['amount'];

            break;
        } 


    }

    if ($find_article == 0) {
        echo "<td>-</td>"; 
        echo "<td>-</td>"; 
        echo "<td>-</td>"; 
        echo "<td>-</td>"; 
        echo "<td>-</td>"; 
        echo "<td>-</td>"; 
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
echo "<td>-</td>"; 
echo "<td>-</td>"; 
echo "<td>-</td>"; 
echo "<td>-</td>"; 
echo "<td>-</td>"; 
echo "<td>-</td>"; 
echo "</tr>";

echo "</table>";