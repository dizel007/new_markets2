<?php 
require_once ("../connect_db.php"); // подключение к БД
require_once '../libs/PDFMerger/PDFMerger.php';
require_once "../pdo_functions/pdo_functions.php";
require_once "functions/functions_yandex.php";
require_once "functions/functions.php";
require_once "../mp_functions/yandex_functions.php";

// выбираем бату и норме заказа 
if  (!isset($_GET['order_number']) OR (!isset($_GET['select_date']))) {
   $need_date_temp='';
    echo <<<HTML

    <form action="#" method= "get">
        <label>Дата сбора заказа</label>
        <input required type="date" name="select_date" value=$need_date_temp readonly>
        <label>Введите номер заказа</label>
        <input required type="text" name="order_number" value="">
        <br><br>
        <input type="submit" value="Получить Ярлыки">
    </form>
    
    HTML;  
    die();
} else {
    $order_number=$_GET['order_number'];
    $need_date_temp = $_GET['select_date'];
    $need_date = date('d-m-Y' , strtotime($need_date_temp)); 
}

// Получаем токены ЯМ
$ya_token =  get_token_yam($pdo);
$campaignId = get_id_company_yam($pdo);

// Вытаскиваем заказы переведенные в доставку на эту дату
$arr_all_new_orders = get_new_orders($ya_token, $campaignId, 'READY_TO_SHIP');


// сортируем заказы только для нашей даты 
$count_zakazov = 0;
$del_i = 0;
foreach ($arr_all_new_orders['orders'] as $order) {
     $need_ship_date = $order['delivery']['shipments'][0]['shipmentDate'];
  
        if (($need_date == $need_ship_date) &&($order['substatus'] == 'READY_TO_SHIP'))  {    /// выбор даты дня отгрузки
            $count_zakazov++;
        } else {
            // все с другими датами отгрузки удаляем
            unset($arr_all_new_orders['orders'][$del_i]);                
        }
$del_i++;
}

// если нет заказов на нашу дату то СТОП
if (count($arr_all_new_orders['orders']) == 0) {
    echo "Нет товаров к отгрузке на эту дату: $need_date";
    die();
}

// print_r($arr_all_new_orders);
// die();

// Содаем все необходимы е дирректории
$arr_dir = make_all_dir (date('Y-m-d'), $order_number) ;

///////////////////////////////// Формируем перечень заказов //////////////////////////////////////

foreach ($arr_all_new_orders['orders'] as $order) { // перебираем все новые заказы
    
    $orderId = $order['id']; // ID  выбранного заказа
    $box_count = 0; // сдвиг номера грузометса, если несколько товаров в заказке
    $item_count_in_order_z = 0;
    $need_ship_date = $order['delivery']['shipments'][0]['shipmentDate'];
    $id_shipment = $order['delivery']['shipments'][0]['id'];
  
        if ($need_date == $need_ship_date)  {    /// выбор даты дня отгрузки
            
            foreach ($order['items'] as $items) { // перебираем все товары из выбранного заказа

                for ($i = 0; $i < $items['count'] ; $i++) { // перебираем все количество этого артикула, и разбиваем по грузоместам
                    $box_number = $box_count + $i + $item_count_in_order_z;

                    $id_box = $order['delivery']['shipments'][0]['boxes'][$box_number]['id']; // берем порядковый номер грузоместа
                        $arr_boxes_all[] =  
                            array ( "id_order" => $orderId,
                                    "offerId" => mb_strtolower($items['offerId']),
                                    "itemsId" => $items['id'],
                                    "offerName" => $items['offerName'],
                                    "priceBeforeDiscount" => $items['priceBeforeDiscount'],
                                    "id_shipment" => $id_shipment,
                                    
                                    "boxe" => $id_box,
                                    "date_ship" => $need_ship_date,
                                    "fullCount"=> 1,
                            );
                            
                }
                $item_count_in_order_z = $item_count_in_order_z + $items['count'];
    
            }
        }

}


// сохраняем JSON всех заказов для сборки
$json_file_link_boxes = $arr_dir['zip_archives']."/".$order_number.'_boxes_shiped.json';
file_put_contents($json_file_link_boxes, json_encode($arr_boxes_all, JSON_UNESCAPED_UNICODE));


// формируем массив по артикулам для запроса этикетов
foreach ($arr_boxes_all as $razbor_article) {
    $new_box_array [$razbor_article['offerId']][] = $razbor_article;
}

