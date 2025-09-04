<?php

use Smalot\PdfParser\Parser;
use Picqer\Barcode\BarcodeGeneratorJPG ;
use setasign\Fpdi\Fpdi;

function parce_ozon_etiketki($filepath, $filename_old){
/**************************************************************
 *  ФУНКЦИЯ парсит этикетку озона и формирует массивы 
 * с данными (для дпльнейшего создания своей этикетки)
 **********************************************************/

try {

    $file_e = $filepath.$filename_old.".pdf";
    $parser = new Parser();
    $pdf = $parser->parseFile($file_e);
    $text = $pdf->getText();
    $blocks = explode("\n\n", $text);
// echo "<pre>";
// print_r($text);
// print_r($blocks);
// 350538438068000
    foreach ($blocks as $string) {
        // echo "<pre>";
        // echo ($string);
        // echo "<br>";
      
       
        $temp = explode("\n", $string);

 

        $count_items = count($temp);
        $temp_t['fbs'] =  $temp[0]; // номер склада ФБС
        $temp_t['number_zakaz'] =  trim($temp[1]); // номер заказа
        
        $temp_t['num_PVZ'] =  trim($temp[$count_items - 4]);  // ПВЗ или КУР
        $temp_t['PVZ']     =  trim($temp[$count_items - 3]);  // ПВЗ или КУР
        $temp_t['size']    = trim($temp[$count_items - 2]);  // габариты - вес
        $temp_t['code']    = trim($temp[$count_items - 1]); // дата и штрих код

        // Выделяем цифры штрих кода
           $position = strrpos($temp_t['code'], '/');
            if ($position !== false) {
                $temp_t['shtihcode'] = trim(substr($temp_t['code'], $position + 1));
            } else {
                $temp_t['shtihcode'] = "";
            }
    // Адрес ПВЗ или полный адрес для курьера
        for ($i = 2; $i <= ($count_items - 5); $i++) {
            $temp_t['adress'][$i-2] = $temp[$i]; 
        }

        // Разбиваем габариты на блоки
        //1000 x 230 x 80 мм| 1800 г.
        $position = strrpos($temp_t['size'], 'мм|');
            if ($position !== false) {
                $temp_t['gabariti'] = trim(mb_substr($temp_t['size'], 0, $position));
            } else {
                $temp_t['gabariti'] = "";
            }
        $position = strrpos($temp_t['size'], 'мм|');
            if ($position !== false) {
                $temp_t['ves'] = trim(mb_substr($temp_t['size'], $position+3));
                $temp_t['ves']  = preg_replace('/[^0-9]/', '', $temp_t['ves']);
               
            } else {
                $temp_t['ves'] = "";
            }



        // echo "<pre>";
        // print_r($temp_t);
        // $priznak = check_our_parce ($temp_t) ;
        // echo "<br>************** $priznak ***************************<br>";

        $array_one_article[] =  $temp_t;
        unset ($temp_t);
        
    }
  
} catch (Exception $e) {
    echo "<br>Ошибка при парсинге: " . $e->getMessage();
}

// echo "<pre>";
// print_r($array_one_article);


return $array_one_article;
}

function check_our_parce ($temp_data) {
    // проверяем номер FBS
    $priznak = 0;
    $length = strlen($temp_data['fbs']);
    for ($i = 0; $i < $length; $i++) {
        $char = $temp_data['fbs'][$i];
        if (!(
            ($char >= '0' && $char <= '9') ||
             $char === ':' || $char === ' ' ||
             $char === 'F' || $char === 'f' ||
             $char === 'B' || $char === 'b' ||
             $char === 'S' || $char === 's'
        )) {
            $priznak ++;
            echo "<br> В номере ФБС ошибка <br>";
            }
    }
    
  // Проверяем номер ПВЗ  
    $length = strlen($temp_data['num_PVZ']);
    // если длина признака не равно 4-м символам, то выводим ошибку
    if ($length != 4) {
        $priznak ++;
        echo "<br> В номере ПВЗ/КУР склада ошибка число символов не равно 4 <br>";
    }

    for ($i = 0; $i < $length; $i++) {
        $char = $temp_data['num_PVZ'][$i];
        if (!($char >= '0' && $char <= '9')) {
            echo "<br>В номере ПВЗ/КУР склада ошибка - есть символы кроме цифр  {$char}<br>";
    
        }
    }

    return $priznak;

}




