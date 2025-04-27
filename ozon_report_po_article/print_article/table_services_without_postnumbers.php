<?php

// echo "<pre>";
// print_r($arr_services_without_postNumber);


echo "<link rel=\"stylesheet\" href=\"css/main_ozon_reports.css\">";

// Начинаем отрисовывать таблицу 

echo "<table class=\"fl-table\">";

// ШАПКА ТАблицы
echo "<tr>";
echo "<th>пп</th>";
echo "<th>Название услуги</th>";
echo "<th>Стоимость</th>";

echo "</tr>";

// echo "<pre>";
$i_serv = 1;
foreach ($arr_services_without_postNumber as $key => $print_service) {
    echo "<tr>";
    echo "<td class= \"\">". $i_serv."</td>";
    echo "<td class= \"\">". $key. "</td>";
if ($print_service >= 0) {
    echo "<td class= \"good_desired_price\">" .$print_service."</td>";
} else {
    echo "<td class= \"bad_desired_price\">" .$print_service."</td>";
}
$summa_services = @$summa_services + $print_service;
$i_serv ++;
echo "</tr>";
} //////////////////////////////// КОНЕЦ ЦИКЛА



echo "<tr>";
echo "<td class= \"\">" . "</td>";
echo "<td class= \"\">"."СУММА ВСЕХ ДОПОЛНИТЕЛЬНЫХ СЕРВИСОВ :" . " </td>";
echo "<td class= \"bad_desired_price\">" . $summa_services."</td>";
echo "<tr>";
echo "</table>";
echo "<br>";
echo "<br>";

