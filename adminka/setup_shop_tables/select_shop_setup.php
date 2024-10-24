<?php

$offset ="../../";
require_once $offset .'connect_db.php';
$link = $offset.'index.php';

echo <<<HTML

 <link rel="stylesheet" href="css/select_shop_setup.css">
<div class="wrapper">
  <h2 class="h2_color_green">НАСТРАИВАЕМ МАГАЗИНЫ ПО ТОВАРАМ</h2>

  <form action="start_shop_tables.php" class="login" method="get">
    <p class="title">Выбор Маркета</p>
    <!-- <i class="fa fa-user"></i> -->


    <p><select size="1" name="shop_name">
    <option value="wb_anmaks" selected>ВБ ООО Анм</option>
    <option value="wb_ip_zel" >ВБ ИП Зел</option>
    <option value="ozon_anmaks" >ОЗОН ООО Анм</option>
    <option value="ozon_ip_zel">ОЗОН ИП Зел</option>
    
   </select></p>
   <label>только активные товары</label>
   <input  class="" type="checkbox"  checked name="all_active" value ="_need_">
    <button>
      <i class="spinner"></i>
      <span class="state">выбрать</span>
    </button>
  </form>
  </p>
<a href="$link">Вернуться на главную станицу</a>
</div>

HTML;




