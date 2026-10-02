<?php




function print_sell_po_gorodam ($arr_warehouse , $art_ar) {

echo '<link rel="stylesheet" href="css/sell_table.css">';
echo "<table class=\"town_mp_table\">";

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

foreach ($art_ar as $nomenklatura=>$x) {
echo "<tr>";
    // выводис артикул      

    echo "<td>{$nomenklatura}</td>";
    foreach ($arr_gorod as $city) {
        $fin_priz = 0;
    foreach ($arr_warehouse as $key_art => $item) {
      
        if ((mb_strtolower($key_art) == mb_strtolower($nomenklatura)) ) {
           
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
    }

echo "</tr>";


}

echo "</table>";

}