<?PHP
// require_once '../../mp_sklad/functions/ozon_catalog.php';
require_once "libs_ozon/function_ozon_reports.php"; // массив с себестоимостью товаров
require_once "libs_ozon/sku_fbo_na_fbs.php"; // массив с себестоимостью товаров

// $ozon_catalog = get_catalog_ozon ();
$ozon_sebest = get_sebestiomost_ozon_with_sku_FBO ();

$ozon_sebest = get_catalog_tovarov_v_mp($ozon_shop, $pdo);




// echo "<pre>";

// print_r($ozon_sebest);


// die();
// делаем один последовательный массив в операциями
foreach ($prod_array as $items) {
    foreach ($items as $item) {
        $new_prod_array[] = $item;
    }
}

// $new_prod_array = json_decode(file_get_contents('xxx.json'),true);


// print_r($new_prod_array);
file_put_contents('xxx.json', json_encode($new_prod_array, JSON_UNESCAPED_UNICODE));


foreach ($new_prod_array as $item) {
    
    if ($item['type'] == 'orders') { 
// Доставка и обработка возврата, отмены, невыкупа   
        $arr_orders[] = $item; 
    } elseif ($item['type'] == 'returns') {
// Доставка и обработка возврата, отмены, невыкупа
        $arr_returns[] = $item;
    } elseif ($item['type'] == 'other') { 
// эквайринг ;претензиям
        $arr_other[] = $item;
    } elseif ($item['type'] == 'services') { 
//продвижения товаров ;хранение/утилизацию ...... SERVICES **************************************
        $arr_services[] = $item;
    } elseif ($item['type'] == 'compensation') { 
//продвижения товаров ;хранение/утилизацию ...... SERVICES **************************************
                $arr_compensation[] = $item;
    } else {
// Если есть неучтенка то сюда
        $arr_index_job[] = $item; /// Проверить нужно будет на существование этого массива

    }
}


$i=0;

/**************************************************************************************************************
 **************************************  ЗАКАЗЫ ************************************************************
 *************************************************************************************************************/
require_once "parts/zakazi.php";

/**************************************************************************************************************
 **************************************  ВОЗВРАТЫ
 *************************************************************************************************************/

if (isset($arr_returns)) { 
    require_once "parts/vozvrati.php";
}

/**************************************************************************************************************
 **************************************  Эквайринг 
 *************************************************************************************************************/

 require_once "parts/ecvairing.php";

/**************************************************************************************************************
 ************************************** Удержание за недовложение товара
 *************************************************************************************************************/
require_once "parts/uderzhania.php";
/**************************************************************************************************************
 ***********************  Сервисы ******************************************************
 *************************************************************************************************************/

require_once "parts/servici.php";

// ВЫВОД ОСНОВНОЙ ТАБЛИЦЫ ////////////////////////////////////////////////////

require_once "print/sum_table.php";

//////////////////////////////////////////////////////////////////////////////////////////////////////
echo "<br>";


//////////////////////////////////////////////////////////////////////////////////////////////////////
echo "<br>";


// print_R($arr_article);
// echo "<br>************************************************************";


echo "<br>";
// if (isset($summa_NACHILS)){echo "НАЧИСЛЕННО                  : $summa_NACHILS<br>";}
if (isset($all_summa_tovarov_)){echo "Подсчитанная сумма товаров (цена для покупателя) : $all_summa_tovarov_<br>";}
echo "<br>";



$dop_uslugi = 0;
if (isset($Summa_uslugi_prodvizhenia_tovara)){echo "Услуги продвижения товаров : $Summa_uslugi_prodvizhenia_tovara<br>";$dop_uslugi+=$Summa_uslugi_prodvizhenia_tovara;}
if (isset($Summa_buy_otzivi)){echo "Приобретение отзывов на платформе : $Summa_buy_otzivi<br>";$dop_uslugi+=$Summa_buy_otzivi;}
if (isset($Summa_zakrepleneie_otzivi)){echo "Закрепление отзыва      : $Summa_zakrepleneie_otzivi<br>";$dop_uslugi+=$Summa_zakrepleneie_otzivi;}
if (isset($Summa_oshibok_prodavca)){echo "Услуга за обработку операционных ошибок продавца: просроченная отгрузка : $Summa_oshibok_prodavca<br>";$dop_uslugi+=$Summa_oshibok_prodavca;}
if (isset($Summa_obrabotka_gruzomestFBO)){echo "Обработка товара в составе грузоместа на FBO : $Summa_obrabotka_gruzomestFBO<br>";$dop_uslugi+=$Summa_obrabotka_gruzomestFBO;}
if (isset($Summa_generacia_videooblozhki)){echo "Генерация видеообложки : $Summa_generacia_videooblozhki<br>";$dop_uslugi+=$Summa_generacia_videooblozhki;}
if (isset($Summa_premiaum_podpiska)){echo "Premium-подписка : $Summa_premiaum_podpiska<br>";$dop_uslugi+=$Summa_premiaum_podpiska;}

if (isset($Summa_hranenia_FBO)){echo "Услуга размещения товаров на складе : $Summa_hranenia_FBO<br>";$dop_uslugi+=$Summa_hranenia_FBO;}
if (isset($Summa_utilizacii_tovara)){echo "Утилизация : $Summa_utilizacii_tovara<br>";}

if (isset($Summa_neizvestnogo)){echo "СЕРВИСЫ (НЕРАЗОБРАННЫЕ)      : $Summa_neizvestnogo<br>";}
if (isset($Summa_izmen_uslovi_otgruzki)){echo "Услуга по изменению условий отгрузки : $Summa_izmen_uslovi_otgruzki<br>";}
if (isset($Summa_pretensii)){echo "сумма начислений по претензиям : $Summa_pretensii<br>";}

$dop_uslugi+=$amount_hranenie;

if (isset($dop_uslugi)){echo "<b>ИТОГО ДОП.УСЛУГИ : $dop_uslugi</b><br>";}






$viplata_na_konec = $accruals_for_sale + $sale_commission + $logistika + $sborka + $lastMile + $dop_uslugi + 
                     $summa_obratnoy_logistik +$return_obrabotka + $amount_ecvairing;






if (isset($viplata_na_konec)){echo "<br><b>К ВЫПЛАТЕ : $viplata_na_konec</b><br>";}

echo "Кол-во обработанных итэмс : $i<br>";

// Если вдруг появились новые данные, которые не учитываются в разборе
if (isset($arr_index_job)){
    $temp = count($arr_index_job);
    echo "<br> <b>Кол-во неразобранных товаров (ОЗОН Добавил новые данные в отчет </b>: $temp<br>"; 
}
if (isset($arr_nerazjbrannoe_222)){
    $temp = count($arr_nerazjbrannoe_222);
    echo "<br> <b>Кол-во неразобранных товаров (ОЗОН Добавил новые данные в отчет </b>: $temp<br>"; 
}




echo "<br><br><br>";


// ВЫВОД ОСНОВНОЙ ТАБЛИЦЫ ////////////////////////////////////////////////////

require_once "print/real_money.php";

echo "<br><br><br>";

// print_r($arr_orders);
// ВЫВОД  ТАБЛИЦЫ FBO FBS////////////////////////////////////////////////////

require_once "print/fbo_fbs_table.php";
