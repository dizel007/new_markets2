<?PHP
// require_once '../../mp_sklad/functions/ozon_catalog.php';
require_once "libs_ozon/function_ozon_reports.php"; // массив с себестоимостью товаров
require_once "libs_ozon/sku_fbo_na_fbs.php"; // массив с себестоимостью товаров

// $ozon_catalog = get_catalog_ozon ();
// $ozon_sebest = get_sebestiomost_ozon_with_sku_FBO ();

$ozon_sebest = get_catalog_tovarov_v_mp($ozon_shop, $pdo,'all');




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
/// Выводим таблицу похожую на озоновскую
if (isset($all_summa_tovarov_)){echo "<b>Подсчитанная сумма товаров (Выкуплено товаров) : $all_summa_tovarov_</b><br>";}
if (isset($sale_commission)){echo "<b>Вознаграждение ОЗОН за продажу : $sale_commission</b><br>";}
echo "<br>";

$plata_za_obrabotku_dostavku = @$sborka  + @$logistika + @$lastMile;
if (isset($plata_za_obrabotku_dostavku)){echo "<b>Плата за обработку и доставку : $plata_za_obrabotku_dostavku</b><br>";}
if (isset($sborka)){echo "Обработка отправления «Drop-off» : $sborka<br>";}
if (isset($logistika)){echo "Логистика : $logistika<br>";}
if (isset($lastMile)){echo "Последняя миля : $lastMile<br>";}

echo "<br>";

$plata_za_vozvrati_i_otmeni = @$summa_obratnoy_logistik  + @$return_obrabotka ;
$vozvrati_i_otmeni = $plata_za_vozvrati_i_otmeni +$amount_vozrat;
if (isset($vozvrati_i_otmeni)){echo "<b>Возвраты и отмены : $vozvrati_i_otmeni</b><br>";}
if (isset($amount_vozrat)){echo "<b>Получено возвратов с учётом вознаграждения : $amount_vozrat</b><br>";}

if (isset($plata_za_vozvrati_i_otmeni)){echo "<b>Плата за возвраты и отмены : $plata_za_vozvrati_i_otmeni</b><br>";}
if (isset($summa_obratnoy_logistik)){echo "Обратная логистика : $summa_obratnoy_logistik<br>";}
if (isset($return_obrabotka)){echo "Обработка возвратов, отмен и невыкупов Партнёрами Ozon : $return_obrabotka<br>";}
echo "<br>";

if (isset($amount_ecvairing)){echo "<b>Оплата эквайринга : $amount_ecvairing</b><br>";}
echo "<br>";



// Формируем сумму Дополнительных Услуг Озона
$dop_uslugi = 0;
// Услуги продвижения товаров
if (isset($Summa_uslugi_prodvizhenia_tovara)){
    $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_uslugi_prodvizhenia_tovara,"Услуги продвижения товаров");
}
// Приобретение отзывов на платформе
if (isset($Summa_buy_otzivi)){
    $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_buy_otzivi,"Приобретение отзывов на платформе");
}
// Закрепление отзыва
if (isset($Summa_zakrepleneie_otzivi)){
    $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_zakrepleneie_otzivi,"Закрепление отзыва");
}
// Услуга за обработку операционных ошибок продавца: просроченная отгрузка
if (isset($Summa_oshibok_prodavca)){
    $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_oshibok_prodavca,"Услуга за обработку операционных ошибок продавца: просроченная отгрузка");
}
// Генерация видеообложки
if (isset($Summa_generacia_videooblozhki)){
    $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_generacia_videooblozhki,"Генерация видеообложки");
}
// Premium-подписка
if (isset($Summa_premiaum_podpiska)){
    $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_premiaum_podpiska,"Premium-подписка");
}
// Реклама трафареты
if (isset($Summa_reklami_trafareti)){
    $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_reklami_trafareti,"Реклама трафареты");
}
//Реклама Поиск в продвижении
if (isset($Summa_reklami_poisk)){
    $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_reklami_poisk,"Реклама Поиск в продвижении");
}
// Услуга размещения товаров на складе
if (isset($Summa_hranenia_FBO)){
    $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_hranenia_FBO,"Услуга размещения товаров на складе");
}
// Обработка товара в составе грузоместа на FBO
if (isset($Summa_obrabotka_gruzomestFBO)){
    $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_obrabotka_gruzomestFBO,"Обработка товара в составе грузоместа на FBO");
}


if (isset($Summa_utilizacii_tovara)){echo "Утилизация : $Summa_utilizacii_tovara<br>";}
if (isset($Summa_dostav_i_obrabotyka_vozvratov)){echo "Доставка и обработка возврата, отмены, невыкупа : $Summa_dostav_i_obrabotyka_vozvratov<br>";}




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
