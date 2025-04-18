<?php

$summa_count = 0;
$summa_price = 0;
echo '<link rel="stylesheet" href="css/sell_table.css">';
echo "<table class=\"sell_mp_table\">";

echo "<thead>";
echo "<tr>";
echo "<th>Артикул</th>"; 
foreach ($arr_warehouse as $gorod => $item) {


foreach ($item as $gorod=>$z) {
    $arr_gorod[$gorod] = $gorod;
}
}; 
foreach ($arr_gorod as $gorod=>$z) {
echo "<th>$gorod</th>"; 
}
echo "</tr>";
echo "</thead>";



foreach ($arr_all_nomenklatura as $nomenklatura) {
echo "<tr>";
    // выводис артикул      

    echo "<td>{$nomenklatura['main_article_1c']}</td>";
    foreach ($arr_gorod as $city) {
        $fin_priz = 0;
    foreach ($arr_warehouse as $key_art => $item) {
      
        if ((mb_strtolower($key_art) == mb_strtolower($nomenklatura['main_article_1c'])) ) {
           
        foreach($item as $key_gorod_item=> $count_item) {
                                    
                        if (($city == $key_gorod_item))  {
                            // echo "<td>{$count_item}<br>$key_gorod_item</td>"; 
                            echo "<td>{$count_item}</td>"; 

                            $fin_priz = 1;
                       } 


                    
                }
                if ($fin_priz == 0) { echo "<td>-</td>";} 
                
            }
         
        } 
//    echo "$key_gorod_item<br>"; 
  


    }

echo "</tr>";


}

// echo "<tr>";
// echo "<td>ИТОГО</td>"; 
// echo "<td>$summa_count</td>"; 
// $summa_price = number_format($summa_price,0);
// echo "<td>$summa_price</td>"; 
// echo "</tr>";

echo "</table>";