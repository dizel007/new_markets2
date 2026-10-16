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
// if ($datediff != 6 )  {
//     die ('<br> Промежуток времени должен быть неделя');
// }

// $dop_link = "?dateFrom=".$dateFrom."&limit=100000&dateTo=".$dateTo."&rrdid=0";
// $link_wb = "https://statistics-api.wildberries.ru/api/v5/supplier/reportDetailByPeriod".$dop_link;
            
$link_wb =  "https://finance-api.wildberries.ru/api/finance/v1/sales-reports/detailed";

$data_wb['dateFrom'] = $dateFrom;
$data_wb['dateTo'] = $dateTo;


// $data_wb['dateFrom'] = '2026-08-31';
// $data_wb['dateTo'] = '2026-09-27';


echo "<pre>";
// print_r($data_wb);



// $arr_result = light_query_with_data__kkkkk ($token_wb, $link_wb, $data_wb);


function light_query_with_data__kkkkk ($token_wb, $link_wb, $data)
{
	$ch = curl_init($link_wb);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization:' . $token_wb,
		'Content-Type:application/json'
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data, JSON_UNESCAPED_UNICODE));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);

	$res = curl_exec($ch);

	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
	curl_close($ch);

	if (($http_code != 200) && ($http_code != 201) && ($http_code != 204)) {
		echo     '<br>Результат обмена(SELECT with Data): ' . $http_code ." link = ".$link_wb ."<br>";
	}

	$res = json_decode($res, true);
 	print_r($res); // выводит результирующий массив
	
	return $res;
}



$file_name = "../!cache/". $userdata['user_login']."/"."wb_report_week.json";
// file_put_contents($file_name, json_encode($arr_result, JSON_UNESCAPED_UNICODE));

$arr_result = json_decode(file_get_contents($file_name), true);


// print_r($arr_result[228]);
// print_r($arr_result);



// foreach ($arr_result as $item) {
// $gg[$item['docTypeName']] = $item['docTypeName'];
// $jj[$item['sellerOperName']] = $item['sellerOperName'];
// }
// echo "<pre>";
// print_r($jj);
// die();
// Проверяем есть ли данные в массиве
if (!isset($arr_result[0]['reportId'] )) {
    echo "<br>НЕ смогли считать данные с ВБ<br>";
    die('Die without Array WB');
}


/// Оставляем только импортные продажи 
foreach ($arr_result as $item) {
 if ($item['reportType'] == 2) {
            $ino_number = $item['reportId'];
            $arr_ino_items[] = $item;
        }
}


//**************************************************************************************************** */
// *******************                 если нет продаж казахам
//**************************************************************************************************** */
if (!isset($arr_ino_items)) {
    echo "<br><br>НИЧЕГО КАЗАХАМ НЕ ТОЛКНУЛИ";
    die();
}


$wb_catalog = get_catalog_tovarov_v_mp_2('wb_anmaks', $pdo);

echo "<br>Номер отчета заграничных поставок INO = $ino_number";


 
echo "<pre>";
print_r($arr_ino_items);

// die();
/*****************************************************************************************************************
 ************************************* Ищем все номера отчетов
 ******************************************************************************************************************/


if ((@$arr_result['code'] == 401)) {
    die ('<br>НЕТ ДАННЫХ ДЛЯ ВЫВОДА, ВБ НЕ ВЕРНУЛ МАССИВ');
}
  // формируем массива с артикулами
  foreach ($arr_ino_items as $item) {
    // if ($item['reportId'] == $ino_number) { 
   

    $arr_key[] = $item['vendorCode']; // массив артикулов
foreach ($wb_catalog as $wb_item) {
    // echo "<br>".$wb_item['mp_article'] ;
    // echo "---".$item['vendorCode'];
    if (mb_strtolower($item['vendorCode']) === mb_strtolower($wb_item['mp_article']))

         $arr_key_barcode[$item['vendorCode']] = $wb_item['barcode'];
    // }
    }

  }
  $arr_key = array_unique($arr_key); //  оставляем только уникальные артикулы


// echo "<pre>";
print_r($arr_key_barcode);
// die('5555555555555555555555555555555555');



