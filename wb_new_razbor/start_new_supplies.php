<?php
require_once "../connect_db.php";


require_once 'libs/fpdf/fpdf.php'; // библиотккеа для создания ПДф файилов



require_once "functions/functions.php";
require_once "functions/recover_func.php"; // функции для восстановления работы вб
require_once "functions/make_1c_func.php"; // создания файла для 1С
require_once "functions/make_zip_func.php";

require_once "get_zakaz_by_check_date.php"; // функция выбора заказов по дате


// die('Ostanovili rabotu / Dieknilu tut ');

// функция записи логов в файл
function write_info_filelog($path, $info_comment) {
    $stamp_date = date('Y-m-d H:i:s');
    file_put_contents( $path, PHP_EOL.$stamp_date."-".$info_comment ,FILE_APPEND);
    usleep(10000); // трата на времени на добавление на вывод данных на экран
};


//******************************************************************************************

$token_wb = $_POST['token'];
$Zakaz_v_1c = $_POST['Zakaz1cNumber'];
$wb_path = $_POST['wb_path'];

/******************************************************************************************
 *  ************   Создаем каталог для сегодняшнего разбора
 ******************************************************************************************/



//******************************************************************************************

$new_date = date('Y-m-d');
make_new_dir_z('reports/'.$new_date,0); // создаем папку с датой
$new_path = 'reports/'.$new_date."/".$Zakaz_v_1c;
$path_qr_supply = $new_path.'/qr_code_supply';
$path_stikers_orders = $new_path.'/stikers_orders';
$path_arhives = $new_path.'/arhives';
$path_recovery = $new_path.'/recovery';


// Если Такой номер заказа на эту дату уже существует то выводим данные для скачивания
if(is_dir($new_path)) {
    $link_alarm_stikers  = $path_arhives."/"."Stikers_".$Zakaz_v_1c." от ".date("Y-M-d").".zip";
    $link_alarm_qr_code  = $path_arhives."/"."QRcode-".$Zakaz_v_1c." от ".date("Y-M-d").".zip";
    
    echo "<a href=\"$link_alarm_stikers\">Скачать стикеры</a><br>";
    echo "<a href=\"$link_alarm_qr_code\">Скачать Qr код поставки</a><br>";
    die("Такой номер ЗАКАЗА на сегодняшнюю дату уже существует<br><a href=\"index.php\">Вернуться</a>");
}


/// проверяем  наличие папки с таким номером заказа
make_new_dir_z($new_path,0); // создаем папку с номером заказа
make_new_dir_z($path_qr_supply,0); // создаем папку с QR
make_new_dir_z($path_stikers_orders,0); // создаем папку со стикерами
make_new_dir_z($path_arhives,0); // создаем папку с архивами
make_new_dir_z($path_recovery,0); // создаем папку с инфой по восстановлению


//********************* Выводим картику с ожиданием *******************************************


$file_Log_name = $new_path.'/filelog.txt'; // название файла с логами

//********************* OutPut КОММЕНТАРИЙ *******************************************
write_info_filelog ($file_Log_name,'Начали разбор заказов');
//********************* OutPut КОММЕНТАРИЙ *******************************************
write_info_filelog ($file_Log_name,'Формирование папок');






// Формируем файл для восстановления работы 

write_info_filelog ($file_Log_name, 'Формируем файл для восстановления работы'); // Вывод коммент-я на экран
create_marker_recover_file($new_path); // создается маркерный файл, работа по сборке не закончена


// die('ffffffffffffffff');
write_info_filelog ($file_Log_name,'Получаем все новые заказы с сайта ВБ'); // Вывод коммент-я на экран


//****************************************************************************************
// Получаем все новые заказы с сайта ВБ
//****************************************************************************************


// $arr_new_zakaz = get_all_new_zakaz ($token_wb);

// **** ВАЖНО !!!!!!!! (нужно закоментить основной массив для основного сбора /////\\\\||||)


/****************************************************************************************
 *  ЗАТЫЧКА ДЛЯ ОСОБЫХ РАЗБОРОВ
 ***************************************************************************************/
// СБОРКА ЗАКАЗОВ ЗА ОПРЕДЕЛЕННУЮ ДАТУ

// $raw_arr_orders_t = get_all_new_zakaz ($token_wb);

//  foreach ($raw_arr_orders_t['orders'] as $order) {

//     if (substr($order['createdAt'],0,10) == '2024-05-03') {
//         $arr_new_zakaz['orders'][] = $order;
//     }
  
//   }
  

$date_orders_select = ''; // дата на которую нуэно собрать заказы (ПОКА ВРУЧНУЮ ИЗМЕНЯЕТСЯ В ФУНКЦИИ)
$raw_arr_orders = select_order_by_check_date($token_wb, $date_orders_select) ;