function format_ozon_etiketka ($filepath, $dop_file_dir ,$filename_old, $array_one_article, $array_dop_list)  {
// Создаем PDF
$pdf = new Fpdi();

// echo "<pre>";
// print_r($array_one_article);

 // Перебираем архив данными / генерируем штрихкод и формируем этикетку
    foreach ($array_one_article as $one_article) {
        GenerateBarcode($one_article['shtihcode'], $filepath);
        make_etiketka ($pdf, $one_article, $filepath);
    }

//  Цепляем дополнительный лист по заказу
    make_pdf_dop_list ($pdf, $array_dop_list, $filepath);
    
// сохраняем поулчившейся ПФД файл для каждого артикула
    $pdf->Output($filepath.$dop_file_dir.$filename_old.'_MERGE.pdf', 'F'); 

}


function make_etiketka ($pdf, array $data_for_etiketka, $filepath){
/************************************************************
 *  ФУНКЦИЯ Формирует PDF этикетку для озона и сохраняет ее в файл
 **********************************************************/





    // Размер 58x80 мм в points
    $width = 40;
    $height = 58;
    
    $pdf->AddPage('L', [$width, $height]);
       // Текст под штрихкодом
      
        // добавляем шрифт ариал
        $pdf->AddFont('TimesNRCyrMT','','timesnrcyrmt.php');// добавляем шрифт ариал
        $pdf->AddFont('TimesNRCyrMT-Bold','','timesnrcyrmt_bold.php'); 
// Прямоуголник
        $pdf->SetLineWidth(0.3); // Толщина линии
        $pdf->Rect(1, 1, 56, 27, 'D'); // D - контур (по умолчанию)

// Прямоуголник
        $pdf->SetLineWidth(0.3); // Толщина линии
        $pdf->Rect(1, 1, 56, 24, 'D'); // D - контур (по умолчанию)
// Прямоуголник черныцй
        $pdf->Rect(42, 1, 15, 7.5, 'DF'); // D - контур (по умолчанию)

// Прямоуголник
        $pdf->Rect(42, 1, 15, 13, 'D'); // D - контур (по умолчанию)

// УБИРАЕМ ВСЕ ПОЛЯ
$pdf->SetMargins(0, 0, 0); // left, top, right
$pdf->SetAutoPageBreak(false); // Отключаем автоматический перенос страниц

// Наносим текст 
// FSB
        $pdf->SetFont('Helvetica','B',11.5);
        $pdf->  SetXY(1, 1);
        $pdf->Cell(56 , 6, MakeUtf8Font($data_for_etiketka['fbs']), 0, 0,'L');
// Номер закза
        $pdf->SetFont('helvetica','',11.5);
        $pdf->  SetXY(1, 5);
        $pdf->Cell(56 ,6, MakeUtf8Font($data_for_etiketka['number_zakaz']),0,0,'L');

// ПВЗ / КУР
        $pdf->SetFont('TimesNRCyrMT','',10.6);
        $pdf->  SetXY(44.5, 9.3);
        $pdf->Cell(11.5 ,4, MakeUtf8Font($data_for_etiketka['PVZ']),0,0,'L');
// номер ПВЗ / КУР
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica','B', 18.3);
        $pdf->  SetXY(41.3, 3);
        $pdf->Cell(11.5 ,4, MakeUtf8Font($data_for_etiketka['num_PVZ']),0,0,'L');
        $pdf->SetTextColor(0, 0, 0);

// Адерс
if ($data_for_etiketka['PVZ'] == "ПВЗ") {   // если доставка в ПВЗ
    $y_t = 15.5;   
    $pdf->SetFont('TimesNRCyrMT','',10);
       $pdf->  SetXY(1, $y_t);

       foreach ($data_for_etiketka['adress'] as $adress) {
         $pdf->  SetXY(1, $y_t);
         $pdf->Cell(11.5 ,4, MakeUtf8Font($adress),0,0,'L');
         $y_t = $y_t + 4;

       }

} elseif($data_for_etiketka['PVZ'] == "КУР") {   // если курьрская доставка
       $y_t = 15.5;
       $pdf->SetFont('TimesNRCyrMT','',5.8);
       foreach ($data_for_etiketka['adress'] as $adress) {
         
            $pdf->  SetXY(1, $y_t);
            $pdf->Cell(30 ,4, MakeUtf8Font($adress), 0, 0,'L');
            $y_t = $y_t + 2.5;
       }
} else {                                         // если не ПВЗ и не Курьер
       $pdf->SetFont('TimesNRCyrMT','',11);
       $pdf->  SetXY(1, 16.5);
       $pdf->Cell(11.5 ,4, MakeUtf8Font("**NO CITY**"),0,0,'L');
       echo "<br>Про верить этикетки, проблема с Адресом (Номер заказа {$data_for_etiketka["number_zakaz"]} )<br>";
       echo "Штрих код  {$data_for_etiketka["shtihcode"]} файл  - {$filepath} )<br>";
}
 
// Габариты 
        $pdf->SetFont('Helvetica','B',6);
        $pdf->  SetXY(15, 24.6);
        $pdf->Cell(15 ,4, MakeUtf8Font($data_for_etiketka['gabariti']), 0, 0, 'R');

        $startX = $pdf->GetX();
        $pdf->SetFont('TimesNRCyrMT','',6);
        $pdf->  SetXY( $startX - 1.5, 24.6);
        $pdf->Cell(5.5 ,4, MakeUtf8Font("мм | "), 0, 0, 'L');

        $startX = $pdf->GetX();
        $pdf->SetFont('Helvetica','B',6);
        $pdf->  SetXY( $startX - 1.2, 24.6);
        $pdf->Cell(7 ,4, MakeUtf8Font($data_for_etiketka['ves']), 0, 0, 'L');

        $startX = $pdf->GetX();
        $pdf->SetFont('TimesNRCyrMT','',6);
        $pdf->  SetXY( $startX - 1.5, 24.6);
        $pdf->Cell(15 ,4, MakeUtf8Font("г."), 0, 0, 'L');

// Дата и номер штрихкожа

$pdf->SetFont('TimesNRCyrMT','',6);
$pdf->SetXY(9, 27.6);
$pdf->Cell(46, 4, MakeUtf8Font($data_for_etiketka['code']), 0, 0, 'С');

// ШТРИХКОД
        $filename = $filepath.'barcode/'.$data_for_etiketka['shtihcode'].'.jpg';
        $pdf->Image($filename, 2, 31, 54, 8, 'JPG');
        // удаляем файл со штрихкодом
        unlink ($filename);

        return $pdf;
         
          
        
}        




