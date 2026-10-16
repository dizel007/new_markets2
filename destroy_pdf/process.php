<?php
require '../vendor/autoload.php';

use setasign\Fpdi\Fpdi;

/**
 * Убирает последнюю страницу из PDF.
 *
 * @param string $input  путь к исходному PDF
 * @param string $output путь для сохранения результата
 * @return string путь к новому PDF
 * @throws RuntimeException
 */
function removeLastPage(string $input, string $output): string
{
    $pdf = new Fpdi();
    $pageCount = $pdf->setSourceFile($input);

    if ($pageCount < 2) {
        throw new RuntimeException('Меньше 2 страниц — удалять нечего');
    }

    for ($i = 1; $i < $pageCount; $i++) {
        $tpl  = $pdf->importPage($i);
        $size = $pdf->getTemplateSize($tpl);

        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tpl);
    }

    $pdf->Output($output, 'F');

    return $output;
}