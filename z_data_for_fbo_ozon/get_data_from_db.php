<?php
require_once "../connect_db.php";
require_once "index_start.php";
// $shop_name = 'ozon_anmaks';
// $date_start = '2025-08-01';
// $date_end = '2025-08-31';

// if (!isset($_GET['need_article'])) {
// $need_article = array ('6210', '6211', '85400-ч', '82400-ч');
// } else {
//     $need_article = $_GET['need_article'];
// }


// echo "<pre>";
// print_r($need_article);

// die();
print_fbo_table_XX($pdo, 'ozon_anmaks', $date_start,$date_end, $need_article  );
print_fbo_table_XX($pdo, 'ozon_ip_zel', $date_start,$date_end, $need_article  );




die();



function print_fbo_table_XX($pdo, $shop_name, $date_start,$date_end, $need_article ) {

echo '<link rel="stylesheet" href="css/fbo_table_data.css">';
echo "<h1 class=\"center\"> FBO  $shop_name  </h1>";
echo "<h2 class=\"center\">Период запроса с ($date_start) по  ($date_end)</h2>";

$sth = $pdo->prepare("SELECT * FROM `z_ozon_fbo_sell` WHERE `type_sklad` = 'fbo' AND `shop_name` = '$shop_name' AND `date` >= :date_start AND `date` <= :date_end");
$sth->execute(array('date_start' => $date_start , 'date_end' => $date_end));
$array_sell_fbo = $sth->fetchAll(PDO::FETCH_ASSOC);

$sth = $pdo->prepare("SELECT * FROM `z_ozon_fbo_stocks` WHERE `shop_name` = '$shop_name' AND `date` >= :date_start AND `date` <= :date_end");
$sth->execute(array('date_start' => $date_start, 'date_end' => $date_end));
$array_stock = $sth->fetchAll(PDO::FETCH_ASSOC);

$sth = $pdo->prepare("SELECT * FROM `z_ozon_fbo_sell` WHERE `type_sklad` = 'fbs' AND `shop_name` = '$shop_name' AND `date` >= :date_start AND `date` <= :date_end");
$sth->execute(array('date_start' => $date_start , 'date_end' => $date_end));
$array_sell_fbs = $sth->fetchAll(PDO::FETCH_ASSOC);

// echo "<pre>";
// print_r($array_stock[0]);


// Формруем массив проданных товаровпо датам / артикулам
foreach ($array_sell_fbo as $items) {
        $arr_for_print_fbo_sell [$items['date']][$items['1c_article']]['fbo_sell'] = $items['fbo_sell'];
        $arr_article[$items['1c_article']] = $items['1c_article']; // массив авртикуло
        $arr_dates[$items['date']] = $items['date']; // массив дат
        $arr_summ_sell_fbo[$items['1c_article']] = @$arr_summ_sell_fbo[$items['1c_article']] + $items['fbo_sell'];
}


// Формруем массив проданных товаровпо датам / артикулам по ФБС
foreach ($array_sell_fbs as $items) {
        $arr_for_print_fbs_sell[$items['date']][$items['1c_article']]['fbo_sell'] = 
        @$arr_for_print_fbs_sell[$items['date']][$items['1c_article']]['fbo_sell'] + $items['fbo_sell'];
        $arr_article[$items['1c_article']] = $items['1c_article']; // массив авртикуло
        $arr_dates[$items['date']] = $items['date']; // массив дат
        $arr_summ_sell_fbs[$items['1c_article']] = @$arr_summ_sell_fbs[$items['1c_article']] + $items['fbo_sell'];
}


// Формруем массив остатков по датам / артикулам
foreach ($array_stock  as $items) {

        $arr_for_print_fbo_in_stock [$items['date']][$items['1c_article']]['fbo_in_stock'] = $items['fbo_in_stock'];
        // $arr_article[$items['1c_article']] = $items['1c_article']; // массив авртикуло
        $arr_dates[$items['date']] = $items['date']; // массив дат

}

sort($arr_dates);

// echo "<pre>";
// print_r($arr_for_print_fbs_sell);
// echo "</pre>";
// die();


echo "<table class=\"sell_mp_table\">";
echo "<thead class=\"color_orange\">";
echo "<tr>";
echo "<td>АРТИКУЛ</td>";
echo "<td>Тип</td>";
foreach ($arr_dates as $dates) {
    $timestamp = strtotime($dates);
$date =  date("d.m", $timestamp);
$date_day_week = date("D", $timestamp);
    echo "<td>$date <br>$date_day_week</td>";
}
echo "<td>СВОД</td>";
echo "</tr>";
echo "</thead>";






foreach ($arr_article as $article) {
    // выводи мне все артикулы, а только выбранные
    $priznak_vivida_articuls = 0;
    foreach ($need_article as $need_art) {  
        if ($need_art == $article) {
              $priznak_vivida_articuls = 1;              
        } 
    }
    if ( $priznak_vivida_articuls == 0) {
        continue;
    }
// закончили выборку выводимых артикулов 

echo "<tr>";
     echo "<td class = \"sticky\" rowspan = 3>{$article}</td>";

// ПРОДАЖИ ФБО **********************************************
     echo "<td class = \"fbo_sell_tovari\">Продажи ФБО </td>";
    foreach ($arr_dates as $date) {
        if (isset($arr_for_print_fbo_sell[$date][$article]['fbo_sell'] )) {
        echo "<td class = \"fbo_sell_tovari\">{$arr_for_print_fbo_sell[$date][$article]['fbo_sell']}</td>";
        } else {
            echo "<td class = \"fbo_sell_tovari\"> - </td>";
    }
    
    }
    // количество заказов по ФБО ИТОГО
     if (isset($arr_summ_sell_fbo[$article] )) {
        echo "<td class = \"fbo_sell_tovari\"> ИТОГО на ФБО  : <b>{$arr_summ_sell_fbo[$article]} </b></td>";
      } else {
         echo "<td class = \"fbo_sell_tovari\"> - </td>";
      }
echo "</tr>";


// ПРОДАЖИ ФБС **********************************************

echo "<td class = \"fbs_sell_tovari\">Продажи ФБC</td>";
 foreach ($arr_dates as $date) {
  if (isset($arr_for_print_fbs_sell[$date][$article]['fbo_sell'] )) {
        echo "<td class = \"fbs_sell_tovari\">{$arr_for_print_fbs_sell[$date][$article]['fbo_sell']}</td>";
        // echo "<td>{$arr_for_print[$date][$article]['fbo_in_stock']}</td>";
        } else {
            echo "<td class = \"fbs_sell_tovari\"> - </td>";
    };
 }
    // количество заказов по ФБС
     if (isset($arr_summ_sell_fbs[$article] )) {
        echo "<td class = \"fbs_sell_tovari\"> ИТОГО на ФБС : <b>{$arr_summ_sell_fbs[$article]} </b></td>";
      } else {
         echo "<td class = \"fbs_sell_tovari\"> - </td>";
      }

echo "<tr class=\"thick_line\">";


// ОСТАТКИ НА ФБО  **********************************************

echo "<td>Остатки на ФБО</td>";
    foreach ($arr_dates as $date) {
        if (isset($arr_for_print_fbo_in_stock[$date][$article]['fbo_in_stock'] )) {
        echo "<td class=\"fbo_stock_line\">{$arr_for_print_fbo_in_stock[$date][$article]['fbo_in_stock']}</td>";
        } else {
            echo "<td class=\"fbo_stock_line\"> - </td>";
            //   echo "<td> - </td>";
    }
    
    }
    echo "<td  class=\"fbo_stock_line\"> остатки ФБО </td>";
  
 echo "</tr>";
      

}

echo "</tr>";
echo "</table>";

}
