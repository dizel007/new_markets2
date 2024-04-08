<?php


function  Parce_excel_1c_sklad ($xls) {
// $xls = PHPExcel_IOFactory::load('temp_sklad/temp.xlsx');
$xls->setActiveSheetIndex(0);
$sheet = $xls->getActiveSheet();

 // ищем строу где есть в первом столбце колонка артикул
for ($j=0; $j<15;$j++) {
 $column_artikle = $sheet->getCellByColumnAndRow(0,$j)->getValue(); // артикул
 if ($column_artikle == 'Артикул' ){
        break;
 }
}
$name_string = $j;
// ищем столбцы с нужными названиями 

$nomenklatura = 3; // значения по умолчания
$quantity = 10; // значения по умолчания

for ($j=0; $j<30;$j++) {
    $poisk_perem = $sheet->getCellByColumnAndRow($j,$name_string)->getValue(); // артикул
    if ($poisk_perem == 'Номенклатура' ){
        $nomenklatura = $j;
        echo "Нашли столбец с номенклатурой - ".$nomenklatura."<br>";
      }
    if ($poisk_perem == 'Доступно' ){
           $quantity = $j;
           echo "Нашли столбец с доступным количеством - ".$quantity."<br>";
           break;
        }

   }
 

$i=14;
$stop =0;
while ($stop <> 1 ) {

    $temp_zero_cell = $sheet->getCellByColumnAndRow(0,$i)->getValue(); // артикул 
    // echo "temp_zero_cell = $temp_zero_cell<br>";
    $temp_name = $sheet->getCellByColumnAndRow($nomenklatura,$i)->getValue(); // название 
    // echo "temp_name = $temp_name<br>";
    $temp_qty = $sheet->getCellByColumnAndRow($quantity,$i)->getValue(); // количество
    // echo "temp_qty = $temp_qty<br>";

    if (($temp_zero_cell <>'') and ($temp_name <> '')) {
        $real_article = $sheet->getCellByColumnAndRow(0,$i)->getValue(); // артикул 

        // echo "MEW = $real_article, QTY=$temp_qty<br>";
    }
    // echo "real_article = $real_article<br>";
if ($temp_qty=='#NULL!') {
    $temp_qty=0;
}
if ($temp_zero_cell == 'ЛЕРУА' ) {
 $arr_article_items[$real_article]['leroy'] = $temp_qty ;
} elseif ($temp_zero_cell == 'ОЗОН' ){
    $arr_article_items[$real_article]['ozon'] = $temp_qty ;
} elseif ($temp_zero_cell == 'WB' ){
    $arr_article_items[$real_article]['wb'] = $temp_qty ;
} elseif ($temp_zero_cell == 'WB ИП' ){
    $arr_article_items[$real_article]['wbip'] = $temp_qty ;
} elseif($temp_zero_cell == 'МАРКЕТПЛЕЙСЫ') {
    $arr_article_items[$real_article]['MP'] = $temp_qty ;
}

    if ($temp_zero_cell == ''){
        // echo "закончили анализ EXCEL файла с остатками товаров<br>";
        break;
    }
    $i++;
}

$json_array_ozon = json_encode($arr_article_items, JSON_UNESCAPED_UNICODE);

file_put_contents('uploads/array_items.json', $json_array_ozon);
// echo "<pre>";
// print_r($arr_article_items);


// Оставляем массив ключ (артикул) значение остаток
foreach ($arr_article_items as $key=>$itemss ) {
    foreach ($itemss as $mp_key=>$ostatok) {
        if ($mp_key == 'MP') {
            $arr_new_ostatoki_MP[mb_strtolower($key)] = $ostatok ; // массив остатков из 1С
        }
    }
    

}

return $arr_new_ostatoki_MP;
}
