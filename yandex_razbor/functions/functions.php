<?php


/********************************************************************************************
 * ****************** Получаем информацию о заказее 
 *************************************************************************************************/


function get_info_about_order($ya_token, $campaignId, $orderId) {

    $ya_link = 'https://api.partner.market.yandex.ru/campaigns/'.$campaignId.'/orders/'.$orderId ;

$result = get_query_without_data($ya_token, $ya_link);
return $result; 

}



/********************************************************************************************
 * ****************** Вычитываем все новые заказы с ЯНДЕКСА
 *************************************************************************************************/
function get_new_orders($ya_token, $campaignId) {

$substatus = 'substatus=STARTED';
$ya_link = 'https://api.partner.market.yandex.ru/campaigns/'.$campaignId.'/orders/?'.$substatus ;

$result = get_query_without_data($ya_token, $ya_link);

return $result;
}



/********************************************************************************************
 ******************* Разбиваем заказы по грузоместаи 
 *************************************************************************************************/
function razbivaev_zakazi_po_gruzomestam ($ya_token, $campaignId, $orderId, $item_from_order) {
// echo $orderId;
// echo "<br>";
// echo $campaignId;
// echo "<br>*********************************************************<br>";
// echo "<pre>";
// print_r ($item_from_order);



foreach ($item_from_order as $item_in_order){
    for ($i = 0; $i < $item_in_order['count'] ; $i++) { // перебираем все количество этого артикула, и разбиваем по грузоместам
    $arr_one_gruz_place['items'][0] =  
    array ("id" => $item_in_order['id'],
           "fullCount"=> 1,
    );

    $arr_boxes_all["boxes"][] = $arr_one_gruz_place;
    }
}

$arr_boxes_all["allowRemove"] = false; // добавляем параметр, что мы ничего из поставки не удаляем.

// echo "<br>************************ arr_boxes_all *********************************<br>";
// print_r($arr_boxes_all);


$ya_link = 'https://api.partner.market.yandex.ru/campaigns/'.$campaignId.'/orders/'.$orderId.'/boxes';
$res = put_query_with_data($ya_token, $ya_link, $arr_boxes_all) ;


// print_r($res); 
    return $res;
    }


/********************************************************************************************
 ******************* Получаем все этикетки одного артикула 
 *************************************************************************************************/
function get_yarliki_odnogo_artikula ($ya_token, $campaignId, $arr_one_article, $dir) {
    // $campaignId = 22076999;
   
  $count_items=0; 


    foreach ($arr_one_article as $items) {
        
        $new_article = change_sku_for_1c_article($items['offerId']);

        make_new_dir_z($dir."/".$new_article,0); // создали директорию для временных файлов

        $orderId =  $items['id_order'];
        $shipmentId =  $items['id_shipment'];
        $boxId =  $items['boxe'];

    $ya_link = 'https://api.partner.market.yandex.ru/campaigns/'.$campaignId.'/orders/'.$orderId.
                '/delivery/shipments/'.$shipmentId.'/boxes/'.$boxId.'/label?PageFormatType=A4';
    
    $result = get_shrih_code($ya_token, $ya_link);
    
    $file_link = $dir."/".$new_article."/".$new_article."(".$count_items.').pdf';
     file_put_contents($file_link, $result);
           $arr_files_name[]= $file_link;

//////  Пробуем Написать артикул на Ярлыке ****************************************************
//////  Пробуем Написать артикул на Ярлыке ****************************************************
//////  Пробуем Написать артикул на Ярлыке ****************************************************
//////  ********************************************************************************

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  
    $count_items++;
 }
// функция по соеденению PDF файлов ******************************************************************************
 require "merge_pdf.php";


 
    return $file_merge_pdf_name ;
}



/********************************************************************************************
 ******************* Формируем директории в папке для этикеток екселей зипов ****************************
 *************************************************************************************************/

 function make_all_dir ($date_query_yandex, $zakaz_1c_number) {
 $date_query_yandex = date('Y-m-d');
//  $zakaz_1c_number = "0312";
 $new_path = 'reports/'.$date_query_yandex."/".$zakaz_1c_number."/";
 make_new_dir_z($new_path,0); // создаем папку с датой
 $path_etiketki = $new_path.'yarliki';
 make_new_dir_z($new_path,0); // создаем папку с датой

 $path_etiketki_merge = $new_path.'yarliki_merge';
 make_new_dir_z($path_etiketki_merge,0); // создаем папку с объеедененными PDF

 $path_excel_docs = $new_path.'excel_docs';
 make_new_dir_z($path_excel_docs,0); // создаем папку с датой
 $path_zip_archives = $new_path.'zip_archives';
 make_new_dir_z($path_zip_archives,0); // создаем папку с датой

 $arr_dir = array ("order_dir" =>  $new_path,
                    "yarliki" =>  $path_etiketki,
                    "yarliki_merge" =>  $path_etiketki_merge,

                    "excel_docs" =>  $path_excel_docs,
                    "zip_archives" =>  $path_zip_archives
 );
 return  $arr_dir;
 }
  

 function make_new_dir_z($dir, $append) {
     //    echo "<br>Создаем папку: $dir";
         if (!is_dir($dir)) {
             mkdir($dir, 0777, True);
         } 
     }        
     

/********************************************************************************************
 ******************* Выводим таблицу с заказами  ***********************************
 *************************************************************************************************/
function print_table_with_orders ($array_orders, $date_orders) {
    echo <<<HTML
    <link rel="stylesheet" href="yandex_razbor/css/style.css">
    <link rel="stylesheet" href="css/style.css">
    <table class="">
    <tr>
        <th>пп</th>
        <th>артикул</th>
        <th>Наименование</th>
         <th>Цена</th>
        <th>Кол-во</th>
    </tr>
    HTML;
    $i=1;
    $summa_tovarov = 0;
    $kolichestvo_tovarov = 0;
    
    foreach ($array_orders as $items)  {
    echo "<tr>";
        echo "<td>".$i."</td>";
        $new_article = change_sku_for_1c_article($items['offerId']);
        echo "<td>".$new_article."</td>";
        echo "<td>".$items['offerName']."</td>";
        echo "<td>".$items['buyerPrice']."</td>";
        echo "<td>".$items['count']."</td>";
    
    echo "</tr>";
    
    $i++;
    $summa_tovarov += $items['buyerPrice']*$items['count'];
    $kolichestvo_tovarov += $items['count'];
    
    }
    echo <<<HTML
    <tr>
        <td></td>
        <td></td>
        <td>Итого </td>
        <td>$summa_tovarov руб</td>
        <td>$kolichestvo_tovarov шт</td>
    </tr>
    HTML;
    
    echo "</table>";
    
    // print_r($arr_all_items);




    }        
    

    /***************************************************************************************************************
 ***************** Функция подмены артикула на 1с-ный
 **************************************************************************************************************/

 function change_sku_for_1c_article($article) {

	switch ($article)  {
		case '1282704105':
			$article = '7245-К-10-30';
			break;
		case '1282759434':
			$article = '7260-К-8-24';
				break;
		case '1282760677':
			$article = '7280-К-6-18';
				break;
	
	
	
	
	
			}

return $article;
 }
