<?php


/************************************************************************************************
 ******  Отрисовываем таблицу с ценами для ВБ************************************************
 ************************************************************************************************/
function print_table_with_prices_WB($wb_catalog, $token_wb, $wb_shop)
{

echo <<<HTML
<link rel="stylesheet" href="css/print_table.css">
<h1 class="text-center">Таблица корректировка цен для : $wb_shop</h1>
  <form action="update_data_wb.php" method="POST">
    <table class="table-fill">


  <tr>
      <th class="width_article text-center">Артикул МП</th>
      <th class="text-center">Цена с БД</th>
      <th class="text-center">Скидка</th>
      <th class="text-center">Цена со скидкой с БД</th>
      <th class="width_date text-center">Дата в БД</th>
   
      <th class="text-center">Цена ВБ</th>
      <th class="text-center">Скидка ВБ</th>
      <th class="text-center">Цена со скидкой в ВБ</th>
      <th class="text-center">Разница цен</th>
      <th class="text-center">Артикул МП</th>
      <th class="text-center">CheckBox</th>
  </tr>
HTML;


  $p = 0;

  foreach ($wb_catalog as $item) {
    // echo $item['sku'],"<br>";
    $check_box = 0; // флаг чтобы обновлять цену и скидку
    $dis_price_now_DB = round($item['dis_price_now_DB'],0); 
    $dis_price_now_WB = round($item['dis_price_now_WB'],0);
    $delta_discount_prices =  round($dis_price_now_DB - $dis_price_now_WB,0);


    // Проверяем одинаковые ли цена на сайт и в БД
    $summa_price = round(($dis_price_now_DB -  $dis_price_now_WB), 0);
    if ($summa_price > 0) {
      $bolshe100 = 'bolshe100';
      // $check_box=1;
    } elseif ($summa_price < -0) {
      $bolshe100 = 'menshe0';
      // $check_box=0;
    } else {
      $bolshe100 = '';
      // $check_box=0;
    }
    


    echo "<tr class=\"\">";

    echo "<td class=\"$bolshe100 text-center\">" . $item['main_article'] . "</td>";
    // данные из БД
    echo  "<td class=\" text-center\">" . $item['price_now_DB'] . "</td>"; // цена до скидкт
    echo  "<td class=\"text-center \">" . $item['discount_now_DB'] . "</td>"; // скидка
    echo  "<td class=\" text-center\">" . $item['dis_price_now_DB'] . "</td>"; // цена со скидкой
    echo  "<td class=\" text-center\">" . $item['date_now_DB'] . "</td>";
    echo <<<HTML
<!-- Дельту цен загоняе мв в форму -->
<input type="hidden" readonly type="text" id="dis_price_now_DB{$p}" name = "dis_price_now_DB{$p}"  value="{$dis_price_now_DB}">

<!--  ЦЕНА НА САЙТЕ ВБ -->
 <td class="width_input">
  <input  class="input_width_vvod" onkeyup="CalculateItem();"  onkeydown="CalculateItem();" onchange="CalculateItem();" onfocus="CalculateItem();"
   type="number" step="1" min="0" max="9999" id="price_now_WB{$p}" name="price_now_WB{$p}" value ="{$item['price_now_WB']}" required>
</td>
<!--  СКИДКА НА ВБ -->
<td class="width_input">
    <input class="input_width_vvod"  required type="number" step="1" min="0" max="70" id="discount_now_WB{$p}" name="discount_now_WB{$p}"
     value ="{$item['discount_now_WB']}" onkeyup="CalculateItem();" onkeydown="CalculateItem();"
      onchange="CalculateItem();" onfocus="CalculateItem();">
</td>

 <!-- СКИДОЧНАЯ ЦЕНА  -->
<td class="width_input">
   <input class="input_width_raschet" readonly type="number" id="dis_price_now_WB{$p}" name = "dis_price_now_WB{$p}"  value="{$dis_price_now_WB}">
  </td>
<!-- РАЗНИЦА В ЦЕНАХ -->
<td class="width_input"> 
  <input  class="input_width_raschet" readonly type="number" id="delta_discount_prices{$p}" name = "_delta_discount_prices_{$p}"  value="{$delta_discount_prices}">
 </td>
HTML;

echo "<td class=\"$bolshe100 text-center\">" . $item['main_article'] . "</td>";


    // тезнические данные
    echo  "<input  type=\"hidden\" name=\"_sku_{$p}\" value=" . $item['sku'] . ">";
    echo  "<input  type=\"hidden\" name=\"type_question\" value=\"discount_update\">";
    echo  "<input  type=\"hidden\" name=\"token_wb\" value=\"$token_wb\">";
    echo  "<input  type=\"hidden\" name=\"wb_shop\" value=\"$wb_shop\">";




  // Кнопки checkBboxi
    if ($check_box == 1) {
    echo  "<td class=\"$bolshe100 text-center\"><input checked type=\"checkbox\" name=\"_need_update_{$p}\" value=\"\"></td>";
    } else {
      echo  "<td class=\"$bolshe100 text-center\"><input  type=\"checkbox\" name=\"_need_update_{$p}\" value=\"\"></td>";
    }
    $p++;

    echo "</tr>";
  }
    echo "</table>";


 echo <<<HTML
<div class="ccc">
<input class="atuin-btn" type="submit" value="ОБНОВИТЬ ДАННЫЕ НА ВБ">
</div>

HTML;
  echo "</form>";






echo <<<HTML
<!-- Подклюяаме Jquery -->
<script src="jquery.min.js"></script> 
<!-- РАсчитываем цену со скидкой и разницу в ценах -->
<script type="text/javascript">
            function CalculateItem()
            {
              Str_Number = event.target.id
              var Str_Number = Str_Number.replace(/\D/g, "");
              
              // alert(inputVat_name);
                try {
                    inputPriceNoVat = Math.round($('#price_now_WB' + Str_Number).val() -  $('#price_now_WB' + Str_Number).val()*$('#discount_now_WB'+ Str_Number).val()/100);
                    delta_discount_prices = Math.round($('#dis_price_now_DB' + Str_Number).val() - inputPriceNoVat);
                    // alert("333sss33=" + inputPriceNoVat);
                  //  console_log(inputPriceNoVat);
                 $('#dis_price_now_WB' + Str_Number).val(inputPriceNoVat);
                 $('#delta_discount_prices' + Str_Number).val(delta_discount_prices);

                //  CalculateSummaKp();

                } catch (e) {
                    $('#dis_price_now_WB' + Str_Number).val('cccccccc');
                    $('#delta_discount_prices' + Str_Number).val('cccccccc');
                }

            }
</script>

<!-- Блокируем отправку формы по ЭНТЕРУ -->
<script type="text/javascript">
$("input").keydown(function(event){
   if (event.keyCode === 13) {
      return false;
  }
}
)
</script> 

HTML;
}


