<?php

require_once "../connect_db.php";


require_once '../libs/PHPExcel-1.8/Classes/PHPExcel.php';
require_once '../libs/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';
require_once '../libs/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';

require_once "../pdo_functions/pdo_functions.php";
require_once "../mp_functions/report_excel_file.php";
require_once "../mp_functions/yandex_api_functions.php";



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
$xls_2 = PHPExcel_IOFactory::load("files/" . $arr_files[1]);


// $f = parce_yandex_excel_report($xls);
$array_first_excel_razbor = parce_yandex_excel_report($xls);


// die('STOP STOP');
$array_second_ex_razbor = parce_yandex_excel_report($xls_2);

// довавляем рекламные суммы к нашему разбору
foreach ($array_first_excel_razbor['data'] as $key => &$item) {
  foreach ($array_second_ex_razbor['data'] as $key_2 => $item_2) {
      if ($key == $key_2) {
        $item['sum_nasha_viplata'] = $item['sum_nasha_viplata'] + $item_2['sum_nasha_viplata'];
        $item['sku'] = $key;
        $item['sum_k_pererchisleniu_za_shtuku'] = round($item['sum_nasha_viplata'] / $item['count_sell'] , 2); // средняя цена за штуку для покупателя
      }
  }
  $summa = @$summa + $item['sum_nasha_viplata'];
  $sum_cinut = @$sum_cinut + $item['count_sell'];
}



// echo "<pre>";
// print_r($array_first_excel_razbor);
// echo "</pre>";
// die();



// Формируем массив Яндекс каталог
foreach ($nomenclatura as $nomen) {
  foreach ($ya_fbs_catalog as $ya_items) {
    if (mb_strtolower($nomen['main_article_1c']) == mb_strtolower($ya_items['main_article'])) {
      $arr_items_yandex[$nomen['main_article_1c']] = $nomen;
      $arr_items_yandex[$nomen['main_article_1c']]['sku'] = $ya_items['sku'];
    }
  }
}


// довавляем минимальную и хорошую цены  + габариты 
foreach ($arr_items_yandex as $catalog) {
  foreach ($array_first_excel_razbor['data'] as $key_art => &$item_2) {
    if (mb_strtolower($catalog['sku']) == mb_strtolower($item_2['sku'])) {
      $item_2['number_in_spisok'] = $catalog['number_in_spisok'];
      $item_2['main_article_1c'] = $catalog['main_article_1c'];
      $item_2['main_price'] = $catalog['main_price'];
      $item_2['sebes_str_item'] = $catalog['min_price'];
      // габариты
      $item_2['gabariti'] = $catalog['dlina']." x ". $catalog['shirina']." x ". $catalog['visota'];
     }
   }
 }

// распределяем коммиссию ровным слоем
$kom_procent = $array_first_excel_razbor['komissii'] / 100;
$proc_rapr_vsey_summi =  $summa / 100;
  foreach ($array_first_excel_razbor['data'] as $key => &$item) {
    
      $item['proc_raspred'] = $item['sum_nasha_viplata'] / $proc_rapr_vsey_summi; // процент распрдеелние стоимости от обещй суммы
      $item['raspred_komissii'] = $item['proc_raspred'] * $kom_procent; // сумма  распрдеелния комиссии
      $item['sum_k_pererchisleniu'] = ($item['sum_nasha_viplata'] + $item['raspred_komissii']); // На счет за все
      $item['price_for_shtuka'] = $item['sum_k_pererchisleniu'] / $item['count_sell'];; // На счет за 1 штуку
}


// сортируем товар  по номерному порядку  (Почему то ключ массива портится)

echo " COUNT  =  $sum_cinut<br>";
echo " POSTUPLENIA =  $summa<br>";
echo " KOMISSII =  " . $array_first_excel_razbor['komissii'] . "<br>";
$na_schet = $summa + $array_first_excel_razbor['komissii'];
echo " NA_SCHET =  " . $na_schet . "<br>";
echo " 1% от суммы  =  " . $proc_rapr_vsey_summi . "<br>";
echo " 1% от комиссии  =  " . $kom_procent . "<br>";



echo "<pre>";
$arr_new__first_excel_razbor  = $array_first_excel_razbor['data'];
// сортируем товар  по номерному порядку  (Почему то ключ массива портится)
array_multisort(array_column($arr_new__first_excel_razbor, 'number_in_spisok'), SORT_ASC, $arr_new__first_excel_razbor);
// восстанавливаем ключи
unset ($item);
foreach ($arr_new__first_excel_razbor as $item ) {

  $item['delta_v_stoimosti'] = $item['price_for_shtuka'] - $item['sebes_str_item'];  // дельма от себестотимости
  $item['delta_good_and_sell_prices'] = $item['price_for_shtuka'] - $item['main_price'];  // дельма от себестотимости

// print_r($item);

  $arr_with_key[$item['main_article_1c']] = $item;

}



echo "<pre>";
// print_r($arr_with_key);




require_once "ya_print_report_table.php";
/*****
 * 
 * 
 * 
 * 
 * 
 */


 
 function obj_to_arr_my($object, $i) {
  echo "*** STARt_FUNCTION *******ppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppp******<<br>";
  
  $array =  (array) $object ;
  
  foreach ($array as $key=>$item) {
  echo "<br>** НОМЕР ЭЛЕМЕНТА В МАССИВЕ[$i] *********{$key}*************************************************************************<br>";
  echo "Количество элементов в массиве =".$count_array = count($array)."<br>";
     // если МАОБ то смотрим сколько элементов в МАОБ
   if (is_array($item)) {
    echo "*** ARRAY ************************/////////////////////********<<br>";
    $count_item = count($item)."<br>";
    echo "******** COUNT_ITEM = $count_item *****<br>";
    // print_r($item);
  
    obj_to_arr_my($item,$i);
  
   }elseif(is_object($item)){

    echo "*** OBJEKT ************************/////////////////////********<<br>";

    obj_to_arr_my($item,$i);

    ///////// Если не объект и не массив то выводим что есть 
   } else {
   echo "<br> **не МАОБ <b><-$item-></b> КОНЕЦ *<br>";
   $message = $item.PHP_EOL;
   file_put_contents("temp/$i.txt", $message, FILE_APPEND);
   }
 

    // print_r($item);
    // file_put_contents("1.txt", print_r($arr_arr[$key],true) , FILE_APPEND);

   
   $i++;
   $count_item = 'z';
   }
 
//  return $arr_arr_j;
 }



