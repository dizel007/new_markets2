<?php

/**
 * Функция объеденияет два ПДФ файла
 * 
 * 
 */
function mergeTwoPdfs($file1, $file2, $outputFile) {
    $pdf = new \setasign\Fpdi\Fpdi();
    
    // Обработка первого файла
    $pageCount1 = $pdf->setSourceFile($file1);
    for ($i = 1; $i <= $pageCount1; $i++) {
        $tpl = $pdf->importPage($i);
        $size = $pdf->getTemplateSize($tpl); // получаем размеры импортируемой страницы
        // Добавляем новую страницу с размерами импортированного шаблона
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl);
    }
    
    // Обработка второго файла
    $pageCount2 = $pdf->setSourceFile($file2);
    for ($i = 1; $i <= $pageCount2; $i++) {
        $tpl = $pdf->importPage($i);
        $size = $pdf->getTemplateSize($tpl);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl);
    }
    
    $pdf->Output('F', $outputFile);
    return file_exists($outputFile);
}