/************************************************************************************************
 * *************** Получаем каталог их БД и берем цены с сайта ВБ *******************************
 ************************************************************************************************/
function get_wb_prices($pdo, $token_wb, $shop_name)
{
	// Получаем из БД каталог ВБ (для )
	$wb_catalog = get_catalog_tovarov_v_mp($shop_name, $pdo, 'active');

	// Достаем с ВБ фактические цены  
	$link_wb = "https://discounts-prices-api.wildberries.ru/api/v2/list/goods/filter?limit=100";
	$res = light_query_without_data($token_wb, $link_wb);
	// Цепляем эти цены к нашему каталогу
	foreach ($res['data']['listGoods'] as $item) {
		foreach ($wb_catalog as &$our_item) {

			if ($our_item['sku'] == $item['nmID']) {
				$our_item['price_now_WB'] = $item['sizes'][0]['price'];
				$our_item['dis_price_now_WB'] = $item['sizes'][0]['discountedPrice'];
				$our_item['discount_now_WB'] = $item['discount'];
				$our_item['date_now_WB'] = date('Y-m-d H:i:s');
			}
		}
	}
	// Достаем последние цены с нашей базы


	return $wb_catalog;
}

/************************************************************************************************
 ******  Обновляем цену и скидку на товар на сайте ВБ и в БД ************************************************
 ************************************************************************************************/
function update_prices_and_discount_inWB_and_inDB($token_wb, $arr_for_update)
{
foreach ($arr_for_update as $item) {
	$data = array("data"=> array(array(
		"nmID" => (int)$item['sku'],
		"price"=> (int)$item['pricenowWB'],
		"discount"=> (int)$item['discountnowWB']
	))
);

$link_wb = 'https://discounts-prices-api.wildberries.ru/api/v2/upload/task';
$res = light_query_with_data($token_wb, $link_wb, $data);
// print_r($res);
usleep(200);


}

}

