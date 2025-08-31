<?php

require_once '../../connect_db.php';
require_once '../include_funcs.php';
require_once 'make_1c_file.php';


require_once '../../pdo_functions/pdo_functions.php'; // подключаем функцию записи в Таблицу действия пользователя




// $ozon_shop = $_GET['ozon_shop'];
// if ($_GET['ozon_shop'] == 'ozon_anmaks') {
//        $token_ozon = $token_ozon;
//        $client_id_ozon = $client_id_ozon;
 
//    }
       
// elseif ($_GET['ozon_shop'] == 'ozon_ip_zel') {
//     //    echo "<br>Выбран магазин ИП Зел<br>";
//        $token_ozon =  $token_ozon_ip;
//        $client_id_ozon =  $client_id_ozon_ip;
//  } else {
//        die ('МАГАЗИН НЕ ВЫБРАН');
//  }




// // Формируем дополнительные переменные после разделения файла

//  $path_excel_docs = $_GET['path_excel_docs'];
//  $number_order = $_GET['number_order'];
//  $path_etiketki = $_GET['path_etiketki'];
//  $now_date_razbora = date('Y-m-d');
//  $date_query_ozon = date('Y-m-d', strtotime($now_date_razbora . ' -5 day'));
//  $dop_days_query = 10;
//  $file_name_OTLADKA = $path_excel_docs."/otladka.txt";


 

// // сохраняем JSON всех заказов 
// $temp_path_all_order = $path_excel_docs."/json_all_order.json";
// $res = json_decode(file_get_contents($temp_path_all_order),true);






// echo "<pre>";
// print_r($res);
// die('cc');








// Запись в таблицу Действия пользователя
insert_in_table_user_action($pdo, $userdata['user_login'] , "RAZBOR_OZON Order№($number_order)");


sleep(4);

// Получаем списoк заказов готовых к отправлению ()
// ***********************************************************************************************************************************
$res_repeat = get_all_waiting_posts_for_need_date($token_ozon, $client_id_ozon, $date_query_ozon, "awaiting_deliver", $dop_days_query);
// сохраняем JSON всех заказов 
$string_json_all_order = json_encode($res_repeat, JSON_UNESCAPED_UNICODE);
$temp_path_all_order = $path_excel_docs."/json_all_repeat_order.json";
file_put_contents($temp_path_all_order, $string_json_all_order);

// ***********************************************************************************************************************************

$arr_reapeat_numbers[]=''; // массив куда добавляем номера записанных в новый массив заказов, чтобы избежать дублирования заказов
/// выбираем из всей пачки только те заказы, которые мы запросили ранее
foreach ($res['result']['postings'] as $old_order) {
  foreach ($res_repeat['result']['postings'] as $new_order) {
    $priz_reapeat = 0;
      if ($old_order['order_number'] == $new_order['order_number']) {
        // проверяем нет ли уже этого отправлния яв новом массиве
        foreach ($arr_reapeat_numbers as $post_number) {
          if ($post_number == $new_order['posting_number']) {
            $priz_reapeat = 1;
          }
        }
        if ($priz_reapeat == 0) {
          // Формируем новый массив, где все разбитые по грузоотправлениям заказы из нового запроса
          $new_res[]=$new_order;
          $arr_reapeat_numbers[] = $new_order['posting_number'];
        }

        
      }
  }
}

// формируем массив для 1с файла
$array_for_1C =  make_array_for_1c_file($new_res);
// формируем массив для листа подборf
$array_for_list_podbora = make_array_for_list_podbora($new_res);

sleep(2);
/******************************************************************************************************************
******  формирование 1С файла 
/******************************************************************************************************************/
  $xls = new PHPExcel();
  $file_name_1c_list = make_1c_file($array_for_1C, $date_query_ozon, $number_order, $path_excel_docs, $xls);
/******************************************************************************************************************
****** формирование листа подбора (из обработанного массива)
/******************************************************************************************************************/
$xls2 = new PHPExcel();
$file_name_list_podbora = make_list_podbora_new ($array_for_list_podbora, $date_query_ozon, $number_order, $path_excel_docs, $xls2);

// формируем массивы где заказы разбиты поартикульно 
foreach ($new_res as $posts_z) {
  $article = $posts_z['products'][0]['offer_id'];
  $arr_article_tovar[$article][] = $posts_z;
}


