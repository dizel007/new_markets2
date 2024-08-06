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
   
$width_pics = 120;


echo <<<HTML
<head>
<link rel="stylesheet" href="pics/css/main_screen.css">

</head>

HTML;
echo <<<HTML


<table class="main_table_screen">
<tr>
<td class="big_text">Общие данные по всем МП </td>
<td><a href = "?transition=50">                      <img class="zoom13" width="$width_pics" src="pics/main_screen/autosklad.jpg" ><div>Автосклад<br>&reg;</div></a></td>
<td><a href = "all_sell/all_sell_index.php">         <img class="zoom13" width="$width_pics" src="pics/main_screen/all_sells.jpg" ><div>Продажи<br>за все время</div></a></td>
<td><a href = "all_sell/all_sell_one_day_index.php"> <img class="zoom13" width="$width_pics" src="pics/main_screen/sell_one_day.jpg" ><div>Продажи<br>на дату</div></a></td>
<td></td>
<td></td>
<td></td>
</tr>



<tr>
<td class="big_text">Разбор заказов на МП</td>
<td><a href = "?transition=10"><img width="$width_pics" src="pics/main_screen/razbor_wb.jpg" ><div>WB Анмакс</div>        </a></td>
<td><a href = "?transition=11"><img width="$width_pics" src="pics/main_screen/razbor_wb_ip.jpg" ><div>WB ИП Зел</div>     </a></td>
<td><a href = "?transition=20"><img width="$width_pics" src="pics/main_screen/razbor_ozon.jpg" ><div>OZON Анмакс</div>    </a></td>
<td><a href = "?transition=21"><img width="$width_pics" src="pics/main_screen/razbor_ozon_ip.jpg" ><div>OZON ИП Зел</div> </a></td>
<td><a href = "?transition=31"><img width="$width_pics" src="pics/main_screen/razbor_yandex.jpg"><div>Yandex Анмакс</div> </a></td>
<td><a href = "leroy/"><img width="$width_pics" src="pics/main_screen/razbor_leroy.jpg"><div>LEROY Анмакс</div> </a></td>
<td><a href = "vse_instrumenti/start.php"><img width="$width_pics" src="pics/main_screen/vse_instrumrnti.jpg"><div>Все ИНСТР</div> </a></td>

</tr>


<tr>
<td class="big_text">Распределение товаров по всем МП </td>

<td colspan="2"><a href = "adminka/start_admin_mode.php" target="_blank"><img width="$width_pics" src="pics/main_screen/admin_mode.jpg" ><div>Остатки МП</div>    </a></td>
<td colspan="1"><a href = "adminka/form_for_insert_new_admin.php" target="_blank"><img width="$width_pics" src="pics/main_screen/new_user.png" ><div>Новый юзер</div>    </a></td>

<td> </td>



</tr>




<tr>

<td class="big_text">Автоскидка 4% на озон </td>

<td> </td>
<td> </td>
<td><a href = "ozon_skidka/index_ozon_skidka.php?ozon_shop=ozon_anmaks" target="_blank"><img width="$width_pics" src="pics/main_screen/ikon_skidka_ozon.jpg" ><div>OZON Анмакс</div>    </a></td>
<td><a href = "ozon_skidka/index_ozon_skidka.php?ozon_shop=ozon_ip_zel" target="_blank"><img width="$width_pics" src="pics/main_screen/ikon_skidka_ozon_ip.jpg" ><div>OZON ИП Зел</div> </a></td>

</tr>

<tr>

<td class="big_text">ответ на отзывы ВБ </td>
<td><a href = "wb_feedback/wb_feedback_start.php?wb_feedback=wb_anmaks" target="_blank"><img width="$width_pics" src="pics/main_screen/otziv_wb.jpg" ><div>WB Анмакс</div>        </a></td>
<td><a href = "wb_feedback/wb_feedback_start.php?wb_feedback=wb_ip_zel" target="_blank"><img width="$width_pics" src="pics/main_screen/otziv_wb_ip.jpg" ><div>WB ИП Зел</div>        </a></td>
<td> </td>


</tr>

<tr>

<td class="big_text">XML отчет </td>
<td><a href = "wb_make_xml/take_data_wb.php"><img class="zoom13" width="$width_pics" src="pics/main_screen/xml.jpg" ><div>XML</div>        </a></td>

</tr>


<tr>
    <td class="big_text">ОТЧЕТЫ </td>
    <td><a href = "wb_reports/wb_report_index.php?wb_shop=wb_anmaks"><img class="zoom13" width="$width_pics" src="pics/main_screen/wb_report_ooo.jpg" ><div>ОТЧЕТЫ WB АНМАКС</div>    </a></td>
    <td><a href = "wb_reports/wb_report_index.php?wb_shop=wb_ip_zel"><img class="zoom13" width="$width_pics" src="pics/main_screen/wb_report_ip.jpg" ><div>ОТЧЕТЫ WB ИП Зел</div>    </a></td>

    <td><a href = "ozon_report/index_ozon_razbor.php?ozon_shop=ozon_anmaks"><img class="zoom13" width="$width_pics" src="pics/main_screen/ozon_report_ooo_td.jpg" ><div>Отчет OZON Анмакс</div>    </a></td>
    <td><a href = "ozon_report/index_ozon_razbor.php?ozon_shop=ozon_ip_zel"><img class="zoom13" width="$width_pics" src="pics/main_screen/ozon_report_ip.jpg" ><div>Отчет OZON ИП Зел</div>    </a></td>

    <td><a href = "yandex_report/start_ya_razbor.php"><img class="zoom13" width="$width_pics" src="pics/main_screen/razbor_yandex.jpg" ><div>Отчет ЯМ</div>    </a></td>

</tr>


<tr>
    <td class="big_text">Получение этикеток с озон (аварийное)</td>
    <td></td>
    <td></td>
    <td><a href = "ozon_razbor/index_ozon_ooo_dop.php"><img class="zoom13" width="$width_pics" src="pics/main_screen/ozon_report_ooo_td.jpg" ><div>ПОЛУЧИТЬ этикетки ОЗОН ООО АНМАКС</div>    </a></td>
    <td><a href = "ozon_razbor/index_ozon_ip_dop.php"><img class="zoom13" width="$width_pics" src="pics/main_screen/ozon_report_ip.jpg" ><div>ПОЛУЧИТЬ этикетки ОЗОН ИП Зел</div>    </a></td>
    <td><a href = "ozon_returns/get_returns.php"><img class="zoom13" width="$width_pics" src="pics/main_screen/ozon_returns.jpg" ><div>Возвраты <br> двух<br> ОЗОНов</div>    </a></td>

</tr>



        </table>
HTML;
}
        die();

    