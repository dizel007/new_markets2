<?php

function report_mp_make_excel_file_morzha($arr_tovari) {

        // Создаем файл для 1С
        $xls = new PHPExcel();
        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();
        $i=2;
        $sum_nasha_viplata = 0;
        $our_pribil = 0;
        $summa_strafa_article = 0;
        $pribil_posle_vicheta_strafa = 0;
        // echo "<pre>";
        // print_R($arr_tovari);



       $sheet->setCellValue("A1", 'Артикул');
       $sheet->setCellValue("B1", 'кол-во');
       $sheet->setCellValue("C1", 'Итого поступление');
       $sheet->setCellValue("D1", 'сумма поступления за шт.');
       $sheet->setCellValue("E1", 'себестоимость');
       $sheet->setCellValue("F1", 'Доход с одной штуки');
       $sheet->setCellValue("G1", 'Наша прибыль');

       $sheet->setCellValue("H1", 'Штрафы, реклама, хранение');
       $sheet->setCellValue("I1", 'Наша прибыль за вычетом всего');


            foreach ($arr_tovari as $key => $items) {
 // print_r($items);	
            $sheet->setCellValue("A".$i, $key);
            $sheet->setCellValue("B".$i, $items['count_sell']);
            $sheet->setCellValue("C".$i, $items['sum_nasha_viplata']);
            $sheet->setCellValue("D".$i, $items['price_for_shtuka']);
            $sheet->setCellValue("E".$i, $items['sebes_str_item']);
            $sheet->setCellValue("F".$i, $items['delta_v_stoimosti']);
            $sheet->setCellValue("G".$i, $items['our_pribil']);
            $sheet->setCellValue("H".$i, $items['summa_strafa_article']);
            $sheet->setCellValue("I".$i, $items['pribil_posle_vicheta_strafa']);


            
            $i++; // смешение по строкам

            $sum_nasha_viplata += $items['sum_nasha_viplata'];
            $our_pribil += $items['our_pribil'];
            $summa_strafa_article += $items['summa_strafa_article'];
            $pribil_posle_vicheta_strafa += $items['pribil_posle_vicheta_strafa'];
    
        }
  // Выводим Итоговые столбы
        $sheet->setCellValue("C".$i, $sum_nasha_viplata);
        $sheet->setCellValue("G".$i, $our_pribil);
        $sheet->setCellValue("H".$i, $summa_strafa_article);
        $sheet->setCellValue("I".$i, $pribil_posle_vicheta_strafa);
$i++;
// выплата с учетом штрафа
$sum_nasha_viplata_s_uchetom_strafov = $sum_nasha_viplata - $summa_strafa_article;

$sheet->setCellValue("B".$i, 'Выплата с учетом штрафов');
$sheet->setCellValue("C".$i, $sum_nasha_viplata_s_uchetom_strafov);

        $objWriter = new PHPExcel_Writer_Excel2007($xls);
        $file_name_report_excel =  "temp/report_wb.xlsx";
        $objWriter->save($file_name_report_excel);
        return    $file_name_report_excel;   
        
    
    }