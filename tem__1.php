<?php

$offset = "";
require_once  "connect_db.php";
require_once "libs/PHPExcel-1.8/Classes/PHPExcel.php";


// print('Next eeDate ' . $date_minus_one_day);
$path = "!!!_zz/";
// $dirs =  list_files($path);

$dirs  = scandir($path );

$ff = count($dirs)-1;
$ff2 = count($dirs)-2;
unset($dirs[$ff]);
unset($dirs[$ff2]);

echo "<pre>";
// print_r($dirs);

// die();

// $dir = '!!2025.04.22';
// $our_date  = preg_replace('/[^0-9.]/', '', $dir);
// $our_date = str_replace('.', '-', $our_date);
// echo "$our_date <br>";

// $ggg = list_files($path.$dir); 
// $files = glob($path.$dir.'/*.xlsx');


// print_r($dirs);

foreach ($dirs as $dir) {
  $our_date  = preg_replace('/[^0-9.]/', '', $dir);
  $our_date = str_replace('.', '-', $our_date);
  // echo "$our_date<br>";
  $files = glob($path.$dir.'/*.xlsx'); // наименоваяния фаилов в директории с датой
  
  if (count($files) >0) {
  // print_r($files);
    $art_all_date_all_artticle[$our_date]  = parce_excel ($files) ;
  }



}

print_r($art_all_date_all_artticle);

// die();
foreach ($art_all_date_all_artticle as $date=>$items) {
    foreach ($items as $article=>$g) {
         foreach ($g as $shop_name=>$count) {

  echo "$shop_name, $article, $count, $date <br>";
  insert_data_about_sell_fbo_ozon($pdo, $shop_name, $article, $count, $date);


         }
    }
}

die();



/*********************************************************************************************
 * 
 *******************************************************************************************/

function list_files($path)
{
	if ($path[mb_strlen($path) - 1] != '/') {
		$path .= '/';
	}
 
	$files = array();
	$dh = opendir($path);
	while (false !== ($file = readdir($dh))) {
		if ($file != '.' && $file != '..' && !is_dir($path.$file) && $file[0] != '.') {
			$files[] = $file;
		}
	}
 
	closedir($dh);
	return $files;
}

/*********************************************************************************************
 * 
 *******************************************************************************************/

function parce_excel ($files) {

    foreach ($files as $file_name) {
        // Создаем файл для 1С
// echo "<br><b>$file_name</b><br>";  
        $xls = PHPExcel_IOFactory::load($file_name);

        $xls->setActiveSheetIndex(0);
        $sheet = $xls->getActiveSheet();

        /// Форматируем екскль документ
        $sheet->getColumnDimension("A")->setWidth(3);
              // перенос текста на след строку
$i = 3;
$temp="";
       do {
if ($sheet->getCell('L'.$i)->getValue() > 0 ) {
        $temp = $sheet->getCell('A'.$i)->getValue();
        $stocks_wb_anmaks = $sheet->getCell('B'.$i)->getValue();
        $stocks_wb_ip_zel = $sheet->getCell('D'.$i)->getValue();
        $stocks_ozon_anmaks = $sheet->getCell('F'.$i)->getValue();
        $stocks_ozon_ip_zel = $sheet->getCell('H'.$i)->getValue();
        $stocks_ya_anmaks_fbs = $sheet->getCell('J'.$i)->getValue();

    //  echo "<br> $temp   +++ $i ++++  $file_name <br>";
// echo "$i <br>";
           $i++;
           $arr_article[$temp]['wb_anmaks']   = @$arr_article[$temp]['wb_anmaks']    + $stocks_wb_anmaks;
           $arr_article[$temp]['wb_ip_zel']   = @$arr_article[$temp]['wb_ip_zel']    + $stocks_wb_ip_zel;
           $arr_article[$temp]['ozon_anmaks'] = @$arr_article[$temp]['ozon_anmaks']  + $stocks_ozon_anmaks;
           $arr_article[$temp]['ozon_ip_zel'] = @ $arr_article[$temp]['ozon_ip_zel'] + $stocks_ozon_ip_zel;
           $arr_article[$temp]['ya_anmaks_fbs'] = @$arr_article[$temp]['ya_anmaks_fbs'] + $stocks_ya_anmaks_fbs;
} else {
    
    $i++;
}
       } while ($temp  != 'ИТОГО');
  
            unset ($arr_article['ИТОГО']);
    }

$xls->disconnectWorksheets();
unset($xls);


        return  $arr_article;

} 




/****************************************************************************************
 * Функция вставки в базу данных данных о продажах
 ****************************************************************************************/
function insert_data_about_sell_fbo_ozon($pdo, $shop_name, $a_1c_article, $fbo_sell, $date) {
$sth = $pdo->prepare("INSERT INTO `z_ozon_fbo_sell` SET `shop_name`= :shop_name, `1c_article` = :1c_article, 
                                       `fbo_sell`= :fbo_sell, `type_sklad`= :type_sklad, `date` =:date");

$sth->execute(array('shop_name' => $shop_name, 
                    '1c_article' => $a_1c_article,
                    'fbo_sell' => $fbo_sell,
                    'type_sklad' => 'fbs',
                    'date' => $date));

}

