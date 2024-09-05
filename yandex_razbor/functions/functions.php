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
// $substatus = 'substatus=READY_TO_SHIP'; // Когда нужно обработатть товары которые уже готовы к отправке
$page=1;
$ya_link = 'https://api.partner.market.yandex.ru/campaigns/'.$campaignId.'/orders/?'.$substatus."&page=".$page ;

$orders = get_query_without_data($ya_token, $ya_link);

// перебираем все страницы заказов
for ($page=1; $page <= $orders['pager']['pagesCount']; $page++) {

    $ya_link = 'https://api.partner.market.yandex.ru/campaigns/'.$campaignId.'/orders/?'.$substatus."&page=".$page ; 
     
    $orders2 = get_query_without_data($ya_token, $ya_link);  
    foreach ($orders2['orders'] as $order ) {
        $result['orders'][] = $order;
    }

}

// echo "КОЛИЧЕСТВО ЗАКАЗОВ ЗА ВСЕ ВРЕМЯ = ".count($result['orders'])." <br>";
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
//  $new_path = 'reports/'.$date_query_yandex."/".$zakaz_1c_number."/";
 $new_path = '../!all_razbor/yandex/'.$date_query_yandex."/".$zakaz_1c_number."/";

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
//   echo "<pre>";
// print_r($array_orders);
foreach ($array_orders as $items)  {
    $new_article = change_sku_for_1c_article($items['offerId']);
    $new_array_orders[$new_article]['summa'] = round(@$new_array_orders[$new_article]['summa'] + ($items['buyerPrice'] + $items['subsidy'])*$items['count'],0);
    $new_array_orders[$new_article]['count'] = @$new_array_orders[$new_article]['count'] + $items['count'];
    $new_array_orders[$new_article]['offerName'] = $items['offerName'];
    $new_array_orders[$new_article]['buyerPrice'] =  round($new_array_orders[$new_article]['summa'] /  $new_array_orders[$new_article]['count'],0); 
}


    echo <<<HTML
    <link rel="stylesheet" href="yandex_razbor/css/style.css">
    <link rel="stylesheet" href="css/style.css">
    <h2>Все товары на дату: $date_orders</h2>
    <table class="">

    <tr>
        <th>пп</th>
        <th>артикул</th>
        <th>Наименование</th>
        <th>Кол-во</th>
        <th>Сред.цена за шт <br> с субсибиями</th>
        <th>Стоимость</th>
    </tr>
    HTML;
    $i=1;
    $summa_tovarov = 0;
    $kolichestvo_tovarov = 0;
    
    foreach ($new_array_orders as $key=>$items)  {
    echo "<tr>";
        echo "<td>".$i."</td>";
        echo "<td>".$key."</td>";
        echo "<td>".$items['offerName']."</td>";
        echo "<td>".$items['count']."</td>";
        echo "<td>".$items['buyerPrice']."</td>";
        echo "<td>".$items['buyerPrice']*$items['count']."</td>";

    
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
        <td>$kolichestvo_tovarov шт</td>
        <td></td>
        <td>$summa_tovarov руб</td>
        
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


 /********************************************************************************************
 ******************* Выводим таблицу заказам  ***********************************
 *************************************************************************************************/
function print_table_with_ALL_orders ($array_mass_orders, $date_orders) {
//     echo "<pre>";
// print_r($array_mass_orders);


    echo <<<HTML
    <link rel="stylesheet" href="yandex_razbor/css/style.css">
    <link rel="stylesheet" href="css/style.css">
    <h2>Все заказы $date_orders</h2>
    <table class="">
    <tr>
        <th>пп</th>
        <th>номер <br> заказа</th>
        <th>Дата <br> отгрузки</th>
        <th>артикул</th>
        <th>Название товара</th>
        <th>Кол-во</th>
        <th>Цена</th>
    </tr>
    HTML;
    $i=1;
    $summa_tovarov = 0;
    $kolichestvo_tovarov = 0;
    

    $i=1;
    foreach ($array_mass_orders as $key=>$items) {
    //  print_r($item);
    $count_td = count($items['data']);
    $j1=0;
    echo "<tr>";
    echo "<td rowspan=\"$count_td\">$i</td>
    <td rowspan=\"$count_td\">".$key."</td>
    <td rowspan=\"$count_td\">".$items['date_delivery']."</td>";
  

    // echo "<td>";
    // echo "<table>";
    foreach ($items['data'] as $prods) {

        $j1++;
        if ($j1 > 1) {
           echo "<tr>"; 
        }
        echo "<td>".$prods['offerId']."</td>";
        echo "<td>".$prods['offerName']."</td>";
        echo "<td>".$prods['count']."</td>";
        echo "<td>".number_format($prods['buyerPrice'],2)."</td>";
        if ($j1 >1) {
            echo "</tr>"; 
         }
        
    }
    
    // echo "</table>";
    // echo "</td>";
    echo "</tr>";
    $i++;
    }
    
    echo "</table>";

}
    
/**********************************************************
 * Формируем массив товаров в ценами для 1С 
 ********************************************************/
function make_array_sell_items_for_1c ($array_mass_orders) {
    foreach ($array_mass_orders as $key=>$items) {
        // print_r($items);
        foreach ($items['data'] as $prods) {
                $sell_tovari[$prods['offerId']]['price'] =  @$sell_tovari[$prods['offerId']]['price'] + ($prods['price'] + $prods['subsidy'])*$prods['count'];
                $sell_tovari[$prods['offerId']]['count'] =  @$sell_tovari[$prods['offerId']]['count'] + $prods['count'];
        }
    }  
// высчитываем среднюю цену продажи 
foreach ($sell_tovari as &$item) {

$item['middle_price'] = round($item['price'] /$item['count'] ,0);


}
    

   return $sell_tovari ;
}


/**********************************************************
 * Формируем массив товаров в ценами для 1С 
 ********************************************************/
function make_array_sell_items ($arr_all_new_orders , $need_date) {
    foreach ($arr_all_new_orders['orders'] as $order) { // перебираем все новые заказы
    
        // формируем массиов товаров по заказам 
        $arr_mass_orders[$order['id']]['data'] = $order['items'];
        $arr_mass_orders[$order['id']]['date_delivery'] = $order['delivery']['shipments'][0]['shipmentDate'];
        
        
        // формируем массиов товаров общим переченем
        
            $orderId = $order['id']; // ID  выбранного заказа
            $item_number = 0; // порядквый номер товаров, если их несколько
            $need_ship_date = $order['delivery']['shipments'][$item_number]['shipmentDate'];
            $id_shipment = $order['delivery']['shipments'][$item_number]['id'];
          
                if ($need_date == $need_ship_date)  {    /// выбор даты дня отгрузки
        
        // формируем массиов товаров по заказам 
        $arr_mass_one_date_orders[$order['id']]['data'] = $order['items'];
        $arr_mass_one_date_orders[$order['id']]['date_delivery'] = $order['delivery']['shipments'][0]['shipmentDate'];
        
        
                  
                    foreach ($order['items'] as $items) { // перебираем все товары из выбранного заказа
                       unset ($items['subsidies']);
                        $arr_all_items[] = $items;
                    }
                }
        
        }

isset($arr_mass_orders)?$return_arrays['arr_mass_orders'] =  $arr_mass_orders:$x=0;
isset($arr_mass_one_date_orders)?$return_arrays['arr_mass_one_date_orders'] =  $arr_mass_one_date_orders:$x=0;
isset($arr_all_items)?$return_arrays['arr_all_items'] =  $arr_all_items:$x=0;


return $return_arrays;
    }