<?php
require_once "../connect_db.php";
require_once '../pdo_functions/pdo_functions.php';

require_once "../mp_functions/wb_api_functions.php";
require_once "../mp_functions/wb_functions.php";
// require_once "wb_get_sebes.php";


/// для ООО
$wb_shop = $_GET['wb_shop'];
if ($_GET['wb_shop'] == 'wb_anmaks') {
    $token_wb = $arr_tokens['wb_anmaks']['token'];
   }
       
elseif ($_GET['wb_shop'] == 'wb_ip_zel') {
    $token_wb = $arr_tokens['wb_ip_zel']['token'];
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

$dop_link = "?dateFrom=".$dateFrom."&dateTo=".$dateTo;
// $link_wb = "https://statistics-api.wildberries.ru/api/v1/supplier/reportDetailByPeriod".$dop_link;
// $link_wb = 'https://statistics-api.wildberries.ru/api/v1/supplier/reportDetailByPeriod'.$dop_link;
  $link_wb =  'https://statistics-api.wildberries.ru/api/v3/supplier/reportDetailByPeriod'.$dop_link;
  $link_wb =  'https://statistics-api.wildberries.ru/api/v4/supplier/reportDetailByPeriod'.$dop_link;// временный метод


$arr_result = light_query_without_data($token_wb, $link_wb);



/*********************************************************
Проверяем нет ли ошибки взаимодействия
***********************************************************/
if (isset($arr_result['code'])) {
    if ($arr_result['code'] == 429) {
    echo "<br>".$arr_result['message']."<br>";
    die ('');
    }
} 

/**********************************************************
Проверяем нет ли ошибки по возварту данных
************************************************************/
if (isset($arr_result['errors'][0])) {
    echo "<br>".$arr_result['errors'][0]."<br>";
    die ('WB не вернул данные');
    } 

/***********************************
Проверяем eсть ли вообще массив 
*****************************************/
if (!isset($arr_result)) {
    echo "<br>Нет массива для вывода<br>";
    die ('WB не вернул данные');
    } 




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
$sum_shtraf = 0 ;
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

echo "<pre>";



require_once "wb_data_razbor.php";


    /// Выводим необработанные строки из отчета
if (isset($array_neuchet)){
    echo "<pre>";
    echo "<br>*************************************  НЕУЧТЕННЫЕ НАЧАЛО *********************<br>";
    // print_r($array_neuchet);
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
} else {
    echo "НЕТ Возвратов <br>";  
}

echo "<br>Продаж: $prodazh";
echo "<br>СТОРНО продаж: $stornoprodazh";
echo "<br>Коррек Продажа: $correctProdazh";
echo "<br>";
echo "<br>Стоимость Хранения (нет артикула):<b> [$sum_storage] </b>{вычитается из Итого к оплате}";
echo "<br>Стоимость Корректировка хранения (нет артикула): <b>[$sum_storage_correctirovka]</b>";
echo "<br>Стоимость Удержания (нет артикула): <b>[$sum_uderzhania]</b> {вычитается из Итого к оплате} ";
echo "<br>Стоимость Штрафы и доплаты (нет артикула):<b> [$sum_shtafi_i_doplati]</b> {вычитается из Итого к оплате}";
echo "<br>Стоимость Частичная компенсация брака (нет артикула):<b> [$sum_brak]</b> {добавляется к перечислению за товар}{добавляется из Итого к оплате} ";




if (isset($arr_count_vozvrat) ) {
$arr_sum = array_merge($arr_count_sell, $arr_count_vozvrat);
}

// print_r($arr_sum);
/******************************************************************************
* Рисуем ттаблицу
 *****************************************************************************/


echo <<<HTML
<table class="prod_table">
  <tr>
<td>Артикул</td>
<td>Кол-во<br> продаж</td>
<td>К перечислению<br> за товар</td>
<td>Авансовая <br>оплата</td>


<td>Возвраты</td>
<td>Стоимость <br> логистки (/шт)</td>
<td>Комиссия ВБ</td>
<td>Итого к оплате</td>
<td>цена за шт</td>
<td>Себест</td>
<td>Дельта</td>
<td>Прибыль<br> с артикула</td>
 </tr>


HTML;

$sebestoimos      = select_all_nomenklaturu($pdo);
// print_r($wb_catalog);
// $sebestoimos = get_sebestiomost_wb ();
// print_r($sebestoimos);
 foreach ($arr_key as $key){
// Находим себестоимость товара
    foreach ($sebestoimos as $sebes_item) {
        $right_key = mb_strtolower(make_right_articl($key));
        $right_atricle = mb_strtolower($sebes_item['main_article_1c']);
        // echo "$right_key  и $right_atricle"."<br>";
        if ($right_atricle ==  $right_key) {
           $sebes_str_item = $sebes_item['min_price'] ;
        //    echo "**************************** $right_key  и $right_atricle"."<br>";
           break;
        } else {
            $sebes_str_item = 0;
        }
       }

     echo "<tr>";
        echo "<td>".$key."</td>";
        echo "<td>".@$arr_count[$key]."</td>";
///     Сумма выплат с ВБ до вычета 
echo "<td class=\"plus\">".number_format(@$arr_sum_k_pererchisleniu[$key],2, ',', ' ')."</td>";

// Авансовая оплата за товар без движения
echo "<td class=\"plus\">".number_format(@$arr_sum_avance[$key],2, ',', ' ')."</td>"; 



///     Сумма выплат с возвратов 
echo "<td class=\"minus\">".number_format(@$arr_sum_vozvratov[$key],2, ',', ' ')."</td>";

///     Сумма ЛОгистики 
$logistika_za_shtuku = @$arr_sum_logistik[$key]/@$arr_count[$key];
 echo "<td class=\"minus\">".number_format(@$arr_sum_logistik[$key],2, ',', ' ').
                            "<br>".number_format(@$logistika_za_shtuku,2, ',', ' ')."</td>";


///     Сумма Комиссии ВБ
echo "<td class=\"minus\">".number_format(@$arr_sum_voznagrazhdenie_wb[$key],2, ',', ' ')."</td>";


///     Сумма к выплате
$temp[$key] =  @$arr_sum_k_pererchisleniu[$key] - @$arr_sum_vozvratov[$key] + @$arr_sum_avance[$key] +  
@$arr_sum_brak[$key] - @$arr_sum_logistik[$key] ;
$sum_nasha_viplata = $sum_nasha_viplata + $temp[$key];

echo "<td class=\"our_many\">".number_format(@$temp[$key],2, ',', ' ')."</td>";  
if ((isset($arr_count[$key]) && ($arr_count[$key]) <> 0)) {
$price_for_shtuka = @$temp[$key]/@$arr_count[$key];
} else {
    $price_for_shtuka = 0;
}
///     Цена за штуку
echo "<td>".number_format($price_for_shtuka,2, ',', ' ')."</td>"; // цена за штукту

///     себестоимость
echo"<td class=\"plus\">"."$sebes_str_item"."</td>"; // себестоимость

///     Разница в стоимости
if ((isset($arr_count[$key]) && ($arr_count[$key]) <> 0)) { // если количество проданного товара не равно Нулю то считаем дельту
$temp_delta = ($price_for_shtuka - $sebes_str_item);
} else {
    $temp_delta = 0;
}

echo"<td class=\"plus\">".number_format($temp_delta,2, ',', ' ')."</td>"; // дельта
$our_pribil  = $temp_delta * @$arr_count[$key];

$sum_our_pribil = @$sum_our_pribil + $our_pribil; // Наша заработок по всем артикулам

///     Заработок с артикула 
echo"<td class=\"our_many\"><b>".number_format($our_pribil,2, ',', ' ')."</b></td>"; // заработали на артикуле
  echo "</tr>";

}

echo"<tr>";
echo"<td></td>";
echo"<td></td>";
echo"<td class=\"plus\"><b>".number_format($sum_k_pererchisleniu,2, ',', ' ')."</b></td>";
echo"<td class=\"plus\"><b>".number_format($sum_avance,2, ',', ' ')."</b></td>";
echo"<td class=\"minus\"><b>".number_format($sum_vozvratov,2, ',', ' ')."</b></td>";

echo"<td class=\"minus\"><b>".number_format($sum_logistiki,2, ',', ' ')."</b></td>";

echo"<td class=\"minus\"><b>".number_format($sum_voznagrazhdenie_wb,2, ',', ' ')."</b></td>";
echo"<td class=\"our_many\"><b>".number_format($sum_nasha_viplata,2, ',', ' ')."</b></td>";
echo"<td></td>";
echo"<td></td>";
echo"<td></td>";
echo"<td class=\"our_many\"><b>".number_format($sum_our_pribil,2, ',', ' ')."</b></td>";
echo "</tr>";

echo"<tr>";
echo"<td></td>";
echo"<td></td>";
$summa_k_perechilseniu_za_tovar = $sum_k_pererchisleniu + $sum_avance + $sum_brak - $sum_vozvratov;
echo"<td class=\"plus\"><b>".number_format($summa_k_perechilseniu_za_tovar,2, ',', ' ')."</b></td>";
echo"<td> <-- </td>";
echo"<td> <-- </td>";
echo"<td></td>";
echo"<td></td>";
// Сумма итого у оплате За вычетов штрафов / Хранение / Удержания /
$summa_itogo_k_oplate = $sum_nasha_viplata - $sum_storage - $sum_uderzhania - $sum_shtafi_i_doplati + $sum_brak;
echo"<td class=\"plus\"><b>".number_format($summa_itogo_k_oplate,2, ',', ' ')."</b></td>";

echo "</tr>";




echo "</table>";

die('РАСЧЕТ ОКОНЧЕН');



