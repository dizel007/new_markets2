<?php
/**
 * Блок который добавляет товары с отчета в XML
 */

// require_once "functions/wb_catalog.php";
$arr_name_cat = $wb_catalog;
$json_xml=file_get_contents('tovari.json');
$xml_data = json_decode($json_xml, true);

$xml = new SimpleXMLElement('<СведТов/>');


$ttt = '<ТаблСчФакт>';
file_put_contents('test.xml',$ttt, FILE_APPEND);   
$StrNumber = 0;

$Summa = 0;
foreach ($xml_data as $item) {


// print_r($item);


    $StrNumber++;

$NDS = round($item['FullPrice'] * 20 / 120, 2);
$price_bez_NDS = $item['FullPrice'] - $NDS;





// **** Подставляем наименование и артикул *******************
foreach ($arr_name_cat as $name) {
    if ($item['key'] == $name['mp_article'] ) {
    $real_name = $name['main_article'].' '.$name['mp_name'];
    $real_name = str_replace("\"", "", $real_name);
    break;
    } else {
        $real_name = "NO DATA";
    }
}

// echo $real_name;

$ttt = '<СведТов НомСтр="'.$StrNumber.'" НаимТов="'.$real_name.'" ОКЕИ_Тов="796" КолТов="'.$item['count'].'" СтТовБезНДС="'.$price_bez_NDS.
'" НалСт="20%" СтТовУчНал="'.$item['FullPrice'].'">
<Акциз>
<БезАкциз>без акциза</БезАкциз>
</Акциз>
<СумНал>
<СумНал>'.'1'.'</СумНал>
</СумНал>
<ДопСведТов КодТов="'.$item['barcode'].'" НаимЕдИзм="шт"/>
</СведТов>';

file_put_contents('test.xml',$ttt, FILE_APPEND);   

// Считаем сумму и НДС
$Summa = $Summa + $item['FullPrice'];
$Summa_NDS = @$Summa_NDS + $NDS;

// if ($StrNumber ==7) {break;}
}

// $Summa_NDS = round($Summa * 20 / 120, 2);
$Summa_bez_NDS = $Summa - $Summa_NDS;

$ttt ='<ВсегоОпл СтТовБезНДСВсего="'.$Summa_bez_NDS.'" СтТовУчНалВсего="'.$Summa.'">
<СумНалВсего>
<СумНал>'.$Summa_NDS.'</СумНал>
</СумНалВсего>
</ВсегоОпл>';
file_put_contents('test.xml',$ttt, FILE_APPEND); 



$ttt = '</ТаблСчФакт>';
file_put_contents('test.xml',$ttt, FILE_APPEND); 
// echo "<pre>";
// print_r($new_arr_xml[0]);



function new_xml_array($StrNumber, $ProductName, $FullPrice, $barcode, $ProductCount){
    $price_bez_NDS = $FullPrice - $FullPrice*0.2;
    $NDS = $FullPrice - $price_bez_NDS;
     $mod_array = array (
         '@attributes' => array(
             'НомСтр' => $StrNumber,
             'НаимТов' => $ProductName,
             'ОКЕИ_Тов' => "796",
             'КолТов' => $ProductCount,
             'СтТовБезНДС' => $price_bez_NDS,
             'НалСт' => "20%",
             'СтТовУчНал' => $FullPrice
         ),
         'Акциз' => array(
             'БезАкциз' => 'без акциза'
         ),
         'СумНал' => array(
             'СумНал' => $NDS
         ),
         'ДопСведТов' => array(
             '@attributes' => array(
                     'КодТов' => $barcode,
                     'НаимЕдИзм' => "шт"
                 )
         )
     );
     return $mod_array; 
 }
 function arrayToXml($array, $xml) {
    echo "<pre>";
    $_xml = $xml;
     
    // If there is no Root Element then insert root
    // if ($_xml === null) {
    //     echo "cjplftv XML";
    //     $_xml = new SimpleXMLElement($rootElement !== null ? $rootElement : '<СведТов/>');
    // }
     
    // Visit all key value pair
    foreach ($array as $k => $v) {
       
        // If there is nested array then
        if ($k == '@attributes' ) {
            foreach ($v as $attr_key => $attribut) {
                echo "<br>********************************************** 111 *******************************************<br>";
                print_r($attr_key);
              

                print_r($attribut);
                if (is_array($attribut)) {
                    echo "<br>********************************************** 222 *******************************************<br>";
                    foreach ($attribut as $one_attr_key=>$one_attr) {

                        if (is_array($one_attr)) {
                          print_r($attr_key);  
                            foreach ($one_attr as $two_attr_key=>$two_attr) {
                                echo "<br>********************************************** 333 *******************************************<br>";

                                $_xml->addAttribute($two_attr_key, $two_attr);
                            } 
                        }else {

                        $_xml->addAttribute($one_attr_key, $one_attr);
                            }
                    }

                } else {
                $_xml->addAttribute($attr_key, $attribut);
                }
            }
       }

        if ((is_array($v)) && ($k != '@attributes')) {
             
            // Call function for nested array
            arrayToXml($v, $k, $_xml->addChild($k));
            }
             
        else {
                // Simply add child element. если массив не содержит признак атрибута
           if  ($k != '@attributes'){
            $_xml->addChild($k, $v);
           }
        }
    }
     
    return $_xml->asXML('1111111111.xml');
}


