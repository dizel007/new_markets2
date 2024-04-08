<?php
/**
 * Формируем лист подбора
 */

$xls2 = new PHPExcel();
$xls2->setActiveSheetIndex(0);
$sheet2 = $xls2->getActiveSheet();

$i=1;


foreach ($array_oben as $array_items) {

    foreach ($array_items['additional_data'] as $items) {
            $sheet2->setCellValue("A".$i, $items['posting_number']);
            $sheet2->setCellValue("B".$i, $items['products'][0]['offer_id']);
            $sheet2->setCellValue("C".$i, $items['products'][0]['name']);
            $sheet2->setCellValue("D".$i, $items['products'][0]['quantity']);
            $sheet2->setCellValue("E".$i, $items['products'][0]['price']);

            $i++; // смешение по строкам

}
    $i++; // смешение по строкам
    $sheet2->setCellValue("A".$i, "Следующий заказ");
    $i++; // смешение по строкам
}

$i--;
$sheet2->setCellValue("A".$i, "Процесс сборки завершен");
$objWriter2 = new PHPExcel_Writer_Excel2007($xls2);

$file_name_list_podbora = $date_query_ozon.$rand10000."_file_list_podbor.xlsx";
$objWriter2->save( $path_excel_docs.$file_name_list_podbora);
$link_list_podbora =  $path_excel_docs.$file_name_list_podbora;

 
