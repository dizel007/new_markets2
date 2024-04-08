<?php
/******************************************************************************************************************
****** Функуия для формирования файла для 1С *********************
/******************************************************************************************************************/

// Из полученного массива формируем массив данных,$array_art   для создания Заказа в 1С.


function   make_1c_file($res, $date_query_ozon, $nomer_zakaz, $path_excel_docs, $xls) {
$kolvo_tovarov = 0;
   foreach ($res['result']['postings'] as $posts) {
      foreach ($posts['products'] as $prods) 
        {
            $artick_temp2 = $prods['offer_id'];
            if ($artick_temp2 == '82401-чн') { // подмена артикула для второго черного 
                $artick_temp2 = '82401-Ч';
            }
           $array_art[$artick_temp2] = @$array_art[$artick_temp2] + $prods['quantity'];
           $kolvo_tovarov = $kolvo_tovarov + $prods['quantity'];
        //    echo $prods['price']."<br>";
          $array_art_price[$artick_temp2] = array("price"    => $prods['price'],
                                                       "quantity" => $array_art[$artick_temp2],
                                                        "name"    => $prods['name']);
        }
 }

// echo "<pre>";
// print_r($array_art_price);

 if (isset($array_art_price)) {
    // Создаем файл для 1С
    $xls->setActiveSheetIndex(0);
    $sheet = $xls->getActiveSheet();
    $i=1;
   //  echo "<pre>";
        foreach ($array_art_price as $key => $items) {
    // print_r($items);	
        $sheet->setCellValue("A".$i, $key);
        $sheet->setCellValue("C".$i, $items['quantity']);
        $sheet->setCellValue("D".$i, round($items['price'], 2));
        $i++; // смешение по строкам
    
    }
    
    $objWriter = new PHPExcel_Writer_Excel2007($xls);
    $file_name_1c_list = $date_query_ozon." (".$nomer_zakaz.") file_1C.xlsx";
   //  $objWriter->save("../EXCEL/".$file_name_1c_list);
    $objWriter->save($path_excel_docs."/".$file_name_1c_list);
          
    } 
   
    return     $file_name_1c_list;
   }

/******************************************************************************************************************
****** Функуия для формирования листа подбора (из обработанного массива)
/******************************************************************************************************************/

/******************************************************************************************************************
****** Функуия для формирования листа подбора (из обработанного массива)
/******************************************************************************************************************/

function make_list_podbora_new ($array_oben, $date_query_ozon, $nomer_zakaz, $path_excel_docs, $xls2) {
/// фронтенд ексель файла
$bg = array(
    'fill' => array(
        'type' => PHPExcel_Style_Fill::FILL_SOLID,
        'color' => array('rgb' => 'd4fce7')
    )
);

$border_inside = array(
    'borders'=>array(
        'outline' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
        ),
        'allborders' => array(
            'style' => PHPExcel_Style_Border::BORDER_THIN,
            'color' => array('rgb' => '000000')
        )
    )
);

//////////////////////////////////////////////////////////////////////////////

$xls2 = new PHPExcel();
$xls2->setActiveSheetIndex(0);
$sheet2 = $xls2->getActiveSheet();

        // Поля
        $sheet2->getPageMargins()->setTop(0.5);
        $sheet2->getPageMargins()->setRight(0.5);
        $sheet2->getPageMargins()->setLeft(0.5);
        $sheet2->getPageMargins()->setBottom(0.5);
        // Ширина столбцов
        $sheet2->getColumnDimension("A")->setWidth(16); // ширина столбца
        $sheet2->getColumnDimension("B")->setWidth(16); // ширина столбца
        $sheet2->getColumnDimension("C")->setWidth(60); // ширина столбца

$i=1;

$sheet2->setCellValue("A".$i, "Первый заказ");
$i++; // смешение по строкам






foreach ($array_oben as $array_items) {
    $i2=$i-1;
    $sheet2->getStyle("A".$i2.":E".$i2 )->applyFromArray($bg); // фон
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

$sheet2->getStyle("A1:E".$i)->applyFromArray($border_inside); // разлинейка ячеек


$objWriter2 = new PHPExcel_Writer_Excel2007($xls2);

$file_name_list_podbora = $date_query_ozon." (".$nomer_zakaz.") file_list_podbor.xlsx";
$objWriter2->save($path_excel_docs."/".$file_name_list_podbora);

return $file_name_list_podbora;
}




function make_list_podbora_new_2 ($array_oben, $date_query_ozon, $nomer_zakaz, $path_excel_docs, $xls2) {
    /// фронтенд ексель файла
    $bg = array(
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'color' => array('rgb' => 'd4fce7')
        )
    );
    
    $border_inside = array(
        'borders'=>array(
            'outline' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            ),
            'allborders' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN,
                'color' => array('rgb' => '000000')
            )
        )
    );
    
    //////////////////////////////////////////////////////////////////////////////
    
    $xls2 = new PHPExcel();
    $xls2->setActiveSheetIndex(0);
    $sheet2 = $xls2->getActiveSheet();
    
            // Поля
            $sheet2->getPageMargins()->setTop(0.5);
            $sheet2->getPageMargins()->setRight(0.5);
            $sheet2->getPageMargins()->setLeft(0.5);
            $sheet2->getPageMargins()->setBottom(0.5);
            // Ширина столбцов
            $sheet2->getColumnDimension("A")->setWidth(16); // ширина столбца
            $sheet2->getColumnDimension("B")->setWidth(16); // ширина столбца
            $sheet2->getColumnDimension("C")->setWidth(60); // ширина столбца
    
    $i=1;
    
    $sheet2->setCellValue("A".$i, "Первый заказ");
    $i++; // смешение по строкам
    
    
    
    
    
    
    foreach ($array_oben as $array_items) {
        $i2=$i-1;
        $sheet2->getStyle("A".$i2.":E".$i2 )->applyFromArray($bg); // фон
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
    
    $sheet2->getStyle("A1:E".$i)->applyFromArray($border_inside); // разлинейка ячеек
    
    
    $objWriter2 = new PHPExcel_Writer_Excel2007($xls2);
    
    $file_name_list_podbora = $date_query_ozon." (".$nomer_zakaz.") file_list_podbor.xlsx";
    $objWriter2->save($path_excel_docs."/".$file_name_list_podbora);
    
    return $file_name_list_podbora;
    }