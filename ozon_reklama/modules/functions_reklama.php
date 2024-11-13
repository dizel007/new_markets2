<?php
/***********************************************************************************
 *                                GET запрос без данных
 ***********************************************************************************/
function GET_without_data_ozon_reklama($access_token_reklama, $url) {
    // print_r($url);
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: Bearer '. $access_token_reklama,
		'Content-Type: application/json',
        'Accept: application/json'
	));
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $send_data); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$res = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код

	curl_close($ch);
	
	$res = json_decode($res, true);

    if (intdiv($http_code,100) > 2) {
        echo     '<br>Результат обмена озон (с данными): '.$http_code. "<br>";
        }
    //  print_r($res);
    return($res);	
    }

/***********************************************************************************
 *                                GET запрос ДАННЫХ ФАЙЛА
 ***********************************************************************************/
function GET_file_data_ozon_reklama($access_token_reklama, $url) {
    // print_r($url);
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Authorization: Bearer '. $access_token_reklama,
		'Content-Type: application/json',
        'Accept: application/json'
	));
	// curl_setopt($ch, CURLOPT_POSTFIELDS, $send_data); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$res = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код

	curl_close($ch);
	
    if (intdiv($http_code,100) > 2) {
        echo     '<br>Результат обмена озон (с данными): '.$http_code. "<br>";
        }

    return($res);	
    }


/***********************************************************************************
 *                          POST запрос с данными
 ***********************************************************************************/

    function POST_with_data_ozon_reklama($access_token_reklama, $url, $send_data = '' ) {

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Authorization: Bearer '. $access_token_reklama,
            'Content-Type: application/json',
            'Accept: application/json'
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $send_data); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $res = curl_exec($ch);
    
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
    
        curl_close($ch);
        
        $res = json_decode($res, true);
    
        if (intdiv($http_code,100) > 2) {
            echo     '<br>Результат обмена озон (с данными): '.$http_code. "<br>";
            }
    
        return($res);	
        }
    
    


/***********************************************************************************
 *                                 ПОЛУЧАЕМ ID ркламных компаний
 ***********************************************************************************/

 function get_company_id_ozon ($access_token_reklama , $type_company ='') {
     $url = "https://api-performance.ozon.ru:443/api/client/campaign";
     $arr_info_company = GET_without_data_ozon_reklama($access_token_reklama, $url);
 // Если выбираем по типу компании, то возвращаем только отрортированный 
     if ($type_company !='') {
        foreach ($arr_info_company['list'] as $item_company) {
            if ($item_company['state'] ==  $type_company) {
                $arr_select_company[] = $item_company;
            }
        }
        if (isset($arr_select_company)) {
            return  $arr_select_company;
        }
     }
     return  $arr_info_company['list'];

 }

/***********************************************************************************
 *             Запрос на подготовку отчета рекламной компании
 ***********************************************************************************/
function request_UUID_for_reklam_company ($access_token_reklama, $arr_company_id , $date_start, $date_stop) {
$url = "https://api-performance.ozon.ru:443/api/client/statistics";

$data = json_encode(array("campaigns"=>$arr_company_id,
            "dateFrom" => $date_start,
            "dateTo" => $date_stop,
            "groupBy" => "NO_GROUP_BY"
 
));



$UUID_request = POST_with_data_ozon_reklama($access_token_reklama, $url, $data );
return $UUID_request['UUID'];
}

