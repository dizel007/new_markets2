<?php

function make_1c_file ($arr_for_1C_file_temp, $new_arr_new_zakaz, $Zakaz_v_1c, $new_path){

$xls = new PHPExcel();
$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();

$next_i = 1;
foreach ($arr_for_1C_file_temp  as $key => $q_items) {
    $right_article = make_right_articl($key);
     $sheet->setCellValue("A".$next_i, $right_article);
     $sheet->setCellValue("C".$next_i, count($new_arr_new_zakaz[$key]));
     // высчитываем среднюю цену за товар
     $sum_q=0;
     foreach ($q_items as $q_item) {
         $sum_q = $sum_q + $q_item['convertedPrice'];
         }
      if (count($q_items) > 0) {   
     $midlle_price_q= ($sum_q/count($q_items))/100;
     $sheet->setCellValue("D".$next_i, $midlle_price_q); // цена за 1 шт товара
      } else {
         $sheet->setCellValue("D".$next_i, "no data"); // цена за 1 шт товара
      }

     $next_i++; // смешение по строкам
 
}
 
 $objWriter = new PHPExcel_Writer_Excel2007($xls);
 $rnd1000001 = "(".rand(0,10000).")";

 $file_name_1c_list_q = $Zakaz_v_1c."_".date('Y-m-d').$rnd1000001."_file_1C_(NEW_funck).xlsx";
 $objWriter->save($new_path."/".$file_name_1c_list_q);  
 return $file_name_1c_list_q;
}