<?php
require_once "../connect_db.php";

// echo "<pre>";
// print_r($arr_catalog);

if (isset($_FILES['file'])) {
    $xml_file =$_FILES['file']['name'];
    $path = 'uploads/';
	if (move_uploaded_file($_FILES['file']['tmp_name'], $path . $xml_file)) {
        // Далее можно сохранить название файла в БД и т.п.
        $success = 'Файл «' . $xml_file . '» успешно загружен.';
    } else {
        $error = 'Не удалось загрузить файл.';
    }
    // print_r($_FILES);
} else {
    echo <<<HTML
    <form action="" method="post" enctype="multipart/form-data">
        <input required type="file" name="file">
        <input type="submit" value="Отправить">
    </form>
HTML;

    echo "Файл отсутствует";
    die('');
}

$xml_file =$_FILES['file']['name'];
$xmlstring = file_get_contents($path.$xml_file);

$xml = simplexml_load_string($xmlstring, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml);
$array = json_decode($json,TRUE);




// Проверяем есть ли номер документа
if (!isset($array['Документ']['ТаблСчФакт']['СведТов'])) {
    die('Не смогли распарсить XML file');
}


$DocNumber = $array['Документ']['СвСчФакт']['@attributes']['НомерДок']." от ". $array['Документ']['СвСчФакт']['@attributes']['ДатаДок'];





echo "<b>НОМЕР УПД :". $DocNumber."</b>" ;



// создаем папку с заказом
$temp_dir = 'reports/'.$DocNumber;
if (!is_dir($temp_dir)) {
	mkdir($temp_dir, 0777, True);
}

echo "<pre>";
// print_r($array['ORDER']['OrderDetail']);
if (isset($array['Документ']['ТаблСчФакт']['СведТов'])) {
$our_array = $array['Документ']['ТаблСчФакт']['СведТов'];
} else {
echo "<br>НЕТ ДАННЫХ ДЛЯ ВЫВОДА<br>";
     
}



// echo "<pre>";
// print_r($our_array);


  // перебираем массив из ВИ
foreach ($our_array as $item) {

$arr_temp['article']=$item['ДопСведТов']['@attributes']['КодТов'];
$arr_temp['count'] = $item['@attributes']['КолТов'];

// $one_price = round ($item['@attributes']['ЦенаТов']*1.2 ,2 );
$one_price = round ($item['@attributes']['СтТовУчНал'] / $item['@attributes']['КолТов'] ,2 );

$arr_temp['one_price'] = $one_price;
$new_arrar_for_excel[] = $arr_temp;


}

// echo "<pre>";
// print_r($new_arrar_for_excel);

//// Формируем файл для 1С
$xls = new PHPExcel();
$file_name_1c_list = make_1c_file ($xls, $new_arrar_for_excel, $temp_dir.'/');

echo <<<HTML
<br><br>
<h2><a href="$file_name_1c_list">Cкачать ЕКСЕЛЬ для 1С </a></h2>
<br><br>
HTML;
die('РАЗОБРАЛИ');








function make_1c_file ($xls, $arr_for_1C, $new_path){

 
    $xls->setActiveSheetIndex(0);
    $sheet = $xls->getActiveSheet();
    $next_i = 1;
    foreach ($arr_for_1C  as $q_items) {
         $new_article = change_article_for_1C($q_items['article']); // подменяем артикул, чтобы бился с 1С артикулами
         $sheet->setCellValue("A".$next_i, $new_article);
         $sheet->setCellValue("C".$next_i, $q_items['count']);
         $sheet->setCellValue("D".$next_i, $q_items['one_price']); // цена за 1 шт товара

         $next_i++; // смешение по строкам
     
    }
       

     $objWriter = new PHPExcel_Writer_Excel2007($xls);
     $rnd1000001 = "(".rand(0,10000).")";
     $file_name_1c_list_q = $new_path."/"."OZON_Zakaz_v_1c_".date('Y-m-d').$rnd1000001."_(NEW_funck).xlsx";
     $objWriter->save($file_name_1c_list_q);  
     return $file_name_1c_list_q;
    
    }

function change_article_for_1C ($article) {
if ($article == '301А' ) {
    $new_article = '301';
// большая решетка
    } elseif ($article == '302А' ) {
        $new_article = '302';
// поддон с решеткой
    } elseif ($article == 'AN.301.315' ) {
        $new_article = 'ANM.301.315';
     }elseif ($article == '315А' ) {
        $new_article = '315';
// решетки водоотводные
    } elseif ($article == 'ANM.503' ) {
        $new_article = '503А';
    } elseif ($article == 'ANM.501' ) {
        $new_article = '501';

    } elseif ($article == 'ANM.508К.10' ) {
        $new_article = '508АК-10';
    
    } elseif ($article == 'ANM.508.10' ) {
        $new_article = '508А-10';
    

    } elseif ($article == 'ANM.508К' ) {
        $new_article = '508АК';

    } elseif ($article == 'ANM.508' ) {
        $new_article = '508А';

    
    } elseif ($article == 'ANM.503' ) {
            $new_article = '503А';
    } else {
        $new_article = $article;
    }


    return $new_article;

}