/***********************************************************************************
 *           Проверяем статус запрашиваемго отчета 
 ************************************************************************************/

 function check_status_UUID_request($access_token_reklama, $UUID_request){
    $url = "https://api-performance.ozon.ru:443/api/client/statistics/{$UUID_request}";
    $status_UUID = GET_without_data_ozon_reklama($access_token_reklama, $url);
return  $status_UUID['state'] ;
 }


 /***********************************************************************************
 *           Получаем ссылку на скачивание запрашиваемого отчета
 ************************************************************************************/

 function link_for_report_request_UUID ($access_token_reklama, $UUID_request){
  $url = "https://api-performance.ozon.ru:443/api/client/statistics/report?UUID=".$UUID_request;
    $link_for_report_request_UUID = GET_file_data_ozon_reklama($access_token_reklama, $url);
return  $link_for_report_request_UUID ;
 }


 
 /***********************************************************************************
 *          Парсим csv файл для рекламных компаний Трафареты и вывод в ТОП
 ************************************************************************************/

 function parce_csv_trafaret_and_vivod_v_top ($file_url){
    $row = 0;

    if (($handle = fopen($file_url, "r")) !== FALSE) {
        while (($data = fgetcsv($handle, 1000, ";")) !== FALSE) {
            $num = count($data);
            for ($c=0; $c < $num; $c++) {
                if ($data[$c] == 'Корректировка' ) break 1; // убираем из рассчета строку Корректировка
                $arr_cvs[$row][]=$data[$c];
            }
            $row++;
        }
    
        fclose($handle);
    }
  
    unset($arr_cvs[0]); // удаляем первую строку
    unset($arr_cvs[1]); // удаляем наименовая столбов
    unset($arr_cvs[$row-1]); // Удаляем итого
    $arr_cvs = array_values($arr_cvs);
    // print_r($arr_cvs);
  return  $arr_cvs ;
   }
 /***********************************************************************************
 *        По СКУ получаем артикул товара в магазине
 ************************************************************************************/

   function find_real_article_by_sku($pdo, $sku , $shop_name) {
    // получаем товары магазина из БД
    // $shop_name = 'ozon_ip_zel';
    $stmt= $pdo->prepare("SELECT * FROM $shop_name WHERE `sku` = $sku");
    $stmt ->execute([]);
    $db_tovar_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $db_tovar_data[0];
    }
/***********************************************************************************
 *         Выводим на экран данные о рекл компаниии Трафареты и ТОП
 ************************************************************************************/

 function print_table_tarfareti_and_top ($key_id_company, $arr_one_company){

echo <<<HTML
<link rel="stylesheet" href="css/reklama_table.css">
<h1> НОМЕР КОМПАНИИ $key_id_company</h1>
<table class="prods_table">
    <tr class = "cells"> 
        <td>sku</td>
        <td>Название товара</td>
        <td>Цена товара, ₽</td>
        <td>Показы</td>
        <td>Клики</td>
        <td>CTR (%)</td>
        <td>Ср. цена клика, ₽</td>
        <td>Расход, ₽, с НДС</td>
        <td>Заказы</td>
        <td>Выручка, ₽</td>
        <td>Заказы модели</td>
        <td>Выручка с заказов модели, ₽</td>
    </tr>
HTML;

// print_r($arr_one_company);


foreach ($arr_one_company as $data_company) {
    echo     "<tr>";
 for ($i=0; $i<=11;$i++) {
    echo     "<td>{$data_company[$i]}</td>";


 }
 echo     "</tr>";
 
}

echo     "</table>";
   }
  

/***********************************************************************************
 *         Выводим на экран данные о рекл компаниии ПРОДВИЖЕНИЕ В ПОИСКЕ
 ************************************************************************************/

 function print_table_poisk ($pdo, $key_id_company, $arr_one_company){

    echo <<<HTML
    <link rel="stylesheet" href="css/reklama_table.css">
    <h1> НОМЕР КОМПАНИИ $key_id_company</h1>
    <table class="prods_table">
        <tr class = "cells"> 
            <td>пп</td>
            <td>Дата</td>
            <td>ID заказа</td>
            <td>Номер заказа</td>
            <td>Ozon ID</td>
            <td>Ozon ID продвигаемого товара</td>
            <td>Артикул</td>
            <td>Наименование</td>
            <td>Количество</td>
            <td>Цена продажи</td>
            <td>Стоимость, ₽</td>
            <td>Ставка, %</td>
            <td>Ставка, ₽</td>
            <td>Расход, ₽</td>

        </tr>
    HTML;
    
    // print_r($arr_one_company);
    
 $i2=1;   
    foreach ($arr_one_company as $data_company) {
        echo     "<tr>";
        echo     "<td class = \"td_tuning\">$i2</td>";

     for ($i=0; $i<=12;$i++) {
        if ($i == 5) {
            $temp =  find_real_article_by_sku($pdo, $data_company[4] , 'ozon_ip_zel');
            // print_r($temp);
            $data_company[5] = $temp['mp_article'];
            // $data_company[6] = $temp['mp_name'];

        }
        echo     "<td class = \"td_tuning\">{$data_company[$i]}</td>";
    
    
     }
     echo     "</tr>";
     $i2++;
    }
    
    echo     "</table>";
       }