function parce_yandex_excel_report($xls)
{
  $xls->setActiveSheetIndex(1);
  $sheet = $xls->getActiveSheet();


//////////////////////////////////////////////////////////////////////////////////////////
// $i= 2 ;
// $cell = $sheet->getCell("A{$i}");
// // Check if cell is merged
// foreach ($sheet->getMergeCells() as $cells) {
//     if ($cell->isInRange($cells)) {
//         echo 'Cell is merged!';
//         break;
//     }
// }


// echo "<pre>";
// $arr_1 = (array) $object ;
// $arr_2 = obj_to_arr_my($object,0) ;
// $arr_3 = obj_to_arr_my($arr_2) ;

// print_r($object);



//  $item_2 =  (array) $item_1 ;
//  print_r($item_2); 

// die('j');
// $sheet->unmergeCellsByColumnAndRow(0,1,6,1);





  $empty_10 = 0; //переменнная которая считает количество пустых ячеек подряд
  // ищем количество массивов для обработки
  $j = 1;
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

  foreach ($type_array as $key => $string_number) {
    // перебераем прямые продажи

    // продажи 
    if ($key == 'Информация о начислениях') {
      $j = $string_number;
      $str_number = 0;
      do {

        $next_string =  $sheet->getCellByColumnAndRow(1, $j)->getValue();
        if ($next_string == '') {
          break;
        }
        $arr_nachilslenia[$str_number]['article'] = mb_strtolower($sheet->getCellByColumnAndRow(11, $j)->getValue());
        $arr_nachilslenia[$str_number]['count_sell'] = $sheet->getCellByColumnAndRow(13, $j)->getValue();
        $arr_nachilslenia[$str_number]['sum_nasha_viplata'] = $sheet->getCellByColumnAndRow(14, $j)->getValue();

        $j++;
        $str_number++;
      } while ($next_string <> '');
    }

    // возвраты 
    if ($key == 'Информация о возвратах и компенсациях покупателям') {
      $j = $string_number;
      $str_number = 0;
      do {

        $next_string =  $sheet->getCellByColumnAndRow(1, $j)->getValue();
        if ($next_string == '') {
          break;
        }
        $arr_vozvrati[$str_number]['article'] = mb_strtolower($sheet->getCellByColumnAndRow(11, $j)->getValue());
        $arr_vozvrati[$str_number]['count_sell'] = $sheet->getCellByColumnAndRow(13, $j)->getValue();
        $arr_vozvrati[$str_number]['sum_nasha_viplata'] = $sheet->getCellByColumnAndRow(14, $j)->getValue();

        $j++;
        $str_number++;
      } while ($next_string <> '');
    }
    // Комиссия ЯМ
    if ($key == 'Информация об удержаниях') {
      $j = $string_number;
      $str_number = 0;
      do {

        $next_string =  $sheet->getCellByColumnAndRow(1, $j)->getValue();
        if ($next_string == '') {
          break;
        }
        // $arr_komissia[$str_number]['article'] = $sheet->getCellByColumnAndRow(10, $j)->getValue();
        // $arr_komissia[$str_number]['count_sell'] = $sheet->getCellByColumnAndRow(12, $j)->getValue();
        $arr_komissia[$str_number]['sum_nasha_viplata'] = $sheet->getCellByColumnAndRow(14, $j)->getValue();
        $summa_komisii = @$summa_komisii + $sheet->getCellByColumnAndRow(14, $j)->getValue();

        $j++;
        $str_number++;
      } while ($next_string <> '');
    }
  }








  // все начисления
  // формируем массив 
  $summa = 0;
  foreach ($arr_nachilslenia as $item) {

    $sum_array[$item['article']]['count_sell'] = @$sum_array[$item['article']]['count_sell'] + $item['count_sell'];
    $sum_array[$item['article']]['sum_nasha_viplata'] = @$sum_array[$item['article']]['sum_nasha_viplata'] + $item['sum_nasha_viplata'];
    // $sum_array[$item['article']]['price_one_shtuka'] = round($sum_array[$item['article']]['summa'] / $sum_array[$item['article']]['count'] ,2);
    $summa = @$summa + $item['sum_nasha_viplata'];
  }


  // все вовзарты 
  $summa_vozvratov = 0;
  if (isset($arr_vozvrati)){
    foreach ($arr_vozvrati as $item) {
      $sum_array[$item['article']]['count_sell'] = @$sum_array[$item['article']]['count_sell'] - $item['count_sell'];
      $sum_array[$item['article']]['sum_nasha_viplata'] = @$sum_array[$item['article']]['sum_nasha_viplata'] + $item['sum_nasha_viplata'];
      $summa_vozvratov = $summa_vozvratov + $item['sum_nasha_viplata'];
    }
  }

  $all_info['data'] = $sum_array;
  (isset($summa_komisii)) ? $all_info['komissii'] =  $summa_komisii : $all_info['komissii'] =  0;
  
  
  


  // echo "<pre>";
  // print_r($all_info);
  // echo "</pre>";
  // die();


  
  return $all_info;



}
