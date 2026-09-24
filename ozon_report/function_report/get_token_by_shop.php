<?php

function get_token_AND_idclient ($arr_tokens, $shop_name) {

if (isset($arr_tokens[$shop_name])) {
    $token  = $arr_tokens[$shop_name]['token'];
    $client_id = $arr_tokens[$shop_name]['id_market'];
    return [$token , $client_id];
    
}  else {
     die('Не нашли магазин');
}

}