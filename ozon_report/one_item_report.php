<?php

require_once "../connect_db.php";

require_once "../mp_functions/ozon_api_functions.php";

require_once "../pdo_functions/pdo_functions.php";

require_once "libs_ozon/function_ozon_reports.php"; // массив с себестоимостью товаров
require_once "libs_ozon/sku_fbo_na_fbs.php"; // массив с себестоимостью товаров
require_once "../mp_functions/report_excel_file.php";


$test_posting = '16450199-0120';


function make_posting_number ($posting_temp_number) {
    if ($posting_temp_number == '') {
        return '';
    }
$pos1 = strpos($posting_temp_number, '-');
$pos2 = strpos($posting_temp_number, '-', $pos1 + strlen('-'));
if ($pos2 > 0) {
$pos4  = mb_substr($posting_temp_number, 0, $pos2);
} else {
    $pos4 = $posting_temp_number;
}
return $pos4;

}

// echo $pos2."<br>";
// echo $pos4;

$ozon_shop = 'ozon_anmaks';

$t = file_get_contents('ozon_est.json');
$array_with_all_sellers = json_decode($t, true);

echo "<pre>";
$i=0;
foreach ($array_with_all_sellers as $arr_temp) {
    foreach ($arr_temp as $add) {

        $array_MINI[] = $add;
     
}




$i=0;
foreach ($array_MINI as $item) {
    $i++;
    $posting_number = $item['posting']['posting_number'];
    $posting_number_for_array = make_posting_number ($posting_number);
    $i++;
    // if ($i > 400) {
    //     break 2 ;
    // }
    $prod_array_2[$posting_number_for_array][] = $item;
    $prod_array_posts[$posting_number_for_array][] = $posting_number_for_array;

}
//     if ($test_posting ==  $posting_number_for_array) {
//     $prod_array[$posting_number_for_array][] = $item;
// }
        // echo "*********************************************** $posting_number <br>";    

}

// print_r($array_our_posting_number);
// $prod_array[0] =   $new_prod_array_33;
// print_r($prod_array_posts);
// $prod_array[] = $item;
// foreach ($prod_array_2 as $item) {
    $prod_array = $prod_array_2;
require "razbor_dannih_one_item.php";
// unset ($prod_array);
// die();
// }

die();








/***************** ФУНКЦИИ ПОШЛИ **********************************************************************************************
 **********************************************************************************************************************/
function print_one_string_in_table($print_item, $parametr, $color_class = '')
// Выводит одну строку с данными из массива
{
    if (isset($print_item[$parametr])) {
        echo "<td class=\"$color_class\">" . round($print_item[$parametr], 2) . "</td>";
    } else {
        echo "<td>" . "" . "</td>";
    }
}

function print_two_strings_in_table($print_item, $parametr1, $parametr2, $color_class = '')
// Выводит две строки с данными из массива
{
    if (isset($print_item[$parametr1])) {
        echo "<td class=\"$color_class\">" .  round($print_item[$parametr1], 2) . "<br>" .  round($print_item[$parametr2], 2) . "</td>";
    } else {
        echo "<td>" . "-" . "</td>";
    }
}

