<?php
/**
 * Создаёт PDF-файл размером 60×40 мм с заданными строками текста.
 *
 * @param array $lines Массив строк текста (каждая строка с новой строки)
 * @param string $outputFile Путь для сохранения PDF-файла
 * @param int $width Ширина страницы в мм (по умолч. 60)
 * @param int $height Высота страницы в мм (по умолч. 40)
 * @param string $orientation Ориентация страницы: 'L' (альбомная) или 'P' (портретная)
 * @return bool true в случае успеха, false при ошибке
 */
function createLabelPdf($lines, $outputFile, $width = 60, $height = 40, $orientation) {
    // Создаём PDF-документ: единицы измерения мм, ориентация, размер страницы
    $pdf = new FPDF($orientation, 'mm', [$width, $height]);
    $pdf->AddPage();
    

            // добавляем шрифт ариал
    $pdf->AddFont('TimesNRCyrMT','','timesnrcyrmt.php');// добавляем шрифт ариал
    $pdf->AddFont('timesnrcyrmt_bold','','timesnrcyrmt_bold.php'); 


    // Устанавливаем шрифт (название, стиль, размер)
    // Доступные шрифты: 'Arial', 'Times', 'Courier'
            $pdf->SetFont('timesnrcyrmt_bold','',13);

    
    // Рисуем рамку (опционально, для наглядности)
    $pdf->Rect(2, 2, $height  - 4, $width - 4);
    
    // Отступы от краёв (в мм)
    $leftMargin = 3;
    $topMargin = 6;
    $lineHeight = 7; // высота строки в мм
    
    // Проходим по каждой строке текста
    foreach ($lines as $index => $line) {
        $y = $topMargin + $index * $lineHeight;
        // Убеждаемся, что текст не выходит за нижнюю границу
        if ($y + $lineHeight > $height - 4) {
            break; // или можно уменьшить шрифт, но для простоты обрываем
        }
        // Устанавливаем позицию X, Y и выводим строку
        $pdf->SetXY($leftMargin, $y);
        $pdf->Cell(0, $lineHeight, MakeUtf8Font($line), 0, 1, 'L'); // 0 = без рамки, 1 = перенос строки, 'L' = выравнивание влево
    }
    
    // Сохраняем файл
    $pdf->Output('F', $outputFile);
    return file_exists($outputFile);
}

/************************************************************
 *  ФУНКЦИЯ переделывает шрифт в UTF-8
 **********************************************************/
function MakeUtf8Font($string) {
          $string = iconv('utf-8', 'windows-1251', $string);
          return $string;
        }


