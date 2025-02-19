<?php
//  "ПРОСТО Подтверждаем тут все заказы";

require_once "../../connect_db.php"; // все функции
require_once "../require_funcs.php"; // все функции

// ***** Вычитываем отправления cо статусом CREATED*********************
$new_array_for_stikers = get_create_spisok_from_lerua($token_lerua, $art_catalog, 'packingCompleted', $lerua_limit_items);

// ***** формируем массив для потверждения заказов
if (isset($new_array_for_stikers)) { // если есть неподтвержденные отправления, то разбиваем их по грузоместам
      foreach ($new_array_for_stikers  as $item) {
           $id_parcel = $item['id'];
           $arr_parcels[] = $id_parcel; // формируем массив с номерами отправлений, для подтверждения этих заказов
    }
}

echo "<pre>";

/****************************************************************************************************
************************* подтвержаем все заказы
****************************************************************************************************/

foreach ($arr_parcels as $item) {
$id_parcel = $item;
$link = 'https://api.lemanapro.ru/marketplace/merchants/v1/documents/'.$id_parcel."?documentType=barcodeSticker";

$result_query = light_query_without_data ($token_lerua, $link, 'Запрос этикетки');

// Проверяем сформировались ли этикетки и готовы для скчаивания
    if ($result_query['status'] == 'DONE') {
    $arr_stikers[$id_parcel] = $result_query['fileUrl'];
    } else {
        sleep(1);
        $result_query = light_query_without_data ($token_lerua, $link, 'Повторный запрос этикетки');
        $arr_stikers[$id_parcel] = $result_query['fileUrl'];
    }
}
echo "<pre>";

print_r($arr_stikers);

$dir = "../reports/".date('Y-m-d');
if (!is_dir($dir)) {
	mkdir($dir, 0777, True);
}

foreach ($arr_stikers as $id_parcel=>$url_stikers) {
    echo "$id_parcel <br>";
$pdf_file_content  = file_get_contents($url_stikers);

$file_path = $dir ."/".$id_parcel.'.pdf';
echo "$file_path <br>";
file_put_contents($file_path, $pdf_file_content);
usleep(10000);
}