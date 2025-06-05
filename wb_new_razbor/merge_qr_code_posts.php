<?php

require_once "../vendor/autoload.php";

// echo "<pre>";
// print_r($app_qr_pdf_file_names);

use setasign\Fpdi\Fpdi;

$pdf = new FPDI();


foreach ($app_qr_pdf_file_names as $pdf_file) {
    $pageCount = $pdf->setSourceFile($path_qr_supply."/".$pdf_file);
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


/******************************************************************************************
 *  ***************   Формируем архив с QR кодам поставок ********************************
 ******************************************************************************************/
$zip_new_qr_posts = new ZipArchive();
$zip_arhive_name_QR = "ALL_QRcode_".$Zakaz_v_1c_ALL."_(".date("Y-M-d").").zip";
$zip_new_qr_posts->open($path_arhives."/".$zip_arhive_name_QR, ZipArchive::CREATE|ZipArchive::OVERWRITE);
$zip_new_qr_posts->addFile($path_qr_supply."/".$link_downloads_PDF_QR_codes, "$link_downloads_PDF_QR_codes"); // Добавляем пдф файлы
$zip_new_qr_posts->close(); 
$link_downloads_qr_posts_2 = $path_arhives."/".$zip_arhive_name_QR;



echo <<<HTML
<!-- ссылка на скачивание ПДФ файлика QR кодами поставок -->
<a target="_blank" href="$link_downloads_PDF_QR_codes"> MERGE QR code Posts</a>
<!-- ССылка на скачивание архива в файликов -->
<a target="_blank" href="$link_downloads_qr_posts_2"> ZIP _ MERGE QR code Posts</a>
HTML;