// Формируем папку с ярлыками 
foreach ($new_box_array as $items) {
   $arr_file_merge_pdf_name[] =  get_yarliki_odnogo_artikula ($ya_token, $campaignId, $items, $arr_dir['yarliki'], $order_number);
}

// print_r($arr_file_merge_pdf_name);
// die();

// Формируем ексель файл 
$return_arrays = make_array_sell_items ($arr_all_new_orders , $need_date);

// print_r($return_arrays);
// die();

$arr_mass_one_date_orders = $return_arrays['arr_mass_one_date_orders'];
// формируем массив для 1С
$sell_tovari = make_array_sell_items_for_1c ($arr_mass_one_date_orders);
// print_r($sell_tovari);
// die();


if (isset($sell_tovari)) {
    // Создаем файл для 1С
    $xls = new PHPExcel();
    $xls->setActiveSheetIndex(0);
    $sheet = $xls->getActiveSheet();
    $i=1;
   
    foreach ($sell_tovari as $key => $items) {
    
       $new_key =  change_sku_for_1c_article($key); // подменяем артикул на наш

        $sheet->setCellValue("A".$i, $new_key);
        $sheet->setCellValue("C".$i, $items['count']);
        $sheet->setCellValue("D".$i, $items['middle_price']);
        $i++; // смешение по строкам
    
    }
    
    $objWriter = new PHPExcel_Writer_Excel2007($xls);
    $excel_1c_file_name = "(".$order_number.")_yandex_file_1C.xlsx";
    $file_name_1c_list =  $arr_dir['excel_docs']."/".$excel_1c_file_name;
   //  $objWriter->save("../EXCEL/".$file_name_1c_list);
    $objWriter->save($file_name_1c_list);
          
    } 
// сохраняем JSON всех заказов для сборки
$json_file_link = $arr_dir['zip_archives']."/".$order_number.'.json';
    file_put_contents($json_file_link, json_encode($arr_all_new_orders, JSON_UNESCAPED_UNICODE));


/*****************************************************************************************************************
 ******  Формируем ZIP архив с этикетаксм и 1С файлом и листом подбора
 ******************************************************************************************************************/
  
  $path_zip_archives = $arr_dir['zip_archives'];
  $path_excel_docs = $arr_dir['excel_docs'];
  $yarliki_merge = $arr_dir['yarliki_merge'];

// ссылка где будет лежить ZIP файл /////////////////////////////////////
  $zip_file_name = "YM_dopXXX_№(".$order_number.") от ".$need_date.".zip"; // название файла ZIP архива
  $link_zip_file = $path_zip_archives."/".$zip_file_name;
 
  $zip_new = new ZipArchive();
  $zip_new->open( $link_zip_file, ZipArchive::CREATE|ZipArchive::OVERWRITE);
  $zip_new->addFile($file_name_1c_list, $excel_1c_file_name); // добавляем для НОВЫЙ 1С файл /// *****************

// Добавляем пдф файлы в архив
  foreach ($arr_file_merge_pdf_name as $merge_pdf_name) {
        $zip_new->addFile($yarliki_merge."/".$merge_pdf_name, $merge_pdf_name); // Добавляем пдф файлы
    }   

  $zip_new->close();  
 

/// отрисовываем форму для вывода

echo <<<HTML

<head>
<link rel="stylesheet" href="css/polychenie_yarlikov.css">  
</head>
<body>
  <div class="container">
      <div class="title_up">скачать АРХИВ с ЯРЛЫКАМИ которые уже в статусе "ГОТОВ к ОТГРУЗКЕ"</div>
    <div class="title">Дата отгузки: $need_date</div>
    <div class="title">Количество отгружаемых заказов: $count_zakazov</div>
    <div class="buttons">
      <a href="$link_zip_file"> <button class="button_download">Скачать архив со ярлыками</button></a>
     
    </div>
    <div class="footer">Формирование архива я ярлыками завершено</div>
</div>

</div>
</body>
HTML;

 /// удаляем файл АВТОСКЛАДА, который сообщает о том, что нужно обновить данные об остатках с 1С
 if (file_exists('../autosklad/uploads/priznak_razbora_net.txt')) {
    unlink('../autosklad/uploads/priznak_razbora_net.txt'); 
 }
// die ('READY ZZZZ ****');


