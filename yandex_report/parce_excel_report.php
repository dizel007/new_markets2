<?php

require_once "../connect_db.php";


require_once '../libs/PHPExcel-1.8/Classes/PHPExcel.php';
require_once '../libs/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';
require_once '../libs/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

require_once "../pdo_functions/pdo_functions.php";
require_once "../mp_functions/report_excel_file.php";
require_once "../mp_functions/yandex_api_functions.php";

require_once "razbor_parts/all_tranzaction.php";
require_once "razbor_parts/vozvrati.php";
require_once "razbor_parts/uderzhania.php";


$ya_token =  get_token_yam($pdo);
$campaignId =  get_id_company_yam($pdo);
$yandex_anmaks_fbs = 'ya_anmaks_fbs';
$ya_fbs_catalog = get_catalog_tovarov_v_mp($yandex_anmaks_fbs, $pdo, 'active'); // получаем yandex каталог
$nomenclatura = select_active_nomenklaturu($pdo);




// Перебираем загруженные файлы
foreach ($_FILES as $key => $files) {
  $file_name =  basename($_FILES[$key]['name']);
  if (file_exists('files/' . $file_name)) {
    unlink('files/' . $file_name); // удаляем файл с таким же названием
  }
  $uploadfile = "files/" . basename($_FILES[$key]['name']);
  move_uploaded_file($_FILES[$key]['tmp_name'], $uploadfile);
  $arr_files[] = $file_name; // массив с названием файлов
}


$xls = PHPExcel_IOFactory::load("files/" . $arr_files[0]);
$xls->setActiveSheetIndex(1);
$sheet = $xls->getActiveSheet();

$empty_10 = 0; //переменнная которая считает количество пустых ячеек подряд
// ищем количество массивов для обработки
$j = 1;

// Находим начальные строки все массивов из эеселя
do {
  $temp = $sheet->getCellByColumnAndRow(0, $j)->getValue();
  if ($temp == 'Информация о бизнесе') {

    $type_array[$sheet->getCellByColumnAndRow(7, $j)->getValue()] = $j + 2;
  }

  if ($temp == '') {
    $empty_10++;
  } else {
    $empty_10 = 0;
  }
  $j++; // добавляем смещение строки
} while ($empty_10 < 3);



// начинаем разбирать все транзацкии 
$all_tranzactions = razbor_all_tranzactions_yandex ($sheet, $type_array);


foreach ($all_tranzactions as $key_orders=> $orders_items) {
    foreach ($orders_items as $key_article=> $items){
        // поартикульно складываем суммы
        $arr_article_data[$key_article]['сумма_операций']  = @ $arr_article_data[$key_article]['сумма_операций'] + $items['сумма_операций'];
        if (isset ($items['Кол-во товаров'])) {
        $arr_article_data[$key_article]['Кол-во товаров']  = @ $arr_article_data[$key_article]['Кол-во товаров'] + $items['Кол-во товаров'];
        }
        // общая сумма товаров
       
}    
}


// находим сумму всех строк без артикула
$premii = 0;
foreach ($arr_article_data as $key => &$iitem) {
  if ($key == '') {
      $premii += $iitem['сумма_операций'];
      unset($arr_article_data[$key]);
  }
}

// echo "<pre>";
// print_r($premii);

$all_vozvrati = razbor_all_vozvrati_yandex ($sheet, $type_array);



// перебираем массив продаж и вычитаем возвраты оттуда
$summa_all_orders = 0;
foreach ($arr_article_data as $key => &$item_sell) {
  foreach ($all_vozvrati as $key_return => $item_return) {

    if ($key == $key_return) {
      $item_sell['сумма_операций'] = $item_sell['сумма_операций'] + $item_return['сумма_операций'];
      $item_sell['Кол-во товаров'] = $item_sell['Кол-во товаров'] - $item_return['кол-во возвратов'];
    }
  }
  $summa_all_orders += $item_sell['сумма_операций'];
}





// Добавим к каждому артикулу процент от полной суммы 
foreach ($arr_article_data as &$article) {
$one_procent_ot_summi = round($summa_all_orders/100,5);
$procent_raspredelenia = round ($article['сумма_операций']/$one_procent_ot_summi,5);
$article['процент_от_суммы'] = $procent_raspredelenia;

}

// echo "<pre>";
// print_r($arr_article_data);
// die();


$summa_vseh_vozvratov = $all_vozvrati['summa_vseh_vozvratov'];

// теперь приложим сумму возвратов для каждого артикула 
// foreach ($arr_article_data as &$article) {
//   $article['сумма_удержания_возвраты'] = round(($summa_vseh_vozvratov/100 * $article['процент_от_суммы']  ),2);
// }


$all_uderzania = razbor_all_uderzania_yandex ($sheet, $type_array);

// echo "<pre>";
// print_r($arr_premii);
// die();



$summa_vseh_uderzanii = $all_uderzania['summa_vseh_uderzanii'];

// теперь приложим сумму удержания_прочие для каждого артикула 
foreach ($arr_article_data as &$article) {
  $article['сумма_удержания_прочие'] = round(($summa_vseh_uderzanii/100 * $article['процент_от_суммы']  ),2);
}

// теперь приложим сумму премирования для каждого артикула 
foreach ($arr_article_data as &$article) {
  $article['сумма_премирования'] = round(($premii/100 * $article['процент_от_суммы']  ),2);
}




// теперь посчитаем сумму после всех вычетов 
$summa_posle_vichitov = 0;
foreach ($arr_article_data as &$article) {
  $article['сумма_за_артикул_после_всех_вычитов'] = $article['сумма_операций']  + $article['сумма_удержания_прочие'] + $article['сумма_премирования'];
  
  isset($article['Кол-во товаров'])?$price_for_one_item = round($article['сумма_за_артикул_после_всех_вычитов']/$article['Кол-во товаров'],2):$price_for_one_item = 0;
 
  $article['получили_за_штуку'] = $price_for_one_item;

}

// цепляем номер вывода артикула в списке 
foreach ($arr_article_data as $key=>&$item_ff) {
foreach ($nomenclatura as $nomen) {
  if (mb_strtolower($nomen['main_article_1c']) == mb_strtolower($key)) {
    $item_ff['number_in_spisok'] = $nomen['number_in_spisok'];
  }

}


}


// echo "<pre>";
// print_r($arr_article_data);


// Сортировка по возрастанию с сохранением ключей
uasort($arr_article_data, function($a, $b) {
    return $a['number_in_spisok'] <=> $b['number_in_spisok'];
});


// print_r($arr_article_data);


// echo "<br> сумма = ".$summa_all_orders;
// die();

require_once "ya_print_report_table.php";
