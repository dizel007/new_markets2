<?php
require_once '../connect_db.php';
require_once '../pdo_functions/pdo_functions.php';


require_once "../mp_functions/ozon_api_functions.php";
require_once "../mp_functions/ozon_functions.php";
require_once "../mp_functions/wb_api_functions.php";
require_once "../mp_functions/wb_functions.php";
require_once "../mp_functions/yandex_api_functions.php";
require_once "../mp_functions/yandex_functions.php";


require_once "../autosklad/functions/parce_excel_sklad_json.php";
require_once "../autosklad/functions/function_autosklad.php";
require_once "../autosklad/functions/write_html_table.php";
require_once "../autosklad/functions/add_info_in_all_catalog.php";
require_once "../autosklad/functions/print_sum_information.php";
require_once "../autosklad/functions/print_info_about_market.php";


echo '<link rel="stylesheet" href="css/main_table.css">';

echo "<pre>";
 
if (isset($_GET['return'])) {
    $return_after_update = $_GET['return'];
} else {
    $return_after_update = 0;
}

// Если вернулись сюда после обновления объема товараа
if ($return_after_update == 777) {
    
    $arr_article_items = json_decode(file_get_contents("uploads/array_items.json"));
    
    foreach ($arr_article_items as $key=>$itemss ) {
        foreach ($itemss as $mp_key=>$ostatok) {
            if ($mp_key == 'MP') {
                $arr_new_ostatoki_MP[mb_strtolower($key)] = $ostatok ; // массив остатков из 1С
            }
        }
        
    
    }
    // echo "<pre >";
    // print_r ($arr_article_items);
    // die();

} else {
if (isset($_FILES['file_excel'])) {
$uploaddir = "uploads/";
$uploadfile = $uploaddir . basename( $_FILES['file_excel']['name']);

    if(move_uploaded_file($_FILES['file_excel']['tmp_name'], $uploadfile))
            {
            echo "Файл с остатками товаров, УСПЕШНО ЗАГРУЖЕН<br>";
            // $xls = PHPExcel_IOFactory::load('temp_sklad/temp.xlsx');
            $xls = PHPExcel_IOFactory::load($uploadfile);
            $arr_new_ostatoki_MP =  Parce_excel_1c_sklad ($xls) ; // парсим Загруженный файл и формируем JSON архив для дальнейшей работы
            
           }
            else
            {
                
            // die ("DIE ОШИБКА при загрузке файла");
            echo "<h1>Подгружены данные из последнего JSON файла</h1>";
            $arr_article_items = json_decode(file_get_contents("uploads/array_items.json"));

            foreach ($arr_article_items as $key=>$itemss ) {
                foreach ($itemss as $mp_key=>$ostatok) {
                    if ($mp_key == 'MP') {
                        $arr_new_ostatoki_MP[mb_strtolower($key)] = $ostatok ; // массив остатков из 1С
                    }
                }
                
            
            }

              }
} 



}



// Доставем информацию по складам ****** АКТИВНЫМ СКЛАДАМ ******
$sklads = select_info_about_sklads($pdo); // ОБщая Информация по складам

$arr_need_ostatok = get_min_ostatok_tovarov($pdo); // массив с утвержденным неснижаемым остатком

// Вся продаваемая номенклатура
$arr_all_nomenklatura = select_active_nomenklaturu($pdo);
// Получаем поартикульное распределние товаров на каждом складе 
$raspredelenie_ostatkov = get_procent_tovarov_marketa($pdo);

// print_r($raspredelenie_ostatkov);
// die();

// Названия магазинов
$wb_anmaks = 'wb_anmaks';
$wb_ip = 'wb_ip_zel';
$ozon_anmaks = 'ozon_anmaks';
$ozon_ip = 'ozon_ip_zel';
$yandex_anmaks_fbs = 'ya_anmaks_fbs';

