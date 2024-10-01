<?php



function print_table_with_dimentions($shop_name, $arr_sum_db ,$arr_sum_ozon) {
// $arr_sum_db = json_decode(file_get_contents('db.txt'), true);

// $arr_sum_ozon = json_decode(file_get_contents('ozon.txt'), true);

// file_put_contents('ozon.txt', json_encode($arr_sum_ozon, JSON_UNESCAPED_UNICODE));
// file_put_contents('db.txt', json_encode($arr_sum_db, JSON_UNESCAPED_UNICODE) );

// echo "<pre>";

// print_r($arr_sum_db);
// print_r($arr_sum_ozon);



echo <<<HTML
<head>
<link rel="stylesheet" href="css/table_dimentions.css">
<title>Список разбобранных заказов</title>
</head>

<body>



<h2 class="h2_color_green center">Список габаритных размеров магазина : $shop_name</h2>

<table class="svod_po_ostatkam">

<tr class="green_color">


<td> Артикул </td>
<td> Длина (БД) </td>
<td> Ширина (БД) </td>
<td> Высота (БД) </td>
<td> Вес (БД) </td>
<td> Объем (БД) </td>
<td> Длина (Озон) </td>
<td> Ширина (Озон) </td>
<td> Высота (Озон) </td>
<td> Вес (Озон) </td>
<td> Объем (БД) </td>
<td> Дельта объема </td>


    <tr>
    
    
HTML;
foreach ($arr_sum_db as $db_dimensions) {
    foreach ($arr_sum_ozon as $ozon_dimensions) {




 if($db_dimensions['product_id'] == $ozon_dimensions['product_id'] )  {
 // смотрим есть ли расхождение в характеристиках 
    if (($db_dimensions['height'] != $ozon_dimensions['height']) OR 
    ($db_dimensions['width'] != $ozon_dimensions['width']) OR 
    ($db_dimensions['depth'] != $ozon_dimensions['depth']) OR 
    ($db_dimensions['weight'] != $ozon_dimensions['weight']))  {
       $alarm_class = 'red_color';
         } else {
        $alarm_class = 'green_color';
        }



echo "<tr  class=\"$alarm_class\">";
    
    echo "<td>".$db_dimensions['mp_article']."</td>";
    echo "<td>".$db_dimensions['height']."</td>";
    echo "<td>".$db_dimensions['width']."</td>";
    echo "<td>".$db_dimensions['depth']."</td>";
    echo "<td>".$db_dimensions['weight']."</td>";
$db_volume = $db_dimensions['height']*$db_dimensions['width']*$db_dimensions['depth']/1000000;
    echo "<td>".$db_volume."</td>";

    echo "<td>".$ozon_dimensions['height']."</td>";
    echo "<td>".$ozon_dimensions['width']."</td>";
    echo "<td>".$ozon_dimensions['depth']."</td>";
    echo "<td>".$ozon_dimensions['weight']."</td>";
    $ozon_volume = $ozon_dimensions['height']*$ozon_dimensions['width']*$ozon_dimensions['depth']/1000000;
    echo "<td>".$ozon_volume."</td>";
    $delta_volume = $db_volume - $ozon_volume;
    echo "<td>".$delta_volume."</td>";

echo "</tr>";
}
    }}
echo <<<HTML
</table>

</body>

HTML;
}