/************************************************************************************************
 ******  Вставляем новую строку в БД  WB ************************************************
 ************************************************************************************************/

 function insert_data_in_prices_table_db_wb($pdo, $shop_name, $data_for_input)
 {
	 $article = $data_for_input['main_article'];
	 $sku = $data_for_input['sku'];
	 $price_old = $data_for_input['price_now_DB'];
	 $dis_price_old = $data_for_input['dis_price_now_DB'];
	 $discount_old = $data_for_input['discount_now_DB'];
	 $date_old = $data_for_input['date_now_DB'];
	 $price_now = $data_for_input['pricenowWB'];
	 $dis_price_now = $data_for_input['dispricenowWB'];
	 $discount_now = $data_for_input['discountnowWB'];
	 $date_now =  date('Y-m-d');
	 $date_stamp = date('Y-m-d H:i:s');
 
 
	 $stmt  = $pdo->prepare("INSERT INTO `mp_prices` (shop_name, sku, article, price_old, dis_price_old, discount_old, date_old, 
												 price_now, dis_price_now, discount_now, date_now, date_stamp)
										 VALUES (:shop_name, :sku, :article, :price_old, :dis_price_old, :discount_old, :date_old, 
												 :price_now, :dis_price_now, :discount_now, :date_now, :date_stamp)");
 
	 $stmt->bindParam(':shop_name', $shop_name);
	 $stmt->bindParam(':sku', $sku);
	 $stmt->bindParam(':article', $article);
	 $stmt->bindParam(':price_old', $price_old);
	 $stmt->bindParam(':dis_price_old', $dis_price_old);
	 $stmt->bindParam(':discount_old', $discount_old);
	 $stmt->bindParam(':date_old', $date_old);
	 $stmt->bindParam(':price_now', $price_now);
	 $stmt->bindParam(':dis_price_now', $dis_price_now);
	 $stmt->bindParam(':discount_now', $discount_now);
	 $stmt->bindParam(':date_now', $date_now);
	 $stmt->bindParam(':date_stamp', $date_stamp);
 
 
	 if (!$stmt->execute()) {
		 print_r($stmt->ErrorInfo());
		 die("<br>Померли на Инсерет в БД (WB)");
	 }
	 return $stmt;
 }


/************************************************************************************************
 ******  Вычитываем из БД самую свежую цену ************************************************
 ************************************************************************************************/
function select_last_data_from_db($pdo, $sku, $shop_name)
{
	$stmt = $pdo->prepare("SELECT * FROM `mp_prices` WHERE `sku` = '$sku' AND `shop_name` = '$shop_name' ORDER BY `date_stamp` DESC LIMIT 1");
	$stmt->execute([]);
	$tovar_table_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
	return $tovar_table_data;
}


