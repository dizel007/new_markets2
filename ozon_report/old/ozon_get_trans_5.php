<?php
$summa_vseh_sborov = $summa_vozvratov+
$summa_pretenzii + $summa_ekvairing + $summa_uslugi_prodvizenia +  $summa_hranenie_utiliz + $summa_get_otzivi +
$summa_change_pay + $summa_korr_cost + $logistika + $sborka + $lastMile;

echo <<<HTML
<table class="prod_table_small">

<tr>
        <td><i>Доставка и обработка возврата, отмены, невыкупа </i></td>
        <td><i>$summa_vozvratov </i></td>
</tr> 
<tr>
        <td><i>Начисления по претензиям  </i></td>
        <td><i>$summa_pretenzii </i></td>
</tr>   
<tr>
        <td><i>Оплата эквайринга  </i></td>
        <td><i>$summa_ekvairing</i></td>
</tr>   
<tr>
        <td><i>Услуги продвижения товаров  </i></td>
        <td>$summa_uslugi_prodvizenia</td>
</tr>   
<tr>
        <td><i>Начисление за хранение/утилизацию возвратов  :</i></td>
        <td>$summa_hranenie_utiliz</td>
</tr>
<tr>
        <td><i>Приобретение отзывов на платформе : </i></td>
        <td>$summa_get_otzivi</td>
</tr>
<tr>
        <td>Услуга по изменению условий отгрузки </td>
        <td>$summa_change_pay </td>
</tr>
<tr>

        <td><i>Корректировки стоимости услуг  </i></td>
        <td><i>$summa_korr_cost </i></td>
</tr>
<tr>
        <td><i>Сумма всех сборов   <i></td>
        <td><i>$summa_vseh_sborov </i></td>
  </tr>
  <tr>
        <td><i>ALL AMOUNY   <i></td>
        <td><i>$sum_nas_all </i></td>
  </tr>
  
</table>
HTML;