/// НАчинаем долгие разбор 
$realTime = microtime(true);
$text_otladka = $realTime." "."Перешли в make_etiketki_for... "."\n";
file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);
// echo "Время начала скрипта : {$startTime} <br>"; ; 

set_time_limit(0); // неограниченное время ожидание ответа от сервера
// ob_start(); // включить буфер
if (!isset($startTime)) {
  $startTime = microtime(true);
}
// перебираем поартикульный массив и формируем строку со списком заказов (поартикульно)
foreach ($arr_article_tovar as $key=> $posts) {
  
  $count_items_in_order = count($posts);

  $realTime = microtime(true);
  $deltaTime = $realTime - $startTime;
  $text_otladka = $deltaTime." "."(Начало)_Создаем_строки_с_номерами_заказов_для_каждого_артикула"."\n";
  file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);
  
  /// Фиксируем время выполенинея скрипта и смотрим сколько он длится
  // если долго длится то выводим информацию на экран, чтобы не оборвалось соедиенние с сервером
  $endTime = microtime(true);
  $rezultTime = $endTime - $startTime;
  if ($rezultTime > 90) {
     echo " Время выполнения скрипта {$rezultTime} секунд. Processing...<br>";
  }

  $string_etiket = '';
  foreach ($posts as $post) {
  $string_etiket = @$string_etiket."\"".$post['posting_number']."\", ";
  }

  if (!isset($string_etiket)) {
    echo "НЕТ ДАННЫХ ДЛЯ Вывода";
    die('<br> ПОмерли без этикеток');
  }
$string_etiket = substr($string_etiket, 0, -2); // удаляем последний разделитель из строки с заказами 

/*****************************************************************************************************************
 ******  Формируем PDF файлы поартикульно
 ******************************************************************************************************************/
$good_key = make_rigth_file_name($key); // убираем все запрещенные символы в наименовании файла

$pdf_file_name = $number_order." (".$good_key.") ".count($posts)."шт";

// Задаем трату на формирование ПДФ этикеток в зависимости от количества товаров
$wait_time_etikets = 3; // время ожидания этикеток в секундах
  if ($count_items_in_order >= 40) {
    $wait_time_etikets = 5;
  } 
  if ($count_items_in_order >= 80) {
    $wait_time_etikets = 6;
  } 
  if ($count_items_in_order >= 120) {
    $wait_time_etikets = 7;
  } 
  if ($count_items_in_order >= 140) {
    $wait_time_etikets = 8;
  } 
  if ($count_items_in_order >= 170) {
    $wait_time_etikets = 10;
  } 
  if ($count_items_in_order >= 200) {
    $wait_time_etikets = 15;
  } 

get_all_barcodes_for_all_sending ($token_ozon, $client_id_ozon,  $string_etiket, $pdf_file_name, $path_etiketki, $wait_time_etikets);
$Arr_filenames_for_zip[$good_key] = $pdf_file_name; // массив в названиями пдф фаилами (чтобы а ЗИП архив их добавить)

$arr_for_merge_pdf[$good_key]['value'] = count($posts);

$realTime = microtime(true);
$deltaTime = $realTime - $startTime;
$text_otladka = $deltaTime." "."(Конец-$good_key) ($count_items_in_order шт) ($wait_time_etikets сек)Создаем строки с номерами заказов для каждого артикула "."\n";
file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);
////////////////////////// DELETE //////////////////////////////////////////////////////////////////
// echo $text_otladka."<br>";
//////////////////////////////////////////////////////////////////////////////////////////////////////////////

}

/*****************************************************************************************************************
 ******  Формируем ZIP архив с этикетаксм и 1С файлом и листом подбора
 ******************************************************************************************************************/

// path_etiketki=../../!all_razbor/ozon/2025-08-21/444(dop)/etiketki

  // $path_zip_archives = $_GET['path_etiketki'];

  $path_zip_archives = $path_etiketki;

  $path_zip_archives = str_replace('etiketki', 'zip_archives',  $path_zip_archives);
  $zip_new = new ZipArchive();
  $zip_new->open($path_zip_archives."/"."etikets_№".$number_order."_от_".date("Y-M-d").".zip", ZipArchive::CREATE|ZipArchive::OVERWRITE);

  // echo "$path_zip_archives<br>";
  // echo "<pre>";
  // print_r($Arr_filenames_for_zip);
  // die();
  foreach ($Arr_filenames_for_zip as $zips) {

    $zip_file_name = $zips.".pdf";
  $zip_new->addFile($path_etiketki."/".$zips.".pdf", "$zip_file_name"); // Добавляем пдф файлы



}
  $zip_new->addFile($path_excel_docs."/".$file_name_1c_list, "$file_name_1c_list"); // добавляем для НОВЫЙ 1С файл /// *****************
