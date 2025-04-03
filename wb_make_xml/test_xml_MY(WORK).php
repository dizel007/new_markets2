<?php
  
// // xml file path
// $path = "44444.xml";
// // Read entire file into string
// $xmlfile = file_get_contents($path);
// // Convert xml string into an object
// $new = simplexml_load_string($xmlfile);
// // Convert into json
// $con = json_encode($new);
// // Convert into associative array
// $newArr = json_decode($con, true);

// echo "<pre>";

///// 1 //////////
// $nalog_form_UPD = 'ON_NSCHFDOPPR_2BM-7721546864-2012052808220682662630000000000_2BM-7727830864-772701001-201407211008287437442_20230803_bd0c0d36-8c65-46b2-ba23-e2f112ef51eb';
$idOtpravl = '2BM-7727830864-772701001-201407211008287437442'; // CONST для АНмакса
$idPol = "2BM-7721546864-2012052808220682662630000000000"; // Константа для ВБ

date_default_timezone_set('Europe/Moscow');
$vremiaPrInfo= date('H.i.s'); // время формирования
// $vremiaPrInfo= "16.32.15"; // время формирования

$DataPrInfo = date('d.m.Y'); // дата вормирования
// $DataPrInfo = "03.08.2023"; // дата вормирования

//////// 2 //////////////
    $nomerChF = $UPD_number;
    $dataChF = date('d.m.Y', strtotime($UPD_date));
$countTovarov = count($arr_key); // количетво товаров в УПД (расчетное значение)


//////// 4 //////////////
$nomOsn = $ino_number; // номер отчета ВБ
$DataOsn = date('d.m.Y', strtotime($dateFrom));
// $DataOsn = date('d.m.Y', strtotime($dateTo . ' +1 day'));

/*
*******************************************************************************************************************************
file_put_contents('test.xml','<?xml version="1.0" encoding="windows-1251"?>');

*/





file_put_contents('test.xml','<?xml version="1.0" encoding="UTF-8"?>');


require_once "block_1.php";// Шапка УПД
require_once "block_2.php";// Шапка УПД
require_once "block_3.php";// Шапка УПД
require_once "block_4.php";// Шапка УПД

file_put_contents('test.xml','</Документ>', FILE_APPEND);
file_put_contents('test.xml','</Файл>', FILE_APPEND);


$text = file_get_contents("test.xml");
$text_new = str_replace('UTF-8', 'windows-1251'  , $text);

$text_new_2 = mb_convert_encoding($text_new, 'windows-1251', 'utf-8');

file_put_contents('test_2.xml' , $text_new_2);

echo "<br><a href=\"test_2.xml\">ОТКРЫТЬ XML_FILE</a><br><br>";


$zip_new = new ZipArchive();
$zip_new->open("reports/XML_upd_№".$nomerChF." от ".date("Y-M-d").".zip", ZipArchive::CREATE|ZipArchive::OVERWRITE);
$file_zip_name = "UPD_№".$nomerChF." от ".date("Y-M-d").".xml";
$zip_new->addFile('test_2.xml', "$file_zip_name"); // добавляем для НОВЫЙ 1С файл /// *****************

$link_path_zip2 = "reports/XML_upd_№".$nomerChF." от ".date("Y-M-d").".zip"; //  ссылка чтобы скачать архив

echo <<<HTML
<br>
<a href="$link_path_zip2">Cкачать архив с XML файлом</a>
<br><br>
HTML;

// echo "<br><b><a href=\"kach.php\">XML_FILE</a></b><br><br>";

