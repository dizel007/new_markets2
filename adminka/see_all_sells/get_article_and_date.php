<?php
/**********************************************************************************************************
 *     ***************    Формируем форму ввода дат 
*********************************************************************************************************/

if (!isset($_GET['date_start']) AND !isset($_GET['date_end'])) {

$date = date("Y-m-d");
$date = strtotime($date);
$date = strtotime("-0 day", $date);
$date_end =  date('Y-m-d', $date);
// echo "$date_end";

$date = date("Y-m-d");
$date = strtotime($date);
$date = strtotime("-7 day", $date);
$date_start = date('Y-m-d', $date);
// echo "<br>$date_start";

} else {

$date_start = $_GET['date_start'];
$date_end = $_GET['date_end'];

}

// вычитываем все активную номенклатуру
  $arr_nomenklatur = select_active_nomenklaturu($pdo);
  foreach ($arr_nomenklatur as $nomencl) {
    $arr_all_activ_article[mb_strtolower($nomencl['main_article_1c'])] = mb_strtolower($nomencl['main_article_1c']);
  }
  



echo <<<HTML
<body>

<form action="#" method="get">
<label>дата начала</label>
<input required type="date" name = "date_start" value="$date_start">
<label>дата окончания</label>
<input required type="date" name = "date_end" value="$date_end">
<br><br>
<label>Артикулы для вывода</label>
<br>
HTML;
$check_ = "";


if (isset($_GET['need_article'])) {
    $need_article = $_GET['need_article'];
    
    // echo "<pre>";
    // print_r($need_article);

} else { // артикулы которые выводятся по умолчанию, если нет выбранных артикулов 
    $need_article = array ('6210-15', '6211-15', '85400-ч');
}



echo "<div class=\"checkbox-flex\">";
///  Формируем перечень артикулов для вывода 
foreach ($arr_all_activ_article as $art_) {
    if (!isset($_GET['need_article'])) {
        if (($art_ == "6210-15") || ($art_ == "6211-15") || ($art_ == "85400-ч") ) {
            $check_ = "checked";
        } else {
            $check_ = "";
        }
    } else {
        $need_article = $_GET['need_article'];

        foreach ($need_article as $need_artp) {
            if ($need_artp == $art_) {
                     $check_ = "checked";
                     break;
                     
            } else {
                   $check_ = "";
            }

        }

    }



 echo "<label><input $check_ type=\"checkbox\" name = \"need_article[]\" value=\"$art_\">$art_ </label>";

}
echo "</div>";

echo "<br>";
echo <<<HTML
<div class="button-container">
     <!-- <div class="checkbox-flex">
   <label><input type="checkbox" checked name = "data_from_site" value="yes">Подтягивать данные с озона за последние дни</label>
    </div> -->
  <button class="btn-center" type="submit">Получить данные с БД</button>
</div>

</form>
HTML;