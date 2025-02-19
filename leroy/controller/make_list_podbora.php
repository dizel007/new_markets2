<?php
require_once '../../connect_db.php';


require_once '../../libs/PHPExcel-1.8/Classes/PHPExcel.php';
require_once '../../libs/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';
require_once '../../libs/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';
require_once "../functions/excel_style.php";


// создаем массив ключ - SKU параметр - артикул
foreach ($new_array_list_podbora as $items) {
    foreach ($items['products'] as $item) {
$arr_catalog_tovarov[$item['lmId']] =  $item['vendorCode'];
    }
}





// Получаем массив по ID отправлению с разбивкой по грузометам и формируем ексель файл ЛИСТ ПОДБОРА
$xls = new PHPExcel();
$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();

//Параметы печати
// Ориентация
 
// Поля
$sheet->getPageMargins()->setTop(0.5);
$sheet->getPageMargins()->setRight(0.5);
$sheet->getPageMargins()->setLeft(0.5);
$sheet->getPageMargins()->setBottom(0.5);
// Ширина столбцов
$sheet->getColumnDimension("A")->setWidth(16); // ширина столбца
$sheet->getColumnDimension("B")->setWidth(60); // ширина столбца
$i=1;

$dop_link = '/boxes';
$sheet->setCellValue("A".$i, 'Лист подбора на '.$date_for_ship);
$sheet->getStyle("A".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$sheet->mergeCells("A1:D1"); 
$sheet->getStyle("A".$i)->getFont()->setBold(true); // жирный текст
$sheet->getStyle("A".$i)->getFont()->setSize(16); // размер текста
$i++;
// шапка в екселе
$sheet->setCellValue("A".$i, '№ отправления');
$sheet->setCellValue("B".$i, 'Наименование');
$sheet->setCellValue("C".$i, 'Кол-во');
$sheet->setCellValue("D".$i, 'Арт.');
$sheet->getStyle("A".$i.":D".$i)->getFont()->setSize(10); // размер текста
$sheet->getStyle("A".$i.":D".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
$i++;
// echo "<pre>";
// print_r($new_array_list_podbora);
// die();


foreach ($new_array_list_podbora as $items) {

    $id_parcel = $items['id']; // MP3290370-001
    $sheet->setCellValue("A".$i, 'Отправление №'.$id_parcel." на сумму: ". number_format($items['parcelPrice'],2). " руб.");
    $sheet->getStyle("A".$i)->getFont()->setBold(true); // жирный текст
    $sheet->getStyle("A".$i)->getFont()->setSize(15); // размер текста
    $i++;
    $link = 'https://api.lemanapro.ru/marketplace/merchants/v1/parcels/'.$id_parcel.$dop_link;


$array_s_item = light_query_without_data ($token_lerua, $link, 'Лист подбора с грузоместами');
sleep(1);
        foreach ($array_s_item as $shiped_posts) {
    
                
                  foreach ($arr_catalog_tovarov as $key => $catalot_item) { // по SKU находим артикул товара
                    if($key == $shiped_posts['products'][0]['sku']) {
                        $artikul = $catalot_item;
                    }

                }
                $sheet->setCellValue("A".$i, $shiped_posts['id']);
                $sheet->setCellValue("B".$i, $art_catalog[$artikul]); // название товара
                $sheet->setCellValue("C".$i, $shiped_posts['products'][0]['quantity']);
                $sheet->getStyle("C".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                

                $sheet->setCellValue("D".$i, $artikul);
                $sheet->getStyle("D".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
                
                $sheet->getStyle("A".$i.":D".$i)->getFont()->setSize(10); // размер текста

                $i++; // смешение по строкам
        }
        $sheet->getStyle("A".$i.":D".$i )->applyFromArray($bg);
$i++;
}
$i--;
$sheet->getStyle("A1:D".$i)->applyFromArray($border_inside); // разлинейка ячеек

$objWriter = new PHPExcel_Writer_Excel2007($xls);
$random = rand(5, 10000);
$link_list_podbora = "../EXCEL/".$date_for_ship."-list_podbora(".$random.").xlsx";
$objWriter->save($link_list_podbora);
