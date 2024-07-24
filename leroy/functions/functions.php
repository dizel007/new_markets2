<?php

/*
*Функция запроса c передаваемыми данными  
*/ 

function query_with_data ($jwt_token, $link, $data_send, $message) {
$ch = curl_init($link);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'x-api-key: b1VSXCMYNYr6H3h0pBLaUczXYEATcS58',
        'Content-Type: application/json',
        'User-Agent: PostmanRuntime/7.32.2',
		"Authorization: Bearer $jwt_token"
    ));
  
  

    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data_send);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res11 = curl_exec($ch);

   $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код

	curl_close($ch);
	
	$res11 = json_decode($res11, true);

    echo     "Результат обмена [".$message."] : ".$http_code. "<br>";

// echo "<pre>";
//    print_r($res11);	
// echo "<pre>";
return $res11;
}

/*
*Функция запроса без данных 
*/ 

$id_parcel = "";

// *******************************  Количество запрашиваемых заказов **************************************

// $dop_link = '?limit='.MAX_LIMIT_ORDERS;  // Дописваем максимальное количество Заказов

// $dop_link = '?limit='.MAX_LIMIT_ORDERS; 
$dop_link = '';
$link = 'https://api.leroymerlin.ru/marketplace/merchants/v1/parcels'.$id_parcel.$dop_link;

function light_query_without_data ($jwt_token, $link, $message) {
$ch = curl_init($link);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'x-api-key: b1VSXCMYNYr6H3h0pBLaUczXYEATcS58',
		'Content-Type: application/json',
		"Authorization: Bearer $jwt_token"
    ));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HEADER, false);
	$res = curl_exec($ch);

   $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
   curl_close($ch);
	
	$res = json_decode($res, true);

    echo     "Результат обмена [".$message."] : ".$http_code. "<br>";

    return $res;
}

function light_query_without_data_with_post ($jwt_token, $link, $message) {
  $ch = curl_init($link);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
          'x-api-key: b1VSXCMYNYr6H3h0pBLaUczXYEATcS58',
      'Content-Type: application/json',
      "Authorization: Bearer $jwt_token"
      ));
    curl_setopt($ch, CURLOPT_POST, 1);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HEADER, false);
    $res = curl_exec($ch);
  
     $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код
     curl_close($ch);
    
    $res = json_decode($res, true);
  
      // echo     "Результат обмена [".$message."] : ".$http_code. "<br>";
  
      return $res;
  }


/*
* Фунция формирования массива с заказами
*/
function get_create_spisok_from_lerua($jwt_token, $art_catalog, $ship_status) {
  $id_parcel = "";

  // *******************************  Количество запрашиваемых заказов **************************************
  
  // $dop_link = '?limit='.MAX_LIMIT_ORDERS;  // Дописваем максимальное количество Заказов

  $dop_link = '?limit='.MAX_LIMIT_ORDERS; 
  // $dop_link = '';
  $link = 'https://api.leroymerlin.ru/marketplace/merchants/v1/parcels'.$id_parcel.$dop_link;
  
  $list_all_sending = light_query_without_data ($jwt_token, $link, 'Список всех отправлений');




  echo "<pre>";
print_r($list_all_sending );
// die('fffffffffffffff');
// 
  // Перебираем все отправления  и ищем новые (созданные)
  $dop_link = '/statuses';
  // echo "<pre>";
  foreach ($list_all_sending as $item) {

    
    // print_r($item);
    // echo "*************************************************";
      // смотрим статус каждого отправления 
      $id_parcel = $item['id'];
      $link = 'https://api.leroymerlin.ru/marketplace/merchants/v1/parcels/'.$id_parcel.$dop_link;
  
      $status_item = light_query_without_data ($jwt_token, $link, 'Статус отпраления');
      // print_r($status_item);
      $item['status'] =  $status_item[0]['name'];
      // если отправление не подтверждено, то добавляем его в новый массив
      
      // if (($item['status'] == $ship_status) && ($item['pickup']['pickupDate'] == '2023-07-17'))  { // Если нужно собрать на определенную дату
      
        if (($item['status'] == $ship_status))  {
      
      $new_array_create_sends[] = $item;
      }
  
  }


  // Подставляем наименование товара в массив
  if (isset($new_array_create_sends)){
  foreach ($new_array_create_sends as &$items) {
      foreach ($items['products'] as &$prods) {
      //  echo $prods['vendorCode']."9999<br>";
       $prods['name'] = $art_catalog[$prods['vendorCode']];
      
      }
   }
   return $new_array_create_sends;
  } 
  
}