foreach ($arr_ino_items as $item) {
    $article_new = $item['vendorCode']; // Вычитываем  артикул

        if (($item['sellerOperName'] == 'Продажа') ) {
            $ARR_all_data[$article_new]['summa_k_perechisleniu'] = @$ARR_all_data[$article_new]['summa_k_perechisleniu'] + $item['forPay'];
            $ARR_all_data[$article_new]['count_sell'] = @$ARR_all_data[$article_new]['count_sell'] + $item['quantity'];
           
        } elseif ( $item['sellerOperName'] == 'Компенсация потерянного товара') {
        // Компенсация потерянного товара

        $ARR_all_data[$article_new]['summa_k_perechisleniu'] = @$ARR_all_data[$article_new]['summa_k_perechisleniu'] + $item['ppvzSalesCommission'];
        $ARR_all_data[$article_new]['count_sell'] = @$ARR_all_data[$article_new]['count_sell'] - $item['quantity'];

 
        } elseif ($item['sellerOperName'] == 'Авансовая оплата за товар без движения') { 
        //************** Авансовая оплата за товар без движения ******************************
        
        $ARR_all_data[$article_new]['summa_anavsa'] = @$ARR_all_data[$article_new]['summa_anavsa'] + $item['ppvzSalesCommission'];

        } elseif (($item['sellerOperName'] == 'Частичная компенсация брака') || ($item['sellerOperName'] == 'Компенсация подмененного товара') ) {
        //  *********************Частичная компенсация брака  ИЛИ Компенсация подмененного товара
        $ARR_all_data[$article_new]['summa_braka'] = @$ARR_all_data[$article_new]['summa_braka'] + $item['ppvzSalesCommission'];
        
        }  elseif (($item['sellerOperName'] == 'Компенсация брака')) {
           //  ********************* компенсация брака  ИЛИ Компенсация подмененного товара
        $ARR_all_data[$article_new]['summa_braka'] = @$ARR_all_data[$article_new]['summa_braka'] + $item['ppvzSalesCommission'];
  
        } elseif ($item['sellerOperName'] == 'Возврат') {
        // Сумма возвоатов ************************************************************************************************************
        
        $ARR_all_data[$article_new]['summa_vozvratov'] = @$ARR_all_data[$article_new]['summa_vozvratov'] + $item['ppvzSalesCommission'];
        $ARR_all_data[$article_new]['count_sell'] = @$ARR_all_data[$article_new]['count_sell'] - 1;
        $ARR_all_data[$article_new]['count_vozvrat'] = @$ARR_all_data[$article_new]['count_vozvrat'] + 1;
      
        } elseif (($item['sellerOperName'] == 'Корректная продажа') ) {
        
        // Сумма к перечислению (Корректная продажа) ********************************************************************************************
        $ARR_all_data[$article_new]['summa_k_perechisleniu'] = @$ARR_all_data[$article_new]['summa_k_perechisleniu'] + $item['ppvzSalesCommission'];
        $ARR_all_data[$article_new]['count_sell'] = @$ARR_all_data[$article_new]['count_sell'] + $item['quantity'];
    
        
        } elseif (($item['sellerOperName'] == 'Сторно продаж') ) {
        // ********************Сторно продаж *****************************************************************************************
        $ARR_all_data[$article_new]['summa_k_perechisleniu'] = @$ARR_all_data[$article_new]['summa_k_perechisleniu'] - $item['ppvzSalesCommission'];
        $ARR_all_data[$article_new]['count_sell'] = @$ARR_all_data[$article_new]['count_sell'] - $item['quantity'];
      
        } elseif (($item['sellerOperName'] == 'Логистика') OR ($item['sellerOperName'] == 'Доставка') ){
        // Сумма логистики ************************************************************************************************************
              // учитываем только прямую логистку 
            if ($item['bonusTypeName'] =='К клиенту при продаже') {
                $ARR_all_data[$article_new]['logistika'] = @$ARR_all_data[$article_new]['logistika'] + $item['deliveryService'];
             
            } elseif ($item['bonusTypeName'] =='К клиенту при отмене') {
                $ARR_all_data[$article_new]['logistika'] = @$ARR_all_data[$article_new]['logistika'] + $item['deliveryService'];
                

            } elseif ($item['bonusTypeName'] =='От клиента при отмене') {
                $ARR_all_data[$article_new]['logistika'] = @$ARR_all_data[$article_new]['logistika'] + $item['deliveryService'];
            }

        } elseif ($item['sellerOperName'] == 'Возмещение издержек по перевозке') {
        // Сумма логистики ИПЕШНИКАМ ************************************************************************************************************
        // $summa_izderzhik_po_perevozke = $summa_izderzhik_po_perevozke + $item['rebill_logistic_cost'];
        //     $arr_sum_logistik[$article_new] = @$arr_sum_logistik[$article_new] + $item['rebill_logistic_cost'];
        //     $sum_logistiki = $sum_logistiki  + $item['rebill_logistic_cost'];
        } elseif ($item['sellerOperName'] == 'Логистика сторно') {
        
        $ARR_all_data[$article_new]['logistika'] = @$ARR_all_data[$article_new]['logistika'] - $item['deliveryService'];
        
        } elseif ($item['sellerOperName'] == 'Хранение') {
        // Стоимость ХРАНЕНИЯ  ****************************************************************************************************
        $ARR_all_data[$article_new]['hranenie'] = @$ARR_all_data[$article_new]['hranenie'] + $item['paidStorageSum'];
  
        
        } elseif ($item['sellerOperName'] == 'Корректировка хранения') {
        // Стоимость Корректировка ХРАНЕНИЯ  ****************************************************************************************************
        $ARR_all_data[$article_new]['hranenie'] = @$ARR_all_data[$article_new]['hranenie'] + $item['paidStorageSum'];
               
        }  elseif ($item['sellerOperName'] == 'Удержания') {
        // Стоимость ПРОЧИЕЕ УДЕРЖАНИЯ ****************************************************************************************************
        $ARR_all_data[$article_new]['uderzhaia'] = @$ARR_all_data[$article_new]['uderzhaia'] + $item['deductionSum'];
      
        
        }  elseif ($item['sellerOperName'] == 'Штрафы и доплаты') {
        // Стоимость ШТРАФЫ И ДОПЛАТЫ  ****************************************************************************************************
        $ARR_all_data[$article_new]['straf_i_doplat'] = @$ARR_all_data[$article_new]['straf_i_doplat'] + $item['penaltySum'];
        
        } elseif ($item['sellerOperName'] == 'Штрафы') {
        // Сумма ШТРАФОв   ************************************************************************************************************
        $ARR_all_data[$article_new]['straf_i_doplat'] = @$ARR_all_data[$article_new]['shrafi'] + $item['penaltySum'];
        $sum_shtraf = $sum_shtraf  + $item['penalty'];
      
       
        } elseif ($item['sellerOperName'] == 'Штраф') {
        // Сумма ШТРАФОв   ************************************************************************************************************
        $ARR_all_data[$article_new]['straf_i_doplat'] = @$ARR_all_data[$article_new]['shrafi'] + $item['penaltySum'];
        $sum_shtraf = $sum_shtraf  + $item['penalty'];


        } else {
        $array_neuchet[] = $item;
        }

   
    
}


