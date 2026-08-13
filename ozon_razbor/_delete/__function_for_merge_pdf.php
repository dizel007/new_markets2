<?php

use Smalot\PdfParser\Parser;
use Picqer\Barcode\BarcodeGeneratorJPG ;
use setasign\Fpdi\Fpdi;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;


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
// die();
// 350538438068000
    foreach ($blocks as $string) {
        // echo "<pre>";
        // echo ($string);
        // echo "<br>";
      
       
        $temp = explode("\n", $string);

 

        $count_items = count($temp);
        $temp_t['fbs'] =  $temp[0]; // номер склада ФБС
        $temp_t_2 =  trim($temp[1]); // номер заказа
        
        $temp_t['PVZ']     =  trim($temp[2]);  // слово ПВЗ или КУР или ПОЧТА
        $temp_t['num_PVZ']  = mb_substr($temp_t_2, -4); // из заказа оставляем только номер ПВЩ

    
        // Выделяме номер заказа (бывает, что он отдельно, а бывает, что слипается с номером ПВЗ)
        $arr_number_zakaz = explode("-", $temp_t_2);
        if (strlen($arr_number_zakaz[2]) >=5) {
            $temp_t['PVZ']     =  trim($temp[2]);  // слово ПВЗ или КУР или ПОЧТА
            $temp_t['num_PVZ']  = mb_substr($temp_t_2, -4); // из заказа оставляем только номер ПВЩ
            $temp_t['number_zakaz'] =   trim(substr($temp_t_2, 0, -4)); // номер ЗАКаза без лишней информации
            $start_i = 3;
        } else  {
            $temp_t['number_zakaz'] =   trim($temp_t_2); // номер ЗАКаза без лишней информации
            $temp_t['num_PVZ']  = trim($temp[2]); // из заказа оставляем только номер ПВЩ
            $temp_t['PVZ']     =  trim($temp[3]);  // слово ПВЗ или КУР или ПОЧТА
            $start_i = 4;

        }

            
        for ($i_z = $start_i; $i_z <=($count_items - 3);  $i_z++) {
            
          $temp_t['adress'][] =  trim($temp[$i_z]);  // цифра с номером ПВЗ или КУР
        }
        
        
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
        // for ($i = 2; $i <= ($count_items - 5); $i++) {
        //     $temp_t['adress'][$i-2] = $temp[$i]; 
        // }

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
        // echo "<br>************** 444 ***************************<br>";

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
        // GenerateBarcode($one_article['shtihcode'], $filepath); // для штрихкоды 
        GenerateBarcode_QR($one_article['shtihcode'], $filepath); // для QR кода
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

// echo "<pre>";
// print_r($data_for_etiketka);


    // Размер 58x80 мм в points
    $width = 40;
    $height = 58;
    
    $pdf->AddPage('L', [$width, $height]);
       // Текст под штрихкодом
      
        // добавляем шрифт ариал
        $pdf->AddFont('TimesNRCyrMT','','timesnrcyrmt.php');// добавляем шрифт ариал
        $pdf->AddFont('TimesNRCyrMT-Bold','','timesnrcyrmt_bold.php'); 
// Прямоуголник РАМКА (самый большой)
        $pdf->SetLineWidth(0.3); // Толщина линии
        $pdf->Rect(1, 1, 56, 36, 'D'); // D - контур (по умолчанию)

// Прямоуголник
        $pdf->SetLineWidth(0.3); // Толщина линии
        $pdf->Rect(1, 1, 56, 32.2, 'D'); // D - контур (по умолчанию)
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
        $pdf->SetFont('helvetica','',10.5);
        $pdf->  SetXY(1, 4.5);
        $pdf->Cell(56 , 6, MakeUtf8Font($data_for_etiketka['number_zakaz']),0,0,'L');

// ПВЗ / КУР / ПОЧТА 
        $pdf->SetFont('TimesNRCyrMT','',10.6);
        if ($data_for_etiketka['PVZ'] == "ПВЗ") {
            $pdf->  SetXY(44.5, 9.3);
            $pdf->Cell(11.5 ,4, MakeUtf8Font($data_for_etiketka['PVZ']),0,0,'L');
        } elseif ($data_for_etiketka['PVZ'] == "ЮПВЗ") {
            $pdf->  SetXY(41.5, 9.3);
            $pdf->Cell(11.5 ,4, MakeUtf8Font("Ю  ПВЗ"),0,0,'L');
            $pdf->Rect(47, 1, 10, 13, 'D'); // черта отделяющая Ю
        }  elseif ($data_for_etiketka['PVZ'] == "КУР") {
            $pdf->  SetXY(44.5, 9.3);
            $pdf->Cell(11.5 ,4, MakeUtf8Font($data_for_etiketka['PVZ']),0,0,'L');

        } elseif ($data_for_etiketka['PVZ'] == "ЮКУР") {
            $pdf->  SetXY(41.5, 9.3);
            $pdf->Cell(11.5 ,4, MakeUtf8Font("Ю  КУР"),0,0,'L');
            $pdf->Rect(47, 1, 10, 13, 'D');  // черта отделяющая Ю
        } elseif ($data_for_etiketka['PVZ'] == "ПОЧТА") {
            $pdf->  SetXY(41.5, 9.3);
            $pdf->Cell(11.5 ,4, MakeUtf8Font($data_for_etiketka['PVZ']),0,0,'L');
        } elseif ($data_for_etiketka['PVZ'] == "ПСТ") {
            $pdf->  SetXY(44.5, 9.3);
            $pdf->Cell(11.5 ,4, MakeUtf8Font($data_for_etiketka['PVZ']),0,0,'L');

        } else {
            $pdf->  SetXY(44.5, 9.3);
            $pdf->Cell(11.5 ,4, MakeUtf8Font('XXX'),0,0,'L'); 
            echo "<br>************************** ALARM ПВЗ/КУР/ПОЧТА/ПСТ ****************************<br>";
        }


// номер ПВЗ / КУР
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica','B', 18.3);
        $pdf->  SetXY(41.3, 3);
        $pdf->Cell(11.5 ,4, MakeUtf8Font($data_for_etiketka['num_PVZ']),0,0,'L');
        $pdf->SetTextColor(0, 0, 0);

//*********************** */ Адерс
if ($data_for_etiketka['PVZ'] == "ПВЗ") {   // если доставка в ПВЗ
    $y_t = 15.5;   
    $pdf->SetFont('TimesNRCyrMT','',10);
       $pdf->  SetXY(1, $y_t);

       foreach ($data_for_etiketka['adress'] as $part_adress) {
        // если адрес длинный, то его нужно перенести на вторую строку
                $pdf->  SetXY(1, $y_t);
                $pdf->Cell(11.5 ,4, MakeUtf8Font($part_adress),0,0,'L');
                $y_t = $y_t + 4;


       }

} elseif ($data_for_etiketka['PVZ'] == "ЮПВЗ") {   // если доставка в Ю ПВЗ
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
       $pdf->SetFont('TimesNRCyrMT','',7);
       foreach ($data_for_etiketka['adress'] as $adress) {
         
            $pdf->  SetXY(1, $y_t);
            $pdf->Cell(30 ,4, MakeUtf8Font($adress), 0, 0,'L');
            $y_t = $y_t + 2.5;
       }
} elseif($data_for_etiketka['PVZ'] == "ЮКУР") {   // если курьрская доставка
       $y_t = 15.5;
       $pdf->SetFont('TimesNRCyrMT','',7);
       foreach ($data_for_etiketka['adress'] as $adress) {
         
            $pdf->  SetXY(1, $y_t);
            $pdf->Cell(30 ,4, MakeUtf8Font($adress), 0, 0,'L');
            $y_t = $y_t + 3.5;
       }
} elseif($data_for_etiketka['PVZ'] == "ПОЧТА") {   // если курьрская доставка
       $y_t = 15.5;
       $pdf->SetFont('TimesNRCyrMT','',5.8);
       foreach ($data_for_etiketka['adress'] as $adress) {
         
            $pdf->  SetXY(1, $y_t);
            $pdf->Cell(30 ,4, MakeUtf8Font($adress), 0, 0,'L');
            $y_t = $y_t + 2.5;
       }
} elseif ($data_for_etiketka['PVZ'] == "ПСТ") {   // если постомат 
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
$Y_coordinata = 33.1;
$Gabarit_font_size = 7;
        $pdf->SetFont('Helvetica','B', $Gabarit_font_size);
        $pdf->  SetXY(15, $Y_coordinata);
        $pdf->Cell(15 ,4, MakeUtf8Font($data_for_etiketka['gabariti']), 0, 0, 'R');

        $startX = $pdf->GetX();
        $pdf->SetFont('TimesNRCyrMT','', $Gabarit_font_size);
        $pdf->  SetXY( $startX - 1.5, $Y_coordinata);
        $pdf->Cell(5.5 ,4, MakeUtf8Font("мм | "), 0, 0, 'L');

        $startX = $pdf->GetX();
        $pdf->SetFont('Helvetica','B', $Gabarit_font_size);
        $pdf->  SetXY( $startX - 0.2, $Y_coordinata);
        $pdf->Cell(7 ,4, MakeUtf8Font($data_for_etiketka['ves']), 0, 0, 'L');

        $startX = $pdf->GetX();
        $pdf->SetFont('TimesNRCyrMT','', $Gabarit_font_size);
        $pdf->  SetXY( $startX - 1.5, $Y_coordinata);
        $pdf->Cell(15 ,4, MakeUtf8Font("г."), 0, 0, 'L');

// Дата и номер штрихкожа
    $pdf->SetFont('TimesNRCyrMT','',6);
    $pdf->SetXY(9, 36.5);
    $pdf->Cell(46, 4, MakeUtf8Font($data_for_etiketka['code']), 0, 0, 'С');


/** ДЛЯ ВСТАВКИ QR кода */
        $filename = $filepath.'barcode/'.$data_for_etiketka['shtihcode'].'.png';
        $pdf->Image($filename, 39, 15, 17, 17, 'PNG');


// /** ДЛЯ ВСТАВКИ ШТРИХ КОДА */
        //  $filename = $filepath.'barcode/'.$data_for_etiketka['shtihcode'].'.jpg';
        //  $pdf->Image($filename, 2, 31, 54, 8, 'JPG');
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


function GenerateBarcode_QR($Barcode_numbers, $filepath) {
/************************************************************
 *  ФУНКЦИЯ ГЕНЕРИРУЕТ КАРТИНКУ JPG  по заданным цифрам
 **********************************************************/
// Создаем экземпляр генератора
$qrCode = QrCode::create($Barcode_numbers)
    ->setSize(400)
    ->setMargin(2);

$writer = new PngWriter();
$result = $writer->write($qrCode);

// Сохраняем в файл
$result->saveToFile($filepath.'barcode/'.$Barcode_numbers.'.png');

// echo "QR-код сохранён в my_qr_code.png";
// Сохраняем в файл
// file_put_contents($filepath.'barcode/'.$Barcode_numbers.'.jpg', $result);

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





/**
 * Выполняет перенос строки, если её длина превышает заданный лимит.
 * Перенос происходит по последнему разделителю (-, _, пробел) в пределах первых $maxLen символов.
 *
 */
function wrapLine(string $str, int $maxLen = 14)
{
    // Если строка уже укладывается в лимит, возвращаем как есть
    if (mb_strlen($str) <= $maxLen) {
        $arr_adress[] = $str;
        return $arr_adress;
    }

    $delimiters = ['-', '_', ' '];
    // Берём первые $maxLen символов для поиска разделителя
    $part = mb_substr($str, 0, $maxLen);
    $lastPos = -1;

    // Ищем самый правый разделитель в этой части
    foreach ($delimiters as $delim) {
        $pos = mb_strrpos($part, $delim);
        if ($pos !== false && $pos > $lastPos) {
            $lastPos = $pos;
        }
    }

    if ($lastPos !== -1) {
        // Разделитель найден – разрываем после него (включая разделитель в первую часть)
        $splitPos = $lastPos + 1;
        $arr_adress[] = mb_substr($str, 0, $splitPos);
        $arr_adress[] = mb_substr($str, $splitPos);
    } else {
        // Разделителей нет – режем принудительно по $maxLen
        $arr_adress[] = mb_substr($str, 0, $maxLen);
        $arr_adress[] = mb_substr($str, $maxLen);
    }

    return $arr_adress;
}