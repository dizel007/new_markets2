<?PHP

$ozon_sebest = get_catalog_tovarov_v_mp($ozon_shop, $pdo, 'all');



// echo "<pre>";
// print_r($res);
// die();


/**************************************************************************************************************
 **************************************  ЗАКАЗЫ ************************************************************
 *************************************************************************************************************/
if (isset($arr_orders)) {
require_once "parts_article/orders_article.php";
}

/**************************************************************************************************************
 **************************************  ЗАКАЗЫ ЗА ГРАНИЦУ ************************************************************
 *************************************************************************************************************/
if (isset($arr_orders_ino)) {
    require_once "parts_article/orders_ino_article.php";
    }

    
/**************************************************************************************************************
 **************************************  ВОЗВРАТЫ
 *************************************************************************************************************/
//06675399-0372-4
if (isset($arr_returns)) {
    require_once "parts_article/returns_article.php";
}

/**************************************************************************************************************
 **************************************  Эквайринг 
 *************************************************************************************************************/
if (isset($arr_other)) {
require_once "parts_article/other_article.php";
}

/**************************************************************************************************************
 ************************************** Удержание за недовложение товара
 *************************************************************************************************************/
require_once "parts_article/uderzhania_article.php";
/**************************************************************************************************************
 ***********************  Сервисы ******************************************************
 *************************************************************************************************************/

require_once "parts_article/servici_article.php";


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// print_r($arr_article);

// 
// ОСтавляем только те заказы, по которым есть движение
$all_count = count($arr_article);

//  ******* убираем все заказы, где нет ПРОДАЖИ И ВОЗВРАТОВ () 
foreach ($arr_article as $key => &$item) {
    if (!isset($item['SELL']) && (!isset($item['RETURN']))) 
    {
         
        unset ($arr_article[$key]);
     } else {
        // сформируем массив существующих артикулов ПОКУПОК И ВОЗВРАТОВ
        if (isset($item['items_buy'])) 
        {
            foreach ($item['items_buy'] as $find_article) 
                {
                    $arr_real_article[mb_strtolower($find_article['c_1c_article'])] = mb_strtolower($find_article['c_1c_article']);
                }
        } elseif (isset($item['items_returns'])) {
             foreach ($item['items_returns'] as $find_article) 
                {
                    $arr_real_article[mb_strtolower($find_article['c_1c_article'])] = mb_strtolower($find_article['c_1c_article']);
                }
        }
    }
}
unset ($item); // 

//********************************************************* */
// Формируем GET параметры для сыслок артикулов 
//********************************************************* */

foreach ($_GET as $key => &$value) {
    if ($key == 'article') {continue;}
      if ($key == 'need_update') {$value = 0;}
    $get_array[$key] = $value;
}
$queryString = http_build_query($get_array);

echo "<br>";
// Выводим все артикулы для тыкания 
foreach ($arr_real_article as $real_article) {
    echo "<a href=\"?$queryString&article=$real_article\">$real_article</a><br>";
}


echo  "<br> ************** $need_article *****************<br>";

// Оставляем только нужный артикул
foreach ($arr_article as $key => &$item) {
   if (isset($item['items_buy'])) 

        {
            foreach ($item['items_buy'] as $key_item => &$find_article) 
                {
                   if (mb_strtolower($find_article['c_1c_article']) != $need_article) {
                         unset ($arr_article[$key]['items_buy'][$key_item]);
                    }


                }
        } elseif (isset($item['items_returns'])) {
             foreach ($item['items_returns'] as $key_item => &$find_article)
                {
                      if (mb_strtolower($find_article['c_1c_article']) != $need_article) {
                         unset ($arr_article[$key]['items_returns'][$key_item]);
                    }
                }
        }
}
// 
// print_r($arr_article);



// die();

$RF_count = count($arr_article);
$INO_count = $all_count - $RF_count;

echo "<br>Количество элементов С продажами или возвратами--". $RF_count;
echo "<br>Количество элементов  без продаж и возвратов --". $INO_count ."<br>";

// die();

// if (isset($viplata_na_konec)) {
//     echo "<br><h3>К ВЫПЛАТЕ : $viplata_na_konec</h3><br>";
// }
// if (isset($viplata_na_konec)){echo "<br><h3>KkkkkkkkkkkkkkkkkЕ : $dop_uslugi</h3><br>";}

echo "Кол-во обработанных Строк : $i<br>";

