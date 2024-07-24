<?php
require_once "../connect_db.php";
require_once '../pdo_functions/pdo_functions.php';

require_once "../mp_functions/wb_api_functions.php";
require_once "../mp_functions/wb_functions.php";
require_once "../mp_functions/report_excel_file.php";


/// для ООО
$wb_shop = $_GET['wb_shop'];
if ($_GET['wb_shop'] == 'wb_anmaks') {
    $token_wb = $arr_tokens['wb_anmaks']['token'];
    $name_mp_shop = 'ВБ ООО АНМАКС';
   }
       
elseif ($_GET['wb_shop'] == 'wb_ip_zel') {
    $token_wb = $arr_tokens['wb_ip_zel']['token'];
    $name_mp_shop = 'ВБ ИП ЗЕЛ';
 } else {
       die ('МАГАЗИН НЕ ВЫБРАН');
 }



if (isset($_GET['dateFrom'])) {
    $dateFrom = $_GET['dateFrom'];
} else {
    $dateFrom = false;
}

if (isset($_GET['dateTo'])) {
    $dateTo = $_GET['dateTo'];
} else {
    $dateTo = false;
}



echo <<<HTML
<head>
<link rel="stylesheet" href="css/main_table.css">

</head>
<body>

<form action="" method="get">
</select>


<label>дата начала</label>
<input required type="date" name = "dateFrom" value="$dateFrom">
<label>дата окончания</label>
<input required type="date" name = "dateTo" value="$dateTo">
<input hidden type="text" name = "wb_shop" value="$wb_shop">
<input type="submit"  value="START">
</form>
HTML;


if (($dateFrom == false) or ($dateTo == false)) {
die ('Нужно выбрать даты');
} 


/*********************************************************
 * Подгоняем даты по неделям, и формируем массив для запроса по неделям
********************************************************/



$time = strtotime($dateFrom);
$temp_start = strtotime('this week monday' , $time);
// Понедельник текущей недели:
$first_monday =  date('Y-m-d', $temp_start); // 10.06.2024 00:00 
$dateFrom = $first_monday;
$i=0;
do {
$date_sunday  = date('Y-m-d', strtotime($first_monday . ' +6 day'));
// echo "Дата понедельника  = ".$first_monday."<br>"; // 10.06.2024 00:00
// echo "Дата воскресенья  = ".$date_sunday."<br>"; // 10.06.2024 00:00

$week_array[$i]['monday'] = $first_monday;
$week_array[$i]['sunday'] = $date_sunday;

// echo "<br> Дата след понедельника  = ".$first_monday."<br>"; // 10.06.2024 00:00
$first_monday = date('Y-m-d', strtotime($first_monday . ' +7 day'));
$i++;
} while ($date_sunday < $dateTo);
$dateTo = $date_sunday;

// die();


/***
 * 
 ********************************************************/



// $dop_link = "?dateFrom=".$dateFrom."&dateTo=".$dateTo;
// $link_wb = "https://statistics-api.wildberries.ru/api/v1/supplier/reportDetailByPeriod".$dop_link;
// $link_wb =  'https://statistics-api.wildberries.ru/api/v3/supplier/reportDetailByPeriod'.$dop_link;
// $link_wb =  'https://statistics-api.wildberries.ru/api/v4/supplier/reportDetailByPeriod'.$dop_link;// временный метод

echo "<pre>";
// print_r($week_array);

foreach ($week_array as $week) {
echo "Запрос данных  c ".$week['monday']." по ".$week['sunday'];
$dop_link = "?dateFrom=".$week['monday']."&dateTo=".$week['sunday'];
$link_wb =  'https://statistics-api.wildberries.ru/api/v5/supplier/reportDetailByPeriod'.$dop_link;
$arr_result_temp = light_query_without_data($token_wb, $link_wb);

/*********************************************************
Проверяем нет ли ошибки взаимодействия
***********************************************************/
if (isset($arr_result_temp['code'])) {
    if ($arr_result_temp['code'] == 429) {
    echo "<br>".$arr_result['message']."<br>";
    die ('');
    }
} 
/**********************************************************
Проверяем нет ли ошибки по возварту данных
************************************************************/
elseif (isset($arr_result_temp['errors'][0])) {
    echo "<br>".$arr_result_temp['errors'][0]."<br>";
    die ('WB не вернул данные');
    } 
elseif  (isset($arr_result_temp[0])){ 
    echo " ...... OK<br>";

    foreach ($arr_result_temp as $item)  {
        $arr_result[] = $item;
       }
       
} else {
    echo ('......WB не вернул данные!!!<br>');
    break;
}


sleep(1);
}


// echo "<pre>";
$text =  json_encode($arr_result, JSON_UNESCAPED_UNICODE);
file_put_contents('array.json', $text);


// file_put_contents('1.txt',$arr_result);
// die();





/***********************************
Проверяем eсть ли вообще массив 
*****************************************/
if (!isset($arr_result)) {
    echo "<br>Нет массива для вывода<br>";
    die ('WB не вернул данные');
    } 

// количество данных в массиве
echo (count($arr_result));


