<?php


$offset ="../";
require_once $offset.'connect_db.php';
require_once $offset."mp_functions/ozon_api_functions.php";
require_once "ozon_return_functions.php";


require_once $offset.'libs/PHPExcel-1.8/Classes/PHPExcel.php';
require_once $offset.'libs/PHPExcel-1.8/Classes/PHPExcel/Writer/Excel2007.php';
require_once $offset.'libs/PHPExcel-1.8/Classes/PHPExcel/IOFactory.php';


require_once 'const_complects.php'; /// массив комплектов разбытый на составляющие

// echo "<pre>";
// print_r($sbor_complect );



//  Получает даты бесплатного хранения до и после сегодня за 20 дней
$date_poisk = $_GET['our_date'];
$date_start = date('Y-m-d', strtotime($date_poisk . " - 20 day"));
$date_finish = date('Y-m-d', strtotime($date_poisk . " + 20 day"));

// формируем запрос на эти даты
$send_data_arr = json_encode(array(
    "filter" => array(
        "last_free_waiting_day" => array(
            "time_from" => $date_start . "T00:00:00Z",
            "time_to" => $date_finish . "T23:59:59Z"
        ),


        "status" => "returned_to_seller"
    ),
    "limit" => 1000,
    "last_id" => 0
));

$ozon_dop_url = "v3/returns/company/fbs";

// получаем массив возвратов c ООО
$arr_ozon_ooo_returns = post_with_data_ozon($token_ozon, $client_id_ozon, $send_data_arr, $ozon_dop_url);
$arr_date = make_simple_return_array_with_our_article ($pdo, $arr_ozon_ooo_returns, 'ozon_anmaks');
foreach ($arr_date as $items) {
    if ($items['returned_to_seller_date_time'] == $date_poisk) {
        // начинаем формировать суммированный массив возвратов для ООО
        $arr_new_date_returns[$items['article']]['quantity'] = @$arr_new_date_returns[$items['article']]['quantity'] +  $items['quantity'];
        $arr_new_date_returns[$items['article']]['price'] = @$arr_new_date_returns[$items['article']]['price'] +  $items['price'];
     
    }
}


// формируем массив с датами, когда были возвраты ДЛЯ ИП
$arr_ozon_ip_returns = post_with_data_ozon($token_ozon_ip, $client_id_ozon_ip, $send_data_arr, $ozon_dop_url);
$arr_date = make_simple_return_array_with_our_article ($pdo, $arr_ozon_ip_returns, 'ozon_ip_zel');
foreach ($arr_date as $items) {
    // продолжаем формировать суммированный массив возвратов для ИП
    if ($items['returned_to_seller_date_time'] == $date_poisk) {
        $arr_new_date_returns[$items['article']]['quantity'] = @$arr_new_date_returns[$items['article']]['quantity'] +  $items['quantity'];
        $arr_new_date_returns[$items['article']]['price'] = @$arr_new_date_returns[$items['article']]['price'] +  $items['price'];
     
    }
}

// echo "<pre>";
// print_r($arr_new_date_returns);

// Теперь нам нужно разбить комплекты на составляющие 
foreach ($arr_new_date_returns as $key=> $items) {
    $priznak_complecta = 0;
    // echo "<br>ZAHOD  =$key= *****=".$items['quantity']." ********************************************************";
    foreach ($sbor_complect as $const_key => $const_items) {
        // echo "<br>ppppppppppp  =$key= *****=".$const_key." ********************************************************";
        if (mb_strtolower($const_key) == mb_strtolower($key)) {
            foreach ($const_items as $need_key=>$value) {
                // echo "<br>*=$key= *{{{$need_key}}}}".$value."**".$items['quantity'];
                // echo "}}**".$items['quantity']."**<br>";
                $complect_array_for_1c[$need_key] = @$complect_array_for_1c[$need_key] + $value*$items['quantity'];
                $priznak_complecta = 1;
                            
                }
             }
        }
        
     if ( $priznak_complecta == 0 )  {
        // echo "<br>vvvvvvvvvvvvvvvvvvvvvvvvvvvvvv*=$key= *{{{}}}}".$items['quantity']."**";
        $complect_array_for_1c[$key] = $items['quantity'];
    }

    }



    $xls = new PHPExcel();
    $xls->setActiveSheetIndex(0);
    $sheet = $xls->getActiveSheet();
    $sheet->setTitle('Возвраты с озона');
    $sheet->getColumnDimension("A")->setWidth(30);
    $sheet->getColumnDimension("B")->setWidth(30);
    

    $sheet->setCellValue("A1", "артикул");
    $sheet->setCellValue("B1", "кол-во");
    
$i=2;
// делаем выборку всех участников

foreach ($complect_array_for_1c as $key=>$val) {
    $sheet->setCellValue("A".$i, $key);
    $sheet->setCellValue("B".$i, $val);
    
$i++;
}

// границы таблицы

$border = array(
	'borders'=>array(
		'allborders' => array(
			'style' => PHPExcel_Style_Border::BORDER_THIN,
			'color' => array('rgb' => '000000')
		)
	)
);
 $i--;
$sheet->getStyle("A1:B".$i)->applyFromArray($border);



    $objWriter = new PHPExcel_Writer_Excel2007($xls);
    $file_path = 'report/file.xlsx';
    $objWriter->save($file_path);



    ob_end_clean();
 
    $file = 'report/file.xlsx';
     
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . filesize($file));
     
    readfile($file);
    exit();