// echo "<pre>";
// print_r($raw_arr_orders );

// die('kkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkkk');



/****************************************************************************************
 *  КОНЕЦ ЗАТЫЧКИ ДЛЯ ОСОБЫХ РАЗБОРОВ
 ***************************************************************************************/














// Сформировали массив с ключем - артикулом и значением - массив отправлений

write_info_filelog ($file_Log_name,'Формируем массив с ключем - артикулом и значением'); // Вывод коммент-я на экран

foreach ($arr_new_zakaz['orders'] as $items) {
    // $new_arr_new_zakaz[$items['article']][] = $items;
    $new_2_article = make_right_articl($items['article']);
    $new_arr_new_zakaz[$new_2_article][] = $items;
}

/******************************************************************************************
 *  ************   Начинаем главный разбор ассоциативного массива
 ******************************************************************************************/

foreach ($new_arr_new_zakaz  as $key => $items) {
    $priznzak_net_massiva=0;
    $priznzak_ne_ves_massiv=0;
    $result_insert_order_in_supply = 777;

    write_info_filelog ($file_Log_name,"Разбираем артикул: $key "); // Вывод коммент-я на экран

//******************************************************************************************
    $time_script = count($new_arr_new_zakaz[$key]) * 50;
    write_info_filelog ($file_Log_name, "TimeScript = $time_script");
    set_time_limit($time_script);

    $right_article = make_right_articl($key);
    $name_postavka = $Zakaz_v_1c."-(".$right_article.") ".count($new_arr_new_zakaz[$key])."шт";
    // формируем одну поставку и туда суем весь товар с этим артикулом
    $supplyId = make_postavka ($token_wb, $name_postavka); // номер поставки
    usleep(20000); // трата на создание Поставки 
/*****************************************************************************************************************
*  Вычитываем информацию о поставке. И вообще существует или она
*********************************************************************************************************************/    
$SupplayId_info = get_info_by_postavka ($token_wb, $supplyId['id']); // информация о поставке
  if (!isset($SupplayId_info['id'])) {
    write_info_filelog ($file_Log_name, "(СБОЙ) Поставка для арт.".$right_article." не создана. Название поставки :$name_postavka"); 
    for ($jjjj=0; $jjjj < 20; $jjjj ++) {
        // unset($supplyId);
        write_info_filelog ($file_Log_name, "Повторный($jjjj) из (20) запуск создания поставка для арт.$right_article Название поставки :$name_postavka" ); // Вывод коммент-я на экран
        $supplyId = make_postavka ($token_wb, $name_postavka); // номер поставки
        usleep(20000); // трата на создание Поставки 
        $SupplayId_info = get_info_by_postavka ($token_wb, $supplyId['id']); // информация о поставке
        if (isset($SupplayId_info['id'])) {
            write_info_filelog ($file_Log_name, "(УСПЕШНО) Поставка для арт.$right_article создана, id поставки ".$supplyId['id']." на ($jjjj) цикле" ); // Вывод коммент-я на экран
            break 1;    
        }

    }
  } else {
    write_info_filelog ($file_Log_name, "(УСПЕШНО) Поставка для арт.$right_article создана, id поставки ".$supplyId['id'] ); // Вывод коммент-я на экран
  }
//********************************************************************************************************************************** */

usleep(300000); // трата на создание Поставки на сайте 1С
    $arr_supply[$right_article] =  array('supplayId'      =>  $supplyId['id'],
                                         'name_postavka'  =>  $name_postavka);
    
    $count_order_art=0; // количество Заказов в поставке
    
/*****************************************************************************************************************
*  ПОПРОБОВАТЬ СДЕЛАТЬ ДРУГОЙ АЛГОРИТМ, ОТПРАВЛЯЕМ ЗАКАЗ В ПОСТАВКУ И СРАЗУ СМОТРИМ, ЧТО ЗАКАЗ ЛЕГ В ПОСТАВКУ
*********************************************************************************************************************/    
    foreach ($items as $item) {
        $orderId = $item['id']; // номер заказа для добавления в сборку
 
    //****  Запуск добавления товара в поставку - НЕВОЗВРАТНАЯ ОПЕРАЦИЯ ***********************************
    //****  раскоментировать при работе     -     НЕВОЗВРАТНАЯ ОПЕРАЦИЯ ***********************************
    //****  раскоментировать при работе     -     НЕВОЗВРАТНАЯ ОПЕРАЦИЯ *******************************
    
    write_info_filelog ($file_Log_name, "Запускаем заказ $orderId на сборку");
    make_sborku_one_article_one_zakaz ($token_wb, $supplyId['id'], $orderId);
    $count_order_art++;
    
    usleep(30000); // трата на времени на добавление товара в поставку  
    
    $result_insert_order_in_supply = test_find_order_in_supply ($token_wb, $orderId, $supplyId['id']); // Проверяем добав-ся заказ в поставку или нет
// Проверка того что заказ добавился в поставку
    for ($jjj = 0; $jjj < 20; $jjj++)  {  
        if ($result_insert_order_in_supply != 0) { // если заказа нет в поставке, то запускаем повтор добавления заказа в поставку
            write_info_filelog ($file_Log_name,"(СБОЙ)Признак $jjj обмена = $result_insert_order_in_supply ; Старт ПОВТОРА доб-я Заказа: $orderId в Поставку: ".$supplyId['id'] ); // Вывод коммент-я на экран
            make_sborku_one_article_one_zakaz ($token_wb, $supplyId['id'], $orderId);
        usleep(10000); // трата на времени на добавление товара в поставку  
            $result_insert_order_in_supply = test_find_order_in_supply ($token_wb, $orderId, $supplyId['id']); // Проверяем добав-ся заказ в поставку или нет
        } else {
            // если появился в поставке, то запишем его в файл восстновления 
            if ($jjj != 0) { // если при первой итерации сразу попали сюда то не выводим это сообщение
                write_info_filelog ($file_Log_name, "Норме цикла ($jjj); Заказ: $orderId появился в поставке:"); // Вывод коммент-я на экран
            }
            make_recovery_json_orders_file($path_recovery, $orderId, $supplyId['id'], $key); 
            break 1;

        }

    }
}


usleep(300000); // трата на времени на добавление товара в поставку  
    $arr_real_orders = get_orders_from_supply($token_wb, $supplyId['id']); // список Заказов которые ТОЧНО полпали в Поставку

    foreach ($arr_real_orders as $orders) {
        $new_real_arr_orders[] = $orders['id']; // массив с номерами заказов
    }

// Проверяем есть ли хоть один заказ в Поставке (По мнению ВБ)
    if (!isset($new_real_arr_orders)) {
        $priznzak_net_massiva = 1;
        // ecли этикеток нет, то снова делаем их запрос 
            for ($error_job = 0 ;$error_job < 12; $error_job++) {
                write_info_filelog ($file_Log_name, "(ALARM) Нет Заказов в поставке:".$supplyId['id']." - цикл :$error_job"); // Вывод коммент-я на экран
              usleep(100000); // 0,1 sec
                $arr_real_orders_error = get_orders_from_supply($token_wb, $supplyId['id']); // список Заказов которые ТОЧНО полпали в Поставку

                foreach ($arr_real_orders_error as $orders) {
                    $new_real_arr_orders[] = $orders['id']; // массив с номерами заказов
                }
            // когда появился хоть один заказ  в поставке
            if (isset($new_real_arr_orders)) { // Появились заказы в поставке
                write_info_filelog ($file_Log_name, "(DIS_ALARM) Появились заказы в поставке:".$supplyId['id']." - цикл :$error_job"); // Вывод коммент-я на экран
            break;
                    
                }
        } 
    }

// проверяем, чтобы количество заказов совпадало отправленные и фактически загруженные

if (count($new_real_arr_orders) < $count_order_art) { 
    $priznzak_ne_ves_massiv = 1;
    for ($jj = 0; $jj < 20; $jj++) {
    //********************* ДАТА ВРЕМЯ + КОММЕНТАРИЙ *******************************************
         write_info_filelog ($file_Log_name, "(ALARM) Не хватает заказов в Поставкe - цикл:$jj");
    //******************************************************************************************
  
     $real_temp_count = count($new_real_arr_orders);
            unset($new_real_arr_orders);
            unset($arr_real_orders_error);

            write_info_filelog ($file_Log_name, "(ALARM) Не хватает Заказов в поставке ($real_temp_count), должно быть ($count_order_art)"); // Вывод коммент-я на экран
         usleep(300000); // 0.3 sec тратим время перед следующим запросом

            $arr_real_orders_error = get_orders_from_supply($token_wb, $supplyId['id']); // список Заказов которые ТОЧНО полпали в Поставку

            foreach ($arr_real_orders_error as $orders) {
                $new_real_arr_orders[] = $orders['id']; // массив с номерами заказов
            }
   // Если все заказы добавились в поставку      
     if (count($new_real_arr_orders) == $count_order_art) { 
        $real_temp_count = count($new_real_arr_orders);
           output_print_comment("(DIS_ALARM) Дописались остальные заказы ($real_temp_count), должно быть ($count_order_art)"); // Вывод коммент-я на экран
           break;
        }
 }
}

/*************************************************************************************************
 *************    Формируем и сохраняем стикеры себе на комп
 ************************************************************************************************/

if (isset($new_real_arr_orders)) { // проверят есть ли массив 
    $ArrFileNameForZIP[] = get_stiker_from_supply ($token_wb, $new_real_arr_orders, $Zakaz_v_1c , $right_article , $path_stikers_orders); // формируем стикеры за этой поставки
} else {
    echo ("НЕТ данных для формирования этикеток. Возможно заказы не подгрузили в поставку WB№_".$supplyId['id']." .<br>");
   }
// *********************  формируем массив реальных заказов для 1С ******

 if (($priznzak_net_massiva == 0) AND ($priznzak_ne_ves_massiv == 0)) {
    $arr_for_1C_file_temp[$key] = $arr_real_orders; // Массчив для 1С файла (и для JS файла)

 } else {
    $arr_for_1C_file_temp[$key] = $arr_real_orders_error; // Массчив для 1С файла (и для JS файла)
 }
   



//*********** удаляем временные массивы ****************
    unset($arr_real_orders);
    unset($arr_real_orders_error);
    unset($new_real_arr_orders);


}