/*******************************************************************************************
*   Запускаем проверки (ЕСТЬ ЛИ МАССИВ)
******************************************************************************************/
// если ВБ не ответил
if ((@$arr_result['code'] == 401)) {
    die ('<br><br>НЕТ ДАННЫХ ДЛЯ ВЫВОДА, ОТРИЦАТЕЛЬНЫЙ РЕЗУЛЬТАТ ОБМЕНА ДАННЫМИ С ВБ');
}

if (!$arr_result) {
    die ('<br><br>НЕТ ДАННЫХ ДЛЯ ВЫВОДА, ВБ ВЕРНУЛ НУЛЕВОЙ МАССИВ ДАННЫХ');
}



  // формируем массива с артикулами
  foreach ($arr_result as $item) {
    if ($item['sa_name'] <>'') {
        $arr_key[] = make_right_articl($item['sa_name']); // массив артикулов
    }
  }
  $arr_key = array_unique($arr_key); //  оставляем только уникальные артикулы


$sum_k_pererchisleniu = 0;
$sum_logistiki = 0;
$sum_storage =0;
$sum_storage_correctirovka = 0;
$return_logistok =0;
$sum_voznagrazhdenie_wb = 0;
$sum_vozvratov = 0;
$sum_avance = 0 ;
$sum_brak = 0;
$sum_nasha_viplata = 0;
$sum_uderzhania = 0; // Удержания (нет привязки к артикулу)
$sum_shtafi_i_doplati = 0; // Штрафы и доплаты (нет привязки к артикулу)
$prodazh=0;
$stornoprodazh=0;
$correctProdazh=0;
$guts_summa_sell=0;
$summa_izderzhik_po_perevozke = 0;
$sum_korrectirovka_eqvairinga = 0;

echo "<pre>";



require_once "wb_data_razbor.php";



// print_r($arr_straf); ///////////////////////////////// DELETE////////////////////////////////////////////////////////////////////////

    /// Выводим необработанные строки из отчета
if (isset($array_neuchet)){
    echo "<pre>";
    echo "<br>*************************************  НЕУЧТЕННЫЕ НАЧАЛО *********************<br>";
    print_r($array_neuchet);
   echo "Количество необработанных строк = ".count($array_neuchet);
    echo "<br>*************************************  НЕУЧТЕННЫЕ КОНЕЦ *********************<br>";
} else {
    echo "Все данные обработаны<br><br>";
}


 // Удаляем из массива все "Продажи СТОРОННО
 //если есть возвраты, то удаляем из массива все Возвраты и Продажи сторно

if (isset($arr_count_vozvrat)) {
 echo "Возвратов :".count($arr_count_vozvrat)."<br>";
foreach ($arr_count_vozvrat as $vozvrat_item) {
    foreach ($arr_count_sell as $key => $sell_item) {
          if (($vozvrat_item['article'] == $sell_item['article']) && ($vozvrat_item['price'] == -$sell_item['price'])) {
                unset($arr_count_sell[$key]);
                break 1;
            }
       }
   }
} 

echo "<br>Продаж: $prodazh";
echo "<br>СТОРНО продаж: $stornoprodazh";
echo "<br>Коррек Продажа: $correctProdazh";
echo "<br>";
echo "<br>Стоимость Хранения (нет артикула):<b> [$sum_storage] </b>{вычитается из Итого к оплате}";
echo "<br>Стоимость Корректировка хранения (нет артикула): <b>[$sum_storage_correctirovka]{вычитается из Итого к оплате}</b>";
echo "<br>Стоимость Удержания (нет артикула): <b>[$sum_uderzhania]</b> {вычитается из Итого к оплате} ";
echo "<br>Стоимость Штрафы и доплаты (нет артикула):<b> [$sum_shtafi_i_doplati]</b> {вычитается из Итого к оплате}";
echo "<br>Стоимость Частичная компенсация брака (нет артикула):<b> [$sum_brak]</b> {добавляется к перечислению за товар}{добавляется из Итого к оплате} ";




// if (isset($arr_count_vozvrat) ) {
// $arr_sum = array_merge($arr_count_sell, $arr_count_vozvrat);
// }

// print_r($arr_sum);
// формируем массив для вывода н аэкран
require_once('wb_raschet_data_for_table.php');

// print_r($array_for_table);
// Выводим данные на экран
require_once('wb_print_report_table_new.php');

// print_r($array_for_table);
$file_name_report_excel = report_mp_make_excel_file_morzha($array_for_table, $name_mp_shop, $dateFrom, $dateTo);
echo "<br> Сумма издержек по перевозке = ".$summa_izderzhik_po_perevozke;
echo "<br> Возмещение издержек по перевозке/по складским операциям с товаром = ".$return_logistok;
echo "<br> Штрафы / Платная приемка МП на СЦ = ".$sum_shtafi_i_doplati;
echo "<br> Корректировка эквайринга = ".$sum_korrectirovka_eqvairinga;
echo "<br><br>";
echo "<br><a href = \"$file_name_report_excel\"> Ссылка для скачивания Отчета</a><br>";

die('<br>РАСЧЕТ ОКОНЧЕН');



