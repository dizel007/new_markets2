<?php
$offset = "";
require_once $offset . "../connect_db.php";
require_once $offset . "../mp_functions/ozon_api_functions.php";



$arr_all_nomenklatura = select_active_nomenklaturu($pdo);
foreach ($arr_all_nomenklatura as $zzz) {
   $arr_poriadkovii_number[mb_strtolower($zzz['main_article_1c'])] = $zzz['number_in_spisok'];
}




/********************************
 * ПОЛУЧАЕМ остатки ФБО ООО
 ****************************/
$filename = 'file_ostatki_fbo_ozon_ooo.json';
 $result_array_ooo = get_data_ostatkov_fbo_ozon ($token_ozon, $client_id_ozon, $filename , 750);
 if (!isset($result_array_ooo['code'])) {
// Формируем массивы для вывода данных на экран
foreach ($result_array_ooo['items'] as $item ){
  $arr_ostatok_fbo_ooo[mb_strtolower($item['offer_id'])][$item['warehouse_name']] = $item['valid_stock_count'];
  $arr_count_in_city_ooo[mb_strtolower($item['offer_id'])] = @$arr_count_in_city_ooo[mb_strtolower($item['offer_id'])] + $item['valid_stock_count'];
}
// Привем массив артикулов в порядок (согласно порядковому нормеру)
foreach ($arr_poriadkovii_number as $key=>$z) {
  if (isset($arr_ostatok_fbo_ooo[$key])){ $arr_sort_ar_ooo[$key] = $z;}
}
// Сортировка по возрастанию с сохранением ключей
asort($arr_sort_ar_ooo);
echo "<h1> Остатики ФБО на озон ООО</h1>";
print_fbo_ostatki_table ($arr_sort_ar_ooo, $arr_ostatok_fbo_ooo, $arr_count_in_city_ooo);
 } else {
    echo "<br> Запросить данные можно будет через 120 секунд <br>";
 }

/********************************
 * ПОЛУЧАЕМ остатки ФБО ИП
 ****************************/
$filename = 'file_ostatki_fbo_ozon_ip.json';
 $result_array_ip = get_data_ostatkov_fbo_ozon ($token_ozon_ip, $client_id_ozon_ip, $filename, 750);
  if (!isset($result_array_ip['code'])) {
// Формируем массивы для вывода данных на экран
foreach ($result_array_ip['items'] as $item ){
  $arr_ostatok_fbo_ip[mb_strtolower($item['offer_id'])][$item['warehouse_name']] = $item['valid_stock_count'];
  $arr_count_in_city_ip[mb_strtolower($item['offer_id'])] = @$arr_count_in_city_ip[mb_strtolower($item['offer_id'])] + $item['valid_stock_count'];
}
// Привем массив артикулов в порядок (согласно порядковому нормеру)
foreach ($arr_poriadkovii_number as $key=>$z) {
  if (isset($arr_ostatok_fbo_ip[$key])){ $arr_sort_ar_ip[$key] = $z;}
}
// Сортировка по возрастанию с сохранением ключей
asort($arr_sort_ar_ooo);
echo "<h1> Остатики ФБО на озон ИП</h1>";
print_fbo_ostatki_table ($arr_sort_ar_ip, $arr_ostatok_fbo_ip, $arr_count_in_city_ip);
} else {
    echo "<br> Запросить данные можно будет через 120 секунд <br>";
 }



/************************************************************************
 * Функуия вывод на экран таблицу с остаткаими по ФБО
 **********************************************************************/
function print_fbo_ostatki_table ($arr_sort_ar_ooo, $arr_ostatok_fbo_ooo, $arr_count_in_city) {
    echo '<link rel="stylesheet" href="css/fbo_ostatki.css">';
echo "<table class=\"sell_mp_table\">";

echo "<thead>";
echo "<tr>";
echo "<th class=\"red_windows\">Артикул</th>"; 
echo "<th>ИТОГО</th>"; 
echo "<th>город</th>"; 
echo "<th >Количество</th>"; 

echo "</tr>";

echo "</thead>";

foreach ($arr_sort_ar_ooo as $atricle=>$z) {
// количество складов где хранится товар
    $string_count = count($arr_ostatok_fbo_ooo[$atricle]);
 echo "<tr>";
      echo "<td rowspan = \"$string_count\">$atricle</td>";
      echo "<td rowspan = \"$string_count\">$arr_count_in_city[$atricle]</td>";
    foreach ($arr_ostatok_fbo_ooo[$atricle] as $city=>$count_items_in_city) {
      
        echo "<td> $city </td>";
        echo "<td> $count_items_in_city </td>";
        echo "</tr>";
    }
}


echo "</table>";
}



function get_data_ostatkov_fbo_ozon ($token_ozon, $client_id_ozon, $filename, $fileTime = 600){
    $filename_s = $filename;
    $filename = '../!cache/'.$filename;
    if (file_exists($filename)) {

    $creationTime = filectime($filename);
    $difftime = Time() - $creationTime;
        echo "Время создания файла $filename_s - $difftime сек<br>";

    if ($difftime >= $fileTime) { 
        echo "Время создания файла более $fileTime секунд<br>";
        // если время создания айла более 10 минут, то снова остатки берем
         unlink($filename);
         // ОЧИСТКА КЕША - это важно!
       // Многократная очистка кеша для Windows
        for ($i = 0; $i < 5; $i++) {
         clearstatcache(true, $filename);
        usleep(200000); // 100ms
    }
         echo "Удаляем старый файл<br>";
         sleep(1);
         $ozon_dop_url = "v1/analytics/manage/stocks";
         $data_for_send = '{ "limit": 1000, "offset": 0 }';
          echo "Запрашиваем Новые данные для файла $filename_s ******************<br>";
         $result_array = post_with_data_ozon($token_ozon, $client_id_ozon, $data_for_send, $ozon_dop_url ) ;
           if (!isset($result_array['code'])) {
                file_put_contents($filename, json_encode($result_array, JSON_UNESCAPED_UNICODE));
                   // Снова очищаем кеш
               for ($i = 0; $i < 5; $i++) {
                    clearstatcache(true, $filename);
                    usleep(50000); // 100ms
                }
           } else {
            echo "<br>Не получили данные для файла $filename_s <br>";
           }
     
    } else {
        // если файл у менее 10 минут то брем с него данные
        echo "Время создания файла менее $fileTime секунд<br>";
        $result_array = json_decode(file_get_contents($filename) , true);

    }
} else { 
    // если файла нет то просим данные
    echo "Файла нет<br>";
    $ozon_dop_url = "v1/analytics/manage/stocks";
    $data_for_send = '{ "limit": 1000, "offset": 0 }';
    echo "Запрашиваем Новые данные для файла $filename_s ******************<br>";
    $result_array = post_with_data_ozon($token_ozon, $client_id_ozon, $data_for_send, $ozon_dop_url ) ;
           if (!isset($result_array['code'])) {
                file_put_contents($filename, json_encode($result_array, JSON_UNESCAPED_UNICODE));
                   // Снова очищаем кеш
               clearstatcache(true, $filename);
           } else {
            echo "<br>Не получили данные для файла $filename_s <br>";
           }
  
}
return  $result_array;
}