<?php
require_once "../connect_db.php";
require_once "../mp_functions/wb_api_functions.php";
require_once "../mp_functions/wb_functions.php";



function is_monday($date) {
    return date('D', strtotime($date)) === 'Mon';
}

function is_sunday($date) {
    return date('D', strtotime($date)) === 'Sun';
}
  

if (isset($_GET['UPD_number'])) {
   $UPD_number  = $_GET['UPD_number'];
} else {
  $UPD_number  = false;
}

if (isset($_GET['UPD_date'])) {
    $UPD_date  = $_GET['UPD_date'];
 } else {
   $UPD_date  = false;
 }

 
if (isset($_GET['dateFrom'])) {
      if (!is_monday($_GET['dateFrom'])) {
        die("<br>Дата начала должна быть понедельником");
      };

    $dateFrom = $_GET['dateFrom'];
} else {
    $dateFrom = false;
}

if (isset($_GET['dateTo'])) {
    if (!is_sunday($_GET['dateTo'])) {
        die("<br>Дата окончания должна быть воскресенье");
      };

    $dateTo = $_GET['dateTo'];
} else {
    $dateTo = false;
}

if (isset($_GET['nalog_form_UPD'])) {
    $nalog_form_UPD  = $_GET['nalog_form_UPD'];
 } else {
   $nalog_form_UPD  = false;
 }


/********
*/
$date_start = strtotime($dateFrom);
$date_stop = strtotime($dateTo);

$datediff = ($date_stop  - $date_start) /  (60 * 60 * 24);


echo <<<HTML
<head>
<link rel="stylesheet" href="css/main_table.css">

</head>
<body>

<form action="" method="get">
<label>Магазин</label>
<select required name="wb">
    <option value = "1">WB ООО</option>
    <!-- <option value = "2">WB ИП</option> -->
</select>
<br>
<br>

<label>дата начала</label>
<input required type="date" name = "dateFrom" value="$dateFrom">
<label>дата окончания</label>
<input required type="date" name = "dateTo" value="$dateTo">
<br>
<br>

<label>Номер УПД</label>
<input required type="text" name = "UPD_number" value="$UPD_number">


<label>дата УПД</label>
<input required type="date" name = "UPD_date" value="$UPD_date">
<br>
<br>
<label>Номер налоговой декларации </label>
<input required type="text" name = "nalog_form_UPD" value="$nalog_form_UPD">


<input type="submit"  value="START">
</form>
HTML;

// 
if (($dateFrom == false) or ($dateTo == false)) {
    die ('<br>Нужно выбрать даты');
    } 

    echo "Delta Days=".$datediff;
if ($datediff != 6 )  {
    die ('<br> Промежуток времени должен быть неделя');
}

$dop_link = "?dateFrom=".$dateFrom."&limit=100000&dateTo=".$dateTo."&rrdid=0";
$link_wb = "https://statistics-api.wildberries.ru/api/v5/supplier/reportDetailByPeriod".$dop_link;

$arr_result = light_query_without_data($token_wb, $link_wb);

// echo "<pre>";
// print_r($arr_result);

// Проверяем есть ли данные в массиве
if (!isset($arr_result[0]['realizationreport_id'] )) {
    echo "<br>НЕ смогли считать данные с ВБ<br>";
    die('Die without Array WB');
}


///
foreach ($arr_result as $item) {
  if ( $item['supplier_oper_name'] == 'Продажа') {
    $temp_mmm = "__".$item['ppvz_office_name'];
    if ($item['report_type'] == 1) {
    $rus_number = $item['realizationreport_id'];
    } elseif ($item['report_type'] == 2) {
        $ino_number = $item['realizationreport_id'];
    }
}
}




$wb_catalog = get_catalog_tovarov_v_mp_2('wb_anmaks', $pdo);

echo "<br>RUS = $rus_number";
echo "<br>INO = $ino_number";
echo "<pre>";
// print_r($wb_catalog);
/*****************************************************************************************************************
 ************************************* Ищем все номера отчетов
 ******************************************************************************************************************/

echo  "<br>$ino_number<br>";

