<?php

function print_sum_information ($arr_all_nomenklatura, $arr_new_ostatoki_MP, $arr_sell_tovari) {


echo <<<HTML

<hr>
<h1>Сводная таблица по 4-м магазинам</h1>
<table class="prods_table">

<tr>
    <td>пп</td>
    <td>арт</td>
    <td>кол-во из 1С</td>
    <td>кол-во продано</td>
    <td>мин остаток</td>
    <td>требуется</td>


</tr>

HTML;


foreach ($arr_all_nomenklatura as $item_99) {
   $article = mb_strtolower($item_99['main_article_1c']);
    echo "<tr>";
        echo "<td>".""."</td>";
        echo "<td>".$article."</td>";
        echo "<td>".@$arr_new_ostatoki_MP[$article]."</td>";
        echo "<td>".@$arr_sell_tovari[$article]."</td>";
        echo "<td>".$item_99['min_ostatok']."</td>";    
        @$need_tovarov = $item_99['min_ostatok'] - (@$arr_new_ostatoki_MP[$article] - @$arr_sell_tovari[$article]);
        if ($need_tovarov <=0) {
            $need_tovarov=0;}
        else {
            $arr_need_tovari[$article] = $need_tovarov;

        }
        echo "<td>".@$need_tovarov."</td>";    
    echo "</tr>";

    
}

echo "</table>";
    
return  @$arr_need_tovari;

};



function make_excel_file_ostatkov($arr_need_tovari) {
if (isset($arr_need_tovari)) {
    // Создаем файл для 1С
    $xls = new PHPExcel();
    $xls->setActiveSheetIndex(0);
    $sheet = $xls->getActiveSheet();
    $i=1;
   //  echo "<pre>";
        foreach ($arr_need_tovari as $key => $items) {
    // print_r($items);	
        $sheet->setCellValue("A".$i, $key);
        $sheet->setCellValue("C".$i, $items);
        $i++; // смешение по строкам
    
    }
    
    $objWriter = new PHPExcel_Writer_Excel2007($xls);
    $file_name_need_tovari =  "file_name_need_tovari.xlsx";
    $objWriter->save("temp/".$file_name_need_tovari);
    return    $file_name_need_tovari;   
    } 

}