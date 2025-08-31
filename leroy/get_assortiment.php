<?php
/*************************************************************
 * GПрограмка для обновления остатков товаров в Леруа
 * Работакт пплохо со стороны Леруа
 * И ограничене либо она работает, либо в ручную с кабинета,
 * в итоге с кабинета удобнее и всегда работает
 **********************************************************/

require_once "../connect_db.php";
require_once "functions/parce_excel_sklad_lerua_json.php";
require_once "functions/functions.php";
require_once "paass.php";


echo <<< HTML
<html>
  <head>
    <meta charset="utf-8" />
    <title>Обновление остаток МП</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	  <link rel="stylesheet" href="css/input_forma.css"/>
    
  </head>
  <body>
    <div class="container">
      <h1 class="form-title">Обновление остатков в леруа </h1>

      <form action= "get_assortiment.php" method="post" enctype="multipart/form-data"> 
	  
	  <div class="file_input_form">   
              <input  class="file_input_button" type="file" name="file_excel" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
			  </div>
        <div class="form-submit-btn">
			<input type="submit" value="Обновить данные">	
        </div>

      </form>
    </div>
  </body>
</html>

HTML;


if (isset($_FILES['file_excel'])) {
  $uploaddir = "upload_lerua/";
  $uploadfile = $uploaddir . basename($_FILES['file_excel']['name']);

  if (move_uploaded_file($_FILES['file_excel']['tmp_name'], $uploadfile)) {
    echo "Файл с остатками товаров, УСПЕШНО ЗАГРУЖЕН<br>";
    $xls = PHPExcel_IOFactory::load($uploadfile);
  } 
  
} else {
  echo "нет загруженного файла";
    die();
}


/// ///////////////////////////////////////////////////////////////////////////////////////


$link = 'https://api.lemanapro.ru/marketplace/api/v1/products/assortment';
$message = 'Запрос ассортимента товаров';

$catalog_lerua = light_query_without_data($access_token, $link, $message);

echo "<pre>";

// убираем лишние позиции
foreach ($catalog_lerua['result']['products'] as $items) {
  if (strpos($items['productId'], '0000')) {
    continue;
  }
  $new_catp[$items['productId']]['productId'] = $items['productId'];
  $new_catp[$items['productId']]['marketplaceId'] = $items['marketplaceId'];
}


// цепояем ексель файл 
// $xls = PHPExcel_IOFactory::load('22222.xlsx');
$sklad_from_excel_file = Parce_excel_1c_sklad_for_lerua($xls);
/// оставляем только склад леруа




// добавляем сток 
foreach ($new_catp as $key => &$itemdata) {
  foreach ($sklad_from_excel_file as $key_excel => $exceldata) {
    if (mb_strtolower($key) == mb_strtolower($key_excel)) {
      $itemdata['stock'] = $exceldata;
      unset($itemdata['productId']);
      break 1;
    } else {
      $itemdata['stock'] = 0;
      unset($itemdata['productId']);
    }
  }
}

// формируем массив для апдейта
foreach ($new_catp as $key => $itemdata) {

  $array_for_update_stoks[] = $itemdata;
}

// print_r($sklad_from_excel_file);
print_r($array_for_update_stoks);



$data_send  = array(
  "data" => array(
    "products" => ($array_for_update_stoks)
  )
);

$data_send_json = json_encode($data_send);

// print_r($data_send);
// die();


$link_stoks = "https://api.lemanapro.ru/marketplace/api/v1/products/stock";
$access_token = '059a02c7-a9f5-4e2a-8f43-8a64031a0db3';

$reddd = query_with_data_POST($access_token, $link_stoks, $data_send_json, $message);


print_r($reddd);


function query_with_data_POST($token_lerua, $link, $data_send, $message)
{
  $ch = curl_init($link);
  curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'x-api-key: b1VSXCMYNYr6H3h0pBLaUczXYEATcS58',
    'Content-Type: application/json',
    'User-Agent: PostmanRuntime/7.32.2',
    "Authorization: Bearer $token_lerua"
  ));



  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
  curl_setopt($ch, CURLOPT_POSTFIELDS, $data_send);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($ch, CURLOPT_HEADER, false);
  $res_obmen = curl_exec($ch);

  $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Получаем HTTP-код

  curl_close($ch);

  $res_obmen = json_decode($res_obmen, true);

  if (($http_code != 200) && ($http_code != 201) && ($http_code != 204)) {
    echo     '<br> Результат обмена (' . $message . '): ' . $http_code;
  }

  return $res_obmen;
}
