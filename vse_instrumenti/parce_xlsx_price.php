<?php

function get_catalog_VI() {
require_once '../libs/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
 
// Файл xlsx
$xls = PHPExcel_IOFactory::load(__DIR__ . '/price/price_VI.xlsx');
 
// Первый лист
$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();

    foreach ($sheet->toArray() as $row) {
    //    print_r($row);
    $arr_catalog[$row[3]]['article'] = $row[3];
    $arr_catalog[$row[3]]['name'] = $row[10];
    $arr_catalog[$row[3]]['price'] = $row[25];
    $arr_catalog[$row[3]]['shtihcod'] = $row[1];
    }

return $arr_catalog;

}
 