if ((@$arr_result['code'] == 401)) {
    die ('<br>НЕТ ДАННЫХ ДЛЯ ВЫВОДА, ВБ НЕ ВЕРНУЛ МАССИВ');
}
  // формируем массива с артикулами
  foreach ($arr_result as $item) {
    if ($item['realizationreport_id'] == $ino_number) { 
   

    $arr_key[] = $item['sa_name']; // массив артикулов
foreach ($wb_catalog as $wb_item) {
    if (mb_strtolower($item['sa_name']) == mb_strtolower($wb_item['mp_article']))
    $arr_key_barcode[$item['sa_name']] = $wb_item['barcode'];
    }
    }
  }
  $arr_key = array_unique($arr_key); //  оставляем только уникальные артикулы



// print_r($arr_key_barcode);

// die();

// die('5555555555555555555555555555555555');
$sum_k_pererchisleniu = 0;
$sum_logistiki = 0;
$sum_shtraf = 0 ;
$sum_voznagrazhdenie_wb = 0;
$sum_vozvratov = 0;
$sum_avance = 0 ;
$sum_brak = 0;
$sum_nasha_viplata = 0;

foreach ($arr_result as $item) {
    $article_new = $item['sa_name']; // Подставляем стандартный артикул
    if ($item['realizationreport_id'] == $ino_number) {

        if (($item['supplier_oper_name'] == 'Продажа') ) {

            $arr_sum_k_pererchisleniu[$article_new] = @$arr_sum_k_pererchisleniu[$article_new] + $item['ppvz_for_pay'];
            $sum_k_pererchisleniu = $sum_k_pererchisleniu  + $item['ppvz_for_pay'];
            $arr_count[$article_new] = @$arr_count[$article_new] + $item['quantity'];
        }elseif( $item['supplier_oper_name'] == 'Компенсация потерянного товара') {
        // Компенсация потерянного товара
        $arr_sum_k_pererchisleniu[$article_new] = @$arr_sum_k_pererchisleniu[$article_new] + $item['ppvz_for_pay'];
        $sum_k_pererchisleniu = $sum_k_pererchisleniu  + $item['ppvz_for_pay'];
        $arr_count[$article_new] = @$arr_count[$article_new] - $item['quantity'];
        
        
        
        } elseif ($item['supplier_oper_name'] == 'Авансовая оплата за товар без движения') { 
        //************** Авансовая оплата за товар без движения ******************************
        
        $arr_sum_avance[$article_new] = @$arr_sum_avance[$article_new] + $item['ppvz_for_pay'];
        $sum_avance = $sum_avance  + $item['ppvz_for_pay'];
        } 
        elseif (($item['supplier_oper_name'] == 'Частичная компенсация брака') || ($item['supplier_oper_name'] == 'Компенсация подмененного товара') ) {
        //  *********************Частичная компенсация брака  ИЛИ Компенсация подмененного товара
        $sum_brak = $sum_brak  + $item['ppvz_for_pay'];
    
        
    }
    elseif (($item['supplier_oper_name'] == 'Компенсация брака')) {
           //  ********************* компенсация брака  ИЛИ Компенсация подмененного товара
       $sum_brak = $sum_brak  + $item['ppvz_for_pay'];
   
      
       

        } elseif ($item['supplier_oper_name'] == 'Возврат') {
        // Сумма возвоатов ************************************************************************************************************
        
        $arr_sum_vozvratov[$article_new] = @$arr_sum_vozvratov[$article_new] + $item['ppvz_for_pay'];
        $sum_vozvratov = $sum_vozvratov  + $item['ppvz_for_pay'];
        $arr_count[$article_new] = @$arr_count[$article_new] - 1;
        
        } elseif (($item['supplier_oper_name'] == 'Корректная продажа') ) {
        
        // Сумма к перечислению (Корректная продажа) ********************************************************************************************
        $arr_sum_k_pererchisleniu[$article_new] = @$arr_sum_k_pererchisleniu[$article_new] + $item['ppvz_for_pay'];
        $sum_k_pererchisleniu = $sum_k_pererchisleniu  + $item['ppvz_for_pay'];
        $arr_count[$article_new] = @$arr_count[$article_new] + $item['quantity'];
        
        } elseif (($item['supplier_oper_name'] == 'Сторно продаж') ) {
        // ********************Сторно продаж *****************************************************************************************
        $arr_sum_k_pererchisleniu[$article_new] = @$arr_sum_k_pererchisleniu[$article_new] - $item['ppvz_for_pay'];
        $sum_k_pererchisleniu = $sum_k_pererchisleniu  - $item['ppvz_for_pay'];
        $arr_count[$article_new] = @$arr_count[$article_new] - $item['quantity'];
        $stornoprodazh++;
        } elseif ($item['supplier_oper_name'] == 'Логистика') {
        // Сумма логистики ************************************************************************************************************
        $arr_sum_logistik[$article_new] = @$arr_sum_logistik[$article_new] + $item['delivery_rub'];
        $sum_logistiki = $sum_logistiki  + $item['delivery_rub'];
      
        } elseif ($item['supplier_oper_name'] == 'Возмещение издержек по перевозке') {
        // Сумма логистики ИПЕШНИКАМ ************************************************************************************************************
        // $summa_izderzhik_po_perevozke = $summa_izderzhik_po_perevozke + $item['rebill_logistic_cost'];
        //     $arr_sum_logistik[$article_new] = @$arr_sum_logistik[$article_new] + $item['rebill_logistic_cost'];
        //     $sum_logistiki = $sum_logistiki  + $item['rebill_logistic_cost'];
        } elseif ($item['supplier_oper_name'] == 'Логистика сторно') {
        
        $arr_sum_logistik[$article_new] = @$arr_sum_logistik[$article_new] - $item['delivery_rub'];
        $sum_logistiki = $sum_logistiki  - $item['delivery_rub'];
        
        } elseif ($item['supplier_oper_name'] == 'Хранение') {
        // Стоимость ХРАНЕНИЯ  ****************************************************************************************************
        $sum_storage = $sum_storage  + $item['storage_fee'];
        
        } elseif ($item['supplier_oper_name'] == 'Корректировка хранения') {
        // Стоимость Корректировка ХРАНЕНИЯ  ****************************************************************************************************
        $sum_storage_correctirovka = $sum_storage_correctirovka  + $item['storage_fee'];
        
        }  elseif ($item['supplier_oper_name'] == 'Удержания') {
        // Стоимость ПРОЧИЕЕ УДЕРЖАНИЯ ****************************************************************************************************
        $sum_uderzhania = $sum_uderzhania  + $item['deduction'];
        
        }  elseif ($item['supplier_oper_name'] == 'Штрафы и доплаты') {
        // Стоимость ШТРАФЫ И ДОПЛАТЫ  ****************************************************************************************************
        $sum_shtafi_i_doplati = $sum_shtafi_i_doplati  + $item['penalty'];
        
        } elseif ($item['supplier_oper_name'] == 'Штрафы') {
        // Сумма ШТРАФОв   ************************************************************************************************************
        $sum_shtraf = $sum_shtraf  + $item['penalty'];
        } else {
        $array_neuchet[] = $item;
        }

   
    }
/*****************************************
**************************************************
*/




/**
 * ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
 */


}



