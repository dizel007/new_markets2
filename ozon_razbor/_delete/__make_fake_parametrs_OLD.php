<?php
require_once "../connect_db.php";
require_once "../vendor/autoload.php";
use setasign\Fpdi\Fpdi;

// echo "<pre>";
$ozon_shop = 'ozon_ip_zel';




// print_r($ozon_catalog);


// // Пусть у JSON файлу с массивов имен файлов этикеток
if (isset($_GET['filepath']) ) {
    $filepath = $_GET['filepath'];
    } else {
        echo "Не получили ссылку на файл";
        die('');
    }
    
//  $filepath ='../../!all_razbor/ozon/2025-02-19/0376/etiketki/';
 $filepath = "../".str_replace("../", "", $filepath);


// Получаем данные с JSON файла array_dop ....
$arr_data_order = json_decode(file_get_contents($filepath."array_dop_info.json"), true);



$number_order =           $arr_data_order['number_order'];
$path_excel_docs =        $arr_data_order['path_excel_docs'];
$path_excel_docs = "../".str_replace("../", "", $path_excel_docs);
$file_name_1c_list =      $arr_data_order['file_name_1c_list'];
$file_non_merge_archive = $arr_data_order['file_non_merge_archive'];
$file_non_merge_archive = "../".str_replace("../", "", $file_non_merge_archive);

// $ozon_shop = $arr_data_order['ozon_shop'];

$ozon_shop = 'ozon_ip_zel';

// Получаем каталог товаров для данного магазина
$ozon_catalog    = get_catalog_tovarov_v_mp($ozon_shop, $pdo, 'active'); // получаем озон каталог


// берем названия файлов (названия не МЕРДЖА)
 $arr_name_articles = json_decode(file_get_contents($filepath."art_etik.json"), true);

// Создаем новую директорую куда будем складывать соедененные файлы
$new_dir_fake_gabarit = $filepath."merge_pdf_fake";
if (is_dir($new_dir_fake_gabarit) !== true) {
    mkdir($new_dir_fake_gabarit, 0777);
}

// габариты этикеткии
$arr =[58,80];
// переделаем массив с именами файлов и соединяем их с файлом где написан артикул
foreach ($arr_name_articles as $key=>$filename) {
    $image_file_name = '_none.jpg';
/// Выбираем из названия файла артикул
    preg_match('/\((.*?)\)/', $filename, $matches); // 
    if (!empty($matches[1])) {
        $temp_article = $matches[1]; // Выведет: нужная часть
    } else {
        $temp_article = 'FAKEFAKE'; // Выведет: нужная часть
    }
    
// Ищем файл с фэйковыми размерами 
foreach ($ozon_catalog as $items) {
    if (mb_strtolower($items['main_article']) == mb_strtolower($temp_article)) {
        $image_file_name = mb_strtolower($temp_article).".jpg";
     
            // смотрим есть ли файл с картинкой для этого артикула 
            if (file_exists('images/'.$image_file_name)) {
                break 1;    
            } else {
                $image_file_name = '_none.jpg'; 
            }
            
    } else {
        $image_file_name = '_none.jpg'; 
    }
}




    $pdf = new Fpdi();
    // получаем количетво страниц в ПФДке
    $page_count = $pdf->setSourceFile( $filepath.'merge_pdf/'.$filename.'_MERGE.pdf'); 
   // перебираем все страницы ПДФ файла
    for ($i=1; $i<=$page_count; $i++) {
           make_pdf_fake_sizes($pdf, $i, $page_count, $image_file_name);
        }
 // промежуточный вариант сохраняем всегда в этот файл

   $pdf->Output( $filepath.'temp.pdf', 'F'); 
   $pdf2 = new Fpdi();
   $pdf2->setSourceFile( $filepath.'temp.pdf'); 
 // пересоздаем этикетки с нормальными размерами 
   for ($i=1; $i<=$page_count; $i++) {
          make_58x80_pdf_file($pdf2, $i  );     
      }

// создаем название файла с зависимости от того, если ли файл для подмены габаритов
    if ($image_file_name != '_none.jpg') {
    $new_file_name = $filename.'_Fake_MERGE.pdf';
    $fake_merge_path = $new_dir_fake_gabarit."/".$new_file_name;
    } else {
        $new_file_name = $filename.'_MERGE.pdf';
        $fake_merge_path = $new_dir_fake_gabarit."/".$new_file_name;
    }
    
    $pdf2->Output($fake_merge_path, 'F'); 
// Создаем массив с названиями и путями файлов пдфф
    $array_pdf_files[] = $new_file_name;
}

