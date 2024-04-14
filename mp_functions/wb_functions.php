<?php 


/****************************************************************************************************************
**************************** Получаем все новые заказы **************************************
****************************************************************************************************************/

function get_all_new_zakaz ($token_wb) {
	$link_wb = 'https://suppliers-api.wildberries.ru/api/v3/orders/new';
	$res = light_query_without_data($token_wb, $link_wb);
	return $res;
}




/*******************************************************************************************************
********      Достаем фактические остатки товаров и цепляем их к каталогу товаров***********************
*******************************************************************************************************/
function get_ostatki_wb ($token_wb, $wb_catalog, $warehouseId) {
	
		// echo $warehouseId;
	// формируем массив с запрашиваемыми баркодами
		foreach ($wb_catalog as $items) {
			$arr_skus[] = $items['barcode'];
		}
	 
		// print_r ($arr_skus);

    $link_wb  = "https://suppliers-api.wildberries.ru/api/v3/stocks/".$warehouseId;
	$data = array("skus"=> $arr_skus);
	$res = light_query_with_data($token_wb, $link_wb, $data);
	
	// Формируем массив для вывода на экран (артикулы, СКУ, имя, Баркод, количество)
		foreach ($res['stocks'] as $prods)  {
			foreach ($wb_catalog as &$items) {
				if ($prods['sku'] == $items['barcode']) {
					$items['quantity'] = $prods['amount'];
				}
			}
		}
	return $wb_catalog;
	}

/*******************************************************************************************************
********      Достаем фактические заказанные товары и цепляем их к каталогу товаров*********************
*******************************************************************************************************/
function get_new_zakazi_wb ($token_wb, $wb_catalog) {

    $link_wb = 'https://suppliers-api.wildberries.ru/api/v3/orders/new';
    $result = light_query_without_data($token_wb, $link_wb);
    
     // формируем массив ключ - артикул ; значение - количество элементов этого артикула

    foreach ($result['orders'] as $items_wb) {
        $arr_name[$items_wb['article']][]= $items_wb;
    // $sum = @$sum + $itemss['convertedPrice']/100;
    }
    unset($items_wb);

if (isset ($arr_name)) {  // проверяем есть ли массив проданных товаров
       foreach ($arr_name as $key => $temp_items) {
			//    print_r($temp_items);
           $arr_article_count[$key] = count($arr_name[$key]);
		   foreach ($temp_items as $perebor_prodazh){
		   	$arr_sum_article_sell[$key] = @$arr_sum_article_sell[$key] + $perebor_prodazh['convertedPrice'];
		   }

       }

	   
	//    print_r($arr_name);
	//    die();


       foreach ($arr_article_count as $key=>$prods)  {
           foreach ($wb_catalog as &$items_wb) {
               // echo "<br>key=$key<br>";
               if ($key == $items_wb['mp_article']) {
                $items_wb['sell_count'] = $prods;
				$items_wb['sell_summa'] = $arr_sum_article_sell[$key];

               } 
           }
    
       }
    }
return $wb_catalog;
}


/****************************************************************************************************************
**************************** Приводим артикулы в соотетствии с ВБ сайтом **************************************
****************************************************************************************************************/

function make_right_articl($article) {
	// КАНТРИ Макси 
		if ($article == '8240282402-ч' ) {
			$new_article = '82402-ч';
		} else if ($article == '8240282402-к' ) {
			$new_article = '82402-к';
		} else if ($article == '8240282402-з' ) {
			$new_article = '82402-з';
	// КАНТРИ Средний 
		} else if ($article == '8240182401-ч' ) {
			$new_article = '82401-ч';
		} else if ($article == '8240182401-з' ) {
			$new_article = '82401-з';
		} else if ($article == '8240182401-к' ) {
			$new_article = '82401-к';
	// КАНТРИ Мини 
		} else if ($article == '8240082400-к' ) {
			$new_article = '82400-к';
		} else if ($article == '8240082400-з' ) {
			$new_article = '82400-з';
		} else if ($article == '8240082400-ч' ) {
			$new_article = '82400-ч';
		} else if ($article == '82552-82552-к' ) {
				$new_article = '82400-к';
		


	// Приствольные круги     
		} else if ($article == '7262-КП(Л)' ) {
			$new_article = '7262-КП';
		} else if ($article == '7262-КП(У)' ) {
			$new_article = '7262-КП';
	
	// Якоря 
		} else if ($article == '8910-8910-30' ) {
			$new_article = '8910-30';
		} else if ($article == '1840-301840-30' ) {
			$new_article = '1840-30';
		} else if ($article == '1940_1940-10' ) {
			$new_article = '1940-10';
	// Метровые борды
		} else if ($article == '7245-К7245-К-16' ) {
			$new_article = '7245-К-16';
		} 
		else if ($article == '7260-К-7260-К-12' ) {
			$new_article = '7260-К-12';
		} 
		else if ($article == '7260-К7260-К-12' ) {
			$new_article = '7260-К-12';


		} else if ($article == '7280-К7280-К-80' ) {
			$new_article = '7280-К-8';
			
		} else if ($article == '7280-К-7280-К-8' ) {
			$new_article = '7280-К-8';
		
		} else if ($article == '7282-к-6-18' ) { // ошибочный артикул на ВБ ИП 
		$new_article = '7280-к-6-18';
		} 
	
	// Вся неучтенка    
		
		else {
			$new_article = $article;
		}
	
		return $new_article;
	}