echo <<<HTML
<table class="prod_table">
  <tr>
<td>Артикул</td>
<td>Кол-во продаж</td>
<td>BarCode</td>
<td>Сумма выплат с ВБ</td>
<td>Авансовая оплата</td>

<td>Компенсация брака</td>
<td>Возвраты</td>
<td>Стоимость логистки</td>

<td>Комиссия ВБ</td>
<td>Штрафы ВБ</td>
<td>НАША ВЫПЛАТА</td>
<td>цена за шт</td>

 </tr>


HTML;
// print_r($arr_key);
// print_r($arr_key_barcode);

    foreach ($arr_key as $key){
     echo "<tr>";
        echo "<td>".$key."</td>";
        echo "<td>".@$arr_count[$key]."</td>";
        echo "<td>".@$arr_key_barcode[$key]."</td>";
///     Сумма выплат с ВБ до вычета 
echo "<td class=\"plus\">".number_format(@$arr_sum_k_pererchisleniu[$key],2, ',', ' ')."</td>";

// Авансовая оплата за товар без движения
echo "<td class=\"plus\">".number_format(@$arr_sum_avance[$key],2, ',', ' ')."</td>"; 


///     Сумма компенсация брака 
echo "<td class=\"plus\">".number_format(@$arr_sum_brak[$key],2, ',', ' ')."</td>"; 

///     Сумма выплат с возвратов 
echo "<td class=\"minus\">".number_format(@$arr_sum_vozvratov[$key],2, ',', ' ')."</td>";

///     Сумма ЛОгистики 
 echo "<td class=\"minus\">".number_format(@$arr_sum_logistik[$key],2, ',', ' ')."</td>";



///     Сумма Комиссии ВБ
echo "<td class=\"minus\">".number_format(@$arr_sum_voznagrazhdenie_wb[$key],2, ',', ' ')."</td>";

///     Сумма Штрафов  
echo "<td class=\"minus\">".number_format(@$arr_sum_shtraf[$key],2, ',', ' ')."</td>";


// ///     Сумма к выплате
$temp[$key] =  @$arr_sum_k_pererchisleniu[$key] - @$arr_sum_vozvratov[$key] + @$arr_sum_avance[$key] +  @$arr_sum_brak[$key] - @$arr_sum_logistik[$key] - @$arr_sum_shtraf[$key];
$sum_nasha_viplata = $sum_nasha_viplata + $temp[$key];

echo "<td class=\"our_many\">".number_format(@$temp[$key],2, ',', ' ')."</td>";  
if (isset($arr_count[$key])) {
$price_for_shtuka = @$temp[$key]/@$arr_count[$key];
$kol_vo_for_xml = $arr_count[$key];
} else {
    $price_for_shtuka = 0;
    $kol_vo_for_xml = 0;
}
echo "<td>".number_format($price_for_shtuka,2, ',', ' ')."</td>";

echo "</tr>";


// /*
// */
$array_for_xml[$key]['key'] = $key;
// $array_for_xml[$key]['count'] = $arr_count[$key];
$array_for_xml[$key]['count'] = $kol_vo_for_xml;


$array_for_xml[$key]['barcode'] = $arr_key_barcode[$key];
$array_for_xml[$key]['FullPrice'] = $temp[$key];
}



