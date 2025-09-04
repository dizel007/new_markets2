<?php

require_once '../vendor/autoload.php';
require_once '../libs/PDFMerger/PDFMerger.php';
require_once '../pdo_functions/pdo_functions.php';
require_once 'function_for_merge_pdf.php';
require_once 'fake_sizes.php'; // массив с левыми габаритами 

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
$new_dir = $filepath."merge_pdf";
if (!is_dir($new_dir)) {
    mkdir($new_dir, 0777);
} 

// Создаем новую директорую куда будем складывать соедененные файлы
$new_dir_barcode = $filepath."barcode";
if (!is_dir($new_dir_barcode)) {
    mkdir($new_dir_barcode, 0777);
} 



// Создаем новую директорую куда будем складывать соедененные файлы c фэйковыми размерами
$new_dir_fake = $filepath."merge_pdf_fake";
if (!is_dir($new_dir_fake)) {
    mkdir($new_dir_fake, 0777);
} 


// достаем массив с названиями файлов
$arr_name_articles = json_decode(file_get_contents($filepath."art_etik.json"), true);

foreach ($arr_name_articles as $key=>$filename) {
      echo "название файла - $filename<br>";
    $array_one_article = parce_ozon_etiketki($filepath , $filename); // формируем свои новые этикетки
   // формируемданные для доп листа
      $array_dop_list['count_elements'] = count($array_one_article);
      $array_dop_list['number_order'] = $number_order;
      $array_dop_list['article'] = $key;
      $array_dop_list['fake'] = "";

    // Запускаем формирование ПДФ этикетки
    format_ozon_etiketka ($filepath, "merge_pdf/" ,$filename, $array_one_article, $array_dop_list);

    
    /****************************************
    корректировка габаритов на левые
    *****************************************/
     foreach ($arr_fake_gabariti_ves as $key_art=> $item_zz) {
        if (mb_strtolower($key_art) == (mb_strtolower($key))) {
          foreach ($array_one_article as &$one_article_z) {
            $one_article_z['gabariti'] = $item_zz['gabariti'];
            $one_article_z['ves'] = $item_zz['ves'];
             }
        $array_dop_list['fake'] = "---/fa/---";
          break 1;
        }

    }
// Запускаем формирование ПДФ этикеток с фейковыми габарита
    format_ozon_etiketka ($filepath, "merge_pdf_fake/" ,$filename, $array_one_article, $array_dop_list);
}



 /*****************************************************************************************************************
 ******  Формируем ZIP архив с этикетаксм и 1С файлом и листом подбора (Простой MERGE-архив)
 ******************************************************************************************************************/
  $zip_new = new ZipArchive();
  $link_merge_file_name = $new_dir."/"."etikets_№".$number_order."_от_".date("Y-M-d")."_MERGE.zip";

  $zip_new->open($link_merge_file_name, ZipArchive::CREATE|ZipArchive::OVERWRITE);
// цепляем ПФД файлы
  foreach ($arr_name_articles as $zips) {
      $zip_file_name = $zips."_MERGE.pdf";
      $zip_new->addFile($new_dir."/".$zips.'_MERGE.pdf', "$zip_file_name"); // Добавляем пдф файлы
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



/*****************************************************************************************************************
 ******  Формируем ZIP архив с этикетаксм и 1С файлом и листом подбора ( Fake MERGE-архив)
 ******************************************************************************************************************/
  $zip_new = new ZipArchive();
$link_merge_file_name_fake = $new_dir_fake."/"."etikets_№".$number_order."_от_".date("Y-M-d")."_Fake_MERGE.zip";
  $zip_new->open($link_merge_file_name_fake, ZipArchive::CREATE|ZipArchive::OVERWRITE);
// цепляем ПФД файлы
  foreach ($arr_name_articles as $zips) {
      $zip_file_name = $zips."_MERGE.pdf";
      $zip_new->addFile($new_dir_fake."/".$zips.'_MERGE.pdf', "$zip_file_name"); // Добавляем пдф файлы
  }
// цепляем С файл
  $zip_new->addFile($path_excel_docs."/".$file_name_1c_list, "$file_name_1c_list"); // добавляем для НОВЫЙ 1С файл /// *****************
// цепляем лист подора
  if (isset($file_name_list_podbora)){ 
    $zip_new->addFile($path_excel_docs."/".$file_name_list_podbora, "$file_name_list_podbora"); // добавляем для НОВЫЙ 1С файл /// *****************
  }
  $zip_new->close();  
  
  $file_size_fake= round((filesize($link_merge_file_name_fake)/1000000),2)." Mb";
  

 
 

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
            <h2>скачать архив со стикерамии c Нормальными размерами</h2>
            <p><a href="$link_merge_file_name">Cкачать архив со стикерамии (Оригинальные габариты)</a></p>
            <p class="bottom">$file_size</p>
      </div>
<!-- Ссылка на этикетки с Фэковыми габаритами -->
   <div class="block block-2">
       <h2>скачать архив со стикерамии c Фейковыми размерами</h2>
      <p><a href="$link_merge_file_name_fake">Скачать архив со стикерамии (Фейковые габариты)</a></p>
      <p class="bottom">$file_size_fake</p>

  </div>

    <div class="block block-3">
    <h2>скачать архив со стикерамии c Оригинальный вариант размерами</h2>
    <p><a  href="$non_merge_file_link">скачать архив со стикерамии листом подбора(старая версия)</a></p>

    </div>
</div>

</body>
  
  
HTML;


