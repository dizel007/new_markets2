<?php
require_once "parce_xlsx_price.php";
$arr_catalog = get_catalog_VI();



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
if (!isset($array['ORDER']['DocumentNumber'])) {
    die('Не смогли распарсить XML file');
}

$DocNumber = $array['ORDER']['DocumentNumber'];
echo "<b>НОМЕР ЗАКАЗА :". $DocNumber."</b>" ;
// создаем папку с заказом
$temp_dir = 'reports/'.$DocNumber;
if (!is_dir($temp_dir)) {
	mkdir($temp_dir, 0777, True);
}

echo "<pre>";
// print_r($array['ORDER']['OrderDetail']);
if (isset($array['ORDER']['OrderDetail'][0])) {
$our_array = $array['ORDER']['OrderDetail'];
} else {
 
 $our_array[0] = $array['ORDER']['OrderDetail'];
}


  // перебираем массив из ВИ
foreach ($our_array as &$item) {

    $item['price'] = get_price_for_1C ($arr_catalog, $item['SenderPrdCode']);
    $barnumber=$item['EAN'];
    $file=$item['SenderPrdCode'];
    	require("barcode/barcode.php");
    $file_name = $file.".png";
   $arr_file_names[] = get_shtrih_code ($item , $DocNumber, $file_name);
    unlink($file_name);
}

// echo count($array['ORDER']['OrderDetail'])."<br>";
// print_r($array['ORDER']['OrderDetail']);


// die('ggg');



// var_dump($arr_file_names);
// die('ffffffffffffffff');

print_r($our_array);

//// Формируем файл для 1С
$file_name_1c_list = make_1c_file ($our_array, $temp_dir.'/');

$zip = new ZipArchive();
$archive_path = $temp_dir. '/'."$DocNumber.zip";
$zip->open($archive_path , ZipArchive::CREATE|ZipArchive::OVERWRITE);

    foreach ($arr_file_names as $arc) {
        $zip->addFile($temp_dir."/".$arc ,  $arc);
    }
    $zip->addFile($temp_dir."/".$file_name_1c_list ,  $file_name_1c_list); // пакуем файл для 1С
$zip->close();
// print_r($arr_file_names);

echo <<<HTML
<br><br>
<h2><a href="$archive_path">Cкачать архив с этикетками</a></h2>
<br><br>
HTML;
die('РАЗОБРАЛИ');






/****************************************************************************************************************
*******************      Функция формирования штрихкодов  ******************************
****************************************************************************************************************/
function get_shtrih_code ($array_items, $DocNumber, $file) {
require_once "../libs/fpdf/fpdf.php";
//create pdf object
// $pdf = new FPDF('L','mm', array(121, 107));
$pdf = new FPDF('L','mm', array(120, 80));

$pdf->AddFont('TimesNRCyrMT','','timesnrcyrmt.php');// добавляем шрифт ариал
$pdf->AddFont('TimesNRCyrMT_bold','','timesnrcyrmt_bold.php');// добавляем шрифт ариал

for ($i=1; $i <= $array_items['QTY']; $i++) {
//add new page
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->image($file ,2,2,'PNG');
// unlink ($file); // удаляем png файл
$pdf->SetFont('TimesNRCyrMT','', 24); // устанавливаем шрифт для артикула

$article = "арт.(".$array_items['SenderPrdCode'].")";
$article_rus = MakeUtf8Font($article);
$pdf->text(10,75 ,$article_rus); // припечатываем артикул к ПДФ

$first_ = substr($array_items['EAN'], 0, 1);
$secont_ = substr($array_items['EAN'], 1,6 );
$third_ = substr($array_items['EAN'], 7,11);

$pdf->SetFont('TimesNRCyrMT_bold','', 24); // устанавливаем шрифт для артикула
$pdf->text(5, 60 ,$first_ ); // припечатываем артикул к ПДФ
$pdf->text(22, 63 ,$secont_ ); // припечатываем артикул к ПДФ
$pdf->text(73, 63 ,$third_ ); // припечатываем артикул к ПДФ

// break; /************************************************************************************************************************/
}
$item_count = round($array_items['QTY'],0);
$file_names = "ВИ_зак№($DocNumber)_арт.(".$article.")_(".$item_count." шт)".".pdf";

$pdf_file = 'reports/'.$DocNumber."/". $file_names; // название PDF  которое сохраниться в итоге
// $pdf_file = MakeUtf8Font($pdf_file);
// $pdf->Output("pdf/$wb_path/".$pdf_file, 'F');

$pdf->Output($pdf_file, 'F');
return $file_names;
}


/****************************************************************************************************************
*******************      Функция перекодировки текста чтобы в ПДФ были русские буквы ******************************
****************************************************************************************************************/
function MakeUtf8Font($string) {
    $string = iconv('utf-8', 'windows-1251', $string);
    return $string;
  }


/****************************************************************************************************************
*******************      Функция перекодировки текста чтобы в ПДФ были русские буквы ******************************
****************************************************************************************************************/
function get_price_for_1C ($arr_catalog, $article) {
    foreach ($arr_catalog as $catalog_item) {
        // echo "<br>7777".$article."*****".$catalog_item['article']."77777<br>";
        if((string)$article == (string)$catalog_item['article']) {
        // echo "<br>".$article."*****".$catalog_item['article']."<br>";
            return $catalog_item['price'];
        }
  }
  return 0;
}


function make_1c_file ($arr_for_1C, $new_path){

    $xls = new PHPExcel();
    $xls->setActiveSheetIndex(0);
    $sheet = $xls->getActiveSheet();
    $next_i = 1;
    foreach ($arr_for_1C  as $q_items) {
         $new_article = change_article_for_1C($q_items['SenderPrdCode']); // подменяем артикул, чтобы бился с 1С артикулами
         $sheet->setCellValue("A".$next_i, $new_article);
         $sheet->setCellValue("C".$next_i, $q_items['QTY']);
         $sheet->setCellValue("D".$next_i, $q_items['price']); // цена за 1 шт товара

         $next_i++; // смешение по строкам
     
    }
       

     $objWriter = new PHPExcel_Writer_Excel2007($xls);
     $rnd1000001 = "(".rand(0,10000).")";
     $file_name_1c_list_q = "VI_Zakaz_v_1c_".date('Y-m-d').$rnd1000001."_(NEW_funck).xlsx";
     $objWriter->save($new_path."/".$file_name_1c_list_q);  
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