// Формируем сумму выкупа для каждого артикула
foreach ( $ARR_all_data as &$temp_data) {
    // if (isset($temp_data['count_sell'])) {
    $temp_data['summa_vikupa'] = @$temp_data['summa_k_perechisleniu'] 
                               + @$temp_data['summa_anavsa'] 
                               - @$temp_data['logistika'] 
                               - @$temp_data['summa_vozvratov'];
                               - @$temp_data['summa_braka'];
                               - @$temp_data['straf_i_doplat'];
                               - @$temp_data['hranenie'];
                               - @$temp_data['uderzhaia'];
 $summ_array_data['sum_nasha_viplata'] = @$summ_array_data['sum_nasha_viplata'] + $temp_data['summa_vikupa'];
    // }
}

/// формируем массив общих суммм

foreach ( $ARR_all_data as $key=>$temp_data_2) {
    if (isset($temp_data_2['count_sell'])) {
      $summ_array_data['summa_k_perechisleniu'] = @$summ_array_data['summa_k_perechisleniu'] + @$temp_data_2['summa_k_perechisleniu'];
      $summ_array_data['summa_anavsa'] = @$summ_array_data['summa_anavsa'] + @$temp_data_2['summa_anavsa'];
      $summ_array_data['logistika'] = @$summ_array_data['logistika'] + @$temp_data_2['logistika'];
      $summ_array_data['summa_vozvratov'] = @$summ_array_data['summa_vozvratov'] + @$temp_data_2['summa_vozvratov'];
      $summ_array_data['summa_braka'] = @$summ_array_data['summa_braka'] + @$temp_data_2['summa_braka'];
      $summ_array_data['straf_i_doplat'] = @$summ_array_data['straf_i_doplat'] + @$temp_data_2['straf_i_doplat'];
      $summ_array_data['hranenie'] = @$summ_array_data['hranenie'] + @$temp_data_2['hranenie'];
      $summ_array_data['uderzhaia'] = @$summ_array_data['uderzhaia'] + @$temp_data_2['uderzhaia'];
    } 
}


