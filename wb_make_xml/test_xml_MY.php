<?php


// $nalog_form_UPD = 'ON_NSCHFDOPPR_2BM-7721546864-2012052808220682662630000000000_2BM-7727830864-772701001-201407211008287437442_20230803_bd0c0d36-8c65-46b2-ba23-e2f112ef51eb';
$idOtpravl = '2BM-7727830864-772701001-201407211008287437442'; // CONST для АНмакса
$idPol = "2BM-7721546864-2012052808220682662630000000000"; // Константа для ВБ

date_default_timezone_set('Europe/Moscow');
$vremiaPrInfo= date('H.i.s'); // время формирования
// $vremiaPrInfo= "16.32.15"; // время формирования

$DataPrInfo = date('d.m.Y'); // дата вормирования
// $DataPrInfo = "03.08.2023"; // дата вормирования

//////// 2 //////////////
    $nomerChF = $UPD_number;
    $dataChF = date('d.m.Y', strtotime($UPD_date));
$countTovarov = count($arr_key); // количетво товаров в УПД (расчетное значение)


//////// 4 //////////////
$nomOsn = $ino_number; // номер отчета ВБ
$DataOsn = date('d.m.Y', strtotime($dateFrom));
// $DataOsn = date('d.m.Y', strtotime($dateTo . ' +1 day'));

/********************************************************************************************************************************
*******************************************************************************************************************************
******************************************************************************************************************************/


// Загружаем XML
$xmlPath = 'new_form.xml';
$arr_name_cat = $wb_catalog;
$json_xml=file_get_contents('tovari.json');
$xml_data = json_decode($json_xml, true);

$dom = new DOMDocument('1.0', 'UTF-8');
$dom->load($xmlPath);

// Включаем форматирование
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;

// Находим элемент <ТаблСчФакт>
$xpath = new DOMXPath($dom);

/***************************************************************************
 *  Функция подменяет атрибут в элементе 
 ******************************************************************************/
function Change_attribute(&$element , $attribute , $data_attribute) {
    if ($element) {
        // Изменяем атрибут "Код"
        if ($element->hasAttribute($attribute)) {
            $element->setAttribute($attribute, $data_attribute); // Новое значение атрибута
        } else {
            echo "Атрибут $attribute не найден!";
        }
    }
    }


/// Изменяем аттрибуты элементов XML документа
$element = $xpath->query('//СвСчФакт')->item(0);
Change_attribute($element , 'НомерДок' , $nomerChF);

$element = $xpath->query('//СвСчФакт')->item(0);
Change_attribute($element , 'ДатаДок' , $dataChF);

$element = $xpath->query('//ДокПодтвОтгрНом ')->item(0);
Change_attribute($element , 'РеквНомерДок' , $nomerChF);

$element = $xpath->query('//ДокПодтвОтгрНом')->item(0);
Change_attribute($element , 'РеквДатаДок' , $dataChF);

$element = $xpath->query('//СвПер')->item(0);
Change_attribute($element , 'ДатаПер' , $dataChF);

$element = $xpath->query('//ОснПер')->item(0);
Change_attribute($element , 'РеквНомерДок' , $nomOsn);

$element = $xpath->query('//ОснПер')->item(0);
Change_attribute($element , 'РеквДатаДок' , $DataOsn);




// Начинаем изменть таблицу товаров



$oldElement = $xpath->query('//ТаблСчФакт')->item(0);



