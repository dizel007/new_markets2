<?php

require_once "../connect_db.php";
require_once '../pdo_functions/pdo_functions.php';

require_once "../mp_functions/wb_api_functions.php";
require_once "../mp_functions/wb_functions.php";

$wb_shop = '';

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


$dop_link = "?dateFrom=".$dateFrom."&dateTo=".$dateTo;

$link_wb =  'https://statistics-api.wildberries.ru/api/v4/supplier/reportDetailByPeriod'.$dop_link;// временный метод


$arr_result = light_query_without_data($token_wb, $link_wb);


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


    // [Россия] => 1000134676
    // [1000134676] => 1000134676
    // [1000134675] => 1000134675


echo "<pre>";

// print_r($arr_result);
// перебираем весь массив и находим номер отчета для России и для загранки
foreach ($arr_result as $items) {
    $temp = "___".$items['ppvz_office_name'];
        if ($items['supplier_oper_name'] == 'Продажа'){
            if (strpos($temp, 'Россия,')) {
                $Russia_report_number = $items['realizationreport_id'];
            } else {
                $zagranica_report_number = $items['realizationreport_id'];
            }
        }
}

/*************************************************************************************
 * ************************************************************************************
 *************************************************************************************/

 /// Перебираем массив и делаем три массива (отчеты Агнета)
foreach ($arr_result as $items) {
if (($items['supplier_oper_name'] == 'Продажа') ) {
    // разбираем ПРОДАЖИ 
    if ($items['realizationreport_id'] == $Russia_report_number) { // для России
        $arr_Rus_data[]=$items;
    } elseif ($items['realizationreport_id'] == $zagranica_report_number) { // для Остальных
        $arr_granicas_data_sell[]=$items;
    } else {
        $arr_data_alarm[]=$items; // если что то не обработали, то это  тут 
    }
  
} elseif ($items['supplier_oper_name'] == 'Возврат') {

    if ($items['realizationreport_id'] == $Russia_report_number) { // для России
        $arr_Rus_data[]=$items;
    } elseif ($items['realizationreport_id'] == $zagranica_report_number) { // для Остальных
        $arr_granicas_data_vozvrat[]=$items;
    } else {
        $arr_data_alarm_2[]=$items; // если что то не обработали, то это  тут 
    }


} else {
    $arr_data_alarm_3[]=$items; // если что то не обработали, то это  тут 
}
}


$otchet_date = 'c_'.$dateFrom.'_po_'.$dateTo."_" ;

// Формируем EXCEL файлы
if (isset($arr_Rus_data)) {
make_agent_report ($arr_Rus_data, $otchet_date.'RUSSIA');
}
if (isset($arr_granicas_data_sell)) {
    make_agent_report ($arr_granicas_data_sell, $otchet_date.'KAZAHI_prodazhi');
}

if (isset($arr_granicas_data_vozvrat)) {
make_agent_report ($arr_granicas_data_vozvrat, $otchet_date.'KAZAHI_vozvrati');
}
// print_r($arr_Rus_data);
// print_r($arr_data_alarm_3);
echo "END";

function make_agent_report ($arr_data, $filename){

    $xls = new PHPExcel();
    $xls->setActiveSheetIndex(0);
    $sheet = $xls->getActiveSheet();
    
    $next_i = 1;
    foreach ($arr_data  as $order) {
        $right_article = make_right_articl($order['sa_name']);
         $sheet->setCellValue("A".$next_i, $right_article);
if ($order['supplier_oper_name'] == 'Продажа') {
         $sheet->setCellValue("C".$next_i, $order['delivery_amount']);
} else {
    $sheet->setCellValue("C".$next_i, -$order['return_amount']);
}
         $sheet->setCellValue("D".$next_i, $order['retail_amount']);
  
         $next_i++; // смешение по строкам
     
    }
     
     $objWriter = new PHPExcel_Writer_Excel2007($xls);
  
     $file_name_1c_list_q = "(".date('Y-m-d').")_".$filename."(NEW).xlsx";
     $objWriter->save($file_name_1c_list_q);  
     return $file_name_1c_list_q;
    }