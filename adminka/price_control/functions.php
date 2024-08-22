<?php


/************************************************************************************************
 ******  Отрисовываем таблицу с ценами ************************************************
 ************************************************************************************************/
function print_table_with_prices($wb_catalog, $token_wb, $wb_shop)
{

echo <<<HTML
<link rel="stylesheet" href="../css/print_table.css">

  <form action="update_data.php" method="POST">
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
    // echo $item['sku'],"<br>";
    $check_box = 0; // флаг чтобы обновлять цену и скидку
    $dis_price_now_DB = round($item['dis_price_now_DB'],0); 
    $dis_price_now_WB = round($item['dis_price_now_WB'],0);
    $delta_discount_prices =  round($dis_price_now_DB - $dis_price_now_WB,0);


    // Проверяем одинаковые ли цена на сайт и в БД
    $summa_price = round(($dis_price_now_DB -  $dis_price_now_WB), 0);
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
    echo  "<td class=\" text-center\">" . $item['price_now_DB'] . "</td>"; // цена до скидкт
    echo  "<td class=\"text-center \">" . $item['discount_now_DB'] . "</td>"; // скидка
    echo  "<td class=\" text-center\">" . $item['dis_price_now_DB'] . "</td>"; // цена со скидкой
    echo  "<td class=\" text-center\">" . $item['date_now_DB'] . "</td>";
    echo <<<HTML
<!-- Дельту цен загоняе мв в форму -->
<input type="hidden" readonly type="text" id="dis_price_now_DB{$p}" name = "dis_price_now_DB{$p}"  value="{$dis_price_now_DB}">

<!--  ЦЕНА НА САЙТЕ ВБ -->
 <td class="width_input">
  <input  class="input_width_vvod" onkeyup="CalculateItem();"  onkeydown="CalculateItem();" onchange="CalculateItem();" onfocus="CalculateItem();"
   type="number" step="1" min="0" max="9999" id="price_now_WB{$p}" name="price_now_WB{$p}" value ="{$item['price_now_WB']}" required>
</td>
<!--  СКИДКА НА ВБ -->
<td class="width_input">
    <input class="input_width_vvod"  required type="number" step="1" min="0" max="70" id="discount_now_WB{$p}" name="discount_now_WB{$p}"
     value ="{$item['discount_now_WB']}" onkeyup="CalculateItem();" onkeydown="CalculateItem();"
      onchange="CalculateItem();" onfocus="CalculateItem();">
</td>

 <!-- СКИДОЧНАЯ ЦЕНА  -->
<td class="width_input">
   <input class="input_width_raschet" readonly type="number" id="dis_price_now_WB{$p}" name = "dis_price_now_WB{$p}"  value="{$dis_price_now_WB}">
  </td>
<!-- РАЗНИЦА В ЦЕНАХ -->
<td class="width_input"> 
  <input  class="input_width_raschet" readonly type="number" id="delta_discount_prices{$p}" name = "_delta_discount_prices_{$p}"  value="{$delta_discount_prices}">
 </td>
HTML;

echo "<td class=\"$bolshe100 text-center\">" . $item['main_article'] . "</td>";


    // тезнические данные
    echo  "<input  type=\"hidden\" name=\"_sku_{$p}\" value=" . $item['sku'] . ">";
    echo  "<input  type=\"hidden\" name=\"type_question\" value=\"discount_update\">";
    echo  "<input  type=\"hidden\" name=\"token_wb\" value=\"$token_wb\">";
    echo  "<input  type=\"hidden\" name=\"wb_shop\" value=\"$wb_shop\">";




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


 echo <<<HTML
<div class="ccc">
<input class="atuin-btn" type="submit" value="ОБНОВИТЬ ДАННЫЕ НА ВБ">
</div>

HTML;
  echo "</form>";






echo <<<HTML
<!-- Подклюяаме Jquery -->
<script src="jquery.min.js"></script> 
<!-- РАсчитываем цену со скидкой и разницу в ценах -->
<script type="text/javascript">
            function CalculateItem()
            {
              Str_Number = event.target.id
              var Str_Number = Str_Number.replace(/\D/g, "");
              
              // alert(inputVat_name);
                try {
                    inputPriceNoVat = Math.round($('#price_now_WB' + Str_Number).val() -  $('#price_now_WB' + Str_Number).val()*$('#discount_now_WB'+ Str_Number).val()/100);
                    delta_discount_prices = Math.round($('#dis_price_now_DB' + Str_Number).val() - inputPriceNoVat);
                    // alert("333sss33=" + inputPriceNoVat);
                  //  console_log(inputPriceNoVat);
                 $('#dis_price_now_WB' + Str_Number).val(inputPriceNoVat);
                 $('#delta_discount_prices' + Str_Number).val(delta_discount_prices);

                //  CalculateSummaKp();

                } catch (e) {
                    $('#dis_price_now_WB' + Str_Number).val('cccccccc');
                    $('#delta_discount_prices' + Str_Number).val('cccccccc');
                }

            }
</script>

<!-- Блокируем отправку формы по ЭНТЕРУ -->
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
