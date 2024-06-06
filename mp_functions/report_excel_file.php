<?php

function report_mp_make_excel_file_morzha($arr_tovari, $name_mp_shop, $date_start, $date_stop)
{

        // Создаем файл для 1С
        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();



        /// Форматируем екскль документ
        $sheet->getColumnDimension("A")->setWidth(3);
        $sheet->getColumnDimension("B")->setWidth(17);
        $sheet->getColumnDimension("C")->setWidth(10);
        $sheet->getColumnDimension("D")->setWidth(12);
        $sheet->getColumnDimension("E")->setWidth(15);
        $sheet->getColumnDimension("F")->setWidth(10);
        $sheet->getColumnDimension("G")->setWidth(10);
        $sheet->getColumnDimension("H")->setWidth(13);
        $sheet->getColumnDimension("I")->setWidth(14);
        $sheet->getColumnDimension("J")->setWidth(14);


        // перенос текста на след строку
        $i_shapka = 4;
        $sheet->getStyle("A" . $i_shapka)->getAlignment()->setWrapText(true);
        $sheet->getStyle("B" . $i_shapka)->getAlignment()->setWrapText(true);
        $sheet->getStyle("C" . $i_shapka)->getAlignment()->setWrapText(true);
        $sheet->getStyle("D" . $i_shapka)->getAlignment()->setWrapText(true);
        $sheet->getStyle("E" . $i_shapka)->getAlignment()->setWrapText(true);
        $sheet->getStyle("F" . $i_shapka)->getAlignment()->setWrapText(true);
        $sheet->getStyle("G" . $i_shapka)->getAlignment()->setWrapText(true);
        $sheet->getStyle("H" . $i_shapka)->getAlignment()->setWrapText(true);
        $sheet->getStyle("I" . $i_shapka)->getAlignment()->setWrapText(true);
        $sheet->getStyle("J" . $i_shapka)->getAlignment()->setWrapText(true);




        $i = 5;
        $sum_nasha_viplata = 0;
        $our_pribil = 0;
        $summa_strafa_article = 0;
        $pribil_posle_vicheta_strafa = 0;
        // echo "<pre>";
        // print_R($arr_tovari);
        $sheet->setCellValue("A1", $name_mp_shop);
        $date_string = "Дата начала: $date_start ; Дата окончания : $date_stop";
        $sheet->setCellValue("A2", $date_string);

        // Шапка таблицы
        $sheet->setCellValue("A" . $i_shapka, 'пп');
        $sheet->setCellValue("B" . $i_shapka, 'Артикул');
        $sheet->setCellValue("C" . $i_shapka, 'кол-во');
        $sheet->setCellValue("D" . $i_shapka, 'Итого поступление');
        $sheet->setCellValue("E" . $i_shapka, 'сумма поступления за шт.');
        $sheet->setCellValue("F" . $i_shapka, 'себестоимость');
        $sheet->setCellValue("G" . $i_shapka, 'Доход с одной штуки');
        $sheet->setCellValue("H" . $i_shapka, 'Наша прибыль');
        $sheet->setCellValue("I" . $i_shapka, 'Габаритные размеры');
        $sheet->setCellValue("J" . $i_shapka, 'Средняя цена на маркете');
  


        foreach ($arr_tovari as $key => $items) {
                // print_r($items);
                $sheet->setCellValue("A" . $i, $i-$i_shapka);
                $sheet->setCellValue("B" . $i, $key);
                $sheet->setCellValue("C" . $i, $items['count_sell']);
                $sheet->setCellValue("D" . $i, $items['sum_nasha_viplata']);
                $sheet->setCellValue("E" . $i, $items['price_for_shtuka']);
                $sheet->setCellValue("F" . $i, $items['sebes_str_item']);
                $sheet->setCellValue("G" . $i, $items['delta_v_stoimosti']);
                $sheet->setCellValue("H" . $i, $items['our_pribil']);
                $sheet->setCellValue("I" . $i, $items['gabariti']);
                $sheet->setCellValue("J" . $i, $items['sum_k_pererchisleniu_za_shtuku']);
                





                $i++; // смешение по строкам

                $sum_nasha_viplata += $items['sum_nasha_viplata'];
                $our_pribil += $items['our_pribil'];
                $summa_strafa_article += $items['summa_strafa_article'];
                $pribil_posle_vicheta_strafa += $items['pribil_posle_vicheta_strafa'];
        }
        // Выводим Итоговые столбы
        $sheet->getStyle("A".$i.":J".$i)->getFont()->setBold(true);
        $sheet->setCellValue("D" . $i, $sum_nasha_viplata);
        $sheet->setCellValue("H" . $i, $our_pribil);
        
        $i++;
        // выплата с учетом штрафа
        $sum_nasha_viplata_s_uchetom_strafov = $sum_nasha_viplata - $summa_strafa_article;

        $sheet->setCellValue("C" . $i, 'Выплата с учетом штрафов');
        $sheet->getStyle("C" . $i)->getAlignment()->setWrapText(true);
        $sheet->setCellValue("D" . $i, $sum_nasha_viplata_s_uchetom_strafov);
        $sheet->setCellValue("G" . $i, 'штрафы');
        $sheet->setCellValue("H" . $i, $summa_strafa_article);
        $i++;
        $sheet->setCellValue("G" . $i, 'Прибыль за вычетом всего');
        $sheet->getStyle("G" . $i)->getAlignment()->setWrapText(true);
        $sheet->setCellValue("H" . $i, $pribil_posle_vicheta_strafa);
        








// Рамки и границы ячеек /////////////////////

        $border = array(
                'borders' => array(
                        'allborders' => array(
                                'style' => PHPExcel_Style_Border::BORDER_THIN,
                                'color' => array('rgb' => '000000')
                        )
                )
        );

        $sheet->getStyle("A".$i_shapka.":J".$i)->applyFromArray($border);
// Выравнивание по середине
        $sheet->getStyle("A".$i_shapka.":J".$i)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A".$i_shapka.":J".$i)->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
// Красим шапку
$bg = array(
	'fill' => array(
		'type' => PHPExcel_Style_Fill::FILL_SOLID,
		'color' => array('rgb' => 'c9c9c2')
	)
);
$sheet->getStyle("A".$i_shapka.":J".$i_shapka)->applyFromArray($bg);


// Формат
// $sheet->getPageSetup()->SetPaperSize(PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4);
 
// Ориентация
// ORIENTATION_PORTRAIT — книжная
// ORIENTATION_LANDSCAPE — альбомная
$sheet->getPageSetup()->setOrientation(PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);

// сохраняем Документ
        $objWriter = new PHPExcel_Writer_Excel2007($xls);
        $file_name_report_excel =  "temp/report_.xlsx";
        $objWriter->save($file_name_report_excel);
        return    $file_name_report_excel;

} /// END FUNCTION