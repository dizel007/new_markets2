<?php



function print_sum_table($shop_name, $arr_sort_ar, $arr_FBS_sells, $arr_FBO_sells) {

$summa_FBS_count = 0;
$summa_FBS_price = 0;
$summa_FBO_count = 0;
$summa_FBO_price = 0;
$all_summa_count = 0;
$all_summa_summa = 0;
//подключение 
echo '<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">';
echo '<link rel="stylesheet" href="css/sell_fbo_fbs_table.css">';
echo '<div class="h1_center"><h1> Таблица заказов '. $shop_name.'</h1></div>';
echo "<table class=\"sell_mp_table\">";

echo "<thead>";
echo "<tr>";
echo "<th class=\"red_windows\">Артикул</th>"; 
echo "<th>Количество<br>по ФБС</th>"; 
echo "<th>Сумма<br> по ФБС</th>"; 
echo "<th class=\"blue_windows\">Количество<br>по ФБО</th>"; 
echo "<th class=\"blue_windows\">Сумма<br>по ФБО</th>"; 
echo "<th class=\"blue_windows\">Количество<br>всего</th>"; 
echo "<th class=\"blue_windows\">Сумма<br>всего</th>"; 

echo "</tr>";

echo "</thead>";

foreach ($arr_sort_ar as $atricle=>$number) {
$all_count = 0;
$all_summa = 0;

  
echo "<tr>";
echo "<td>{$atricle}</td>";
// *****************************************************************
// Заказы ФБС 
// *****************************************************************

if (isset($arr_FBS_sells[$atricle]) ) {
    
      $link_for_article_fbs = "get_FBO_FBS_orders_by_article.php?shopname=".$shop_name."&article=".urlencode($atricle)."&delivery_schema=fbs";


      echo "<td><a href=\"$link_for_article_fbs\" target=\"_blank\">{$arr_FBS_sells[$atricle]['count']}</a></td>";
            // echo "<td>{$arr_FBS_sells[$atricle]['count']}</td>";  
            $summa_FBS = number_format($arr_FBS_sells[$atricle]['price'],0);
            echo "<td>{$summa_FBS}</td>"; 
            $summa_FBS_count += $arr_FBS_sells[$atricle]['count'];
            $summa_FBS_price += $arr_FBS_sells[$atricle]['price'];
    } 
         else {
    echo "<td>  --  </td>"; 
    echo "<td>  --  </td>"; 
    }
// *****************************************************************
// заказы ФБО
// *****************************************************************
if (isset($arr_FBO_sells[$atricle])) {

      $link_for_article_fbo = "get_FBO_FBS_orders_by_article.php?shopname=".$shop_name."&article=".urlencode($atricle)."&delivery_schema=fbo";

      echo "<td><a href=\"$link_for_article_fbo\"  target=\"_blank\">{$arr_FBO_sells[$atricle]['count']}</a></td>";

            // echo "<td>{$arr_FBO_sells[$atricle]['count']}</td>";  
            $summa_FBO = number_format($arr_FBO_sells[$atricle]['price'],0);
            echo "<td>{$summa_FBO}</td>"; 
            $summa_FBO_count += $arr_FBO_sells[$atricle]['count'];
            $summa_FBO_price += $arr_FBO_sells[$atricle]['price']; 
 
        
}  else {
    echo "<td>  --  </td>"; 
    echo "<td>  --  </td>"; 
}
// *****************************************************************
// заказы ВСЕГО
// *****************************************************************

if (isset($arr_FBO_sells[$atricle]) OR isset($arr_FBS_sells[$atricle])) {
     $all_count = @$arr_FBS_sells[$atricle]['count'] + @$arr_FBO_sells[$atricle]['count'];
     $all_summa = @$arr_FBS_sells[$atricle]['price']  + @$arr_FBO_sells[$atricle]['price']; 
      $all_summa_count += $all_count;
      $all_summa_summa += $all_summa; 

      $link_for_article_all = "get_FBO_FBS_orders_by_article.php?shopname=".$shop_name."&article=".urlencode($atricle);

      echo "<td><a href=\"$link_for_article_all\" target=\"_blank\">{$all_count}</a></td>";


            // echo "<td>{$all_count}</td>";  
            $all_summa = number_format($all_summa,0);
            echo "<td>{$all_summa}</td>"; 
                       
            
        
} 


 echo "</tr>";

}
echo "<tfoot>";
    echo "<tr>";
    echo "<td>ИТОГО</td>"; 
    echo "<td><b>$summa_FBS_count</b></td>"; 
    $summa_FBS_price = number_format($summa_FBS_price,0);
    echo "<td><b>$summa_FBS_price</b></td>"; 
    echo "<td><b>$summa_FBO_count</b></td>"; 
    $summa_FBO_price = number_format($summa_FBO_price,0);
    echo "<td><b>$summa_FBO_price</b></td>"; 

    $all_summa_count = number_format($all_summa_count,0);
    echo "<td><b>$all_summa_count</b></td>"; 
    $all_summa_summa = number_format($all_summa_summa,0);
    echo "<td><b>$all_summa_summa</b></td>"; 


    echo "</tr>";
echo "</tfoot>";
echo "</table>";
}