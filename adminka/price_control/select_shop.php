<?php

$offset ="../../";
require_once $offset .'connect_db.php';
$link = $offset.'index.php';



if (isset($_GET['shop_name'])) {
// выбираем куда в какой магазин улетим

switch ($_GET['shop_name']) {
  case 'wb_anmaks':
    header('Location: get_price_table_wb.php?shop_name='.$_GET['shop_name'], true, 301);
      break;
  case 'wb_ip_zel':
    header('Location: get_price_table_wb.php?shop_name='.$_GET['shop_name'], true, 301);
      break;
  case 'ozon_anmaks':
    header('Location: get_price_table_ozon.php?shop_name='.$_GET['shop_name'], true, 301);
      break;
  case 'ozon_ip_zel':
    header('Location: get_price_table_ozon.php?shop_name='.$_GET['shop_name'], true, 301);
    break;
}


} else {
// выводим табличку с выбором магазина


echo <<<HTML
 <link rel="stylesheet" href="css/select_shop.css">
<div class="wrapper">
  <form action="#" class="login" method="get">
    <p class="title">Выбор Маркета</p>
    <i class="fa fa-user"></i>


    <p><select size="1" name="shop_name">
    <option value="wb_anmaks" selected>ВБ Анмакс</option>
    <option value="wb_ip_zel">ВБ ИП Зел</option>
    <option value="ozon_anmaks">Озон Анмакс</option>
    <option value="ozon_ip_zel">Озон ИП Зел</option>
    
   </select></p>
   
    <button>
      <i class="spinner"></i>
      <span class="state">выбрать</span>
    </button>
  </form>
  </p>
<a href="$link"> Вернуться на главную станицу</a>
</div>

HTML;
}