// Если вдруг появились новые данные, которые не учитываются в разборе
if (isset($arr_index_job)) {
    $temp = count($arr_index_job);
    echo "<br> <b>Кол-во неразобранных товаров (ОЗОН Добавил новые данные в отчет </b>: $temp<br>";
}
if (isset($arr_nerazjbrannoe_222)) {
    $temp = count($arr_nerazjbrannoe_222);
    echo "<br> <b>Кол-во неразобранных товаров (ОЗОН Добавил новые данные в отчет </b>: $temp<br>";
}




echo "<br><br>";

/*******************************************************************************************
 ******************************* ДОПОЛНИТЕЛЬНЫЙ РАЗБОР ДАННЫХ
 *****************************************************************************************/
// доставаем всю номенклатуру
$arr_all_nomenklatura = select_all_nomenklaturu($pdo);
foreach ($arr_all_nomenklatura as $nomenklatura) {
    $arr_prices[mb_strtolower($nomenklatura['main_article_1c'])]['min_price']  = $nomenklatura['min_price'];
    $arr_prices[mb_strtolower($nomenklatura['main_article_1c'])]['main_price']  = $nomenklatura['main_price'];
}



// echo "<pre>";
// print_r($arr_prices['1940-10']);
// print_r($arr_prices);

// die();
// print_r($arr_article);
// die();

// Получеам список всех складов ФБО
$ozon_link = 'v1/cluster/list';
$send_data = array(
  "cluster_type" => "CLUSTER_TYPE_OZON"
);
$send_data = json_encode($send_data);

$warehouse_clusters = send_injection_on_ozon($token, $client_id, $send_data, $ozon_link );


foreach ($warehouse_clusters['clusters'] as $clusters) {
    foreach ($clusters['logistic_clusters'] as $logistic_clusters) {
        foreach ($logistic_clusters['warehouses'] as $warehouses) {
        
            $arr_warehouses[$warehouses['warehouse_id']] = $warehouses['name'];
        }

    }

}
// Добавляем наши склады по ФБС 
$ozon_link = 'v1/warehouse/list';
$our_FBS_warehouse = send_injection_on_ozon($token, $client_id, '', $ozon_link );
foreach ($our_FBS_warehouse['result'] as $warehouses) {
       $arr_warehouses[$warehouses['warehouse_id']] = 'НАШ СКЛАД_'.$warehouses['name'];
      
}




