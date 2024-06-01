<?php
require_once 'include_funcs.php';
/*
Подключаем PHPExcel
*/
require_once '../libs/PHPExcel-1.8/Classes/PHPExcel.php';
require_once '../libs/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';
require_once '../libs/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

echo <<<HTML

<img src="../pics/ozon_sklad.jpg">

HTML;

if (isset($_GET['date_query_ozon'])) {
    $date_query_ozon = $_GET['date_query_ozon'];  
 }else {
    $date_query_ozon = date('Y-m-d'); 
}

echo <<<HTML
<h2>Получить  штрихкоды скомплектованных заказов</h2>
<div>
    <form method="get" action="controller/make_etikets_for_all_dop2.php">
        <label>Дата сборки </label>
    <input  required type="date" name="date_query_ozon" value="$date_query_ozon">
    <br><br>
    <label> Номер заказа </label>
    <input  required type="nubmer" name="nomer_zakaz" value="">
    <br><br>
    <input type="submit" value="Получить штрихкоды">
   
</div>
</form>    
<hr>
HTML;


die('');
// если есть Дата поиска, то начинаем вычитывать данные с сайта ОЗОН
