<?php




function print_article_from_clusters ($arr_for_art_report , $art_ar) {

echo "<pre>";
// print_r($arr_for_art_report['82401-ч']);




foreach ($arr_for_art_report as $article => $cityClusterFrom) {
    foreach ($cityClusterFrom as $nameCity => $orders) {
         foreach ($orders as $order) {
// print_r($order);
        $warehouse_name = $order['analytics_data']['warehouse_name'];
        $cluster_from = $order['financial_data']['cluster_from'];
        $cluster_to = $order['financial_data']['cluster_to'];

$arr_article_cluster_from_quantity[$article][$cluster_from][$cluster_to] = @$arr_article_cluster_from_quantity[$article][$cluster_from][$cluster_to] + 
                                                                    $order['products'][0]['quantity'];
$arr_all_sells_in_cluster[$article][$cluster_from] = @$arr_all_sells_in_cluster[$article][$cluster_from] + $order['products'][0]['quantity'];
    }
}}

// print_r($arr_all_sells_in_cluster['6211']['Самара']);

// die();

echo '<link rel="stylesheet" href="css/sell_table.css">';

echo '<h1 style="text-align: center;">Поартикульные продажи товаров на кластерам</h1>';

foreach ($arr_article_cluster_from_quantity as $article => $clusterFromData) {
      echo "<table class=\"town_mp_table\">";
            echo "<thead>";
            echo "<tr >";
                echo "<th colspan=\"4\"  style=\"width:40%; font-size:16px; background-color: SteelBlue; \">$article </th>"; 
          

    foreach ($clusterFromData as $clusterFrom => $clusteToQuantity) {
       $stringCount = count($clusteToQuantity);
        

            echo "</tr>";
            echo "</thead>";
             echo "<tr>";
            echo "<td style=\"width:40%\" rowspan=\"$stringCount\">$clusterFrom </td>"; 
            $all_quantity =  $arr_all_sells_in_cluster[$article][$clusterFrom];
            echo "<td style=\"width:10%\" rowspan=\"$stringCount\" > $all_quantity</td>"; 
   foreach ($clusteToQuantity as $clusteTo => $quantity_z) {
    
        
            echo "<td style=\"width:40%\" >$clusteTo </td>"; 
            echo "<td style=\"width:10%\" >$quantity_z</td>"; 
            
        echo "</tr>";

              
            }
         



    }


echo "</table>";


echo "<br><br>";
}}