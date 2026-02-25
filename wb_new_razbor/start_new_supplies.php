<?php

echo "<pre>";



require_once "../connect_db.php";
require_once "../pdo_functions/pdo_functions.php";

require_once 'libs/fpdf/fpdf.php'; // библиотккеа для создания ПДф файилов
require_once "functions/functions.php";
require_once "functions/recover_func.php"; // функции для восстановления работы вб
require_once "functions/make_1c_func.php"; // создания файла для 1С
require_once "functions/make_zip_func.php";
require_once "get_zakaz_by_check_date.php"; // функция выбора заказов по дате


//******************************************************************************************

$token_wb = $_POST['token'];
$Zakaz_v_1c = $_POST['Zakaz1cNumber'];
$wb_path = $_POST['wb_path'];

// Запись в таблицу Действия пользователя
insert_in_table_user_action($pdo, $userdata['user_login'] , "RAZBOR_WB Order№($Zakaz_v_1c)");

// die('Ostanovili rabotu / Dieknilu tut ');

// функция записи логов в файл
function write_info_filelog($path, $info_comment) {
    $stamp_date = date('Y-m-d H:i:s');
    file_put_contents( $path, PHP_EOL.$stamp_date."-".$info_comment ,FILE_APPEND);
    usleep(10000); // трата на времени на добавление на вывод данных на экран
};




/******************************************************************************************
 *  ************   Создаем каталог для сегодняшнего разбора
 ******************************************************************************************/

// C*********** НОВЫЙ ВАРИАНТ ПАПОК
$new_date = date('Y-m-d');
make_new_dir_z('../!all_razbor/wb/'.$new_date,0); // создаем папку с датой
$new_path = '../!all_razbor/wb/'.$new_date."/".$Zakaz_v_1c;
$path_qr_supply = $new_path.'/qr_code_supply';
$path_stikers_orders = $new_path.'/stikers_orders';
$path_arhives = $new_path.'/arhives';
$path_recovery = $new_path.'/recovery';


/******************************************************************************************
 *  ************   Формируем название файла для СТИКЕРОВ
 ******************************************************************************************/
$stikers_file_name = "Stikers_".$Zakaz_v_1c."_(".date("Y-m-d").").zip";
$path_for_zip_arhive_strikers = $path_arhives."/".$stikers_file_name; // путь к ЗИП архиву со стикерам
$QR_code_post_file_name = "QRcode_".$Zakaz_v_1c."_(".date("Y-M-d").").zip";