/************************************************************************************************
 ******  Вставляем новую строку в БД  OZON  ************************************************
 ************************************************************************************************/

 function insert_data_in_prices_table_db_OZON($pdo, $shop_name, $data_for_input)
 {
	 $article =                 $data_for_input['main_article'];
	 $sku =                     $data_for_input['sku'];
   $product_id =              $data_for_input['product_id'];
	 $price_old =               $data_for_input['price_now_DB'];
	 $dis_price_old =           $data_for_input['dis_price_now_DB']; 

	 $price_na_mp_old =         $data_for_input['price_na_mp_old']; // //// OLD DB
	 $price_seller_na_mp_old =  $data_for_input['price_seller_na_mp_old']; ///////   OLD DB



	 $date_old =                $data_for_input['date_now_DB'];
	 $price_now =               $data_for_input['pricenow_OZON'];
	 $dis_price_now =           $data_for_input['dispricenow_OZON'];

   $price_na_mp_ozon =        $data_for_input['price_na_mp_ozon']; // //// OZON
	 $price_seller_na_mp_ozon = $data_for_input['price_seller_na_mp_ozon']; //////// OZON



	 $date_now =  date('Y-m-d');
	 $date_stamp = date('Y-m-d H:i:s');
 
 
	 $stmt  = $pdo->prepare("INSERT INTO `mp_prices` (shop_name, sku, product_id, article, price_old, dis_price_old, price_na_mp_old, 
   price_seller_na_mp_old, date_old,  price_now, dis_price_now, 
   price_na_mp_ozon, price_seller_na_mp_ozon, date_now, date_stamp)
										
                     VALUES (:shop_name, :sku, :product_id, :article, :price_old, :dis_price_old, :price_na_mp_old, :price_seller_na_mp_old,
                     :date_old, :price_now, :dis_price_now, :price_na_mp_ozon, :price_seller_na_mp_ozon, :date_now, :date_stamp)");
 
	 $stmt->bindParam(':shop_name', $shop_name);
	 $stmt->bindParam(':sku', $sku);
   $stmt->bindParam(':product_id', $product_id);
	 $stmt->bindParam(':article', $article);
	 $stmt->bindParam(':price_old', $price_old);
	 $stmt->bindParam(':dis_price_old', $dis_price_old);
	 $stmt->bindParam(':price_na_mp_old', $price_na_mp_old);
   $stmt->bindParam(':price_seller_na_mp_old', $price_seller_na_mp_old);
   $stmt->bindParam(':date_old', $date_old);
	 $stmt->bindParam(':price_now', $price_now);
	 $stmt->bindParam(':dis_price_now', $dis_price_now);
	 $stmt->bindParam(':price_na_mp_ozon', $price_na_mp_ozon);
   $stmt->bindParam(':price_seller_na_mp_ozon', $price_seller_na_mp_ozon);
   $stmt->bindParam(':date_now', $date_now);
	 $stmt->bindParam(':date_stamp', $date_stamp);
 
	 if (!$stmt->execute()) {
		 print_r($stmt->ErrorInfo());
		 die("<br>Померли на Инсерет в БД (OZON)");
	 }
	 return $stmt;
 }



 
/************************************************************************************************
 ******  Отрисовываем таблицу с ценами для ВБ************************************************
 ************************************************************************************************/