/*
* Фунция формирования массива с заказами
*/
function get_create_spisok_with_need_date($array_zakaz, $data_send) {
foreach ($array_zakaz as $item) {

  if ($item['pickup']['pickupDate'] == $data_send) {
    $new_array_zakaz[]=$item;
  }
}
return $new_array_zakaz;
}




/*
*Функция которая в зависимости от товара разбивает его по количеству
*/ 

function make_right_posts_gruzomesta_NEW ($id_post, $post_array) {

$const_K_8 = 6;
$const_K_12 = 8;
$const_K_16 = 10;

$sku_K_8 = 90502008;
$sku_K_12 = 90502007;
$sku_K_16 = 90502006;




// формируем массив для каждой позиции товара
foreach ($post_array as $products) {

    for ($i=0; $i < $products['qty']; $i++) {
        // echo "i-".$i."<br>";

/* 
  7260 >= 12 штук 
 */
  if ( ($products['lmId'] == $sku_K_12 ) AND ($products['qty'] >= $const_K_12 ) ) {
    $array_tovar['products'][0] = array(
        "sku" => $products['lmId'],
        "quantity" => $const_K_12
      );
      $data_send[] = $array_tovar;
            $i = $i - 1 ; // увеличиваем на количество товаров в упаковке
            $products['qty'] = $products['qty'] - $const_K_12;

 /* 
  7260 МЕНЕЕ 12 штук 
 */

    } elseif ( ($products['lmId'] == $sku_K_12 ) AND ($products['qty'] < $const_K_12 ) ) {

        $array_tovar['products'][0] = array(
            "sku" => $products['lmId'],
            "quantity" => $products['qty']
          );
          $data_send[] = $array_tovar;
          $i=$i + $products['qty']; // все товары закидываем в последнюю посылку
   

    /*
     *****   7245  >=16 штук  *******************************
      */
        }  elseif ( ($products['lmId'] == $sku_K_16 ) AND ($products['qty'] >= $const_K_16 ) ) {

            $array_tovar['products'][0] = array(
                "sku" => $products['lmId'],
                "quantity" => $const_K_16
              );
        $data_send[] = $array_tovar;
        $i=$i - 1; // увеличиваем на количество товаров в упаковке
        $products['qty'] = $products['qty'] - $const_K_16;


 /* 
  7245 МЕНЕЕ 16 штук 
 */

} elseif ( ($products['lmId'] == $sku_K_16 ) AND ($products['qty'] < $const_K_16 ) ) {

    $array_tovar['products'][0] = array(
        "sku" => $products['lmId'],
        "quantity" => $products['qty']
      );
      $data_send[] = $array_tovar;

  $i=$i + $products['qty']; // все товары закидываем в последнюю посылку
  
    
    /* ********************************   Смотрим есть ли у нас метровый бордюр 7280-К-8 в количество 8 штук  ******************************* */
        }      elseif ( ($products['lmId'] == $sku_K_8 ) AND ($products['qty'] >= $const_K_8 ) ) {
        
            $array_tovar['products'][0] = array(
                "sku" => $products['lmId'],
                "quantity" => $const_K_8
              );
        $data_send[] = $array_tovar;
                $i=$i - 1; // увеличиваем на количество товаров в упаковке
                $products['qty'] = $products['qty'] - $const_K_8;
                
                }
 /* 
  7245 МЕНЕЕ 16 штук 
 */

 elseif ( ($products['lmId'] == $sku_K_8 ) AND ($products['qty'] < $const_K_8 ) ) {

    $array_tovar['products'][0] = array(
        "sku" => $products['lmId'],
        "quantity" => $products['qty']
      );
      $data_send[] = $array_tovar;

  $i=$i + $products['qty']; // все товары закидываем в последнюю посылку
       
  
  // Все остальгные отправления делаем по 1 штуке
} else {
    $array_tovar['products'][0] = array(
        "sku" => $products['lmId'],
        "quantity" => 1
      );
      $data_send[] = $array_tovar;
            
        }
             }
}

// echo "<pre>";
// print_r($data_send);
// echo "<pre>";
return $data_send;
}




