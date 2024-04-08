<?php
require_once '../connect_db.php';
require_once '../pdo_functions/pdo_functions.php';

require_once "../mp_functions/ozon_api_functions.php";
require_once "../mp_functions/ozon_functions.php";
require_once "../mp_functions/wb_api_functions.php";
require_once "../mp_functions/wb_functions.php";

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
$arr_all_nomenklatura = select_all_nomenklaturu($pdo);

// print_r($arr_all_nomenklatura);


// Названия магазинов
$wb_anmaks = 'wb_anmaks';
$wb_ip = 'wb_ip_zel';
$ozon_anmaks = 'ozon_anmaks';
$ozon_ip = 'ozon_ip_zel';

// Формируем каталоги товаров
$wb_catalog      = get_catalog_tovarov_v_mp($wb_anmaks , $pdo);
$wbip_catalog    = get_catalog_tovarov_v_mp($wb_ip, $pdo); // фомируем каталог
$ozon_catalog    = get_catalog_tovarov_v_mp($ozon_anmaks, $pdo); // получаем озон каталог
$ozon_ip_catalog = get_catalog_tovarov_v_mp($ozon_ip, $pdo); // получаем озон каталог


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

// print_r ($ozon_ip_catalog);
// die();

//*****************************  *************

// Добавляем в каталог процент распрделения и остаток из 1С для магазина Озон ООО 
$wb_catalog      = get_db_procent_magazina ($wb_catalog, $sklads, $wb_anmaks , $arr_new_ostatoki_MP);
$wbip_catalog    = get_db_procent_magazina ($wbip_catalog, $sklads, $wb_ip, $arr_new_ostatoki_MP);
$ozon_catalog    = get_db_procent_magazina ($ozon_catalog, $sklads, $ozon_anmaks, $arr_new_ostatoki_MP);
$ozon_ip_catalog = get_db_procent_magazina ($ozon_ip_catalog, $sklads, $ozon_ip, $arr_new_ostatoki_MP);


// print_r ($ozon_ip_catalog);
// die();
//*****************************  Формируем массив из всех каталогов  *****************************

$all_catalogs[]= $wb_catalog;
$all_catalogs[]= $wbip_catalog;
$all_catalogs[]= $ozon_catalog;
$all_catalogs[]= $ozon_ip_catalog;

//*****************************  получаем массив (артикул - кол-во проданного товара  *****************************
$arr_sell_tovari = make_array_all_sell_tovarov($all_catalogs);

// print_r($$wb_catalog);
// die();
// // выводим шапку таблицы ВБ

// write_table_Sum_information($arr_new_ostatoki_MP, $arr_sell_tovari, $arr_need_ostatok);


$wb_catalog = add_all_info_in_catalog ($wb_catalog, $all_catalogs, $arr_sell_tovari) ;
$wbip_catalog = add_all_info_in_catalog ($wbip_catalog, $all_catalogs, $arr_sell_tovari) ;
$ozon_catalog = add_all_info_in_catalog ($ozon_catalog, $all_catalogs, $arr_sell_tovari) ;
$ozon_ip_catalog = add_all_info_in_catalog ($ozon_ip_catalog, $all_catalogs, $arr_sell_tovari) ;

// $arr_all_nomenklatura;  // - перечень номенклатуры 
// print_r($arr_all_nomenklatura);
// die();
// print_r($wb_catalog[0]);


$link_all_update = "update_all_markets_ALL.php";
echo "<form action=\"$link_all_update\" method=\"post\">";
echo "<table class=\"prods_table\">";
echo "<tr>";

//  ******************* j,ofz byajhvfwbz
echo "<td>";
echo "<table>";
echo "<tr>";

echo "<td>Артикул<br> 1С</td>";
echo "<td>Кол-во 1с</td>";
echo "<td>SELL</td>";
echo "</tr>";
 foreach ($arr_all_nomenklatura as $item) {
    echo "<tr>";
    echo "<td>".$item['main_article_1c']."</td>";
    echo "<td>".$arr_new_ostatoki_MP[mb_strtolower($item['main_article_1c'])]."</td>";
    echo "<td>".@$arr_sell_tovari[mb_strtolower($item['main_article_1c'])]."</td>";

// echo "<td><input  type=\"checkbox\" name=\"\" readonly> </td>"; 
}
echo "</tr>";
echo "</table>";
    echo "</td>";

// ******************************************* WB OOO **************************************
echo "<td>";
show_update_part_table($arr_all_nomenklatura, $arr_new_ostatoki_MP, $wb_catalog, $wb_anmaks);
echo "</td>";

 //******************************************* * WB IP ************************ 
echo "<td>";
show_update_part_table($arr_all_nomenklatura, $arr_new_ostatoki_MP, $wbip_catalog,$wb_ip);
echo "</td>";

//******************************************* * WB IP ************************ 
echo "<td>";
show_update_part_table($arr_all_nomenklatura, $arr_new_ostatoki_MP, $ozon_catalog, $ozon_anmaks);
echo "</td>";


//******************************************* * WB IP ************************ 
echo "<td>";
show_update_part_table($arr_all_nomenklatura, $arr_new_ostatoki_MP, $ozon_ip_catalog,$ozon_ip);
echo "</td>";




/***************************************************************
 * *********************** ЩЯЩТ
 */
echo "</tr>";
echo "<input class=\"btn\" type=\"submit\" value=\"ОБНОВИТЬ ALLLL ДАННЫЕ\">";

echo "</table>";



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



print_info_about_market ($arr_all_nomenklatura, $wb_catalog, $wbip_catalog, $ozon_catalog , $ozon_ip_catalog);

die('Закончили разбор');



function show_update_part_table($arr_all_nomenklatura, $arr_new_ostatoki_MP, $mp_catalog, $mp_name) {


    echo "<table >";
    echo "<tr>";
   
    echo "<td>Артикул МП</td>";
    echo "<td>Кол-во МП<br>факт</td>";
    echo "<td>Кол-во МП<br> Upd</td>";
    echo "<td>Upd</td>";

     foreach ($arr_all_nomenklatura as $item) {
        echo "<tr>";
        echo "<td>".$item['main_article_1c']."</td>";
    
    // ******************************************* WB OOO **************************************

        foreach ($mp_catalog as $temp_item) {
            if (mb_strtolower($temp_item['main_article']) == mb_strtolower($item['main_article_1c'])) {
                $temp_sku =$temp_item['sku'];
                echo "<td>".$temp_item['mp_article']."</td>";
                echo "<td>".$temp_item['quantity']."</td>";

                $temp_barcode = $temp_item['barcode'];
                $name_for_barcode = "_".$mp_name."_mp_BarCode_".$temp_sku;
                $name_for_value = "_".$mp_name."_mp_value_".$temp_sku;
                $kolvo_tovarov_dlya_magazina = $temp_item['kolvo_tovarov_dlya_magazina'];

                
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