/************************************************************
 *  ФУНКЦИЯ переделывает шрифт в UTF-8
 **********************************************************/
function MakeUtf8Font($string) {
          $string = iconv('utf-8', 'windows-1251', $string);
          return $string;
        }






function GenerateBarcode($Barcode_numbers, $filepath) {
/************************************************************
 *  ФУНКЦИЯ ГЕНЕРИРУЕТ КАРТИНКУ JPG  по заданным цифрам
 **********************************************************/
$generator = new BarcodeGeneratorJPG ();
    // Настройки размера
$width = 3;    // толщина линий (в пикселях)
$height = 100; // высота штрихкода (в пикселях)

    $barcodeData = $generator->getBarcode(
    $Barcode_numbers,
    // $generator::TYPE_CODE_128,
    $generator::TYPE_CODE_128,
    $width,
    $height,
);

// Сохраняем в файл
file_put_contents($filepath.'barcode/'.$Barcode_numbers.'.jpg', $barcodeData);

}


function make_pdf_dop_list ($pdf, array $array_dop_list, $filepath){
/************************************************************
 *  ФУНКЦИЯ Формирует PDF этикетку для озона и сохраняет ее в файл
 **********************************************************/
    // Размер 58x80 мм в points
    $width = 40;
    $height = 58;
    
    $pdf->AddPage('L', [$width, $height]);
       // Текст под штрихкодом
      
        // добавляем шрифт ариал
        $pdf->AddFont('TimesNRCyrMT','','timesnrcyrmt.php');// добавляем шрифт ариал
        $pdf->AddFont('TimesNRCyrMT-Bold','','timesnrcyrmt_bold.php'); 
// Прямоуголник
        $pdf->SetLineWidth(0.3); // Толщина линии
        $pdf->Rect(1, 1, 56, 38, 'D'); // D - контур (по умолчанию)

// УБИРАЕМ ВСЕ ПОЛЯ
$pdf->SetMargins(0, 0, 0); // left, top, right
$pdf->SetAutoPageBreak(false); // Отключаем автоматический перенос страниц

// Наносим текст 
// Номер заказа
        $pdf->SetFont('TimesNRCyrMT-Bold','',14);
        $pdf->  SetXY(3, 5);
        $pdf->Cell(56 , 6, MakeUtf8Font("Заказ № ".$array_dop_list['number_order']), 0, 0,'L');
// Номер артикул
        $pdf->SetFont('TimesNRCyrMT-Bold','', 14);
        $pdf->  SetXY(3, 12);
        $pdf->Cell(56 ,6, MakeUtf8Font($array_dop_list['article']),0,0,'L');

// Номер количество элементов
        $pdf->SetFont('TimesNRCyrMT-Bold','',14);
        $pdf->  SetXY(3, 19);
        $pdf->Cell(56 ,6, MakeUtf8Font($array_dop_list['count_elements']." шт"),0,0,'L');
// Fake
        $pdf->SetFont('TimesNRCyrMT-Bold','',14);
        $pdf->  SetXY(3, 26);
        $pdf->Cell(56 ,6, MakeUtf8Font($array_dop_list['fake']),0,0,'L');
        return $pdf;
         
          
        
} 