if ($oldElement) {


    // Создаем новый элемент <ТаблСчФакт>
    $newElement = $dom->createElement('ТаблСчФакт');




    $StrNumber = 0;

    $Summa = 0;
    foreach ($xml_data as $item) {
    
    
    // print_r($item);
    
    
        $StrNumber++;
    
    $NDS = round($item['FullPrice'] * 20 / 120, 2);
    $price_bez_NDS = $item['FullPrice'] - $NDS;
    
    
    
    
    
    // **** Подставляем наименование и артикул *******************
/********************************************************************************************************** */

    foreach ($arr_name_cat as $name) {
        if (mb_strtolower($item['key']) == mb_strtolower($name['mp_article'] )) {
        $real_name = $name['main_article'].' '.$name['mp_name'];
        $real_name = str_replace("\"", "", $real_name);
        break;
        } else {
            $real_name = "NO DATA";
        }
    }
    
   
    // Считаем сумму и НДС
    $Summa = $Summa + $item['FullPrice'];
    $Summa_NDS = @$Summa_NDS + $NDS;
    
    // if ($StrNumber ==7) {break;}

    
/********************************************************************************************************** */

   // Добавляем подэлементы СведТов
    $newRow_DopSved = $dom->createElement('СведТов', '&#xA;');
       // Добавляем атрибуты
        $newRow_DopSved->setAttribute('НомСтр', $StrNumber);
        $newRow_DopSved->setAttribute('НалСт', '20%');
        $newRow_DopSved->setAttribute('НаимТов', $real_name);
        $newRow_DopSved->setAttribute('ОКЕИ_Тов', '796');
        $newRow_DopSved->setAttribute('НаимЕдИзм', 'шт');
        $newRow_DopSved->setAttribute('КолТов', $item['count']);
        $newRow_DopSved->setAttribute('ЦенаТов', $NDS);
        $newRow_DopSved->setAttribute('СтТовБезНДС', $price_bez_NDS);
        $newRow_DopSved->setAttribute('СтТовУчНал', $item['FullPrice']);
    $newElement->appendChild($newRow_DopSved);

            // Добавляем подэлементы СведТов 
            $newRow_DopSvedTov = $dom->createElement('ДопСведТов', '');
                $newRow_DopSvedTov->setAttribute('КодТов', $item['barcode']);
                $newRow_DopSved->appendChild($newRow_DopSvedTov);

            // Добавляем подэлементы Акциз 
            $newRow_Akciz = $dom->createElement('Акциз', '&#xA;');
            $newRow_DopSved->appendChild($newRow_Akciz);
            $newRow_BEZ_Akciz = $dom->createElement('БезАкциз', 'без акциза');
            $newRow_Akciz->appendChild($newRow_BEZ_Akciz);

            // Добавляем подэлементы Акциз 
            $newRow_SumNal = $dom->createElement('СумНал', '&#xA;');
            $newRow_DopSved->appendChild($newRow_SumNal);
            $newRow_SumNal_2 = $dom->createElement('СумНал', $NDS);
            $newRow_SumNal->appendChild($newRow_SumNal_2);




    }

    $summa_bez_nds = $Summa - $Summa_NDS;
// Добавляем подэлементы ВсегоОпл **************************************************************
   $newRow_Vsego_opl = $dom->createElement('ВсегоОпл', '&#xA;');
    $newRow_Vsego_opl->setAttribute('СтТовБезНДСВсего', $summa_bez_nds);
    $newRow_Vsego_opl->setAttribute('СтТовУчНалВсего', $Summa);
    $newElement->appendChild($newRow_Vsego_opl);

                // // Добавляем подэлементы СумНалВсего 
                $newRow_SumNalVsego = $dom->createElement('СумНалВсего', '&#xA;');
                $newRow_Vsego_opl->appendChild($newRow_SumNalVsego);
                $newRow_SumNal_3 = $dom->createElement('СумНал', $Summa_NDS);
                $newRow_SumNalVsego->appendChild($newRow_SumNal_3);

              
// Заменяем старый элемент новым
    $oldElement->parentNode->replaceChild($newElement, $oldElement);

    // Принудительное добавление переносов строк
    $xmlString = $dom->saveXML();
    $xmlString = preg_replace('/></', ">\n<", $xmlString); // Добавляем перенос строк между тегами

    // Сохраняем измененный XML с переносами строк
    $updatedPath = 'test_new.xml';
    file_put_contents($updatedPath, $xmlString);

    echo "Элемент <ТаблСчФакт> успешно заменен! Файл сохранен: " . $updatedPath;
} else {
    echo "Элемент <ТаблСчФакт> не найден!";
}



echo "<br><a href=\"test_new.xml\">ОТКРЫТЬ XML_FILE</a><br><br>";


$zip_new = new ZipArchive();
$zip_new->open("reports/XML_upd_№".$nomerChF." от ".date("Y-M-d").".zip", ZipArchive::CREATE|ZipArchive::OVERWRITE);
$file_zip_name = "UPD_№".$nomerChF." от ".date("Y-M-d").".xml";
$zip_new->addFile('test_new.xml', "$file_zip_name"); // добавляем для НОВЫЙ 1С файл /// *****************

$link_path_zip2 = "reports/XML_upd_№".$nomerChF." от ".date("Y-M-d").".zip"; //  ссылка чтобы скачать архив

echo <<<HTML
<br>
<a href="$link_path_zip2">Cкачать архив с XML файлом</a>
<br><br>
HTML;

// echo "<br><b><a href=\"kach.php\">XML_FILE</a></b><br><br>";

