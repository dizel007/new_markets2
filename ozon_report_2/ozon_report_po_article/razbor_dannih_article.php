<?PHP

// echo "<br>START TRAZBOR DANNIH<br>";
// print_r($arr_type_items_WITH_POSTING_NUMBER);
// die();
$folder_razbor = "_parts_razbor_article";
/**************************************************************************************************************
 **************************************  ЗАКАЗЫ ************************************************************
 *************************************************************************************************************/
$i_orders = 0;
if (isset($arr_type_items_WITH_POSTING_NUMBER['orders'])) 
    {
        require_once "$folder_razbor/zakazi_article.php";
    }
// echo "i_orders = $i_orders. <br>";

/**************************************************************************************************************
 **************************************  ВОЗВРАТЫ
 *************************************************************************************************************/
//06675399-0372-4
$i_returns = 0;
if (isset($arr_type_items_WITH_POSTING_NUMBER['returns'])) {
    require_once "$folder_razbor/vozvrati_article.php";
}
// echo "i_returns = $i_returns. <br>";

/**************************************************************************************************************
 **************************************  Эквайринг 
 *************************************************************************************************************/
$i_other = 0;
if (isset($arr_type_items_WITH_POSTING_NUMBER['other'])) {
require_once "$folder_razbor/ecvairing_article.php";
}
// echo "i_other = $i_other. <br>";


 /***********************  Сервисы ******************************************************
 *************************************************************************************************************/
// $i_services  = 0;
// if (isset($arr_type_items_WITH_POSTING_NUMBER['services'])) {
// require_once "$folder_razbor/servici_article.php";
// echo "<pre>";
// print_r($arr_type_items_WITH_POSTING_NUMBER['services']);
// }
// echo "i_services = $i_services. <br>";

/**************************************************************************************************************
 ************************************** Удержание за недовложение товара
 *************************************************************************************************************/
// $i_compensation  = 0;
// if (isset($arr_type_items_WITH_POSTING_NUMBER['compensation'])) {
// require_once "$folder_razbor/uderzhania_article.php";
// }
// echo "i_compensation = $i_compensation. <br>";
// echo "количество элементов = ". count($arr_article). "<br>";




// print_r($arr_article);



// ОСтавляем только те заказы, по которым есть движение 

// die();

if (!isset($arr_article)) { echo "<br>ДАННЫХ ДЛЯ ВЫВОДА"; die();}

// die();
// echo "количество элементов WORK(нужного артикула) = ". count($arr_article). "<br>";


// die();



//*****************************************************************************************************
// перебираем все заказы, и перебираем все проданные товары по штукчно и
// и будем формировать все расходы по каждому товару в заказе 
//*****************************************************************************************************
// print_r($arr_article_WORK);
// echo "dddddd<br><br><br>";
require_once "make_array_for_print.php";



///  Выводим таблицу с дополнительными сервисами, которые не смогли привязать к заказам
// require_once "print_article/table_services_without_postnumbers.php";

// Выводим таблицу со всеми заказами 
require_once "print_article/real_money_article.php";

// Выводим таблицу со возвратами
// require_once "print_article/returns_items_article.php";






die();
// 0153247992-0053