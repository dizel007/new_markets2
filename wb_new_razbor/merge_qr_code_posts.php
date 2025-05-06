<?php

require_once "../vendor/autoload.php";

use setasign\Fpdi\Fpdi;

$pdf = new FPDI();


foreach ($app_qr_pdf_file_names as $pdf_file) {
    $pageCount = $pdf->setSourceFile($pdf_file);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
    }
}

// Создаем файл с номером заказа и артикулом



$Pdf_QR_name = "QRcode_".$Zakaz_v_1c."_(".date("Y-M-d").").pdf";
$link_downloads_PDF_QR_codes = $path_arhives."/".$Pdf_QR_name;

$pdf->Output('F', $link_downloads_PDF_QR_codes);




echo <<<HTML
<a href="$link_downloads_PDF_QR_codes"> MERGE QR code Posts</a>

HTML;