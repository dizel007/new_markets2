<?php
require_once "../connect_db.php";
require_once "index_start.php";
$shop_name = 'ozon_anmaks';
// $date_start = '2025-08-01';
// $date_end = '2025-08-31';

print_fbo_table_XX($pdo, 'ozon_anmaks', $date_start,$date_end );
print_fbo_table_XX($pdo, 'ozon_ip_zel', $date_start,$date_end );


die();



function print_fbo_table_XX($pdo, $shop_name, $date_start,$date_end ) {

echo "<h1> FBO  $shop_name  </h1>";

$sth = $pdo->prepare("SELECT * FROM `z_ozon_fbo_sell` WHERE `shop_name` = '$shop_name' AND `date` >= :date_start AND `date` <= :date_end");
$sth->execute(array('date_start' => $date_start , 'date_end' => $date_end));
$array_sell = $sth->fetchAll(PDO::FETCH_ASSOC);

$sth = $pdo->prepare("SELECT * FROM `z_ozon_fbo_stocks` WHERE `shop_name` = '$shop_name' AND `date` >= :date_start AND `date` <= :date_end");
$sth->execute(array('date_start' => $date_start, 'date_end' => $date_end));
$array_stock = $sth->fetchAll(PDO::FETCH_ASSOC);


// echo "<pre>";
// print_r($array_stock[0]);


// Формруем массив проданных товаровпо датам / артикулам
foreach ($array_sell  as $items) {
        $arr_for_print_fbo_sell [$items['date']][$items['1c_article']]['fbo_sell'] = $items['fbo_sell'];
        $arr_article[$items['1c_article']] = $items['1c_article']; // массив авртикуло
        $arr_dates[$items['date']] = $items['date']; // массив дат
        $arr_summ_sell[$items['1c_article']] = @$arr_summ_sell[$items['1c_article']] + $items['fbo_sell'];
}


// Формруем массив остатков по датам / артикулам
foreach ($array_stock  as $items) {

        $arr_for_print_fbo_in_stock [$items['date']][$items['1c_article']]['fbo_in_stock'] = $items['fbo_in_stock'];
        // $arr_article[$items['1c_article']] = $items['1c_article']; // массив авртикуло
        $arr_dates[$items['date']] = $items['date']; // массив дат

}



// echo "<pre>";
// print_r($arr_for_print_fbo_in_stock);
// echo "</pre>";
// die();

echo '<link rel="stylesheet" href="css/fbo_table_data.css">';

echo "<table class=\"sell_mp_table\">";
echo "<thead>";
echo "<tr>";
echo "<td>АРТИКУЛ</td>";
foreach ($arr_dates as $dates) {
    $timestamp = strtotime($dates);
$date =  date("d", $timestamp);
    echo "<td>$date</td>";
}
echo "<td>СВОД</td>";
echo "</tr>";
echo "</thead>";


foreach ($arr_article as $article) {
echo "<tr>";
     echo "<td rowspan = 2>{$article}</td>";
    foreach ($arr_dates as $date) {
        if (isset($arr_for_print_fbo_sell[$date][$article]['fbo_sell'] )) {
        echo "<td>{$arr_for_print_fbo_sell[$date][$article]['fbo_sell']}</td>";
        // echo "<td>{$arr_for_print[$date][$article]['fbo_in_stock']}</td>";
        } else {
            echo "<td> - </td>";
    }
    
    }
      echo "<td> ИТОГО  : <b>{$arr_summ_sell[$article]} </b></td>";
echo "</tr>";

echo "<tr>";
    foreach ($arr_dates as $date) {
        if (isset($arr_for_print_fbo_in_stock[$date][$article]['fbo_in_stock'] )) {
        echo "<td>{$arr_for_print_fbo_in_stock[$date][$article]['fbo_in_stock']}</td>";
        } else {
            echo "<td> - </td>";
            //   echo "<td> - </td>";
    }
    
    }
    echo "<td> остатки ФБО </td>";
  
 echo "</tr>";
      

}

echo "</tr>";
echo "</table>";

}
