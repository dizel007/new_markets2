<?php 

/**********************************************************************************************************************
 ********************   Функия подменяет СКУ ФБО на СКУ ФБС 
 **********************************************************************************************************************/
function change_SKU_fbo_fbs($ozon_sebest, $sku){
    /// Функия подменяет СКУ ФБО на СКУ ФБС - 
        foreach ($ozon_sebest as $item_cat) {
            if ($sku == $item_cat['skuFBO']) {
                $new_sku  = $item_cat['sku'];
                break;
            } else {$new_sku  = $sku;}
    
        }
        
        return $new_sku;
        }

/**********************************************************************************************************************
 ********************   Функия возвращает Артикул по СКУ ФБС **********************************************************
 **********************************************************************************************************************/

function get_article_by_sku_fbs($ozon_sebest, $sku) {
    foreach ($ozon_sebest as $item_cat) {
        if (($sku == $item_cat['sku']) OR (($sku == $item_cat['skuFBO']))) {
            $article = $item_cat['mp_article'];
            break;
        } else {$article  = 'SKU = '.$sku;}

    }
    
    return $article;
    }