<?php
/**
 *  ФУНЦКИЯ НУЖНА ДЛЯ ОТЧЕТОВ ОЗОНА 
 */
function get_sebestiomost_ozon_with_sku_FBO() {
    $arr_catalog = array(
// Новые метровые без якорей
        array('article' =>'7280-К-6', 'sebestoimost' =>1400 , 'skuFBS' =>'1283708573', 'skuFBO' =>'1283708573'),
        array('article' =>'7260-К-8', 'sebestoimost' =>1200 , 'skuFBS' =>'1282808793', 'skuFBO' =>'1282808793'),
        array('article' =>'7245-К-10', 'sebestoimost' =>900 , 'skuFBS' =>'1282804426', 'skuFBO' =>'1282804426' ),
// Новые метровые с якорями
        array('article' =>'7280-К-6-18', 'sebestoimost' =>1400 , 'skuFBS' =>'1282760677', 'skuFBO' =>'1282760677'),
        array('article' =>'7260-К-8-24', 'sebestoimost' =>1200 , 'skuFBS' =>'1282759434', 'skuFBO' =>'1282759434'),
        array('article' =>'7245-К-10-30', 'sebestoimost' =>900 , 'skuFBS' =>'1282704105', 'skuFBO' =>'1282704105' ),
// Старые метровые без якорей
        array('article' =>'7245-К-16', 'sebestoimost' =>1400 , 'skuFBS' =>'522674167', 'skuFBO' =>'522674166'),
        array('article' =>'7260-К-12', 'sebestoimost' =>1200 , 'skuFBS' =>'522675673', 'skuFBO' =>'522675674'),
        array('article' =>'7280-К-8', 'sebestoimost' =>900 , 'skuFBS' =>'522678569', 'skuFBO' =>'522678568' ),
// Старые метровые поштукчно
        array('article' =>'7280', 'sebestoimost' =>100 , 'skuFBS' =>'664665914', 'skuFBO' =>'664665914' ),
        array('article' =>'7260', 'sebestoimost' =>110 , 'skuFBS' =>'664697071', 'skuFBO' =>'664697071' ),
        array('article' =>'7245', 'sebestoimost' =>120 , 'skuFBS' =>'664720473', 'skuFBO' =>'664720473' ),
// Якоря
        array('article' =>'1940-10', 'sebestoimost' =>230 , 'skuFBS' =>'233924855', 'skuFBO' =>'233924852' ),
        array('article' =>'1840-30', 'sebestoimost' =>200 , 'skuFBS' =>'521884852', 'skuFBO' =>'521884851' ),
        array('article' =>'8910-30', 'sebestoimost' =>200 , 'skuFBS' =>'523170684', 'skuFBO' =>'523170685' ),
// Придверные решетки
        array('article' =>'ANM.39*59', 'sebestoimost' =>1500 , 'skuFBS' =>'518682944', 'skuFBO' =>'518682943' ),
        array('article' =>'ANM.49*99', 'sebestoimost' =>2500 , 'skuFBS' =>'500199266', 'skuFBO' =>'500199267' ),
        
        array('article' =>'508АК', 'sebestoimost' =>300 , 'skuFBS' =>'507383556', 'skuFBO' =>'507383556' ),
        array('article' =>'503А', 'sebestoimost' =>700 , 'skuFBS' =>'508149302', 'skuFBO' =>'508149302' ),
        array('article' =>'508А', 'sebestoimost' =>300 , 'skuFBS' =>'508336745', 'skuFBO' =>'508336745' ),
    
        
        array('article' =>'508А-10', 'sebestoimost' =>3000 , 'skuFBS' =>'508143277', 'skuFBO' =>'508143276' ),
        array('article' =>'508АК-10', 'sebestoimost' =>3000 , 'skuFBS' =>'508352124', 'skuFBO' =>'508352129' ),
        array('article' =>'503А-10', 'sebestoimost' =>7000 , 'skuFBS' =>'513511679', 'skuFBO' =>'513511677' ),
        
        array('article' =>'7262-КП', 'sebestoimost' =>500 , 'skuFBS' =>'985937305', 'skuFBO' =>'985937306' ),
    
        array('article' =>'82400-Ч', 'sebestoimost' =>250 , 'skuFBS' =>'233035518', 'skuFBO' =>'233035516' ),
        array('article' =>'82401-Ч', 'sebestoimost' =>280 , 'skuFBS' =>'232956901', 'skuFBO' =>'232956898' ),
        array('article' =>'82402-Ч', 'sebestoimost' =>380 , 'skuFBS' =>'233024314', 'skuFBO' =>'233024311' ),
                
        array('article' =>'82400-З', 'sebestoimost' =>270 , 'skuFBS' =>'233024178', 'skuFBO' =>'233024174' ),
        array('article' =>'82401-З', 'sebestoimost' =>350 , 'skuFBS' =>'233036611', 'skuFBO' =>'233036607' ),
        array('article' =>'82402-З', 'sebestoimost' =>400 , 'skuFBS' =>'233024288', 'skuFBO' =>'233024286' ),
        
        array('article' =>'82400-К', 'sebestoimost' =>270 , 'skuFBS' =>'233036616', 'skuFBO' =>'233036608' ),
        array('article' =>'82401-К', 'sebestoimost' =>300 , 'skuFBS' =>'233036422', 'skuFBO' =>'233036420' ),
        array('article' =>'82402-К', 'sebestoimost' =>400 , 'skuFBS' =>'233029730', 'skuFBO' =>'233029725' ),
        
    );
  return  $arr_catalog; 
};



