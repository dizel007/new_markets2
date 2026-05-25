<?php

require_once '../vendor/autoload.php';
// require_once '../libs/PDFMerger/PDFMerger.php';
require_once '../pdo_functions/pdo_functions.php';
require_once 'functions/conver_pdf14.php';
require_once 'functions/make_title_list.php';
require_once 'functions/merge_two_pdf.php';


// require_once 'function_for_merge_pdf.php';
// require_once 'fake_sizes.php'; // массив с левыми габаритами 

// error_reporting(E_ERROR | E_PARSE | E_NOTICE);


// // Пусть у JSON файлу с массивов имен файлов этикеток
if (isset($_GET['number_order']) ) {
  $date_razbora = $_GET['date_razbora'];
  $number_order = $_GET['number_order'];
  // $filepath = $_GET['filepath'];
  $for_all_filepath = '../!all_razbor/ozon/'.$date_razbora."/".$number_order."/";
  $filepath = $for_all_filepath.'etiketki/';
} else {
    echo "Не получили ссылку на файл";
    die('');
}

// файл со всеми путями
$array_dop_files = json_decode(file_get_contents($filepath."array_dop_info.json"), true);

// echo "<pre>";
// print_r($array_dop_files);

$number_order = $array_dop_files['number_order'];
$path_excel_docs = $for_all_filepath.'excel_docs';
$file_name_1c_list = $array_dop_files['file_name_1c_list'];
$file_name_list_podbora =  $array_dop_files['file_name_list_podbora'];


// Создаем новую директорую куда будем складывать соедененные файлы
$new_dir_merge = $filepath."merge_pdf";
if (!is_dir($new_dir_merge)) {
    mkdir($new_dir_merge, 0777);
} 

// Создаем новую директорую куда будем складывать соедененные файлы
$new_dir_barcode = $filepath."barcode";
if (!is_dir($new_dir_barcode)) {
    mkdir($new_dir_barcode, 0777);
} 

// Создаем новую директорую куда будем складывать переконвертированные файлы
$new_dir_convert = $filepath."convert";
if (!is_dir($new_dir_barcode)) {
    mkdir($new_dir_barcode, 0777);
} 





// достаем массив с названиями файлов
$arr_name_articles = json_decode(file_get_contents($filepath."art_etik.json"), true);


foreach ($arr_name_articles as $key=>$filename) {
  $outputFile = convertPdfTo14($filepath.$filename.".pdf");
//  создаем титульный лист с информацией по заказу 
  $textLines = explode(' ', $filename); 
  createLabelPdf($textLines, $filepath."barcode/".'label.pdf', 60, 40, 'P');
//  объедениям файлы
$temp_file_name = $new_dir_merge."/".$filename."_merge_14.pdf";
$arr_merge_zip_files[] = $temp_file_name;
mergeTwoPdfs($outputFile, $filepath."barcode/".'label.pdf', $temp_file_name);

}



 /*****************************************************************************************************************
 ******  Формируем ZIP архив с этикетаксм и 1С файлом и листом подбора (Простой MERGE-архив)
 ******************************************************************************************************************/
  $zip_new = new ZipArchive();
  $link_merge_file_name = $new_dir_merge."/"."etikets_№".$number_order."_от_".date("Y-M-d")."_MERGE.zip";

  $zip_new->open($link_merge_file_name, ZipArchive::CREATE|ZipArchive::OVERWRITE);
// цепляем ПФД файлы
  foreach ($arr_merge_zip_files as $zips) {
      $path_parts = pathinfo($zips);
      // print_r($path_parts);
      $zip_new->addFile($zips, $path_parts['basename']); // Добавляем пдф файлы
  }
// цепляем С файл
  $zip_new->addFile($path_excel_docs."/".$file_name_1c_list, "$file_name_1c_list"); // добавляем для НОВЫЙ 1С файл /// *****************
// цепляем лист подора
  if (isset($file_name_list_podbora)){ 
    $zip_new->addFile($path_excel_docs."/".$file_name_list_podbora, "$file_name_list_podbora"); // добавляем для НОВЫЙ 1С файл /// *****************
  }
  $zip_new->close();  


  $file_size= round((filesize($link_merge_file_name)/1000000),2)." Mb";
  $non_merge_file_link = mb_substr($array_dop_files['file_non_merge_archive'], 3);


 

// ссылка для перехода к созданию этикеток с фэйковыми размероами
  echo <<<HTML
  <head>
  <link rel="stylesheet" href="css/link_button.css">
  <script type="text/javascript" src="js/download.js"></script>
  <script type="text/javascript" src="js/jquery.min.js"></script>
 
</head>
<!-- Ссылка на этикетки с реальными габаритами -->
<body>
   <div class="container">
        <div class="block block-1">
            <h2> ОБъедененный вариант ЭТИКЕТОК</h2>
            <p><a href="$link_merge_file_name">ОБЪЕДЕНЕННЫЕ ЭТИКЕТКИ</a></p>
            <p class="bottom">$file_size</p>
      </div>

    <div class="block block-3">
    <h2> Оригинальный вариант ЭТИКЕТОК</h2>
    <p><a  href="$non_merge_file_link">ОРИГИНАЛ(старая версия)</a></p>

    </div>
</div>

</body>
  
  
HTML;