/*************************************************************************************************
 *************    НОвый массив 1С с учетом облманых массивов по списываию данных с сайта ВБ
 ************************************************************************************************/
output_print_comment("Формируем файл для 1С"); // Вывод коммент-я на экран
// возвращаем название 1С файла
$file_name_1c_list_q = make_1c_file ($arr_for_1C_file_temp, $new_arr_new_zakaz, $Zakaz_v_1c, $new_path);

/******************************************************************************************
 *  ***************   Формируем архив со стикерами для данного Заказа
 ******************************************************************************************/
make_stikers_zip ($ArrFileNameForZIP, $path_arhives, $Zakaz_v_1c, $path_stikers_orders, $new_path, $file_name_1c_list_q );

    $link_download_stikers = $path_arhives."/"."Stikers_".$Zakaz_v_1c." от ".date("Y-M-d").".zip";


/******************************************************************************************
 *  ************************   Формируем JSON со списком поставок (Для продолжения обработки)
 ******************************************************************************************/
 
$filedata_json = json_encode($arr_supply, JSON_UNESCAPED_UNICODE);
$file_json_new = $new_path."/".$Zakaz_v_1c." от ".date("Y-M-d").".json";
file_put_contents($file_json_new, $filedata_json, FILE_APPEND); // добавляем данные в файл с накопительным итогом