function print_table_with_prices_OZON($ozon_catalog, $ozon_shop)
{

echo <<<HTML
<link rel="stylesheet" href="css/print_table.css">
<h1 class="text-center">Таблица корректировка цен для : $ozon_shop</h1>
  <form action="update_data_ozon.php" method="POST">
    <table class="table-fill">


  <tr>
      <th class="width_article text-center">Артикул МП</th>
      <th class="text-center">Цена в ЛК БД</th>
      <th class="text-center">Цена Продавца БД</th>
      <th class="text-center">Цена на МП с БД</th>
      <th class="width_date text-center">Дата в БД</th>
   
      <th class="text-center">Цена в ЛК Озон</th>
      <th class="text-center">Цена Продавца Озон</th>
      <th class="text-center">Цена на МП Озон</th>
      <th class="text-center">Разница цен <br> Продавца</th>
      <th class="text-center">Артикул МП</th>
      <th class="text-center">CheckBox</th>
  </tr>
HTML;


  $p = 0;

  foreach ($ozon_catalog as $item) {
    // echo $item['sku'],"<br>";
    $check_box = 0; // флаг чтобы обновлять цену и скидку
    $seller_price_now_DB = round($item['price_seller_na_mp_ozon_DB'],0); 
    $dis_price_now_OZON = round($item['price_seller_na_mp_ozon'],0);
    $delta_discount_prices =  round($seller_price_now_DB - $dis_price_now_OZON,0);


    // Проверяем одинаковые ли цена на сайт и в БД
    $summa_price = round(($seller_price_now_DB -  $dis_price_now_OZON), 0);
    if ($summa_price > 0) {
      $bolshe100 = 'bolshe100';
      // $check_box=1;
    } elseif ($summa_price < -0) {
      $bolshe100 = 'menshe0';
      // $check_box=0;
    } else {
      $bolshe100 = '';
      // $check_box=0;
    }
    


    echo "<tr class=\"\">";

    echo "<td class=\"$bolshe100 text-center\">" . $item['mp_article'] . "</td>";
    // данные из БД
    echo  "<td class=\" text-center\">" . $item['price_now_DB'] . "</td>"; // цена в личном кабинете БД
    echo  "<td class=\"text-center \">" . $item['price_seller_na_mp_ozon_DB'] . "</td>"; // цена продавца в БД
    echo  "<td class=\" text-center\">" . $item['price_na_mp_ozon_DB'] . "</td>"; // цена на Маркете в БД
    echo  "<td class=\" text-center\">" . $item['date_old_DB'] . "</td>";
    echo <<<HTML
<!-- Дельту цен загоняе мв в форму -->
<input type="hidden" readonly type="text" id="seller_price_now_DB{$p}" name = "seller_price_now_DB{$p}"  value="{$seller_price_now_DB}">

<!--  ЦЕНА В ЛК ОЗОН -->
 <td class="width_input">
  <input  class="input_width_vvod" onkeyup="CalculateItem();"  onkeydown="CalculateItem();" onchange="CalculateItem();" onfocus="CalculateItem();"
   type="number" step="1" min="0" max="9999" id="price_now_OZON{$p}" name="price_now_OZON{$p}" value ="{$item['price_now_ozon']}" required>
</td>
<!--  ЦЕНА ПРОДАВЦВ НА ОЗОН -->
<td class="width_input">
    <input class="input_width_vvod"  required type="number" step="1" min="0" max="9999" id="price_seller_na_mp_ozon{$p}" name="price_seller_na_mp_ozon{$p}"
     value ="{$item['price_seller_na_mp_ozon']}" onkeyup="CalculateItem();" onkeydown="CalculateItem();"
      onchange="CalculateItem();" onfocus="CalculateItem();">
</td>

 <!-- ЦЕНА НА САЙТЕ ОЗОН (ДЛЯ ПОКУПАТЕЛЯ)  -->
<td class="width_input">
   <input class="input_width_raschet" readonly type="number" id="dis_price_now_OZON{$p}" name = "dis_price_now_OZON{$p}"  value="{$item['price_na_mp_old_DB']}">
  </td>
<!-- РАЗНИЦА В ЦЕНАХ -->
<td class="width_input"> 
  <input  class="input_width_raschet" readonly type="number" id="delta_seller_prices{$p}" name = "delta_seller_prices{$p}"  value="{$delta_discount_prices}">
 </td>
HTML;

echo "<td class=\"$bolshe100 text-center\">" . $item['main_article'] . "</td>";


    // тезнические данные
    echo  "<input  type=\"hidden\" name=\"sku{$p}\" value=" . $item['sku'] . ">";
    echo  "<input  type=\"hidden\" name=\"type_question\" value=\"discount_update\">";
    echo "<input type=\"hidden\" type=\"text\" name = \"product_id{$p}\"  value=\"{$item['product_id']}\">";
    echo "<input type=\"hidden\" type=\"text\" name = \"mp_article{$p}\"  value=\"{$item['mp_article']}\">";
        echo  "<input  type=\"hidden\" name=\"ozon_shop\" value=\"$ozon_shop\">";




  // Кнопки checkBboxi
    if ($check_box == 1) {
    echo  "<td class=\"$bolshe100 text-center\"><input checked type=\"checkbox\" name=\"need_update{$p}\" value=\"\"></td>";
    } else {
      echo  "<td class=\"$bolshe100 text-center\"><input  type=\"checkbox\" name=\"need_update{$p}\" value=\"\"></td>";
    }
    $p++;

    echo "</tr>";
  }
    echo "</table>";


 echo <<<HTML
<div class="ccc">
<input class="atuin-btn" type="submit" value="ОБНОВИТЬ ДАННЫЕ НА $ozon_shop">
</div>

HTML;
  echo "</form>";



echo <<<HTML
<!-- Подклюяаме Jquery -->
<script src="jquery.min.js"></script> 
<!-- РАсчитываем цену со скидкой и разницу в ценах -->
<script type="text/javascript">
            function CalculateItem()
            {
              Str_Number = event.target.id
              var Str_Number = Str_Number.replace(/\D/g, "");
              
                try {
                    
        delta_seller_prices = Math.round($('#seller_price_now_DB' + Str_Number).val() - $('#price_seller_na_mp_ozon' + Str_Number).val());
                   
                 
                 $('#delta_seller_prices' + Str_Number).val(delta_seller_prices);

              

                } catch (e) {
                    $('#delta_seller_prices' + Str_Number).val('7778777');
                }

            }
</script>

<!-- Блокируем отправку формы по ЭНТЕРУ -->
<script type="text/javascript">
$("input").keydown(function(event){
   if (event.keyCode === 13) {
      return false;
  }
}
)
</script> 

HTML;
}