// echo "<pre>";
// print_r($array_for_xml);

$json_xml= json_encode($array_for_xml);
file_put_contents('tovari.json', $json_xml);


echo"<tr>";
echo"<td></td>";
echo"<td></td>";
echo"<td></td>";
echo"<td class=\"plus\"><b>".number_format($sum_k_pererchisleniu,2, ',', ' ')."</b></td>";
echo"<td class=\"plus\"><b>".number_format($sum_avance,2, ',', ' ')."</b></td>";
echo"<td class=\"plus\"><b>".number_format($sum_brak,2, ',', ' ')."</b></td>";
echo"<td class=\"minus\"><b>".number_format($sum_vozvratov,2, ',', ' ')."</b></td>";
echo"<td class=\"minus\"><b>".number_format($sum_logistiki,2, ',', ' ')."</b></td>";
echo"<td class=\"minus\"><b>".number_format($sum_voznagrazhdenie_wb,2, ',', ' ')."</b></td>";
echo"<td class=\"minus\"><b>".number_format($sum_shtraf,2, ',', ' ')."</b></td>";
echo"<td class=\"our_many\"><b>".number_format($sum_nasha_viplata,2, ',', ' ')."</b></td>";

echo "</tr>";



echo "</table>";


require_once "test_xml_MY.php";
die('РАСЧЕТ ОКОНЧЕН');






function get_catalog_tovarov_v_mp_2($market_name, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM $market_name");
    $stmt->execute();
    $arr_catalog = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($arr_catalog as $catalog) {
       $new_arr_cat[$catalog['id']] = $catalog['main_article'];
    }
    
    $new_arr_cat= array_unique($new_arr_cat,SORT_STRING );
    foreach ($new_arr_cat as $key => $item) {
       foreach ($arr_catalog as $cata) {
       if ($item == $cata['main_article']) {
          $super_new_arr [] = $cata;
 
             }
    }}
 
 // print_r($new_arr_cat);
 // print_r($super_new_arr);
 //    die();
 return $super_new_arr;
 
 }