// для восстановления 
$recovery_array = ["token"             => $token_wb,
                   "json_path"         => $file_json_new,
                   "path_qr_supply"    => $path_qr_supply,
                   "path_arhives"      => $path_arhives,
                   "downloads_stikers" => $link_download_stikers,
                   "Zakaz1cNumber"     => $Zakaz_v_1c];
$recovery_data_json = json_encode($arr_supply, JSON_UNESCAPED_UNICODE);
$file_recovery_data_json = $new_path."/not_ready_supply.json"; // создаем файл для продолжение перевода в доставку товаров
file_put_contents($file_recovery_data_json, $recovery_data_json,  FILE_APPEND); // добавляем данные в файл с накопительным итогом

/******************************************************************************************
 *  *********************   Формируем JSON со списком реальных заказов (ДЛЯ ОТРАБОТКИ)
 ******************************************************************************************/
 
 $filedata_json_orders = json_encode($arr_for_1C_file_temp, JSON_UNESCAPED_UNICODE);
 file_put_contents($new_path."/".$Zakaz_v_1c." от ".date("Y-M-d")."_real_orders.json", $filedata_json_orders, FILE_APPEND); // добавляем данные в файл с накопительным итогом


/******************************************************************************************
 *  **************   Выводим кнопку для продолжения работы -> перевод поставок в ДОСТАВКУ
 ******************************************************************************************/
$link_q1=$path_arhives."/"."Stikers_".$Zakaz_v_1c." от ".date("Y-M-d").".zip";

 echo "<a href=\"$link_q1\">СКАЧАТЬ АРХИВ СО СТИКЕРАМИ И ФАЙЛОМ для 1С(новый)</a>"; // 

echo <<<HTML
<form action="make_dostavka.php" method="post">
<label for="wb">ПЕРЕВЕЗТИ ЗАКАЗЫ В ДОСТАВКУ</label><br>
<label for="wb">Номер заказа</label><br>
  <input hidden type="text" name="token" value="$token_wb">
  <input hidden type="text" name="json_path" value="$file_json_new">
  
  <input hidden type="text" name="path_qr_supply" value="$path_qr_supply">
  <input hidden type="text" name="path_arhives" value="$path_arhives">
  <input hidden type="text" name="downloads_stikers" value="$link_download_stikers">

  <input hidden type="text" name="path_recovery" value="$path_recovery">

  <input hidden type="text" name="Zakaz1cNumber" value="$Zakaz_v_1c">
  <input type="submit" value="В ДОСТАВКУ">
</form>
HTML;

die('РАЗБОР ОКОНЧЕН (STOP)');






