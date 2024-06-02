<?php
function send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url ) {
 
	$ch = curl_init('https://api-seller.ozon.ru/'.$ozon_dop_url);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Api-Key:' . $token,
		'Client-Id:' . $client_id, 
		'Content-Type:application/json'
	));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $send_data); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$res = curl_exec($ch);

    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код

	curl_close($ch);
	
	$res = json_decode($res, true);
        if (intdiv($http_code,100) <>2 ) {
        echo     'Результат обмена ОЗОН: '.$http_code. "<br>";
        }
   
    return($res);	

}

/* * ********
Выводим список заказов ОЗОН на определенную дату 
РАБОЧАЯ ВЕРСИЯ 
*** ожидает упаковки ****
*** */
function get_all_waiting_posts_for_need_date($token, $client_id, $date_query_ozon, $send_status, $dop_days_query){
    // awaiting_packaging - заказы ожидают сборку
    // awaiting_deliver   - заказы ожидают отгрузку 
// echo "<br>";
// echo $token."<br>";
// echo $client_id."<br>";
// echo $date_query_ozon."<br>";

$temp_dop_day = "+".$dop_days_query.' day';
$date_query_ozon_end = date('Y-m-d', strtotime($temp_dop_day, strtotime($date_query_ozon)));

                        
// echo "<br>";


$send_data=  array(
    "dir" => "ASC",
    "filter" => array(
    "cutoff_from" => $date_query_ozon."T00:00:00Z",
    "cutoff_to" =>   $date_query_ozon_end."T23:59:59Z",
    "delivery_method_id" => [ ],
    "provider_id" => [ ],
    "status" => $send_status,
    "warehouse_id" => [ ]
    ),
    "limit" => 1000,
    "offset" => 0,
    "with" => array(
    "analytics_data"  => true,
    "barcodes"  => true,
    "financial_data" => true,
    "translit" => true
    )
    );

 $send_data = json_encode($send_data, JSON_UNESCAPED_UNICODE)  ;  


$ozon_dop_url = "v3/posting/fbs/unfulfilled/list";


// запустили запрос на озона
$res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_dop_url );
return $res;
}


/****************************************************************************************************************
************************************* убиарем из названия файлов запрещенные символы ****************************
****************************************************************************************************************/

function make_rigth_file_name($temp_file_name) {
    $temp_file_name=str_replace('*','_',$temp_file_name);
    $temp_file_name=str_replace('/','_',$temp_file_name);
    $temp_file_name=str_replace('\'','_',$temp_file_name);
    $temp_file_name=str_replace(':','_',$temp_file_name);
    $temp_file_name=str_replace('?','_',$temp_file_name);
    $temp_file_name=str_replace('>','_',$temp_file_name);
    $temp_file_name=str_replace('<','_',$temp_file_name);
    $temp_file_name=str_replace('|','_',$temp_file_name);
    $right_file_name=str_replace('"','_',$temp_file_name);
    return $right_file_name;
    }

/****************************************************************************************************************
********************Создает пдф файл с названием и содержанием АРТИКУЛА (Для цепляния к этикеткам)  **************
****************************************************************************************************************/

    function make_pdf_file($arr_for_merge_pdf , $path_etiketki, $order_number){

        require_once '../../libs/fpdf/fpdf.php';
        // подключаем шрифты
        // define('FPDF_FONTPATH',"fpdf/font/");
        
        foreach ($arr_for_merge_pdf as $good_key=>$item) {

            // $filename = $item['fileName'];
            $value = $item['value'];

        //create pdf object
        $pdf = new FPDF('L','mm', [40, 58]);
        //add new page
        $pdf->AliasNbPages();
        
        $pdf->AddPage();
        
       
        // добавляем шрифт ариал
        $pdf->AddFont('TimesNRCyrMT','','timesnrcyrmt.php');// добавляем шрифт ариал
        $pdf->AddFont('TimesNRCyrMT-Bold','','timesnrcyrmt_bold.php'); 
        $pdf->SetFont('TimesNRCyrMT-Bold','',18);
        // $pdf->Cell(0 ,0, MakeUtf8Font($filename),'',0,'C');

        $pdf->SetFont('TimesNRCyrMT-Bold','',16);

        $pdf->  SetXY(5, 6);
        $pdf->Cell(0 ,0, MakeUtf8Font("Заказ № ".$order_number),0,0,'L');
        $pdf->  SetXY(5, 12);
        $pdf->Cell(0 ,0, MakeUtf8Font($good_key),0,0,'L');
        $pdf->  SetXY(5, 19);
        $pdf->Cell(0 ,0, MakeUtf8Font($value." шт" ),0,0,'L');

        $pdf->Output("".$path_etiketki."/".$good_key.".pdf", 'F');
        unset ($pdf);
        }  
        
        }
        function MakeUtf8Font($string) {
          $string = iconv('utf-8', 'windows-1251', $string);
          return $string;
        }