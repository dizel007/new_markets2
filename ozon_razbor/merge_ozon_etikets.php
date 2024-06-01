<?php

error_reporting(E_ERROR | E_PARSE | E_NOTICE);

if (isset($_GET['filepath']) ) {
$filepath = $_GET['filepath'];
} else {
    echo "Не получили ссылку на файл";
    die('');
}

$filepath = str_replace("//", "/", $filepath);
$filepath = str_replace("../", "", $filepath);
// Создаем новую директорую 
$new_dir = $filepath."merge_pdf";
mkdir($new_dir, 0777);
$arr_name_articles = json_decode(file_get_contents($filepath."art_etik.json"), true);

echo  "<pre>";
print_r($arr_name_articles);


require_once '../libs/PDFMerger/PDFMerger.php';


use PDF_Merger\PDFMerger;


foreach ($arr_name_articles as $key=>$filename) {
$pdf_yandex= new PDFMerger;
$file_etiket = $filepath.$filename;
$file_key = $filepath.$key.".pdf";
// echo "<br>";
// echo $file_etiket;

// echo "<br>";
// echo $file_key;


    $pdf_yandex->addPDF( $file_key);
    $pdf_yandex->addPDF( $file_etiket);
    $link_merge_pdf_file  = __DIR__."/".$new_dir."/".$filename.'_MERGE.pdf' ;
    $pdf_yandex->merge('file',  $link_merge_pdf_file );


unset ($pdf_yandex);
}

/*****************************************************************************************************************
 ******  Формируем ZIP архив с этикетаксм и 1С файлом и листом подбора
 ******************************************************************************************************************/
  $zip_new = new ZipArchive();
  $zip_new->open($path_zip_archives."/"."etikets_№".$number_order." от ".date("Y-M-d").".zip", ZipArchive::CREATE|ZipArchive::OVERWRITE);
  foreach ($Arr_filenames_for_zip as $zips) {
  $zip_new->addFile($path_etiketki."/".$zips, "$zips"); // Добавляем пдф файлы
}
  $zip_new->addFile($path_excel_docs."/".$file_name_1c_list, "$file_name_1c_list"); // добавляем для НОВЫЙ 1С файл /// *****************
if (isset($file_name_list_podbora)){ 
  $zip_new->addFile($path_excel_docs."/".$file_name_list_podbora, "$file_name_list_podbora"); // добавляем для НОВЫЙ 1С файл /// *****************
}
  $zip_new->close();  

  $link_path_zip2 = $path_zip_archives."/"."etikets_№".$number_order." от ".date("Y-M-d").".zip"; //  ссылка чтобы скачать архив

  echo <<<HTML
  <br><br>
  <a href="$link_path_zip2"> скачать архив со стикерамии листом подбора</a>
  <br><br>
HTML;