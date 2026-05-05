<?php
/**********************************************************************************************
 * Тут формируется отчетный файл эксель 
 **********************************************************************************************/
$arr_real_ozon_data_EXCEL = $arr_real_ozon_data;




// сортируем массив согласно назначенным номерам
usort($arr_real_ozon_data_EXCEL, fn($a, $b) => $a['number_in_spisok'] <=> $b['number_in_spisok']);

unset ($excel_item);

// echo "<pre>";
// print_r($arr_real_ozon_data_EXCEL);

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
// use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
// use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// Создаём новую книгу
$spreadsheet = new Spreadsheet();

// ------------------------------
// Лист "Worksheet" (первый лист)
// ------------------------------
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Worksheet');


$sheet->setCellValue('A1', $shop_name);
$sheet->setCellValue('A2', 'Дата начала: ' .$date_from. ' Дата окончания :'.$date_to);


// Заголовки таблицы (строка 5, так как в вашем файле 4 пустые строки сверху)
$headers = [
    'пп', 'Артикул', 'кол-во', 'Итого поступление', 'сумма поступления за шт.',
    'себестоимость', 'Доход с одной штуки', 'Наша прибыль', 
    'Средняя цена на маркете', 'себестоимость'
];

$col = 'A';
foreach ($headers as $header) {
    $sheet->setCellValue($col . '5', $header);
    $col++;
}

/// 
$ex_i = 0;
foreach ($arr_real_ozon_data_EXCEL as $excel_item) {
$ex_i ++;
$data[] = [$ex_i, 
         $excel_item['mp_article'],
         $excel_item['count']['summa'],
         round($excel_item['summa']['bez_vsego_s_ino_tovarami'],0),
         round($excel_item['one_item']['bez_vsego_s_ino_tovarami'],0),
         $excel_item['min_price'],
         $excel_item['diff_min_price'],
         round($excel_item['summa']['pribil'],0),
         $excel_item['one_item']['accruals_for_sale'],
         $excel_item['summa']['sebestoimost'],
        ];

         

}

// die();

$row = 6;
foreach ($data as $item) {
    $col = 'A';
    foreach ($item as $value) {
        $sheet->setCellValue($col . $row, $value);
        $col++;
    }
   
    $row++;
}



/// считаем суммы даннных
$firstDataRow = 6;
$lastDataRow   = $sheet->getHighestRow(); // максимальный номер строки с любыми данными

/// вставляем суммы нужных колонок 
$sheet->setCellValue("D" . ($lastDataRow + 1), "=SUM(D{$firstDataRow}:D{$lastDataRow})");
$sheet->setCellValue("H" . ($lastDataRow + 1), "=SUM(H{$firstDataRow}:H{$lastDataRow})");
$sheet->setCellValue("J" . ($lastDataRow + 1), "=SUM(J{$firstDataRow}:J{$lastDataRow})");


// Итоговая строка (сумма прибыли и себестоимости)
$lastRow = $row;


// Применяем базовое форматирование (жирный шрифт для заголовков, границы)
$headerRow = 5;
$lastCol = 'J';
$sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->getFont()->setBold(true);
$sheet->getStyle("A{$headerRow}:{$lastCol}{$lastRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
// Автоширина колонок
// foreach (range('A', 'J') as $col) {
//     $sheet->getColumnDimension($col)->setAutoSize(true);
// }

$sheet->getColumnDimension('A')->setWidth(8);

// Сохраняем файл
$writer = new Xlsx($spreadsheet);
$filename_link_excel = '../../!cache/report_'.$shop_name.'_.xlsx';
$writer->save($filename_link_excel);

// echo "<a href=\"$filename\"'> EXCEL </a>";