// Формируем каталоги товаров
$wb_catalog      = get_catalog_tovarov_v_mp($wb_anmaks ,       $pdo, 'active');
$wbip_catalog    = get_catalog_tovarov_v_mp($wb_ip,            $pdo, 'active'); // фомируем каталог
$ozon_catalog    = get_catalog_tovarov_v_mp($ozon_anmaks,      $pdo, 'active'); // получаем озон каталог
$ozon_ip_catalog = get_catalog_tovarov_v_mp($ozon_ip,          $pdo, 'active'); // получаем озон каталог
$ya_fbs_catalog  = get_catalog_tovarov_v_mp($yandex_anmaks_fbs, $pdo, 'active'); // получаем yandex каталог
// Формируем массив в номенклатурой, с учетом того, что один товар можнт продаваться под разным артикулом на Маркете

/* *****************************      Получаем Фактические остатки с ВБ *****************************/
$wb_catalog = get_ostatki_wb ($token_wb, $wb_catalog, $sklads[$wb_anmaks ]['warehouseId']);
//*****************************      Достаем фактически заказанные товары  *****************************
$wb_catalog = get_new_zakazi_wb ($token_wb, $wb_catalog);

/* *****************************      Получаем Фактические остатки с ВБ ИП *****************************/
$wbip_catalog = get_ostatki_wb ($token_wb_ip, $wbip_catalog, $sklads[$wb_ip]['warehouseId']); // цепляем остатки 
//*****************************      Достаем фактически заказанные товары  WB IP *****************************
$wbip_catalog = get_new_zakazi_wb ($token_wb_ip, $wbip_catalog);

//***************************** Получаем Фактические остатки с OZON *****************************
$ozon_catalog = get_ostatki_ozon ($token_ozon, $client_id_ozon, $ozon_catalog); // цепояем остатки
//*****************************  Достаем фактически заказанные товары OZON *****************************
$ozon_catalog = get_new_zakazi_ozon ($token_ozon, $client_id_ozon, $ozon_catalog); // цепляем продажи

//***************************** Получаем Фактические остатки с OZON *****************************
$ozon_ip_catalog = get_ostatki_ozon ($token_ozon_ip, $client_id_ozon_ip, $ozon_ip_catalog); // цепояем остатки
//*****************************  Достаем фактически заказанные товары OZON *****************************
$ozon_ip_catalog = get_new_zakazi_ozon ($token_ozon_ip, $client_id_ozon_ip, $ozon_ip_catalog); // цепляем продажи

// var_dump($ozon_ip_catalog);

//***************************** Получаем Фактические остатки с ЯНДЕКС *****************************
$ya_fbs_catalog = get_ostatki_yandex ($yam_token, $campaignId_FBS, $ya_fbs_catalog); // цепояем остатки
//*****************************  Достаем фактически заказанные товары YANDEX *****************************
$ya_fbs_catalog = get_new_zakazi_yandex ($yam_token, $campaignId_FBS, $ya_fbs_catalog); // цепляем продажи




$wb_catalog      = get_db_procent_tovara_v_magazine ($wb_catalog, $raspredelenie_ostatkov, $$wb_anmaks, $arr_new_ostatoki_MP);
$wbip_catalog    = get_db_procent_tovara_v_magazine ($wbip_catalog, $raspredelenie_ostatkov, $wb_ip, $arr_new_ostatoki_MP);
$ozon_catalog    = get_db_procent_tovara_v_magazine ($ozon_catalog, $raspredelenie_ostatkov, $ozon_anmaks, $arr_new_ostatoki_MP);
$ozon_ip_catalog = get_db_procent_tovara_v_magazine ($ozon_ip_catalog, $raspredelenie_ostatkov, $ozon_ip, $arr_new_ostatoki_MP);
$ya_fbs_catalog  = get_db_procent_tovara_v_magazine ($ya_fbs_catalog, $raspredelenie_ostatkov, $yandex_anmaks_fbs, $arr_new_ostatoki_MP);


// print_r ($wb_catalog);
// die();
//*****************************  Формируем массив из всех каталогов  *****************************

$all_catalogs[] = $wb_catalog;
$all_catalogs[] = $wbip_catalog;
$all_catalogs[] = $ozon_catalog;
$all_catalogs[] = $ozon_ip_catalog;
$all_catalogs[] = $ya_fbs_catalog;
//*****************************  получаем массив (артикул - кол-во проданного товара  *****************************
$arr_sell_tovari = make_array_all_sell_tovarov($all_catalogs);

// print_r($arr_sell_tovari);
// die();
// // выводим шапку таблицы ВБ

