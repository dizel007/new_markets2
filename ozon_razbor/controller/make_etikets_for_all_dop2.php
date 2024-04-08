<?php
require_once '../include_funcs.php';
require_once 'make_1c_file.php';
/*
Подключаем PHPExcel
*/
require_once '../../libs/PHPExcel-1.8/Classes/PHPExcel.php';
require_once '../../libs/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';
require_once '../../libs/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

if (isset($_GET['date_query_ozon'])) {
    $date_query_ozon = $_GET['date_query_ozon'];
    $new_path = '../reports/'.$date_query_ozon."/";
    make_new_dir_z($new_path,0); // создаем папку с датой
    $path_etiketki = $new_path.'etiketki';
    make_new_dir_z($path_etiketki,0); // создаем папку с датой
    $path_excel_docs = $new_path.'excel_docs';
    make_new_dir_z($path_excel_docs,0); // создаем папку с датой
    $path_zip_archives = $new_path.'zip_archives';
    make_new_dir_z($path_zip_archives,0); // создаем папку с датой

    echo  $date_query_ozon;
} 
  else {echo "<br> Дата сортировки не выбрана"; die('DIE*DIE*DIE');}

  $nomer_zakaz = $_GET['nomer_zakaz'];
// Получаем списрк заказов готовых к отправлению (Берем только на выбранное число)

$res = get_all_waiting_posts_for_need_date($token, $client_id, $date_query_ozon, "awaiting_deliver",0);


// echo  "<pre>";
// print_r($res);
// die('ffffffffffffffffffffffffffff');


/******************************************************************************************************************
******  формирование 1С файла 
/******************************************************************************************************************/
   $xls = new PHPExcel();
   $file_name_1c_list = make_1c_file($res, $date_query_ozon, $nomer_zakaz, $path_excel_docs, $xls);

/******************************************************************************************************************
****** формирование листа подбора (из обработанного массива)
/******************************************************************************************************************/

$temp_path = $path_excel_docs."/json_list_podbora.json";
if (file_exists($temp_path)) {
$json_arr_obmen = file_get_contents($temp_path);   
$array_oben = json_decode($json_arr_obmen, true);
echo "<br> Массив для создания Листа подбора  <br>";
echo "<pre>";
$xls2 = new PHPExcel();
$file_name_list_podbora = make_list_podbora_new ($array_oben, $date_query_ozon, $nomer_zakaz, $path_excel_docs, $xls2);
} else { 
  echo "Нет файла для формирования листа подбора";
  // unset($file_name_list_podbora);
}




// формируем массиы где заказы разбиты поартикульно 
foreach ($res['result']['postings'] as $posts_z) {
  $article = $posts_z['products'][0]['offer_id'];
  $arr_article_tovar[$article][] = $posts_z;
}


// перебираем поартикульный массив и формируем строку со списком заказов (поартикульно)
foreach ($arr_article_tovar as $key=> $posts) {
  $string_etiket = '';
  foreach ($posts as $post) {
  $string_etiket =@$string_etiket."\"".$post['posting_number']."\", ";
  }

  if (!isset($string_etiket)) {
    echo "НЕТ ДАННЫХ ДЛЯ Вывода";
    die('<br> ПОмерли без этикеток');
  }
$string_etiket = substr($string_etiket, 0, -2); // удаляем последний разделитель из строки с заказами 

echo "<br>Разбираем артикул : $key<br>";
echo "Строка заказов артикула: $string_etiket<br>";

/*****************************************************************************************************************
 ******  Формируем PDF файлы поартикульно
 ******************************************************************************************************************/
$good_key = make_rigth_file_name($key); // убираем все запрещенные символы в наименовании файла

$pdf_file_name = $nomer_zakaz." (".$good_key.") ".count($posts)."шт(dop)";
get_all_barcodes_for_all_sending ($token, $client_id,  $string_etiket, $pdf_file_name, $path_etiketki);
$Arr_filenames_for_zip[] = $pdf_file_name.".pdf"; // массив в названиями пдф фаилами (чтобы а ЗИП архив их добавить)
}


/*****************************************************************************************************************
 ******  Формируем ZIP архив с этикетаксм и 1С файлом и листом подбора
 ******************************************************************************************************************/
  $zip_new = new ZipArchive();
  $file_zip_name = "OZON_№".$nomer_zakaz." от ".date("Y-M-d")."(dop).zip"; // Имя файлы для ЗИП архива
  $zip_new->open($path_zip_archives."/".$file_zip_name, ZipArchive::CREATE|ZipArchive::OVERWRITE);
  foreach ($Arr_filenames_for_zip as $zips) {
  $zip_new->addFile($path_etiketki."/".$zips, "$zips"); // Добавляем пдф файлы
}
  $zip_new->addFile($path_excel_docs."/".$file_name_1c_list, "$file_name_1c_list"); // добавляем для НОВЫЙ 1С файл /// *****************
if (isset($file_name_list_podbora)){ 
  $zip_new->addFile($path_excel_docs."/".$file_name_list_podbora, "$file_name_list_podbora"); // добавляем для НОВЫЙ 1С файл /// *****************
}
  $zip_new->close();  

 $link_path_zip2 = $path_zip_archives."/".$file_zip_name; //  ссылка чтобы скачать архив

echo <<<HTML
<br><br>
<a href="$link_path_zip2"> скачать архив со стикерамии листом подбора</a>
<br><br>
HTML;
die ('<br> Дошли до финиша');
