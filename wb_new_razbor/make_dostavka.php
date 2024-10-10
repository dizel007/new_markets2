<?php
require_once "../connect_db.php";

require_once "functions/functions.php";
require_once "functions/recover_func.php";
require_once "functions/send_mail_func.php";

// require_once "functions/dop_moduls_for_orders.php";
usleep(500000); // трата на транзакции на сайте ВБ (перевод состояния поставок)


// функция записи логов в файл
function write_info_filelog_2($path, $info_comment) {
    $stamp_date = date('Y-m-d H:i:s');
    file_put_contents( $path, PHP_EOL.$stamp_date."-".$info_comment ,FILE_APPEND);
    usleep(10000); // трата на времени на добавление на вывод данных на экран
};

// $new_path = 'reports/'.$new_date."/".$Zakaz_v_1c;

$file_json = $_POST['json_path'];
$token_wb = $_POST['token'];
// $wb_path = $_POST['wb_path'];

$path_qr_supply =  $_POST['path_qr_supply'];
$path_arhives  = $_POST['path_arhives'];
$link_downloads_stikers = $_POST['downloads_stikers']; //
$path_recovery = $_POST['path_recovery']; 
$Zakaz_v_1c = $_POST['Zakaz1cNumber'];

$data = file_get_contents($file_json);
$arr_data = json_decode($data,true);


$file_Log_name = $path_arhives.'/..'; // название файла с логами
$file_Log_name = $file_Log_name.'/filelog.txt'; // название файла с логами


//********************* OutPut КОММЕНТАРИЙ *******************************************
write_info_filelog_2 ($file_Log_name,"Начали собирать Заказ :$Zakaz_v_1c");

echo "Начали собирать Заказ :$Zakaz_v_1c.<br>";
echo "<pre>";
// print_r($arr_data);
// die('DOST');

/************************************************************************************************
 *  ***************   Перебираем массив поставок и отправляем в доставку ************************
 *  ***************   и получаем QR код каждой поставки                   ************************
 ************************************************************************************************/
foreach ($arr_data as $key=>$supply) {
    echo "<br> Номер поставки :".$supply['supplayId']."; Название поставки :".$supply['name_postavka']."<br>";  

    put_supply_in_deliver ($token_wb, $supply['supplayId']); // отправляем поставку в доставку
        usleep(500000); // трата на формирование этикетки
    $app_qr_pdf_file_names[] = get_qr_cod_supply($token_wb, $supply['supplayId'], $supply['name_postavka'] ,$path_qr_supply);
}

echo "<br> ИНФОРМАЦИЯ ПО QR кодам Поставки (ДЛЯ ОТРАБОТКИ)<br>";  
echo "<pre>";
print_r($app_qr_pdf_file_names);


/******************************************************************************************
 *  ***************   Формируем архив с QR кодам поставок ********************************
 ******************************************************************************************/
$zip_new = new ZipArchive();
$zip_arhive_name = "QRcode_".$Zakaz_v_1c."_(".date("Y-M-d").").zip";
$zip_new->open($path_arhives."/".$zip_arhive_name, ZipArchive::CREATE|ZipArchive::OVERWRITE);
 foreach ($app_qr_pdf_file_names as $zips) {
    $zip_new->addFile($path_qr_supply."/".$zips, "$zips"); // Добавляем пдф файлы
 }
    $zip_new->close(); 

    $link_downloads_qr_codes = $path_arhives."/".$zip_arhive_name;
echo <<<HTML
<a href="$link_downloads_stikers"> Стикеры заказов</a>
<a href="$link_downloads_qr_codes"> QR коды поставки</a>

HTML;



// высылаем на почту письмо с данными
// sendmail($Zakaz_v_1c, $link_downloads_stikers, $link_downloads_qr_codes);





/// Запись в ВБ со ссылкой на архив этикеток
$date_otgruzki = date('Y-m-d');
$stmt = $pdo->prepare("SELECT `name_market` FROM tokens WHERE token='$token_wb'");
$stmt->execute([]);
$arr_name_shop = $stmt->fetchAll(PDO::FETCH_COLUMN);
$name_shop = $arr_name_shop[0];


// Корректируем адрес QR кодов поставки
$first_adress_part = DOMAIN_NAME.'/wb_new_razbor';
$link2 = str_replace('..','', $link_downloads_qr_codes);
$link2 = str_replace('\\','/', $link2);
$link2 = $first_adress_part."/".$link2;

// Обновляем ссылку в БД на QR коды поставки
$stmt = $pdo->prepare("UPDATE `table_razbor` SET `link2` = :link2 WHERE `type_shop` = :type_shop AND
          `number_order` = :number_order AND `date_razbora` = :date_razbora " );
$stmt->execute(array('link2'       => $link2,
                    'type_shop'    => $name_shop, 
                    'number_order' => $Zakaz_v_1c, 
                    'date_razbora' => $date_otgruzki));




// дошли до конца и удаляем маркерный файл о незаконченности выполения скрипта
delete_marker_recover_file($path_recovery); 


die('<br><br><br>ПЕРЕДАНО В ДОСТАВКУ');

