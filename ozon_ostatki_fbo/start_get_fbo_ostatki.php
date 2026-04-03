<?php
$offset = "";
require_once $offset . "../connect_db.php";
require_once $offset . "../mp_functions/ozon_api_functions.php";



$arr_all_nomenklatura = select_active_nomenklaturu($pdo);
foreach ($arr_all_nomenklatura as $zzz) {
   $arr_poriadkovii_number[mb_strtolower($zzz['main_article_1c'])] = $zzz['number_in_spisok'];
}


// ОЗОН АНМКАС
    $client_id_ozon = $arr_tokens['ozon_anmaks']['id_market'];
    $token_ozon = $arr_tokens['ozon_anmaks']['token'];

$shop_name = 'ozon_anmaks';
$ozon_catalog    = get_catalog_tovarov_v_mp($shop_name, $pdo ,'active'); // получаем озон каталог

foreach ($ozon_catalog as $ozon_items) {
    $ozon_sku_items[] = $ozon_items['sku'];
}



/********************************
 * ПОЛУЧАЕМ остатки ФБО ООО
 ****************************/

$ozon_dop_url = "v1/analytics/stocks";
$json_data = json_encode(array("skus" => $ozon_sku_items));
$result_array_ooo = post_with_data_ozon($token_ozon, $client_id_ozon, $json_data, $ozon_dop_url ) ;

// echo "<pre>";
// print_r($result_array_ooo);

// Формируем массивы для вывода данных на экран
foreach ($result_array_ooo['items'] as $item ){
    if ($item['available_stock_count'] > 5) {
  $arr_ostatok_fbo_ooo[mb_strtolower($item['offer_id'])][$item['warehouse_name']] = $item['available_stock_count'];
  $arr_count_in_city_ooo[mb_strtolower($item['offer_id'])] = @$arr_count_in_city_ooo[mb_strtolower($item['offer_id'])] + $item['available_stock_count'];
    }
}
// Привем массив артикулов в порядок (согласно порядковому нормеру)
foreach ($arr_poriadkovii_number as $key=>$z) {
  if (isset($arr_ostatok_fbo_ooo[$key])){ $arr_sort_ar_ooo[$key] = $z;}
}
// Сортировка по возрастанию с сохранением ключей
asort($arr_sort_ar_ooo);

echo "<h1> Остатики ФБО на озон ООО</h1>";
print_fbo_ostatki_table ($arr_sort_ar_ooo, $arr_ostatok_fbo_ooo, $arr_count_in_city_ooo);





// die();
/********************************
 * ПОЛУЧАЕМ остатки ФБО ИП
 ****************************/

// озон ИП зел
    $client_id_ozon_ip = $arr_tokens['ozon_ip_zel']['id_market'];
    $token_ozon_ip = $arr_tokens['ozon_ip_zel']['token'];


$shop_name = 'ozon_ip_zel';
$ozon_catalog    = get_catalog_tovarov_v_mp($shop_name, $pdo ,'active'); // получаем озон каталог

foreach ($ozon_catalog as $ozon_items) {
    $ozon_sku_items[] = $ozon_items['sku'];
}



/********************************
 * ПОЛУЧАЕМ остатки ФБО ООО
 ****************************/

$ozon_dop_url = "v1/analytics/stocks";
$json_data = json_encode(array("skus" => $ozon_sku_items));
$result_array_ip = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $json_data, $ozon_dop_url ) ;

// echo "<pre>";
// print_r($result_array_ip);

// Формируем массивы для вывода данных на экран
foreach ($result_array_ip['items'] as $item ){
    if ($item['available_stock_count'] > 5) {
  $arr_ostatok_fbo_ip[mb_strtolower($item['offer_id'])][$item['warehouse_name']] = $item['available_stock_count'];
  $arr_count_in_city_ip[mb_strtolower($item['offer_id'])] = @$arr_count_in_city_ip[mb_strtolower($item['offer_id'])] + $item['available_stock_count'];
    }
}
// Привем массив артикулов в порядок (согласно порядковому нормеру)
foreach ($arr_poriadkovii_number as $key=>$z) {
  if (isset($arr_ostatok_fbo_ip[$key])){ $arr_sort_ar_ip[$key] = $z;}
}
// Сортировка по возрастанию с сохранением ключей
asort($arr_sort_ar_ip);

echo "<h1> Остатики ФБО на озон ИП </h1>";
print_fbo_ostatki_table ($arr_sort_ar_ip, $arr_ostatok_fbo_ip, $arr_count_in_city_ip);





/************************************************************************
 * Функуия вывод на экран таблицу с остаткаими по ФБО
 **********************************************************************/
function print_fbo_ostatki_table ($arr_sort_ar, $arr_ostatok_fbo, $arr_count_in_city) {
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

$b = 0;
foreach ($arr_sort_ar as $atricle=>$z) {
// количество складов где хранится товар
$b++;
    $string_count = count($arr_ostatok_fbo[$atricle]);
    ($b%2 == 1)? $color_string = "t_ever": $color_string = "t_not_ever";
 echo "<tr >";
      echo "<td class= \"$color_string\"rowspan = \"$string_count\">$atricle</td>";
      echo "<td class= \"$color_string\"rowspan = \"$string_count\">$arr_count_in_city[$atricle]</td>";
    foreach ($arr_ostatok_fbo[$atricle] as $city=>$count_items_in_city) {
      
        echo "<td class= \"$color_string\"> $city </td>";
        echo "<td class= \"$color_string\"> $count_items_in_city </td>";
        echo "</tr>";
    }
}


echo "</table>";
}

