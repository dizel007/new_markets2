<?php


/************************************************************************************************
 ******  Отрисовываем таблицу с ценами ************************************************
 ************************************************************************************************/
function print_table_with_prices($wb_catalog, $token_wb, $wb_shop)
{

echo <<<HTML
<link rel="stylesheet" href="../css/print_table.css">

  <form action="temp_422.php" method="POST">
    <table class="table-fill">


  <tr>
      <th class="width_article text-center">Артикул МП</th>
      <th class="text-center">Цена с БД</th>
      <th class="text-center">Скидка</th>
      <th class="text-center">Цена со скидкой с БД</th>
      <th class="width_date text-center">Дата в БД</th>
   
      <th class="text-center">Цена ВБ</th>
      <th class="text-center">Скидка ВБ</th>
      <th class="text-center">Цена со скидкой в ВБ</th>
      <th class="text-center">Разница цен</th>
      <th class="text-center">Артикул МП</th>
      <th class="text-center">CheckBox</th>
  </tr>
HTML;


  $p = 0;

  foreach ($wb_catalog as $item) {
    $check_box= 0; // флаг чтобы обновлять цену и скидку
    $dis_price_old = round($item['dis_price_old'],0); 
    $dis_price_now = round($item['dis_price_now'],0);
    $delta_discount_prices =  round($dis_price_old - $dis_price_now,0);


    // Проверяем одинаковые ли цена на сайт и в БД
    $summa_price = round(($dis_price_old -  $dis_price_now), 0);
    if ($summa_price > 0) {
      $bolshe100 = 'bolshe100';
      $check_box=1;
    } elseif ($summa_price < -0) {
      $bolshe100 = 'menshe0';
      $check_box=0;
    } else {
      $bolshe100 = '';
      $check_box=0;
    }
    


    echo "<tr class=\"\">";

    echo "<td class=\"$bolshe100 text-center\">" . $item['main_article'] . "</td>";
    // данные из БД
    echo  "<td class=\" text-center\">" . $item['price_old'] . "</td>"; // цена до скидкт
    echo  "<td class=\"text-center \">" . $item['discount_old'] . "</td>"; // скидка
    echo  "<td class=\" text-center\">" . $item['dis_price_old'] . "</td>"; // цена со скидкой
    echo  "<td class=\" text-center\">" . $item['date_old'] . "</td>";
    echo <<<HTML
<!-- Дельту цен загоняе мв в форму -->
<input type="hidden" readonly type="text" id="dis_price_old{$p}" name = "dis_price_old{$p}"  value="{$dis_price_old}">

<!--  ЦЕНА НА САЙТЕ ВБ -->
 <td class="width_input">
  <input  class="input_width_vvod" onkeyup="CalculateItem();"  onkeydown="CalculateItem();" onchange="CalculateItem();" onfocus="CalculateItem();"
   type="number" step="1" min="0" max="9999" id="price_now{$p}" name="_price_now_{$p}" value ="{$item['price_now']}" required>
</td>
<!--  СКИДКА НА ВБ -->
<td class="width_input">
    <input class="input_width_vvod"  required type="number" step="1" min="0" max="70" id="discount_now{$p}" name="_discount_now_{$p}"
     value ="{$item['discount_now']}" onkeyup="CalculateItem();" onkeydown="CalculateItem();"
      onchange="CalculateItem();" onfocus="CalculateItem();">
</td>

 <!-- СКИДОЧНАЯ ЦЕНА  -->
<td class="width_input">
   <input class="input_width_raschet" readonly type="number" id="sum_price{$p}" name = "sum_price{$p}"  value="{$dis_price_now}">
  </td>
<!-- РАЗНИЦА В ЦЕНАХ -->
<td class="width_input"> 
  <input  class="input_width_raschet" readonly type="number" id="delta_discount_prices{$p}" name = "_delta_discount_prices_{$p}"  value="{$delta_discount_prices}">
 </td>
HTML;

echo "<td class=\"$bolshe100 text-center\">" . $item['main_article'] . "</td>";







  // Кнопки checkBboxi
    if ($check_box == 1) {
    echo  "<td class=\"$bolshe100 text-center\"><input checked type=\"checkbox\" name=\"_need_update_{$p}\" value=\"\"></td>";
    } else {
      echo  "<td class=\"$bolshe100 text-center\"><input  type=\"checkbox\" name=\"_need_update_{$p}\" value=\"\"></td>";
    }
    $p++;

    echo "</tr>";
  }
    echo "</table>";

    // тезнические данные
    echo  "<input  type=\"hidden\" name=\"_sku_{$p}\" value=" . $item['sku'] . ">";
    echo  "<input  type=\"hidden\" name=\"type_question\" value=\"discount_update\">";
    echo  "<input  type=\"hidden\" name=\"token_wb\" value=\"$token_wb\">";
    echo  "<input  type=\"hidden\" name=\"wb_shop\" value=\"$wb_shop\">";
 
  echo  "<input class=\"btn\" type=\"submit\" value=\"ОБНОВИТЬ ДАННЫЕ НА ВБ\">";

  echo "</form>";






  // ******************************** Начало ФОРМЫ **************************************************
  echo <<<HTML



<script src="jquery.min.js"></script>
<script type="text/javascript">
            function CalculateItem()
            {
              Str_Number = event.target.id
              var Str_Number = Str_Number.replace(/\D/g, "");
              
              // alert(inputVat_name);
                try {
                    inputPriceNoVat = $('#price_now' + Str_Number).val() -  $('#price_now' + Str_Number).val()*$('#discount_now'+ Str_Number).val()/100;
                    delta_discount_prices = $('#dis_price_old' + Str_Number).val() - inputPriceNoVat;
                    // alert("333sss33=" + inputPriceNoVat);
                  //  console_log(inputPriceNoVat);
                 $('#sum_price' + Str_Number).val(inputPriceNoVat);
                 $('#delta_discount_prices' + Str_Number).val(delta_discount_prices);

                //  CalculateSummaKp();

                } catch (e) {
                    $('#sum_price' + Str_Number).val('cccccccccccccccccc');
                    $('#delta_discount_prices' + Str_Number).val('cccccccccccccccccc');
                }

            }
</script>


<script type="text/javascript">
$("input").keydown(function(event){
   if (event.keyCode === 13) {
      return false;
  }
}
)
</script> 


<script type="text/javascript">
 function CalculateSummaKp(){
  summa = 0;
 kolvo_strok = $('#all_kolvo').val();
 console.log(kolvo_strok);
 for (i = 0; i<kolvo_strok; i++) { // вычисляем сумму все строк
     summa_stroki = $('#sum_price' + i).val();
     console.log(summa_stroki);
     summa = summa + parseFloat(summa_stroki) ;
          }
$('#summa_our_kp').val(summa);

console.log(summa);
    }

</script>
HTML;
}
