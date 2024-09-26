<?php

$offset ="../../";
require_once $offset .'connect_db.php';
$link = $offset.'index.php';

// выводим табличку с выбором магазина

$dateFrom='';

echo <<<HTML
<head>
<link rel="stylesheet" href="css/select_shop_link_razbor.css">
<title>Портал Маркет</title>
    <style>
        body {
                background-image: url(../../pics/upbanner.jpg);
                background-repeat: no-repeat;
            }
    </style>
</head>

<body>



<div class="wrapper">
  <form action="get_razbor_link.php" class="login" method="post">
  <p class="title">Номер заказа</p>

    <input class ="date_class" type="number" name = "order_number" value=""> 
    <p class="title">Выбор маркета</p>
    <select class ="date_class" size="1" name="shop_name">
      <option value="" selected>все Магазины</option>
      <option value="wb_anmaks" >ВБ Анмакс</option>
      <option value="wb_ip_zel">ВБ ИП Зел</option>
      <option value="ozon_anmaks">Озон Анмакс</option>
      <option value="ozon_ip_zel">Озон ИП Зел</option>
    
   </select>
   <p class="title">Дата отгрузки</p>
   <input class ="date_class" type="date" name = "date_order" value=""> 

   <input class="button_input" type="submit">
    <!-- <button>
      <span class="state">Запрос</span>
    </button> -->
  </form>
  </p>
<a href="$link"><h2 class="h2_color_red center">Вернуться на главную станицу</h2></a>
</div>

</body>
HTML;