// write_table_Sum_information($arr_new_ostatoki_MP, $arr_sell_tovari, $arr_need_ostatok);


$wb_catalog      = add_all_info_in_catalog ($wb_catalog,      $arr_sell_tovari) ;
$wbip_catalog    = add_all_info_in_catalog ($wbip_catalog,    $arr_sell_tovari) ;
$ozon_catalog    = add_all_info_in_catalog ($ozon_catalog,    $arr_sell_tovari) ;
$ozon_ip_catalog = add_all_info_in_catalog ($ozon_ip_catalog, $arr_sell_tovari) ;
$ya_fbs_catalog  = add_all_info_in_catalog ($ya_fbs_catalog,  $arr_sell_tovari) ;
// $arr_all_nomenklatura;  // - перечень номенклатуры 

// print_r($ya_fbs_catalog);


// Цепляем к номенклатуре признак того, что товар был заблокирван
foreach ($arr_all_nomenklatura as &$item) {
    foreach ($raspredelenie_ostatkov as $block_tovar ) {
        if (mb_strtolower($item['main_article_1c']) ==  mb_strtolower($block_tovar['main_article_1c'])) {
            $item['block_tovar'] = $block_tovar['block_tovar'];
            break;
        }else {
        $item['block_tovar'] = 0;
       }
    }
}

// print_r($arr_all_nomenklatura);
// die();


$link_all_update = "update_all_markets_ALL.php";
echo "<form action=\"$link_all_update\" method=\"post\">";
echo "<table class=\"prods_table\">";
echo "<tr>";

//  ******************* j,ofz byajhvfwbz
echo "<td>";
    echo "<table>";
        echo "<tr  class=\"rovnay_table_shapka\">";
            echo "<td colspan=\"3\" >ОБщие данные</td>";
        echo "</tr>";
        
        echo "<tr  class=\"rovnay_table_shapka\">";
            echo "<td>Артикул<br> 1С</td>";
            echo "<td>Кол-во 1с</td>";
            echo "<td>SELL</td>";
        echo "</tr>";

    foreach ($arr_all_nomenklatura as $item) {
        
      if ($item['block_tovar'] == 1) {
        $block_tovar='block_tovar';
      } else {
        $block_tovar='';
      }

        echo "<tr  class=\"rovnay_table\">";
            echo "<td class =\"$block_tovar\">".$item['main_article_1c']."</td>";
            echo "<td".@$arr_new_ostatoki_MP[mb_strtolower($item['main_article_1c'])]."</td>";
            echo "<td>".@$arr_sell_tovari[mb_strtolower($item['main_article_1c'])]."</td>";
        echo "</tr>";
     }
    
echo "</table>";
echo "</td>";   

// ******************************************* WB OOO **************************************
echo "<td>";
// show_update_part_table($arr_all_nomenklatura, $arr_new_ostatoki_MP, $wb_catalog, $wb_anmaks);
show_update_part_table($arr_all_nomenklatura, $wb_catalog, $wb_anmaks);

echo "</td>";

 //******************************************* * WB IP ************************ 
echo "<td>";
show_update_part_table($arr_all_nomenklatura, $wbip_catalog,$wb_ip);
echo "</td>";

//******************************************* * WB IP ************************ 
echo "<td>";
show_update_part_table($arr_all_nomenklatura,  $ozon_catalog, $ozon_anmaks);
echo "</td>";


//******************************************* * WB IP ************************ 
echo "<td>";
show_update_part_table($arr_all_nomenklatura,  $ozon_ip_catalog,$ozon_ip);
echo "</td>";

//******************************************* * YANDEX ************************ 
echo "<td>";
show_update_part_table($arr_all_nomenklatura, $ya_fbs_catalog,$yandex_anmaks_fbs);
echo "</td>";



/***************************************************************
 * *********************** ЩЯЩТ
 */
echo "</tr>";
echo "<input class=\"btn\" type=\"submit\" value=\"ОБНОВИТЬ ALLLL ДАННЫЕ\">";

echo "</table>";

echo "</form>";

