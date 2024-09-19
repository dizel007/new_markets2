<?php

$offset ="../../";
require_once $offset .'connect_db.php';
$link = $offset.'index.php';

echo <<<HTML

 <link rel="stylesheet" href="css/select_shop.css">
<div class="wrapper">
  <h1>СРАВНИВАЕМ ГАБАРИТНЫЕ РАЗМЕРЫ ТОВАРОВ (по ОЗОНУ)</h1>
  <form action="get_demensions.php" class="login" method="post">
    <p class="title">Выбор Маркета</p>
    <i class="fa fa-user"></i>


    <p><select size="1" name="ozon_shop">
    <option value="ozon_anmaks" selected>ОЗОН ООО Анм</option>
    <option value="ozon_ip_zel">ОЗОН ИП Зел</option>
    
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




