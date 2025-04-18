<?php
// session_start();

require_once("connect_db.php"); // подключение к БД
require_once('pdo_functions/pdo_functions.php');
require_once "index_main_page/index_function.php";
// echo "<pre>";
// print_r($userdata);

// Настраиваем доступность 
$access_level = $userdata['userType'];



echo <<<HTML
<head>
    <link rel="stylesheet" href="pics/css/main_screen.css">
    <link rel="stylesheet" href="pics/css/new_main_table.css">
    <title>Портал Маркет</title>
    <style>
        body {
                background-image: url(pics/upbanner.jpg);
                background-repeat: no-repeat;
            }
    </style>
</head>

HTML;
echo <<<HTML

<body>
<!-- Баннер на Бэкграунде -->
<div class="upbanner"></div>
HTML;


echo "<div class=\"container\"><div class = \"zagolovok\">Сводные данные по всем МП </div></div>";

// <!-- Контеукнер с блоками -->
echo "<div class=\"container\">";


// Автосклад
print_one_block ('table_element', "autosklad/start_mp.php", 'АВТОСКЛАД',
                'Распределение складских остатков по всех маркетплэйсам', 'pics/main_screen/autosklad.jpg');
// склад ЛЕРУА
print_one_block ('table_element', "leroy/get_assortiment.php", 'Леруа Склад',
                'Распределение складских остатков по всех маркетплэйсам', 'pics/main_screen/razbor_leroy.jpg');

print_one_block ('table_element', "all_sell/all_sell_index.php", 'ПРОДАЖИ ВСЕ',
                'Список товаров, которые были проданы за всё время', 'pics/main_screen/all_sells.jpg');

print_one_block ('table_element', "ozon_fbo_orders/get_fbo_orders.php", 'ПРОДАЖИ FBO озон',
                'Список товаров, которые были проданы за всё время', 'pics/main_screen/all_sells_fbo_ozon.jpg');

print_one_block ('table_element', "all_sell/all_sell_one_day_index.php", 'ПРОДАЖИ ДЕНЬ',
                'Список товаров, которые были проданы за один день', 'pics/main_screen/sell_one_day.jpg');

  



echo "</div>";// Конец контейнера


// <!--**********************************************************************************************-->
// <!--**************************** Контеукнер ПО РАЗБОРУ МАРКЕТОВ **********************************-->
// <!--**********************************************************************************************-->
echo "<div class=\"container\"><div class = \"zagolovok\">Разбор заказов на маркетах</div></div>";

echo "<div class=\"container\">";


print_one_block ('table_element_razbor', "wb_new_razbor/index_wb.php", 'WB Анмакс',
'Формирование QR кодов для склада (сбор по артикулам)', 'pics/main_screen/razbor_wb.jpg');

print_one_block ('table_element_razbor', "wb_new_razbor/index_wbip.php", 'WB ИП Зел',
'Формирование QR кодов для склада (сбор по артикулам)', 'pics/main_screen/razbor_wb_ip.jpg');

print_one_block ('table_element_razbor', "ozon_razbor/index_ozon.php?shop_name=ozon_anmaks", 'OZON Анмакс',
'Формирование штрихкодов для склада (сбор по артикулам)', 'pics/main_screen/razbor_ozon.jpg');

print_one_block ('table_element_razbor', "ozon_razbor/index_ozon.php?shop_name=ozon_ip_zel", 'OZON ИП Зел',
'Формирование штрихкодов для склада (сбор по артикулам)', 'pics/main_screen/razbor_ozon_ip.jpg');

print_one_block ('table_element_razbor', "yandex_razbor/index_yandex.php", 'Yandex Анмакс',
'Формирование штрихкодов для склада (сбор по артикулам)', 'pics/main_screen/razbor_yandex.jpg');

print_one_block ('table_element_razbor', "leroy/", 'LEROY Анмакс',
'Формирование штрихкодов для склада (сбор по артикулам)', 'pics/main_screen/razbor_leroy.jpg');

print_one_block ('table_element_razbor', "vse_instrumenti/start.php", 'Все ИНСТР',
'Формирование штрихкодов для склада (сбор по артикулам)', 'pics/main_screen/vse_instrumrnti.jpg');



echo "</div>";// Конец контейнера ПО РАЗБОРУ МАРКЕТОВ


// <!--**********************************************************************************************-->
// <!--**************************** Контеукнер Вспомогательные функции ОТЗЫВ/АвтоСкидки/Распределение товаров *************-->
// <!--**********************************************************************************************-->

echo "<div class=\"container\"><div class = \"zagolovok\">Вспомогательные функции ОТЗЫВ/АвтоСкидки</div></div>";