unlink($filepath.'temp.pdf');

/*****************************************************************************************************************
 ******  Формируем ZIP архив с этикетаксм и 1С файлом и листом подбора
 ******************************************************************************************************************/
$zip_new = new ZipArchive();
// название файла с архивом
$merge_file_name = "etikets_№".$number_order."_от_".date("Y-M-d")."_fake_MERGE.zip";

$zip_new->open($new_dir_fake_gabarit."/". $merge_file_name , ZipArchive::CREATE|ZipArchive::OVERWRITE);

foreach ($array_pdf_files as $zips) {
$zip_new->addFile($new_dir_fake_gabarit."/".$zips, "$zips"); // Добавляем пдф файлы
}
$zip_new->addFile($path_excel_docs."/".$file_name_1c_list, "$file_name_1c_list"); // добавляем для НОВЫЙ 1С файл /// *****************
$zip_new->close();  


$link_path_zip2 = $new_dir_fake_gabarit."/".$merge_file_name; //  ссылка чтобы скачать архив

$file_size= round((filesize( $link_path_zip2)/1000000),2)." Mb";




/**
 *  ОТРИСОВЫВАЕ СТРАНИЦУ ДЛЯ СКАЧИВАНИЯ 
 */

// ссылка для перехода к созданию этикеток с фэйковыми размероами
  echo <<<HTML
  <head>
  <link rel="stylesheet" href="css/link_button.css">
  <script type="text/javascript" src="js/download.js"></script>
  <script type="text/javascript" src="js/jquery.min.js"></script>
 
</head>
<body>
  <div class="button">
  <a href="$link_path_zip2">скачать архив с ФЕЙКОВЫМИ ГАБАРИТНЫМИ РАЗМЕРАМИ (MERGE)</a>
  <p class="top">$merge_file_name</p>
  <p class="bottom">$file_size</p>
  
</div>
<div style="text-align:center">
 <a  href="$file_non_merge_archive">скачать архив со стикерамии листом подбора(старая версия)</a>
</div>

</div>


</body>
  
  
HTML;


die();


// Сохранение отредактированного PDF

function make_pdf_fake_sizes($pdf, $i, $page_count, $image_file_name) {
    $arr =[58,80];
    $pageId = $pdf->importPage($i);
    $pdf->AddPage('P', $arr); 
    $pdf->useTemplate($pageId, 0, 0, 58,40); 


// $pdf->SetFillColor(255,255,255);
 // на последней страницу не устанавливаем габариты
        
if ($i < $page_count) { 
    if ($image_file_name != "_none.jpg") { 
        $pdf->Image('images/'.$image_file_name, $x = 1, $y= 22.4,  $width = 55, $hight = 3, '' );
    }
    
} else {
    if ($image_file_name != "_none.jpg") { 
     $pdf->Image('images/_last_fake.jpg', $x = 1, $y= 24.4,  $width = 55, $hight = 3, '' );
    }
}
}

function make_58x80_pdf_file($pdf2, $i) {
   
    $pageId = $pdf2->importPage($i); 
    $arr =[58,40];
    $pdf2->AddPage('L', $arr); 
    $pdf2->useTemplate($pageId, 0, 0, 58,80); 
           


}
