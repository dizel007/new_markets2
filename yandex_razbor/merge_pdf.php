<?php


require_once "../vendor/autoload.php";

use setasign\Fpdi\Fpdi;

$pdf = new FPDI();


foreach ($arr_files_name as $pdf_file) {
    $pageCount = $pdf->setSourceFile($pdf_file);
    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $templateId = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($templateId);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($templateId);
    }
}

// Создаем файл с номером заказа и артикулом

$pdf_2 = new FPDI();


// добавляем шрифт TimesNRCyrMT
$pdf_2->AddFont('TimesNRCyrMT','','timesnrcyrmt.php');// добавляем шрифт ариал
$pdf_2->AddFont('TimesNRCyrMT-Bold','','timesnrcyrmt_bold.php'); 
$pdf_2->AddPage('P', [58, 40]);


$pdf_2->SetFont('TimesNRCyrMT-Bold', '', 10);
$pdf_2->SetMargins(1, 1, 1); // слева, сверху, справа
$pdf_2->MultiCell(40, 4, iconv('UTF-8', 'windows-1251', "Заказ: $order_number" ), 0 , 'L');
$pdf_2->MultiCell(40, 4, iconv('UTF-8', 'windows-1251', "Арт: $new_article"    ), 0 , 'L');
$pdf_2->MultiCell(40, 4, iconv('UTF-8', 'windows-1251', "К-во: $count_items шт"), 0 , 'L');

$link_example = __DIR__."/".$dir."_merge/example.pdf";
$pdf_2->Output('F', $link_example );

     $pageCount = $pdf->setSourceFile($link_example);
for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {

    $templateId = $pdf->importPage($pageNo);
    $size = $pdf->getTemplateSize($templateId);
    $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
    $pdf->useTemplate($templateId);

}

unlink ($link_example); // удаляем файл с номером и артикулом

$file_merge_pdf_name =  $new_article."(".$count_items." шт).pdf";
$link_merge_pdf_file  = __DIR__."/".$dir."_merge/".$file_merge_pdf_name ;

$pdf->Output('F', $link_merge_pdf_file);

//**



//
// require_once '../libs/PDFMerger/PDFMerger.php';
// use PDF_Merger\PDFMerger;
// $pdf_yandex = new PDFMerger;

// foreach ( $arr_files_name as $pdf_file) {
//     $pdf_yandex->addPDF("$pdf_file", '1');
// }
// $file_merge_pdf_name =  $new_article."(".$count_items." шт).pdf";
// $link_merge_pdf_file  = __DIR__."/".$dir."_merge/".$file_merge_pdf_name ;

// //  $pdf_yandex->merge('file', __DIR__."\\".$dir."_merge\\".$items['offerId']."(".$count_items." шт).pdf");
//  $pdf_yandex->merge('file', $link_merge_pdf_file );
