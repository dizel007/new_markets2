<?PHP

$ozon_sebest = get_catalog_tovarov_v_mp($ozon_shop, $pdo, 'all');

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
// file_put_contents('xxx.json', json_encode($new_prod_array, JSON_UNESCAPED_UNICODE));


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
    }
     elseif ($item['type'] == 'services') {
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


$i = 0;

/**************************************************************************************************************
 **************************************  ЗАКАЗЫ ************************************************************
 *************************************************************************************************************/
require_once "parts_one/zakazi_one.php";

/**************************************************************************************************************
 **************************************  ВОЗВРАТЫ
 *************************************************************************************************************/

if (isset($arr_returns)) {
    require_once "parts_one/vozvrati_one.php";
}

/**************************************************************************************************************
 **************************************  Эквайринг 
 *************************************************************************************************************/

require_once "parts_one/ecvairing_one.php";

/**************************************************************************************************************
 ************************************** Удержание за недовложение товара
 *************************************************************************************************************/
require_once "parts_one/uderzhania_one.php";
/**************************************************************************************************************
 ***********************  Сервисы ******************************************************
 *************************************************************************************************************/

require_once "parts_one/servici_one.php";


print_r($arr_article['31591429-0093']);
// print_r($arr_article);
// 
// die();

/// Добавляем рассчетные данные в таблицу
foreach ($arr_article as $key => $item) {
    // Цепляем наш артикул
    $article = get_article_by_sku_fbs($ozon_sebest, $key); // получаем артикл по СКУ
    $arr_article[$key]['article'] = $article;
    // подсчитываем полную логистику
    $arr_article[$key]['FULL_logistika'] = @$arr_article[$key]['logistika'] + @$arr_article[$key]['logistika_vozvrat'];
    // Все обработки отправления
    $arr_article[$key]['FULL_obrabotka'] = @$arr_article[$key]['sborka'] + @$arr_article[$key]['obrabotka_otpravlenii_v_SC'] + @$arr_article[$key]['obrabotka_otpravlenii_v_PVZ'];

    //Цепляем Сумма за вычетом эквайринга
    @$amount_bez_equaring = $item['amount'] + $item['amount_ecvairing']; // сумма к выплате (уже без эквайринг) 
    $arr_article[$key]['amount_bez_equaring'] = $amount_bez_equaring;
    // Цена за штуку 
    @$one_shtuka = round($amount_bez_equaring / $item['count'], 2); // цена за штуку нам в карман (минус эквайринг)
    $arr_article[$key]['one_shtuka'] = $one_shtuka;
    // Цена за штуку для покупателя
    if (isset($item['count'])) {
    @$one_shtuka_buyer = round($item['accruals_for_sale'] / $item['count'], 2); // цена за штуку для покупателя
    $arr_article[$key]['one_shtuka_buyer'] = $one_shtuka_buyer;
    } else {
    @$one_shtuka_buyer = 0; // цена за штуку для покупателя
    $arr_article[$key]['one_shtuka_buyer'] = $one_shtuka_buyer;
    }
    // Формируем цену за вычетом всего КРОМЕ доп.услугж

    $arr_article[$key]['price_minus_all_krome_dop_uslug'] = @$arr_article[$key]['accruals_for_sale'] +
        @$arr_article[$key]['sale_commission'] +
        @$arr_article[$key]['FULL_obrabotka'] +
        @$arr_article[$key]['FULL_logistika'] +
        @$arr_article[$key]['lastMile'] +
        @$arr_article[$key]['back_logistika_vozvrat'] +
        @$arr_article[$key]['return_obrabotka'] +
        @$arr_article[$key]['get_vozvrat_amount'] +
        @$arr_article[$key]['amount_ecvairing'];
}
// Для расчета процента распределения дополнительных услуг нужно получить сумму к выплате

// $arr_sum_data = make_array_sum($arr_article);










// echo "<br>";
// echo "<br>";

// /// Выводим таблицу похожую на озоновскую *************************************************
// if (isset($arr_sum_data['accruals_for_sale'])) {
//     echo "<b>Подсчитанная сумма товаров (Выкуплено товаров) : " . $arr_sum_data['accruals_for_sale'] . "</b><br>";
// }
// if (isset($arr_sum_data['sale_commission'])) {
//     echo "<b>Вознаграждение ОЗОН за продажу : " . $arr_sum_data['sale_commission'] . "</b><br>";
// }
// echo "<br>";

// //// **************************  ДОСТАВКА (ЛОГИСТИКА) ****************************************************

// $plata_za_obrabotku_dostavku = @$arr_sum_data['FULL_obrabotka'] + @$arr_sum_data['FULL_logistika'] + @$arr_sum_data['lastMile'];

// if (isset($plata_za_obrabotku_dostavku)) {
//     echo "<b>Плата за обработку и доставку : $plata_za_obrabotku_dostavku</b><br>";
// }
// if (isset($arr_sum_data['FULL_obrabotka'])) {
//     echo "Обработка отправления «Drop-off» : " . $arr_sum_data['FULL_obrabotka'] . "<br>";
// }
// if (isset($arr_sum_data['FULL_logistika'])) {
//     echo "Логистика : " . $arr_sum_data['FULL_logistika'] . "<br>";
// }
// if (isset($arr_sum_data['lastMile'])) {
//     echo "Последняя миля : " . $arr_sum_data['lastMile'] . "<br>";
// }


// echo "<br>";
// /////************************************************* ВОЗВРАТЫ И ОТМЕНЫ******************************************
// $plata_za_vozvrati_i_otmeni = @$arr_sum_data['back_logistika_vozvrat'] + @$arr_sum_data['return_obrabotka'];
// $vozvrati_i_otmeni = $plata_za_vozvrati_i_otmeni + @$arr_sum_data['get_vozvrat_amount'];
// if (isset($vozvrati_i_otmeni)) {
//     echo "<b>Возвраты и отмены : " . $vozvrati_i_otmeni . "</b><br>";
// }
// if (isset($arr_sum_data['get_vozvrat_amount'])) {
//     echo "<b>Получено возвратов с учётом вознаграждения : " . $arr_sum_data['get_vozvrat_amount'] . "</b><br>";
// }

// if (isset($plata_za_vozvrati_i_otmeni)) {
//     echo "<b>Плата за возвраты и отмены : $plata_za_vozvrati_i_otmeni</b><br>";
// }
// if (isset($arr_sum_data['back_logistika_vozvrat'])) {
//     echo "Обратная логистика : " . $arr_sum_data['back_logistika_vozvrat'] . "<br>";
// }
// if (isset($arr_sum_data['return_obrabotka'])) {
//     echo "Обработка возвратов, отмен и невыкупов Партнёрами Ozon : " . $arr_sum_data['return_obrabotka'] . "<br>";
// }
// echo "<br>";

// if (isset($amount_ecvairing)) {
//     echo "<b>Оплата эквайринга : $amount_ecvairing</b><br>";
// }
// echo "<br>";

// /// ДОПОЛНИТЕЛЬНЫЕ УСЛУГУ

// // Формируем сумму Дополнительных Услуг Озона
// $dop_uslugi = 0;
// // Premium-подписка
// if (isset($Summa_premiaum_podpiska)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_premiaum_podpiska, "Premium-подписка");
// }

// // Услуги продвижения товаров
// if (isset($Summa_uslugi_prodvizhenia_tovara)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_uslugi_prodvizhenia_tovara, "Услуги продвижения товаров");
// }


// //Краткосрочное размещение возврата FBS
// if (isset($arr_sum_data['amount_hranenie'])) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $arr_sum_data['amount_hranenie'], "Краткосрочное размещение возврата FBS + утилизацию возвратов");
// }

// // Приобретение отзывов на платформе
// if (isset($Summa_buy_otzivi)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_buy_otzivi, "Приобретение отзывов на платформе");
// }
// // Закрепление отзыва
// if (isset($Summa_zakrepleneie_otzivi)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_zakrepleneie_otzivi, "Закрепление отзыва");
// }
// // Услуга за обработку операционных ошибок продавца: просроченная отгрузка
// if (isset($Summa_oshibok_prodavca)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_oshibok_prodavca, "Услуга за обработку операционных ошибок продавца + просроченная отгрузка");
// }
// // Генерация видеообложки
// if (isset($Summa_generacia_videooblozhki)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_generacia_videooblozhki, "Генерация видеообложки");
// }
// //Реклама Поиск в продвижении
// if (isset($Summa_reklami_poisk)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_reklami_poisk, "Реклама Поиск в продвижении");
// }
// // Реклама трафареты
// if (isset($Summa_reklami_trafareti)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_reklami_trafareti, "Реклама трафареты");
// }

// // Услуга размещения товаров на складе
// if (isset($Summa_hranenia_FBO)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_hranenia_FBO, "Услуга размещения товаров на складе");
// }
// // Обработка товара в составе грузоместа на FBO
// if (isset($Summa_obrabotka_gruzomestFBO)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_obrabotka_gruzomestFBO, "Обработка товара в составе грузоместа на FBO");
// }
// // Услуга за обработку операционных ошибок продавца: отмена
// if (isset($Summa_oshibka_obrabotki)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_oshibka_obrabotki, "Услуга за обработку операционных ошибок продавца: отмена");
// }
// // Кросс-докинг
// if (isset($Summa_kross_doking)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_kross_doking, "Кросс-докинг");
// }

// // Подписка Premium
// if (isset($Summa_primiun_5000)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_primiun_5000, "Подписка Premium");
// }
// // Подписка Premium Plus
// if (isset($Summa_primiun_plus25000)) {
//     $dop_uslugi = print_on_screen_one_string_and_return_summ($dop_uslugi, $Summa_primiun_plus25000, "Подписка Premium Plus");
// }





// if (isset($Summa_utilizacii_tovara)) {
//     echo "Утилизация : $Summa_utilizacii_tovara<br>";
// }

// $dop_uslugi += @$amount_hranenie;
// if (isset($dop_uslugi)) {
//     echo "<b>ИТОГО ДОП.УСЛУГИ : $dop_uslugi</b><br>";
// }



// echo "<br><br>";


// //Оплата эквайринга
// if (isset($arr_sum_data['amount_ecvairing'])) {
//     print_on_screen_one_string_and_return_summ($dop_uslugi, $arr_sum_data['amount_ecvairing'], "Оплата эквайринга");
// }

// echo "<br><br>";

// if (isset($Summa_dostav_i_obrabotyka_vozvratov)) {
//     echo "Доставка и обработка возврата, отмены, невыкупа : $Summa_dostav_i_obrabotyka_vozvratov<br>";
// }

// if (isset($Summa_neizvestnogo)) {
//     echo "СЕРВИСЫ (НЕРАЗОБРАННЫЕ)      : $Summa_neizvestnogo<br>";
// }

// if (isset($Summa_izmen_uslovi_otgruzki)) {
//     echo "Услуга по изменению условий отгрузки : $Summa_izmen_uslovi_otgruzki<br>";
// }
// if (isset($Summa_pretensii)) {
//     echo "сумма начислений по претензиям : $Summa_pretensii<br>";
// }




// $viplata_na_konec = $arr_sum_data['accruals_for_sale'] + // вся сумма продаж
    // $arr_sum_data['sale_commission'] +   // комиссия озон
    // $plata_za_obrabotku_dostavku +       // логисткиа
    // $vozvrati_i_otmeni +                 // возвраты 
    // $dop_uslugi +                        // реклама доп услугу
    // $arr_sum_data['amount_ecvairing'];  // эквайринг



$plata_za_obrabotku_dostavku = @$arr_sum_data['FULL_obrabotka'] + @$arr_sum_data['FULL_logistika'] + @$arr_sum_data['lastMile'];
$plata_za_vozvrati_i_otmeni = @$arr_sum_data['back_logistika_vozvrat'] + @$arr_sum_data['return_obrabotka'];
$vozvrati_i_otmeni = $plata_za_vozvrati_i_otmeni + @$arr_sum_data['get_vozvrat_amount'];



if (isset($viplata_na_konec)) {
    echo "<br><h3>К ВЫПЛАТЕ : $viplata_na_konec</h3><br>";
}
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

foreach ($arr_article as $key => $item) {
    $desired_price = 0; // Обнуляем желаемую цену
    $need_up_price = 0; // ОБнуляем дельту между ценой за штуку и желаемой ценой


    $article = get_article_by_sku_fbs($ozon_sebest, $key); // получаем артикл по СКУ
    /// ОБЩИЕ СУММЫ 

    $article_1C =  get_main_article_by_sku_fbs($ozon_sebest, $key);

    // Высчитываем процент распределения ДопУслуг

    // $one_proc_ot_vsey_summi = round($arr_sum_data['price_minus_all_krome_dop_uslug'] / 100, 2);
    // $arr_article[$key]['proc_item_ot_vsey_summi'] = round($arr_article[$key]['price_minus_all_krome_dop_uslug'] / $one_proc_ot_vsey_summi, 2);

    // Распределяем сумму дополнительных услуг в процентоном соотношении
    // if ($arr_article[$key]['proc_item_ot_vsey_summi'] > 0.1) {
    //     $arr_article[$key]['dop_uslugi_each_item'] = round(($dop_uslugi / 100 * $arr_article[$key]['proc_item_ot_vsey_summi']), 2);
    // } else {
    //     $arr_article[$key]['dop_uslugi_each_item'] = 0;
    // }


    // Наша цена за вычетом всех услуг
    // $arr_article[$key]['real_price_minus_all']  = $arr_article[$key]['price_minus_all_krome_dop_uslug'] + $arr_article[$key]['dop_uslugi_each_item'];

    // if (isset($item['count'])) {
    //     $arr_article[$key]['real_price_minus_all_one_shtuka'] = round($arr_article[$key]['real_price_minus_all'] / $arr_article[$key]['count'], 2);
    // } else {
    //     $arr_article[$key]['real_price_minus_all_one_shtuka'] = 0;
    // }

    /// Ищем себестоимость и желаемую цену товара
    foreach ($arr_all_nomenklatura as $nomenclatura) {

        if (mb_strtolower($nomenclatura['main_article_1c']) ==  mb_strtolower($article_1C)) {
            // себестоиомсть
            $arr_article[$key]['min_price'] = $nomenclatura['min_price'];
            // дельта от себестоимости и реальной ценой продажи
            if ($arr_article[$key]['min_price'] > 0) { // Если нет себестоимости, то ничего не добавляем к прибыли
                $arr_article[$key]['min_price_delta']  = $arr_article[$key]['real_price_minus_all_one_shtuka'] - $arr_article[$key]['min_price']; // желаемая цена товара
            } else {
                $arr_article[$key]['min_price_delta'] = 0;
            }
            // хорошая цена
            $arr_article[$key]['main_price']  = $nomenclatura['main_price']; // желаемая цена товара
            // дельта меду жор ценой и ценой продажи
             $arr_article[$key]['main_price_delta']  = $arr_article[$key]['real_price_minus_all_one_shtuka'] - $arr_article[$key]['main_price']; // желаемая цена товара
                    
        }
    }
    /// Высчитывает сколько заработали на одном артикуле 
    if (isset($arr_article[$key]['min_price_delta']) && (isset($arr_article[$key]['count']))) {
        $arr_article[$key]['zarabotali_na_artikule'] =  $arr_article[$key]['min_price_delta'] * $arr_article[$key]['count'];
    } else {
        $arr_article[$key]['zarabotali_na_artikule'] = 0; 
    }
}
// Костыль для дельты 
foreach ($arr_article as $key => $item) {
    if (!isset($arr_article[$key]['main_price_delta'])) {
        $arr_article[$key]['main_price_delta'] = 0;
    }  
    if (!isset($arr_article[$key]['min_price_delta'])) {
        $arr_article[$key]['min_price_delta'] = 0;
    }  
}

// Приводим массив в нужный порядок
$k=0;
foreach ($arr_article as $key => $item) {
    $article_1C =  get_main_article_by_sku_fbs($ozon_sebest, $key);
    $priznak_nomenclaturi = 0;
      foreach ($arr_all_nomenklatura as $nomenclatura) {
         if (mb_strtolower($nomenclatura['main_article_1c']) ==  mb_strtolower($article_1C)) {
             $arr_article[$key]['poriad_number']  = $nomenclatura['number_in_spisok']; // порядковый номер
             $priznak_nomenclaturi = 1;
            break;
         }
        if ($priznak_nomenclaturi <> 1) { // Если нет товара в номенклатуре, то убираем эи товары вниз
            $arr_article[$key]['poriad_number']  = 1000000 + $k; // порядковый номер  
            $k++;

        }
}
}



foreach ($arr_article as $poriadok) {
    $arr_poriadok[] = $poriadok['poriad_number'];
}
sort($arr_poriadok);
$arr_temp= $arr_article; // временный массив, чтобы снова создать этот с этим же названием
unset($arr_article);

foreach($arr_poriadok as $number) {
    foreach ($arr_temp as $key=>$item) {
        if ($number == $item['poriad_number']) {
            $arr_article[$key] = $item;
            
        }
    }
    
}



// ***** формируем новый массив сумм с дополнительными данными
// unset($arr_sum_data);
// $arr_sum_data = make_array_sum($arr_article);

// echo "<pre>";
// print_r($arr_article);
/************************************************************************************************************ */
// Формируем  массив для вывода екселя
/************************************************************************************************************ */
$nomenclatura_2 = select_all_nomenklaturu($pdo);
// echo "<pre>";
// print_r($ozon_sebest);
// echo "*******************************************************************";
// print_r($nomenclatura_2);
// echo "*******************************************************************";
// print_r($arr_article);
// $file_name_report_excel = report_mp_make_excel_file_morzha($arr_excel, $name_mp_shop, $date_from, $date_to);
/************************************************************************************************************ */
/// КОНЕЦ формирования массива для екселя
/************************************************************************************************************ */


// ВЫВОД Первой ТАБЛИЦЫ ////////////////////////////////////////////////////
// require_once "print/new_sum_table.php";

// ВЫВОД ОСНОВНОЙ ТАБЛИЦЫ ////////////////////////////////////////////////////

require "print_one/real_money_one.php";
// echo "<br><a href = \"$file_name_report_excel\"> Ссылка для скачивания Отчета</a><br>";
// echo "<br><br><br>";

// print_r($arr_orders);
// ВЫВОД  ТАБЛИЦЫ FBO FBS////////////////////////////////////////////////////

// require "print/fbo_fbs_table.php";
// *********************************************** 0102957581-0048 ************************************
