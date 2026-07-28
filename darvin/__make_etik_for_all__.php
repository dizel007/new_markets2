<?php
/**
 * Просто создаем ПДФ файл 60*40 для этикетки
 */

require '../vendor/autoload.php';


    $pdf = new TCPDF('L', 'mm', [60, 40], true, 'UTF-8', false);
    // Создание PDF с размером страницы 60x40 мм
   
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    $pdf->setAutoPageBreak(false);   // отключаем авто-перенос страниц
    $pdf->AddPage();

    // Подключаем шрифт (если не добавили заранее, лучше сделать один раз)
    $fontname = TCPDF_FONTS::addTTFfont('arialbd.ttf', 'TrueTypeUnicode', '', 96);
    // $fontname = 'helvetica'; // или 'arialbd', если успешно добавили
    $pdf->SetFont($fontname, '', 12);

    // Размещаем текст под штрих-кодом (при высоте кода примерно 25 мм)
$pdf->SetX(2);
$pdf->Cell(20, 4, 'Экобордюр пластиковый', 0, 1, 'L');
$pdf->SetX(2);
$pdf->Cell(20, 4, 'КОНТУР, черный', 0, 1, 'L');
$pdf->SetX(2);
$pdf->Cell(20, 4, 'L=1000 H-80', 0, 1, 'L');

    // Сохраняем PDF
   
    $pdfPath = __DIR__ . '/'.'_een_.pdf';
    $pdf->Output($pdfPath, 'F');



