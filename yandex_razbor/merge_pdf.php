<?php

// require_once '../libs/PDFMerger/PDFMerger.php';
use PDF_Merger\PDFMerger;
$pdf_yandex = new PDFMerger;

foreach ( $arr_files_name as $pdf_file) {
    $pdf_yandex->addPDF("$pdf_file", '1');
}
$file_merge_pdf_name =  $new_article."(".$count_items." шт).pdf";
$link_merge_pdf_file  = __DIR__."\\".$dir."_merge\\".$file_merge_pdf_name ;

//  $pdf_yandex->merge('file', __DIR__."\\".$dir."_merge\\".$items['offerId']."(".$count_items." шт).pdf");
 $pdf_yandex->merge('file', $link_merge_pdf_file );