// перебираем все заказы, и перебираем все проданные товары по штукчно и
// и будем формировать все расходы по каждому товару в заказе 
foreach ($arr_article as $items_in_order) {
    // если есть проданные товары 
    if (isset($items_in_order['items_buy'])) {
    foreach ($items_in_order['items_buy'] as $gruz_key => &$one_tovar) {
        $article_good_format = mb_strtolower($one_tovar['c_1c_article']);


 // находим себестоимость и хорошую цену для нашего артикула
 // *************************************************************
        if (isset($arr_prices[$article_good_format])) {
           

            $one_tovar['min_price'] = $arr_prices[mb_strtolower($one_tovar['c_1c_article'])]['min_price'];
            $one_tovar['main_price'] = $arr_prices[mb_strtolower($one_tovar['c_1c_article'])]['main_price'];
        } else {
            // когда не нашли цену по ску (Какие то разобвые продажи)
            $one_tovar['min_price'] = 0;
            $one_tovar['main_price'] = 0;
        }

// Цепояем какие типы операций были в этом заказе 
            if (isset($items_in_order['SELL'])) {
                $one_tovar['SELL'] = $items_in_order['SELL'];
            } 
            if (isset($items_in_order['RETURN'])){
                $one_tovar['RETURN'] = $items_in_order['RETURN'];
            }
            if (isset($items_in_order['ACQUIRING'])){
                $one_tovar['ACQUIRING'] = $items_in_order['ACQUIRING'];
            }
            if (isset($items_in_order['SERVICES'])){
                $one_tovar['SERVICES'] = $items_in_order['SERVICES'];
            }
            if (isset($items_in_order['UDERZHANIA'])){
                $one_tovar['UDERZHANIA'] = $items_in_order['UDERZHANIA'];
            }

// Цепляем дополнительную информацию
        $one_tovar['post_number'] = $items_in_order['post_number']; 
        if (isset($items_in_order['warehouse_id'])) {
            $one_tovar['warehouse_id'] = $items_in_order['warehouse_id'];
            if (isset($arr_warehouses[$items_in_order['warehouse_id']])) {
                $one_tovar['warehouse_name'] = $arr_warehouses[$items_in_order['warehouse_id']];
            } else {
                $one_tovar['warehouse_name'] = 'Не нашли склад';
            }
        } else {
            $one_tovar['warehouse_id'] = 'za_graznica';
            $one_tovar['warehouse_name'] = 'ЗАГРАНИЦА';
        }
        $one_tovar['order_date'] = $items_in_order['order_date'];
        $one_tovar['post_number_gruzomesto'] = $gruz_key;
// цепляем эквайринг 1,1%
$one_tovar['acquiring'] = - round(($one_tovar['accruals_for_sale']/100) * 1.1,2) ;

// цепляем поздннюю отгрузку
if (isset($items_in_order['pozdniaa_otgruzka'])) {
$one_tovar['pozdniaa_otgruzka'] =  $items_in_order['pozdniaa_otgruzka']/$items_in_order['count']; 
}


// Формируем новый массив
        $article_strTolower = mb_strtolower($one_tovar['c_1c_article']); // артикул в нижнем регитсре
        $one_tovar_reestr[$article_strTolower][$items_in_order['delivery_schema']][] = $one_tovar;


    }
    }

    // цепляем возраты 

        // если есть проданные товары 
    elseif (isset($items_in_order['items_returns'])) {
    foreach ($items_in_order['items_returns'] as $gruz_key => &$one_tovar) {
        $article_good_format = mb_strtolower($one_tovar['c_1c_article']);


 // находим себестоимость и хорошую цену для нашего артикула
 // *************************************************************
        if (isset($arr_prices[$article_good_format])) {
           

            $one_tovar['min_price'] = $arr_prices[mb_strtolower($one_tovar['c_1c_article'])]['min_price'];
            $one_tovar['main_price'] = $arr_prices[mb_strtolower($one_tovar['c_1c_article'])]['main_price'];
        } else {
            // когда не нашли цену по ску (Какие то разобвые продажи)
            $one_tovar['min_price'] = 0;
            $one_tovar['main_price'] = 0;
        }

// Цепояем какие типы операций были в этом заказе 
            if (isset($items_in_order['SELL'])) {
                $one_tovar['SELL'] = $items_in_order['SELL'];
            } 
            if (isset($items_in_order['RETURN'])){
                $one_tovar['RETURN'] = $items_in_order['RETURN'];
            }
            if (isset($items_in_order['ACQUIRING'])){
                $one_tovar['ACQUIRING'] = $items_in_order['ACQUIRING'];
            }
            if (isset($items_in_order['SERVICES'])){
                $one_tovar['SERVICES'] = $items_in_order['SERVICES'];
            }
            if (isset($items_in_order['UDERZHANIA'])){
                $one_tovar['UDERZHANIA'] = $items_in_order['UDERZHANIA'];
            }

// Цепляем дополнительную информацию
        $one_tovar['post_number'] = $items_in_order['post_number']; 
        if (isset($items_in_order['warehouse_id'])) {
            $one_tovar['warehouse_id'] = $items_in_order['warehouse_id'];
            if (isset($arr_warehouses[$items_in_order['warehouse_id']])) {
                $one_tovar['warehouse_name'] = $arr_warehouses[$items_in_order['warehouse_id']];
            } else {
                $one_tovar['warehouse_name'] = 'Не нашли склад';
            }
        } else {
            $one_tovar['warehouse_id'] = 'za_graznica';
            $one_tovar['warehouse_name'] = 'ЗАГРАНИЦА';
        }
        $one_tovar['order_date'] = $items_in_order['order_date'];
        $one_tovar['post_number_gruzomesto'] = $gruz_key;
// цепляем эквайринг 1,1%
if (isset($one_tovar['accruals_for_sale'])) {
    $one_tovar['acquiring'] = - round(($one_tovar['accruals_for_sale']/100) * 1.1,2) ;
} else {
   $one_tovar['acquiring'] = 0; 
   $one_tovar['accruals_for_sale'] = 0;
}

// Формируем новый массив
        $article_strTolower = mb_strtolower($one_tovar['c_1c_article']); // артикул в нижнем регитсре
        $one_tovar_reestr[$article_strTolower][$items_in_order['delivery_schema']][] = $one_tovar;

    }
    }
// echo "<br> *********************************";
}


// print_r($one_tovar);
// die();

///  Выводим таблицу с дополнительными сервисами, которые не смогли привязать к заказам
// require_once "print_article/table_services_without_postnumbers.php";

// Выводим таблицу со всеми заказами 
require_once "print_article/real_money_article.php";