if (isset($file_name_list_podbora)){ 
  $zip_new->addFile($path_excel_docs."/".$file_name_list_podbora, "$file_name_list_podbora"); // добавляем для НОВЫЙ 1С файл /// *****************
}
  $zip_new->close();  

  $link_path_zip2 = $path_zip_archives."/"."etikets_№".$number_order."_от_".date("Y-M-d").".zip"; //  ссылка чтобы скачать архив

 /// Формируем ПДФ файл с анименованием артикула 
// make_pdf_file($arr_for_merge_pdf, $path_etiketki , $number_order);

// Готовим информацию, чтобы сеодение файл с артикулом с файлом этикеток
file_put_contents($path_etiketki."/art_etik.json", json_encode($Arr_filenames_for_zip, JSON_UNESCAPED_UNICODE));
  $array_dop_files['number_order'] = $number_order;
  $array_dop_files['filepath'] = "$path_etiketki/";
  $array_dop_files['path_excel_docs'] = $path_excel_docs;
  $array_dop_files['file_name_1c_list'] = $file_name_1c_list;
  $array_dop_files['file_name_list_podbora'] = $file_name_list_podbora;
  $array_dop_files['file_non_merge_archive'] = $link_path_zip2;
  $array_dop_files['ozon_shop'] = $ozon_shop;

file_put_contents($path_etiketki."/array_dop_info.json", json_encode($array_dop_files, JSON_UNESCAPED_UNICODE));





/**************************************************************************************************************
 **********************************     Запись о разборе в БД     ********************************************
 ******************************************************************************************************************/
$link_2_test = $path_etiketki."/merge_pdf/"."etikets_№".$number_order."_от_".date("Y-M-d")."_MERGE.zip";

// file_put_contents('../gg.txt', $link_2_test );

$link_2_ = str_replace('.zip','', $link_path_zip2)."_MERGE.zip";
 insert_info_in_table_razbor($pdo, $ozon_shop, $number_order, $now_date_razbora,  $link_path_zip2, $link_2_test);

/// удаляем файл АВТОСКЛАДА, который сообщает о том, что нужно обновить данные об остатках с 1С
$file_priznak_razbora = '../../autosklad/uploads/priznak_razbora_net.txt';
  if (file_exists($file_priznak_razbora)) {
unlink($file_priznak_razbora);
$message_file_priznak ="";
  }
   else {
    $message_file_priznak ="Файл признак разбора отсутствует";
  }
// die ('<br> Дошли до финиша');
/***********************
 * *
 *****************************/
$realTime = microtime(true);
$text_otladka = $realTime." "."(Закончили РАЗБОР уходим на объединение Этикеток "."\n";
file_put_contents($file_name_OTLADKA, $text_otladka, FILE_APPEND);

// header('Location: ../merge_ozon_etikets.php?filepath='."$path_etiketki/", true, 301);


// укорачиваем передаваемую информацию
$filepath = str_replace( '../../!all_razbor/ozon/', '' , $path_etiketki );
$filepath = str_replace( "/etiketki", '' , $filepath );

// echo <<<HTML
//  <br><br>
//  <a href="$link_path_zip2"> скачать архив со стикерамии листом подбора</a>
//  <br><br>
//  <!-- <a href="../dop_ozon_etikets.php?filepath=$filepath/">MERGE</a>
//  <br> -->
//  <a href="../dop_ozon_etikets.php?date_razbora=$now_date_razbora&number_order=$number_order">ОБЪЕДЕНИТЬ !!!</a>
//  <br><br>
//  HTML;


echo <<<HTML
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/merge_file.css">
    
  </head>
<body>
    <div class="container">
        <h1>Выберите действие</h1>
        <div class="button-container">
            <a href="$link_path_zip2" class="btn btn-primary">скачать архив со стикерамии листом подбора</a>
            <a href="../dop_ozon_etikets.php?date_razbora=$now_date_razbora&number_order=$number_order" class="btn btn-secondary">
                ОБЪЕДЕНИТЬ !!!</a>
        </div>
        <br><br><br>
        <div>$message_file_priznak</div>
    </div>

    <!-- Font Awesome для иконок -->
    <!-- <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script> -->
</body>
HTML;