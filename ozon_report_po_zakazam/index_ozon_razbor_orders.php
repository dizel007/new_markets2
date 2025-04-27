<?php
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/

require_once "../connect_db.php";
require_once "../mp_functions/ozon_api_functions.php";
require_once "../pdo_functions/pdo_functions.php";
require_once "libs_ozon/function_ozon_reports.php"; // массив с себестоимостью товаров
require_once "libs_ozon/sku_fbo_na_fbs.php"; // массив с себестоимостью товаров

// выбираем магазин 
if (isset($_GET['ozon_shop'])) {
    $ozon_shop = $_GET['ozon_shop'];
 if ($ozon_shop  == 'ozon_anmaks') {
        $token =  $token_ozon;
        $client_id =  $client_id_ozon;
        $name_mp_shop = 'OZON ООО АНМАКС';
  
    }
        
 elseif ($ozon_shop  == 'ozon_ip_zel') {
        $token =  $token_ozon_ip;
        $client_id =  $client_id_ozon_ip;
        $name_mp_shop = 'OZON ИП ЗЕЛ';
  }
}
   
/// выбираем дату

if (isset($_GET['dateFrom'])) {
    $date_from = $_GET['dateFrom'];
} else {
    $date_from = false;
}

if (isset($_GET['dateTo'])) {
    $date_to = $_GET['dateTo'];
} else {
    $date_to = false;
}



echo <<<HTML
<head>
<link rel="stylesheet" href="../css/main_ozon.css">

</head>
HTML;






echo <<<HTML
<head>
<link rel="stylesheet" href="css/main_table.css">

</head>
<body>

<form action="#" method="get">
<label>Магазин </label>
<select required name="ozon_shop">
    <option value = "ozon_anmaks">Озон ООО</option>
    <option value = "ozon_ip_zel">OZON_ИП</option>
</select>


<label>дата начала</label>
<input required type="date" name = "dateFrom" value="$date_from">
<label>дата окончания</label>
<input required type="date" name = "dateTo" value="$date_to">


<input type="submit"  value="START">
</form>
HTML;

if (($date_from == false) or ($date_to == false)) {
    die ('Нужно выбрать даты');
    } 

// $date_from = "2023-08-19";
// $date_to = "2023-09-19";
echo "Период запроса с ($date_from) по  ($date_to)<br>";
$ozon_link = 'v3/finance/transaction/list';
$send_data = array(
    "filter" => array(
        "date" => array (
            "from" => $date_from."T00:00:00.000Z",
            "to"=> $date_to."T00:00:00.000Z"
    ),
        "operation_type" => [],
        "posting_number" => "",
        "transaction_type" => "all"
    ),
    "page" => 1,
    "page_size" => 1000
);
$send_data = json_encode($send_data);

$res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_link );


// если ошибка при обмене то выводим е
if (isset($res['message'])) {

    echo "<pre>";
    print_r($res);
    die('ОШИБКА ПРИ ЗАПРОСЕ');
    
}


$page_count = $res['result']['page_count'];
$row_count = $res['result']['row_count'];
echo $page_count ." ". $row_count."<br>";

// Запрашиваем все страницы отчета
for ($i=1; $i <=$page_count; $i ++) {
    $send_data = array(
        "filter" => array(
            "date" => array (
                "from" => $date_from."T00:00:00.000Z",
                "to"=> $date_to."T00:00:00.000Z"
        ),
            "operation_type" => [],
            "posting_number" => "",
            "transaction_type" => "all"
        ),
        "page" => $i,
        "page_size" => 1000
    );
    $send_data = json_encode($send_data);
    $res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_link );
    $prod_array[] = $res['result']['operations'];
}




foreach ($prod_array as $arr_temp) {
    foreach ($arr_temp as $add) {

        $array_MINI[] = $add;
     
}

unset($prod_array);

}
foreach ($array_MINI as $item) {
   
    $posting_number = $item['posting']['posting_number'];
    $posting_number_for_array = make_posting_number ($posting_number);
  
    $prod_array_2[$posting_number_for_array][] = $item;
    $prod_array_posts[$posting_number_for_array][] = $posting_number_for_array;

}

$prod_array = $prod_array_2;



// echo "<pre>";
// print_r($prod_array);

// die();



require_once "razbor_dannih_one_item.php";


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

//// НАХОДИМ ЦЕНУ ИЗ БД (минимальную или нормальную)
function get_min_price_ozon($otchet_article, $arr_all_nomenklatura, $type_price)
{
    foreach ($arr_all_nomenklatura as $nomenclatura) {

        // echo "$otchet_article)  ({$nomenclatura['main_article_1c']}";
        $price = 0;
        if (mb_strtolower($otchet_article) == mb_strtolower($nomenclatura['main_article_1c'])) {

            if ($type_price == 'min') {
                $price = $nomenclatura['min_price'];
            }
            if ($type_price == 'norm') {
                $price = $nomenclatura['main_price'];
            }
            break;
        }
    }


    return $price;
}


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