// print_r($arr_82400);
print_r($ARR_all_data);

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

    foreach ($ARR_all_data as $key=>$item){
     echo "<tr>";
        echo "<td>".$key."</td>";
        echo "<td>".@$item['count_sell']."</td>";
        echo "<td>".$arr_key_barcode[$key]."</td>";
///     Сумма выплат с ВБ до вычета 
echo "<td class=\"plus\">".number_format(@$item['summa_k_perechisleniu'],2, ',', ' ')."</td>";

// Авансовая оплата за товар без движения
echo "<td class=\"plus\">".number_format(@$item['summa_anavsa'],2, ',', ' ')."</td>"; 


///     Сумма компенсация брака 
echo "<td class=\"plus\">".number_format(@$item['summa_braka'],2, ',', ' ')."</td>"; 

///     Сумма выплат с возвратов 
echo "<td class=\"minus\">".number_format(@$item['summa_vozvratov'],2, ',', ' ')."</td>";

///     Сумма ЛОгистики 
 echo "<td class=\"minus\">".number_format(@$item['logistika'],2, ',', ' ')."</td>";



///     Сумма Комиссии ВБ
echo "<td class=\"minus\">".number_format(@$arr_sum_voznagrazhdenie_wb[$key],2, ',', ' ')."</td>";

///     Сумма Штрафов  
echo "<td class=\"minus\">".number_format(@$item['straf_i_doplat'],2, ',', ' ')."</td>";


// ///     Сумма к выплате
echo "<td class=\"our_many\">".number_format(@$item['summa_vikupa'],2, ',', ' ')."</td>";  

if (isset($item['count_sell'])) {
$price_for_shtuka = @$item['summa_vikupa']/$item['count_sell'];
$kol_vo_for_xml = $item['count_sell'];
// $summa_vikupa = $item['summa_vikupa'];
$summa_vikupa = number_format($item['summa_vikupa'],2,'.','');
// echo "**$summa_vikupa <br>";
} else {
    $price_for_shtuka = 0;
    $kol_vo_for_xml = 0;
    $summa_vikupa = 0;
}
echo "<td>".number_format($price_for_shtuka,2, ',', ' ')."</td>";

echo "</tr>";

// /*
// */
$array_for_xml[$key]['key'] = $key;
$array_for_xml[$key]['count'] = $kol_vo_for_xml;
$array_for_xml[$key]['barcode'] = $arr_key_barcode[$key];
$array_for_xml[$key]['FullPrice'] = $summa_vikupa;

}


 // удаляем все строки где количество равно НУЛЮ
foreach ($array_for_xml as $key=>&$xml_temp) {
    if ($xml_temp['count'] == 0) {
        unset($array_for_xml[$key]);
    }
}


// echo "<pre>";
// print_r($array_for_xml);

$json_xml= json_encode($array_for_xml);
file_put_contents('tovari.json', $json_xml);


echo"<tr>";
echo"<td></td>";
echo"<td></td>";
echo"<td></td>";
echo"<td class=\"plus\"><b>".number_format(@$summ_array_data['summa_k_perechisleniu'],2, ',', ' ')."</b></td>";
echo"<td class=\"plus\"><b>".number_format(@$summ_array_data['summa_anavsa'],2, ',', ' ')."</b></td>";
echo"<td class=\"plus\"><b>".number_format(@$summ_array_data['summa_braka'],2, ',', ' ')."</b></td>";
echo"<td class=\"minus\"><b>".number_format(@$summ_array_data['summa_vozvratov'],2, ',', ' ')."</b></td>";
echo"<td class=\"minus\"><b>".number_format(@$summ_array_data['logistika'],2, ',', ' ')."</b></td>";
echo"<td class=\"minus\"><b>".number_format(0,2, ',', ' ')."</b></td>";
echo"<td class=\"minus\"><b>".number_format(@$summ_array_data['straf_i_doplat'],2, ',', ' ')."</b></td>";
echo"<td class=\"our_many\"><b>".number_format(@$summ_array_data['sum_nasha_viplata'],2, ',', ' ')."</b></td>";

echo "</tr>";



echo "</table>";


// echo "<pre>";
// print_r($array_for_xml);


// die();


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


