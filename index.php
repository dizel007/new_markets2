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


echo <<<HTML
<table>
<tr>

<td><a href = "?transition=50"><img src="pics/main_screen/autosklad.jpg" ><div>Автосклад</div>        </a> </td>
<td></td>
<td></td>
<td></td>
<td></td>
<td></td>
</tr>

<tr>
<td><img src="pics/main_screen/razbor.jpg" alt="альтернативный текст"> </td>
<td><a href = "?transition=10"><img src="pics/main_screen/razbor_wb.jpg" ><div>WB Анмакс</div>        </a></td>
<td><a href = "?transition=11"><img src="pics/main_screen/razbor_wb_ip.jpg" ><div>WB ИП Зел</div>     </a></td>
<td><a href = "?transition=20"><img src="pics/main_screen/razbor_ozon.jpg" ><div>OZON Анмакс</div>    </a></td>
<td><a href = "?transition=21"><img src="pics/main_screen/razbor_ozon_ip.jpg" ><div>OZON ИП Зел</div> </a></td>
<td><a href = "?transition=31"><img src="pics/main_screen/razbor_yandex.jpg"><div>Yandex Анмакс</div> </a></td>
</tr>

<tr>

<td> </td>
<td> </td>
<td> </td>
<td><img src="pics/main_screen/razbor_wb_ip.jpg" alt="альтернативный текст"> </td>

<td><img src="pics/main_screen/autosklad.jpg" alt="альтернативный текст"> </td>
<td><img src="pics/main_screen/autosklad.jpg" alt="альтернативный текст"> </td>

</tr>

<tr>

<td> </td>
<td><img src="pics/main_screen/razbor_wb_ip.jpg" alt="альтернативный текст"> </td>

<td><img src="pics/main_screen/autosklad.jpg" alt="альтернативный текст"> </td>
<td><img src="pics/main_screen/autosklad.jpg" alt="альтернативный текст"> </td>

</tr>

</table>

HTML;

        echo "<a href = \"?transition=50\">Автосклад </a>";
        echo "<br><br>";
        echo "<br>*********************** РАЗБОР ТОВАРА  ******************************<br>";


        echo "<a href = \"?transition=10\">Разбор ВБ Анмакс</a>";
        echo "<br><br>";
        echo "<a href = \"?transition=11\">Разбор ВБ ИП</a>";
        echo "<br><br>";
        echo "<a href = \"?transition=20\">Разбор ОЗОН Анмакс</a>";
        echo "<br><br>";
        echo "<a href = \"?transition=21\">Разбор ОЗОН ИП Зел</a>";
        echo "<br><br>";
        echo "<a href = \"?transition=31\">Разбор ЯндексМаркет ООО ТД Анмакс</a>";
        echo "<br><br>";
        echo "<br><br>";
        echo "<br>*********************** СОГЛСАСОВАНИЕ СКИДКИ ******************************<br>";  
        
        echo "<a href= \"ozon_skidka/index_ozon_skidka.php?ozon_shop=ozon_anmaks\"> ОЗОН АНМАКС СКИДКА</a>";
        echo "<br><br>";
        echo "<a href= \"ozon_skidka/index_ozon_skidka.php?ozon_shop=ozon_ip_zel\"> ОЗОН ИП Зел СКИДКА</a>";


        
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
        echo "<br>*********************** ОТЗЫВЫ  WB ******************************<br>";
        echo "<a href= \"wb_feedback/wb_feedback_start.php?wb_feedback=wb_anmaks\">ОТЗЫВЫ WB АНМАКС</a>";
        echo "<br><br>";
        echo "<a href= \"wb_feedback/wb_feedback_start.php?wb_feedback=wb_ip_zel\">ОТЗЫВЫ WB ИП</a>";

        echo "<br><br>";


        die();
}
