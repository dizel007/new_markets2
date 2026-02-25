<?php

function make_1c_file ($arr_for_1C_file_temp, $Zakaz_v_1c, $new_path){

$xls = new PHPExcel();
$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();

$next_i = 1;
$midlle_price_q = 0;
foreach ($arr_for_1C_file_temp  as $key => $q_items) {

     $sheet->setCellValue("A".$next_i, $key);
     $sheet->setCellValue("C".$next_i, $q_items['count']);
     // высчитываем среднюю цену за товар
     $midlle_price_q = $q_items['price']/$q_items['count'];
     $sheet->setCellValue("D".$next_i, $midlle_price_q); // цена за 1 шт товара
     $next_i++; // смешение по строкам
 
}
 
 $objWriter = new PHPExcel_Writer_Excel2007($xls);
 $rnd1000001 = "(".rand(0,10000).")";

 $file_name_1c_list_q = $Zakaz_v_1c."_".date('Y-m-d').$rnd1000001."_file_1C_(NEW_funck).xlsx";
 $objWriter->save($new_path."/".$file_name_1c_list_q);  
 return $file_name_1c_list_q;
}