// Выводим на экран сводную таблицу по продажам
$arr_need_tovari = print_sum_information ($arr_all_nomenklatura, $arr_new_ostatoki_MP, $arr_sell_tovari) ;
// Смотрим есть ли массив с товарами для пополнения
if (isset($arr_need_tovari)){
    $link_file_need_tovari = "temp/".make_excel_file_ostatkov($arr_need_tovari);
    echo "<a href = \"$link_file_need_tovari\">скачать файл товаров, которые нужно пополнить</a>" ;
    echo "<br>";
    echo "<br>";
} else {
    echo "<br>Товары пополнять не нужно <br><br>";    
}



// print_r($ya_fbs_catalog);
// die();

print_info_about_market ($arr_all_nomenklatura, $wb_catalog, $wbip_catalog, $ozon_catalog , $ozon_ip_catalog, $ya_fbs_catalog);

die('Закончили разбор');




/**********************************************************************************************
* Выводим таблицу товаров для обновления остатков для одного МП
*************************************************************************************************/

// function show_update_part_table($arr_all_nomenklatura, $arr_new_ostatoki_MP, $mp_catalog, $mp_name) {

 function show_update_part_table($arr_all_nomenklatura, $mp_catalog, $mp_name) {

echo <<<HTML
 <link rel="stylesheet" href="pics/css/styles.css">
HTML;
    
    echo "<table >";


    echo "<tr  class=\"rovnay_table_shapka\">";
    echo "<td colspan=\"7\" >$mp_name</td>";
    echo "</tr>";



    echo "<tr  class=\"rovnay_table_shapka\">";
   
    echo "<td>Артикул МП</td>";
    echo "<td>Кол-во МП<br>факт</td>";
    echo "<td>Кол-во МП<br> Upd</td>";
    echo "<td>Upd</td>";

     foreach ($arr_all_nomenklatura as $item) {
        echo "<tr class=\"rovnay_table\">";

        echo "<td>".$item['main_article_1c']."</td>";
    
    // ******************************************* WB OOO **************************************

        foreach ($mp_catalog as $temp_item) {
            
            if (mb_strtolower($temp_item['main_article']) == mb_strtolower($item['main_article_1c'])) {
                $temp_sku =$temp_item['sku'];
                // echo "<td>".$temp_item['mp_article']."</td>";



                $temp_barcode = $temp_item['barcode'];
                $name_for_barcode = "_".$mp_name."_mp_BarCode_".$temp_sku;
                $name_for_value = "_".$mp_name."_mp_value_".$temp_sku;
                $kolvo_tovarov_dlya_magazina = $temp_item['update_kolvo_tovarov_dlya_magazina'];

// Определяем цвет ячейки в зависимости от количества товара
    $count_cell_color = ''; // изменяем цвет ячейки в зависимости от количества товара
    if (isset($temp_item['quantity'])) {
        ($temp_item['quantity'] == $kolvo_tovarov_dlya_magazina)?$count_cell_color = 'green_color': $z= '';
        ($temp_item['quantity'] <> $kolvo_tovarov_dlya_magazina)?$count_cell_color = 'yellow_color': $z= '';
        ($temp_item['quantity'] < 10)?$count_cell_color = 'orange_color': $z= '';
        ($temp_item['quantity'] == 0)?$count_cell_color = 'zero_alarm_color': $z= '';
    
echo "<td class=\"$count_cell_color\" >".$temp_item['quantity']."<br>f=".@$temp_item['fake_count']."</td>";
    } else {
        echo "<td class=\"$count_cell_color\" >"."НД"."<br>f=".@$temp_item['fake_count']."</td>";
    }

                
 echo <<<HTML
        <input hidden type="text" name="$name_for_barcode" value=$temp_barcode>
     
        <td><input class="text-field__input future_ostatok" type="number" name="$name_for_value" value=$kolvo_tovarov_dlya_magazina></td>
HTML;

               $check_update = $temp_item['nead_update'];
               $name_for_checkUpdate = "_".$mp_name."_mp_check_".$temp_sku;
               if ($check_update  == 1) {
                   echo  "<td><input type=\"checkbox\" checked name=\"$name_for_checkUpdate\"> </td>";
                 } else {
                   echo  "<td><input type=\"checkbox\" name=\"$name_for_checkUpdate\" > </td>";
                }


            }
        }

        
                   

        echo "</tr>";
        
    }

    echo "</table>";


}