/***********************************************************************************
 *         Выводим на экран данные о рекл компаниии ПРОДВИЖЕНИЕ В ПОИСКЕ
 ************************************************************************************/

 function print_table_poisk_summ_data ($pdo, $key_id_company, $arr_one_company){

    echo <<<HTML
    <link rel="stylesheet" href="css/reklama_table.css">
    <h1> НОМЕР КОМПАНИИ $key_id_company</h1>
    <table class="prods_table">
        <tr class = "cells"> 
            <td>Артикул продвижения</td>
            <td>Проданный товар</td>
            <td>SKU прод товар</td>
            <td>Количество</td>
            <td>Сумма проданного</td>
            <td>Сред цена за шт</td>
            <td>Затраты на рекламу</td>
            <td>ДРР %</td>
            <td>Сред затраты на рекл за шт</td>
        
        </tr>
    HTML;
    
    // print_r($arr_one_company);
    
$summ_one_artik_all =   0; 
$count_one_artik_all =  0; 
$reklam_cost_one_artik_all =  0; 
    

    foreach ($arr_one_company as $key_art=>$data_company) {
$row_count = count($data_company)+1;
foreach ($data_company as $items) {
    $reklam_sku = $items['reklam_sku'];

}
echo     "<tr>";
        
        echo     "<td rowspan=\"$row_count\" class = \"td_tuning\">$key_art<br>$reklam_sku</td>";
        $summ_one_artik = 0; // сумма товаров по одному рекламному товару
        $count_one_artik = 0; // количество товаров по одному рекламному товару
        $reklam_cost_one_artik = 0; // затраты на рекламу по рекламному товару

        foreach ($data_company as $items) {
        
        echo     "<td class = \"td_tuning\">".$items['buy_article']."</td>";
        echo     "<td class = \"td_tuning\">".$items['buy_sku']."</td>";

        echo     "<td class = \"td_tuning\">".$items['count']."</td>";
        echo     "<td class = \"td_tuning\">".$items['summa']."</td>";
        $midl_price = round($items['summa']/$items['count'],2);
        echo     "<td class = \"td_tuning\">".$midl_price."</td>";
        echo     "<td class = \"td_tuning\">".$items['stavka']."</td>";
        echo     "<td class = \"td_tuning\">".$items['reklam']."</td>";
            $midl_zetrati = round($items['stavka']/$items['count'],2);
        echo     "<td class = \"td_tuning\">".$midl_zetrati."</td>";
            $summ_one_artik = $summ_one_artik + $items['summa'];
            $count_one_artik = $count_one_artik + $items['count'];
            $reklam_cost_one_artik = $reklam_cost_one_artik + $items['stavka'];
    
     echo     "</tr>";
        }
        $summ_one_artik_all =  $summ_one_artik_all + $summ_one_artik;
        $count_one_artik_all = $count_one_artik_all + $count_one_artik;
        $reklam_cost_one_artik_all =  $reklam_cost_one_artik_all + $reklam_cost_one_artik;
        // Итоговые данные
        echo     "<tr>";
        echo     "<td class = \"td_tuning_summ\">".'ИТОГО :'."</td>";
        echo     "<td class = \"td_tuning_summ\"></td>";
        echo     "<td class = \"td_tuning_summ\">".$count_one_artik."</td>";
        echo     "<td class = \"td_tuning_summ\">".$summ_one_artik."</td>";
        echo     "<td class = \"td_tuning_summ\"></td>";
        echo     "<td class = \"td_tuning_summ\">".$reklam_cost_one_artik."</td>";
        echo     "<td class = \"td_tuning_summ\"></td>";
        echo     "<td class = \"td_tuning_summ\"></td>";
        echo     "</tr>";
  }
        // Итоговые данные
        echo     "<tr>";
        echo     "<td class = \"td_tuning_summ_all\">".'ИТОГО :'."</td>";
        echo     "<td class = \"td_tuning_summ_all\"></td>";
        echo     "<td class = \"td_tuning_summ_all\"></td>";
        echo     "<td class = \"td_tuning_summ_all\">".$count_one_artik_all."</td>";
        echo     "<td class = \"td_tuning_summ_all\">".$summ_one_artik_all."</td>";
        echo     "<td class = \"td_tuning_summ_all\"></td>";
        echo     "<td class = \"td_tuning_summ_all\">".$reklam_cost_one_artik_all."</td>";
        echo     "<td class = \"td_tuning_summ_all\"></td>";
        echo     "<td class = \"td_tuning_summ_all\"></td>";
        echo     "</tr>";
  
    echo     "</table>";
       }