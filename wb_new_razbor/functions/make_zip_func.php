<?php
/************************************************************************************************************
**** Функуия формирует архив, чо всеми СТИКЕРАМИ с ВБ *******************************************************
*************************************************************************************************************/
function make_stikers_zip ($ArrFileNameForZIP, $path_arhives, $Zakaz_v_1c, $path_stikers_orders, $new_path, $file_name_1c_list_q ) {
    $zip_new = new ZipArchive();
    $zip_new->open($path_arhives, ZipArchive::CREATE|ZipArchive::OVERWRITE);
    
    foreach ($ArrFileNameForZIP as $zips) {

        $zip_new->addFile($path_stikers_orders."/".$zips, "$zips"); // Добавляем пдф файлы

    }
    
    $zip_new->addFile($new_path."/".$file_name_1c_list_q, "$file_name_1c_list_q"); // добавляем для НОВЫЙ 1С файл /// *****************
    $zip_new->close();  
}