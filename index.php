<?php
// session_start();
require_once("connect_db.php"); // подключение к БД
require_once('pdo_functions/pdo_functions.php');



// Формируем тип перехода (Все переходы должны быть через index.php)
isset($_GET['transition']) ? $transition = $_GET['transition'] : $transition = 0; // показывает куда переходить

switch ($transition) {
    case 10: // Разбор ВБ
        require_once('wb_new_razbor/index_wb.php');

        break;


    case 11: // Разбор ВБ ИП
        require_once('wb_new_razbor/index_wbip.php');

        break;

    case 20: // Разбор OZON OOO
        require_once('ozon_razbor/index_ozon.php');
        break;

    case 21: // Разбор OZON IP
        require_once('ozon_razbor/index_ozon_ip.php');
        break;

    case 31: // Разбор Yandex
        require_once('yandex_razbor/index_yandex.php');
        break;

    case 50: // Автосклад
        require_once('autosklad/start_mp.php');
       break;
    case 61: // ОЗОН СКИДКА
        require_once('ozon_skidka/index_ozon_skidka.php');
        break;




        //    header('ozon_report/index_ozon_razbor.php', true, 301); 



        // 
    case 0: // основная таблица со всеми КП
        //         $arr_temp = get_catalog_wb();

$width_pics = 100;
echo <<<HTML


<table class="main_screen">
<tr>
<td></td>
<td><a href = "?transition=50"><img width="$width_pics" src="pics/main_screen/autosklad.jpg" ><div>Автосклад</div></a></td>
<td><a href = "all_sell/all_sell_index.php"><img width="$width_pics" src="pics/main_screen/all_sells.jpg" ><div>ПРОДАЖИ</div></a></td>
<td></td>
<td></td>
<td></td>
<td></td>
</tr>

<tr>
<td><img width="$width_pics" src="pics/main_screen/razbor.jpg" alt="Разбор Заказов"> </td>
<td ><a href = "?transition=10"><img width="$width_pics" src="pics/main_screen/razbor_wb.jpg" ><div>WB Анмакс</div>        </a></td>
<td><a href = "?transition=11"><img width="$width_pics" src="pics/main_screen/razbor_wb_ip.jpg" ><div>WB ИП Зел</div>     </a></td>
<td><a href = "?transition=20"><img width="$width_pics" src="pics/main_screen/razbor_ozon.jpg" ><div>OZON Анмакс</div>    </a></td>
<td><a href = "?transition=21"><img width="$width_pics" src="pics/main_screen/razbor_ozon_ip.jpg" ><div>OZON ИП Зел</div> </a></td>
<td><a href = "?transition=31"><img width="$width_pics" src="pics/main_screen/razbor_yandex.jpg"><div>Yandex Анмакс</div> </a></td>
<td><a href = "leroy/"><img width="$width_pics" src="pics/main_screen/razbor_leroy.jpg"><div>LEROY Анмакс</div> </a></td>
<td><a href = "vse_instrumenti/start.php"><img width="$width_pics" src="pics/main_screen/vse_instrumrnti.jpg"><div>Все ИНСТР</div> </a></td>

</tr>

<tr>

<td><img width="$width_pics" src="pics/main_screen/skidka_ozon.jpg" > </td>

<td> </td>
<td> </td>
<td><a href = "ozon_skidka/index_ozon_skidka.php?ozon_shop=ozon_anmaks"><img width="$width_pics" src="pics/main_screen/ikon_skidka_ozon.jpg" ><div>OZON Анмакс</div>    </a></td>
<td><a href = "ozon_skidka/index_ozon_skidka.php?ozon_shop=ozon_ip_zel"><img width="$width_pics" src="pics/main_screen/ikon_skidka_ozon_ip.jpg" ><div>OZON ИП Зел</div> </a></td>

</tr>

<tr>

<td> </td>
<td><a href = "wb_feedback/wb_feedback_start.php?wb_feedback=wb_anmaks"><img width="$width_pics" src="pics/main_screen/otziv_wb.jpg" ><div>WB Анмакс</div>        </a></td>
<td><a href = "wb_feedback/wb_feedback_start.php?wb_feedback=wb_ip_zel"><img width="$width_pics" src="pics/main_screen/otziv_wb_ip.jpg" ><div>WB ИП Зел</div>        </a></td>
<td> </td>


</tr>

<tr>

<td> </td>
<td><a href = "wb_make_xml/take_data_wb.php"><img width="$width_pics" src="pics/main_screen/xml.jpg" ><div>XML</div>        </a></td>

<td><img width="$width_pics" src="pics/main_screen/autosklad.jpg" ><div>ОТЧЕТЫ</div>  </td>
<td><img width="$width_pics" src="pics/main_screen/autosklad.jpg" ><div>ОТЧЕТЫ</div>  </td>
<td><img width="$width_pics" src="pics/main_screen/autosklad.jpg" ><div>ОТЧЕТЫ</div>  </td>


</tr>




</table>

HTML;
echo "<br><br>";
echo "<br><br>";

        
        echo "<br><br>";
        echo "<br>*********************** ОТЧЕТЫ ОЗОН ******************************<br>";
        echo "<a href= \"ozon_report/index_ozon_razbor.php?ozon_shop=ozon_anmaks\">ОТЧЕТЫ ОЗОН АНМАКС</a>";
        echo "<br><br>";
        echo "<a href= \"ozon_report/index_ozon_razbor.php?ozon_shop=ozon_ip_zel\">ОТЧЕТЫ ОЗОН ИП Зел</a>";
        echo "<br><br>";

        
        echo "<br><br>";
        echo "<br>*********************** ОТЧЕТЫ WB ******************************<br>";
        echo "<a href= \"wb_reports/wb_report_index.php?wb_shop=wb_anmaks\">ОТЧЕТЫ WB АНМАКС</a>";
        echo "<br><br>";
        echo "<br><br>";
        echo "<br><br>";


        die();
}
