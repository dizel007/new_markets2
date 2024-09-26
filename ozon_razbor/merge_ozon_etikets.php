<?php

require_once '../libs/PDFMerger/PDFMerger.php';

require_once '../pdo_functions/pdo_functions.php';
error_reporting(E_ERROR | E_PARSE | E_NOTICE);

// // Пусть у JSON файлу с массивов имен файлов этикеток
if (isset($_GET['filepath']) ) {
$filepath = $_GET['filepath'];
} else {
    echo "Не получили ссылку на файл";
    die('');
}


// print_r($_GET['filepath'] );
// echo "<br>";
// Костыли чтобы исправить адрес пути у фаилам

$filepath = "../".str_replace("../", "", $filepath);

// print_r($filepath);


$array_dop_files = json_decode(file_get_contents($filepath."array_dop_info.json"), true);

// echo  "<pre>";
// print_r($array_dop_files);


$number_order = $array_dop_files['number_order'];
$path_excel_docs = $array_dop_files['path_excel_docs'];

// $path_excel_docs = str_replace("//", "/", $path_excel_docs);
$path_excel_docs = "../".str_replace("../", "", $path_excel_docs);
// echo  "<br>********************************<br>";
// print_r($path_excel_docs);
// echo  "<br>********************************<br>";
$file_name_1c_list = $array_dop_files['file_name_1c_list'];
$file_name_list_podbora =  $array_dop_files['file_name_list_podbora'];


// Создаем новую директорую куда будем складывать соедененные файлы
$new_dir = $filepath."merge_pdf";
mkdir($new_dir, 0777);
$arr_name_articles = json_decode(file_get_contents($filepath."art_etik.json"), true);

// echo  "<pre>";
// print_r($arr_name_articles);





use PDF_Merger\PDFMerger;

// переделаем массив с именами файлов и соединяем их с файлом где написан артикул
foreach ($arr_name_articles as $key=>$filename) {
  //  echo "$filename<br>";

    $pdf_ozom_merge= new PDFMerger;
    $file_etiket = $filepath.$filename;

    $file_key = $filepath.$key.".pdf";


    
    $pdf_ozom_merge->addPDF($file_etiket); // этикетки
    $pdf_ozom_merge->addPDF($file_key);    // допописание

    $link_merge_pdf_file  = __DIR__."/".$new_dir."/".$filename.'_MERGE.pdf' ;

    $pdf_ozom_merge->merge('file',  $link_merge_pdf_file );
unset ($pdf_ozom_merge);
// echo "$key<br>";
}

/*****************************************************************************************************************
 ******  Формируем ZIP архив с этикетаксм и 1С файлом и листом подбора
 ******************************************************************************************************************/
  $zip_new = new ZipArchive();
  $zip_new->open($new_dir."/"."etikets_№".$number_order."_от_".date("Y-M-d")."_MERGE.zip", ZipArchive::CREATE|ZipArchive::OVERWRITE);
  foreach ($arr_name_articles as $zips) {
    // echo $new_dir."/".$zips.'_MERGE.pdf'."<br>";
  $zip_new->addFile($new_dir."/".$zips.'_MERGE.pdf', "$zips"); // Добавляем пдф файлы
}
$zip_new->addFile($path_excel_docs."/".$file_name_1c_list, "$file_name_1c_list"); // добавляем для НОВЫЙ 1С файл /// *****************
if (isset($file_name_list_podbora)){ 
  $zip_new->addFile($path_excel_docs."/".$file_name_list_podbora, "$file_name_list_podbora"); // добавляем для НОВЫЙ 1С файл /// *****************
}
  $zip_new->close();  
$merge_file_name = "etikets_№".$number_order."_от_".date("Y-M-d")."_MERGE.zip";
  $link_path_zip2 = $new_dir."/".$merge_file_name; //  ссылка чтобы скачать архив

  $file_size= round((filesize( $link_path_zip2)/1000000),2)." Mb";


  $non_merge_file_link = mb_substr($array_dop_files['file_non_merge_archive'], 3);

  echo <<<HTML
  <head>
  <link rel="stylesheet" href="css/link_button.css">
  <script type="text/javascript" src="js/download.js"></script>
  <script type="text/javascript" src="js/jquery.min.js"></script>
 
</head>
<body>
  <div class="button">
  <a href="$link_path_zip2">скачать архив со стикерамии (MERGE)</a>
  <p class="top">$merge_file_name</p>
  <p class="bottom">$file_size</p>
  
</div>
<div style="text-align:center">
 <a  href="$non_merge_file_link">скачать архив со стикерамии листом подбора(старая версия)</a>
</div>
</body>
  
  
HTML;


