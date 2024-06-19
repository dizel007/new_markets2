<?php
require_once 'PHPExcel-1.8/Classes/PHPExcel.php';
require_once 'PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';
require_once 'PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';




$xls = PHPExcel_IOFactory::load("../".$_GET['LinkKp']);
$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();
$i=19;
$stop =0;

$kp_name = $sheet->getCellByColumnAndRow(6, 6)->getValue();
$Zakazchik = $sheet->getCellByColumnAndRow(9, 8)->getValue();
$Phone = $sheet->getCellByColumnAndRow(9, 10)->getValue();
$Email = $sheet->getCellByColumnAndRow(9, 11)->getValue();
$ZakupName = $sheet->getCellByColumnAndRow(2, 16)->getValue();