echo "<div class=\"container\">";

        print_one_block ('table_element', "wb_feedback/wb_feedback_start.php?wb_feedback=wb_anmaks", 'WB Анмакс отзывы',
        'Автоматический ответ на положительные отзывы с оценкой 5', 'pics/main_screen/otziv_wb.jpg');

        print_one_block ('table_element', "wb_feedback/wb_feedback_start.php?wb_feedback=wb_ip_zel", 'WB ИП Зел отзывы',
        'Автоматический ответ на положительные отзывы с оценкой 5', 'pics/main_screen/otziv_wb_ip.jpg');

        print_one_block ('table_element', "ozon_feedbacks/get_feedback.php", 'ОЗОНЫ отзывы',
        'Автоматический ответ на положительные отзывы с оценкой 4 и 5', 'pics/main_screen/ikon_skidka_ozon.jpg');

        print_one_block ('table_element', "ozon_skidka/index_ozon_skidka.php?ozon_shop=ozon_anmaks", 'OZON Анмакс скидка',
        'Одобрение скидки от 4 до 7 процентов (по возрастающей)', 'pics/main_screen/ikon_skidka_ozon.jpg');

        print_one_block ('table_element', "ozon_skidka/index_ozon_skidka.php?ozon_shop=ozon_ip_zel", 'OZON ИП Зел скидка',
        'Одобрение скидки от 4 до 7 процентов (по возрастающей)', 'pics/main_screen/ikon_skidka_ozon_ip.jpg');

        print_one_block ('table_element', "adminka/raspredelenie_tovarov/start_admin_mode.php", 'Распределение остатков по складам',
        'Распределение товаров по складам по заданным процентам', 'pics/main_screen/sklad_raspred.jpg');

        

echo "</div>";//  Конец контейнера ПО Вспомогательные функции ОТЗЫВ/АвтоСкидки 



// <!--**********************************************************************************************-->
// <!--**************************** Контеукнер XML отчет *************-->
// <!--**********************************************************************************************-->
echo "<div class=\"container\"><div class = \"zagolovok\">XML отчет</div></div>";

echo "<div class=\"container\">";
    print_one_block ('table_element', "wb_make_xml/take_data_wb.php", 'XML',
    'Формирование УПД файла в формате XML на ВБ ООО', 'pics/main_screen/xml.jpg');
echo "</div>";// Конец контейнера XML отчет 



// <!--**********************************************************************************************-->
// <!--**************************** Контейнер ОТЧЕТЫ *************-->
// <!--**********************************************************************************************-->
echo "<div class=\"container\"><div class = \"zagolovok\">ОТЧЕТЫ</div></div>";

echo "<div class=\"container\">";

        print_one_block ('table_element_razbor', "wb_reports/wb_report_index.php?wb_shop=wb_anmaks", 'ОТЧЕТЫ WB АНМАКС',
        'Формирование отчетов на основании недельных отчетов ВБ. Отчеты только по целым неделям', 'pics/main_screen/wb_report_ooo.jpg');

        print_one_block ('table_element_razbor', "wb_reports/wb_report_index.php?wb_shop=wb_ip_zel", 'ОТЧЕТЫ WB ИП Зел',
        'Формирование отчетов на основании недельных отчетов ВБ. Отчеты только по целым неделям', 'pics/main_screen/wb_report_ip.jpg');

        print_one_block ('table_element_razbor', "ozon_report/index_ozon_razbor.php?ozon_shop=ozon_anmaks", 'ОТЧЕТЫ OZON АНМАКС',
        'Формирование отчетов на основании данных с Озона. Отчеты можно делать за период не более месяца', 'pics/main_screen/ozon_report_ooo.jpg');

        print_one_block ('table_element_razbor', "ozon_report/index_ozon_razbor.php?ozon_shop=ozon_ip_zel", 'ОТЧЕТЫ OZON ИП Зел',
        'Формирование отчетов на основании данных с Озона. Отчеты можно делать за период не более месяца', 'pics/main_screen/ozon_report_ip_z.jpg');

        print_one_block ('table_element_razbor', "yandex_report/start_ya_razbor.php", 'ОТЧЕТЫ ЯМ АНМАКС',
        'Формирование отчетов на основании ексель отчетов с ЯМ. Ссылка на скачивания отчетов внутри', 'pics/main_screen/razbor_yandex.jpg');


echo "</div>";//Конец контейнера ОТЧЕТЫ


// <!--**********************************************************************************************-->
// <!--**************************** Вспомогательные модули по работе с озоном *************-->
// <!--**********************************************************************************************-->
echo "<div class=\"container\"><div class = \"zagolovok\"> Вспомогательные модули по работе с озоном  </div></div>";


