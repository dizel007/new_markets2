<?php

$offset ="../../";
require_once $offset .'connect_db.php';
$link = $offset.'index.php';

echo <<<HTML
 <link rel="stylesheet" href="css/select_shop.css">
<div class="wrapper">
  <form action="get_price_table.php" class="login" method="get">
    <p class="title">Выбор Маркета</p>
    <i class="fa fa-user"></i>


    <p><select size="1" name="wb_shop">
    <option value="wb_anmaks" selected>ВБ Анмакс</option>
    <option value="wb_ip_zel">ВБ ИП Зел</option>
    
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