// Если Такой номер заказа на эту дату уже существует то выводим данные для скачивания
if(is_dir($new_path)) {
   
    $link_alarm_qr_code  = $path_arhives."/".$QR_code_post_file_name;
    
    //Проверяем ечть ли Признак, что все разбирали
    $check_alarm_marker = check_marker_recover_file($new_path);
    // echo "<br>++++$check_alarm_marker+++<br>";
    if ($check_alarm_marker == 1) {
        $recovery_file = $new_path."/not_ready_supply.json";
        echo "<br>По признакам разбор заказом не был закончен";
        echo "<br>";
        echo "<br>";
        echo "<a href=\"recovery_dostavka.php?filerecovery=$recovery_file\">Попытка продолжить разбор (перевод постаки в доставку)</a><br>";
        echo "<br>";
        echo "<br>";
       
    } else {
        echo "<a href=\"$path_for_zip_arhive_strikers\">Скачать стикеры</a><br>";
        echo "<a href=\"$link_alarm_qr_code\">Скачать Qr код поставки</a><br>";
        echo "<br>По признакам товары были переданы в Доставку";
        echo "<br>Такой номер ЗАКАЗА на сегодняшнюю дату уже существует";
    }
    echo "<br><a href=\"../index.php\">Вернуться</a>";
    die("<br><br>***************** ************ Попали в ветку, что уже разбирали этот заказ ************* *****************");
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

write_info_filelog ($file_Log_name,'Получаем все новые заказы с сайта ВБ'); // Вывод коммент-я на экран

//****************************************************************************************
// дата на которую нуэно собрать заказы 
//****************************************************************************************

if (isset($_POST['date_sbora_zakaza'])) {
    $date_orders_select = $_POST['date_sbora_zakaza']; // заказ на определенную дату
  } else {
    $date_orders_select = ''; // собираем все заказы
  }
 
//****************************************************************************************
// Получаем все новые заказы с сайта ВБ
//****************************************************************************************
sleep(1); // делаем трату 1 секунда. 
$arr_new_zakaz = select_order_by_check_date($token_wb, $date_orders_select) ;
// если вернулся пустой саммив, то ждем еще 2 секунды и снова пробуем достать заказы
if (!isset($arr_new_zakaz)) {
    sleep(2);
    $arr_new_zakaz = select_order_by_check_date($token_wb, $date_orders_select) ;
}

// ЕСЛИ НУЖНО СОХРАНИТЬ СЫРЫЕ ДАННЫЕ С ВБ 
$raw_data_json = json_encode($arr_new_zakaz, JSON_UNESCAPED_UNICODE);
file_put_contents($new_path."/".$Zakaz_v_1c." от ".date("Y-m-d")."_raw_data.json", $raw_data_json, FILE_APPEND); // добавляем данные в файл с накопительным итогом

// Сформировали массив с ключем - артикулом и значением - массив отправлений

write_info_filelog ($file_Log_name,'Формируем массив с ключем - артикулом и значением'); // Вывод коммент-я на экран

foreach ($arr_new_zakaz['orders'] as $items_tw) {
    $new_2_article = make_right_articl($items_tw['article']);
    $new_arr_new_zakaz[$new_2_article][] = $items_tw;
}

/******************************************************************************************
 *  ************   Начинаем главный разбор ассоциативного массива
 ******************************************************************************************/


foreach ($new_arr_new_zakaz  as $key => $items) {

    $result_insert_order_in_supply = 777;

    write_info_filelog ($file_Log_name,"Разбираем артикул: $key "); // запись в файл коммент-я на экран

//******************************************************************************************
    $time_script = count($new_arr_new_zakaz[$key]) * 50;
    write_info_filelog ($file_Log_name, "TimeScript = $time_script");
    set_time_limit($time_script);

    $right_article = make_right_articl($key);
    $name_postavka = $Zakaz_v_1c."-(".$right_article.") ".count($new_arr_new_zakaz[$key])."шт";

//*****************************************************************************************************************
// формируем одну поставку и туда суем весь товар с этим артикулом
//*****************************************************************************************************************

    $supplyId = make_postavka ($token_wb, $name_postavka); // номер поставки
    usleep(50000); // трата на создание Поставки 

/*****************************************************************************************************************
*  Вычитываем информацию о поставке. И вообще существует или она
*********************************************************************************************************************/    
$SupplayId_info = get_info_by_postavka ($token_wb, $supplyId['id']); // информация о поставке

  if (!isset($SupplayId_info['id'])) {
    write_info_filelog ($file_Log_name, "(СБОЙ) Поставка для арт.".$right_article." не создана. Название поставки :$name_postavka"); 
    for ($jjjj = 0; $jjjj < 20; $jjjj ++) {
        // unset($supplyId);
        write_info_filelog ($file_Log_name, "Повторный($jjjj) из (20) запуск создания поставка для арт.$right_article Название поставки :$name_postavka" ); // Вывод коммент-я на экран
        $supplyId = make_postavka ($token_wb, $name_postavka); // номер поставки
        usleep(50000); // трата на создание Поставки 
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
    
/*****************************************************************************************************************
**** Формируем перечень заказов выбранного артикула, для перенесения их в поставку, 
метод запрещает передавать за раз более 100 заказов
*********************************************************************************************************************/    
$limit_100 = 0;
$count_100 = 0;
// формируем массивы по сто заказов сформированы поартикульно
foreach ($items as $item) {

// *********************  формируем массив реальных заказов для 1С ******
$arr_for_1C_file_temp[$right_article]['count'] =  @$arr_for_1C_file_temp[$right_article]['count'] + 1;
$arr_for_1C_file_temp[$right_article]['price'] =  @$arr_for_1C_file_temp[$right_article]['price'] + $item['convertedPrice']/100;

 // формируем массивы по сто отправлений. Сформированы поартикульно
    $orders_m_id[$count_100]['orders'][$limit_100] = $item['id'];
    $limit_100++;
    if ($limit_100 == 99) {
        $limit_100 = 0;
        $count_100++;
    }
}

// теперь все эти массивы поочерредно запихиваем в поставку 
$send_count_orders = 0;

foreach ($orders_m_id as $number_arr => $one_array_orders) {

 $send_count_orders_temp = count($one_array_orders['orders']); // количетво отправленнных заказов в данной итерации цикла
 $send_count_orders = $send_count_orders + $send_count_orders_temp; // количество отправлений в поставке данного артикула
// Запускаем певервод отправлений на сборку 
 write_info_filelog ($file_Log_name, " Старт заказов арт.: $right_article на сборку! кол-во заказов в массиве = $send_count_orders;Номер массива соток() = $number_arr");
    make_sborku_one_article_one_zakaz ($token_wb, $supplyId['id'], $one_array_orders);

   echo ("<br> ********** result  ********** кол-во отправленных заказов = ($send_count_orders) ************<br>");
   usleep(500000); // трата 500 мс между запросами  
 
    // ***** теперь сравниваем количество отправленных заказов с количеством заказов  в поставке 
write_info_filelog ($file_Log_name, "Запрос IDS отправлений в поставке, нужно для понимания легли ли отправления в поставку");
$new_real_arr_orders = get_orders_from_supply($token_wb, $supplyId['id']); // список Заказов которые ТОЧНО полпали в Поставку
$return_count_orders =  count($new_real_arr_orders); //  количество заказов в поставке от ВБ

 write_info_filelog ($file_Log_name, " кол-во отпр. заказов : $send_count_orders - ! - Факт. кол-во заказов в поставке = $return_count_orders");



 // если не совпало количество, то повтораяем запрос 
  if ( $return_count_orders != $send_count_orders) {
       for ($jjj = 0; $jjj < 20; $jjj++)  {  
            write_info_filelog ($file_Log_name, "(ALARM) Не хватает заказов в Поставкe - цикл:$jjj");
            write_info_filelog ($file_Log_name, " ПОВТОР = $jjj; Запускаем заказы артикула - $right_article на сборку! кол-во отправленных заказо = $send_count_orders; Номер массива = $number_arr");
            make_sborku_one_article_one_zakaz ($token_wb, $supplyId['id'], $one_array_orders);
            
            usleep(500000); // трата 300 мс между запросами 
            write_info_filelog ($file_Log_name, " ПОВТОР! Запрос IDS отправлений в поставке, нужно для понимания легли ли отправления в поставку");

            $new_real_arr_orders = get_orders_from_supply($token_wb, $supplyId['id']); // список Заказов которые ТОЧНО полпали в Поставку
            $return_count_orders =  count($new_real_arr_orders); //  вернули количество заказов в поставке от ВБ
        write_info_filelog ($file_Log_name, " ПОВТОР! кол-во отпр. заказов : $send_count_orders - ! - Факт. кол-во заказов в поставке = $return_count_orders");
          
         if ( $return_count_orders == $send_count_orders) {
        // Если количество товаров совпало то выходим из цикла
            break 1;
         } 
       }

  }
}

/*************************************************************************************************
 *************    Формируем и сохраняем стикеры себе на комп
 ************************************************************************************************/
write_info_filelog ($file_Log_name, " Формируем и сохраняем стикеры себе на комп");

if (isset($new_real_arr_orders)) { // проверят есть ли массив 
    $ArrFileNameForZIP[] = get_stiker_from_supply ($token_wb, $new_real_arr_orders, $Zakaz_v_1c , $right_article , $path_stikers_orders); // формируем стикеры за этой поставки
   } else {
    write_info_filelog ($file_Log_name, "НЕТ данных для формирования этикеток. Возможно заказы не подгрузили в поставку WB№_".$supplyId['id']);
    echo ("НЕТ данных для формирования этикеток. Возможно заказы не подгрузили в поставку WB№_".$supplyId['id']." .<br>");
   }



//*********** удаляем временные массивы ****************
unset($new_real_arr_orders);
unset($orders_m_id);  // 


}



/*************************************************************************************************
 *************    НОвый массив 1С с учетом облманых массивов по списываию данных с сайта ВБ
 ************************************************************************************************/
output_print_comment("Формируем файл для 1С"); // Вывод коммент-я на экран
write_info_filelog ($file_Log_name, " Формируем файл для 1С");
// возвращаем название 1С файла
$file_name_1c_list_q = make_1c_file ($arr_for_1C_file_temp, $Zakaz_v_1c, $new_path);

/******************************************************************************************
 *  ***************   Формируем архив со стикерами для данного Заказа
 ******************************************************************************************/
write_info_filelog ($file_Log_name, " Формируем архив со стикерами для данного Заказа");
make_stikers_zip ($ArrFileNameForZIP, $path_for_zip_arhive_strikers, $Zakaz_v_1c, $path_stikers_orders, $new_path, $file_name_1c_list_q );

 /******************************************************************************************
 **********************   Формируем JSON со списком реальных заказов (ДЛЯ ОТРАБОТКИ)
 ******************************************************************************************/
write_info_filelog ($file_Log_name, " Формируем JSON со списком реальных заказов (ДЛЯ ОТРАБОТКИ)");
$filedata_json_orders = json_encode($arr_for_1C_file_temp, JSON_UNESCAPED_UNICODE);
file_put_contents($new_path."/".$Zakaz_v_1c." от ".date("Y-m-d")."_real_orders.json", $filedata_json_orders, FILE_APPEND); // добавляем данные в файл с накопительным итогом


/******************************************************************************************
 **************************   Формируем JSON со списком поставок (Для продолжения обработки)
 ******************************************************************************************/
 
$filedata_json = json_encode($arr_supply, JSON_UNESCAPED_UNICODE);
$file_json_new = $new_path."/".$Zakaz_v_1c." от ".date("Y-m-d").".json";
file_put_contents($file_json_new, $filedata_json, FILE_APPEND); // добавляем данные в файл с накопительным итогом

// для восстановления 
$recovery_array = ["token"             => $token_wb,
                   "json_path"         => $file_json_new,
                   "path_qr_supply"    => $path_qr_supply,
                   "path_arhives"      => $path_arhives,
                   "downloads_stikers" => $path_for_zip_arhive_strikers,
                   "path_recovery"     => $path_recovery,
                   "Zakaz1cNumber"     => $Zakaz_v_1c];
$recovery_data_json = json_encode($recovery_array, JSON_UNESCAPED_UNICODE);
$file_recovery_data_json = $new_path."/not_ready_supply.json"; // создаем файл для продолжение перевода в доставку товаров
file_put_contents($file_recovery_data_json, $recovery_data_json,  FILE_APPEND); // добавляем данные в файл с накопительным итогом


/******************************************************************************************
 *  **************   Выводим кнопку для продолжения работы -> перевод поставок в ДОСТАВКУ
 ******************************************************************************************/

echo "<br>";
 echo "<a href=\"$path_for_zip_arhive_strikers\">СКАЧАТЬ АРХИВ СО СТИКЕРАМИ И ФАЙЛОМ для 1С(новый)</a>"; // 

echo <<<HTML
<form action="make_dostavka.php" method="post">
<label for="wb">ПЕРЕВЕСТИ ЗАКАЗЫ В ДОСТАВКУ</label><br>
<label for="wb">Номер заказа</label><br>
  <input hidden type="text" name="token" value="$token_wb">
  <input hidden type="text" name="json_path" value="$file_json_new">
  
  <input hidden type="text" name="path_qr_supply" value="$path_qr_supply">
  <input hidden type="text" name="path_arhives" value="$path_arhives">
  <input hidden type="text" name="downloads_stikers" value="$path_for_zip_arhive_strikers">

  <input hidden type="text" name="path_recovery" value="$path_recovery">

  <input hidden type="text" name="Zakaz1cNumber" value="$Zakaz_v_1c">
  <input type="submit" value="В ДОСТАВКУ">
</form>
HTML;


/******************************************************************************************
 *  **************  Запись в БД со ссылкой на архив этикеток
 ******************************************************************************************/

$date_otgruzki = date('Y-m-d');
$stmt = $pdo->prepare("SELECT `name_market` FROM tokens WHERE token='$token_wb'");
$stmt->execute([]);
$arr_name_shop = $stmt->fetchAll(PDO::FETCH_COLUMN);
$name_shop = $arr_name_shop[0];
insert_info_in_table_razbor($pdo, $name_shop, $Zakaz_v_1c, $date_otgruzki,  $path_for_zip_arhive_strikers, '');

/// удаляем файл АВТОСКЛАДА, который сообщает о том, что нужно обновить данные об остатках с 1С
$filePath_autisklad = '../autosklad/uploads/priznak_razbora_net.txt';
if (file_exists($filePath_autisklad)) {
    if (unlink($filePath_autisklad)) {
        echo "Файл Признак разбора удален.<br>";
    } 
}
// unlink('../autosklad/uploads/priznak_razbora_net.txt');

die('РАЗБОР ОКОНЧЕН (STOP)');