echo "<div class=\"container\">";


print_one_block ('table_element', "ozon_razbor/index_ozon_dop.php?shop_name=ozon_anmaks", 'ПОЛУЧИТЬ этикетки ОЗОН ООО АНМАКС',
'Если произошел сбой, при получении этикеток, то можно получить этикети из состояния отгужаются', 'pics/main_screen/ozon_report_ooo_td.jpg');

print_one_block ('table_element', "ozon_razbor/index_ozon_dop.php?shop_name=ozon_ip_zel", 'ПОЛУЧИТЬ этикетки ОЗОН ИП Зел',
'Если произошел сбой, при получении этикеток, то можно получить этикети из состояния отгужаются', 'pics/main_screen/ozon_report_ip.jpg');

print_one_block ('table_element', "ozon_returns/get_returns.php", 'Возвраты с двух ОЗОНов',
'Модуль формирует список товаров по возвратам, для формирования актов оприходования', 'pics/main_screen/ozon_returns.jpg');

print_one_block ('table_element', "adminka/link_for_razbor/select_shop.php", 'Скачивание разобранных заказов',
'Через данный модуль можно будет скачать ранее разобранные заказы', 'pics/main_screen/check_orders.jpg');


echo "</div>";//<!-- Конец контейнера Вспомогательные модули по работе с озоном-->




// <!--**********************************************************************************************-->
// <!--**************************** Контейнер Администрирование*************-->
// <!--**********************************************************************************************-->
if ($access_level >=7 ) { //*** ВИДИМ тольео админоам с уровенем семь */
echo "<div class=\"container\"><div class = \"zagolovok\"> Администрирование  </div></div>";

echo "<div class=\"container\">";


    print_one_block ('table_element_admin', "adminka/dimensions/select_shop_demensions.php", 'Контроль размеров товаров',
    'Сравниваем габаритные размеры товаров в БД и нв личных кабинетах МП', 'pics/main_screen/size_control.jpg');

    print_one_block ('table_element_admin', "adminka/price_control/select_shop.php", 'Контроль цены товаров',
    'Сравниваем цены товаров в БД и нв личных кабинетах МП', 'pics/main_screen/fix_price.jpg');

    print_one_block ('table_element_admin', "adminka/setup_shop_tables/select_shop_setup.php", 'Настройка товаров в магазинах',
    'Настройка распределения товаров и отключение артикулов', 'pics/main_screen/setup_shop.png');

    print_one_block ('table_element_admin', "adminka/insert_new_admin/form_for_insert_new_admin.php", 'Новый юзер',
    'Добавляем нового пользователя с правами нулевого админа', 'pics/main_screen/new_user.png');
    print_one_block ('table_element_admin', "adminka/find_product_id_ozon/select_shop_find_product.php", 'Insert product_id в БД Ozon',
    'Добавляем параметр product_id для каталога товаров озон', 'pics/main_screen/product_id.jpg');
    

echo "</div>"; // Конец контейнера Администрирование  (Второй) 

// <!--**********************************************************************************************-->
// <!--**************************** Контейнер Администрирование (Вторая линия)*************-->
// <!--**********************************************************************************************-->

echo "<div class=\"container\">";


echo "</div>";// Конец контейнера Администрирование  (Второй)

}


// <!--**********************************************************************************************-->
// <!--**************************** Контейнер Дополнительные Функции*************-->
// <!--**********************************************************************************************-->
if ($access_level >=7 ) { //*** ВИДИМ тольео админоам с уровенем семь */
    echo "<div class=\"container\"><div class = \"zagolovok\"> Дополнительные Функции  </div></div>";
    
    echo "<div class=\"container\">";
    
    print_one_block ('table_element_admin', "ozon_report/index_ozon_razbor_orders.php", 'Обзор заказов озон ООО ',
        'Выводим информацию по каждому заказу ', 'pics/main_screen/ozon_report_ooo.jpg');
    
    
        print_one_block ('table_element_admin', "ozon_report/index_ozon_razbor_orders.php", 'Обзор заказов озон ООО ',
        'Выводим информацию по каждому заказу ', 'pics/main_screen/ozon_report_ip.jpg');
    
    
    echo "</div>"; // Конец контейнера Дополнительные Функции  (Второй) 
    
    // <!--**********************************************************************************************-->
    // <!--**************************** Контейнер Администрирование (Вторая линия)*************-->
    // <!--**********************************************************************************************-->
    
    echo "<div class=\"container\">";
    
    
    echo "</div>";// Конец контейнера Дополнительные Функции  (Второй)
    
    }


echo "<body>";


die();
