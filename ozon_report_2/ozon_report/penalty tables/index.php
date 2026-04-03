<?php
/*******************************************************************************************************************
 * Выводим таблицу со штрафами, Она показывает по каким заказм и за что мы получили штрафы
 * также цепляем ссылку для перехода в кабинет озона, чтобы проверить акутальность данных
 * 
 *****************************************************************************************************************/
 
// Инициализация переменной
$MeData = [];
$GOOD_DATA  = 0;
// Если данные пришли методом POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['myDataStrafi'])) {
    $jsonString = $_POST['myDataStrafi'];
    $MeData = json_decode($jsonString, true); // true = ассоциативный массив

    // Проверка на ошибки JSON
    if (json_last_error() !== JSON_ERROR_NONE) {
        $MeData = ['error' => 'Некорректный JSON: ' . json_last_error_msg()];
    }
}
?>
<?php if ($_SERVER['REQUEST_METHOD'] === 'POST'): ?>
    <!-- Если это POST-запрос (данные отправлены) -->
  
    <pre><?php $GOOD_DATA  = 1; ?></pre>

<?php else: ?>
    <!-- Если это GET-запрос (обычный переход по ссылке) -->

    <!-- Скрытая форма для отправки данных -->
    <form id="sendForm" method="POST" style="display: none;">
        <input type="hidden" name="myDataStrafi" id="dataField" value="">
    </form>

    <script>
        (function() {
            // Читаем данные из localStorage
            const jsonData = localStorage.getItem('myDataStrafi');

            if (jsonData) {
                // Записываем JSON-строку в скрытое поле формы
                document.getElementById('dataField').value = jsonData;
                // Автоматически отправляем форму
                document.getElementById('sendForm').submit();
            } else {
                // Если данных нет – показываем сообщение
                document.body.innerHTML = '<h1>❌ Данные не найдены </h1>'
                    
            }
        })();
    </script>
<?php endif; ?>

</body>
</html>
<?php

if ($GOOD_DATA != 1) {
    echo "Нет данных для выдачи";
    die();
}
// CSS цепляем
echo "<link rel=\"stylesheet\" href=\"../css/form_dates.css\">";

// Начинаем отрисовывать таблицу 




foreach ($MeData as $name_strafa=>$data_strafa){
foreach ($data_strafa as $number_posting=>$summa_strafa){
    $shoper_id_number_temp = explode('-', $number_posting);
    $shoper_id_number = $shoper_id_number_temp[0];  // получаем ID спокупателчч
    $arr_shtraf_orders[$shoper_id_number][$number_posting]['summa'] = @$arr_shtraf_orders[$shoper_id_number][$number_posting]['summa'].$summa_strafa."<br>";
    $arr_shtraf_orders[$shoper_id_number][$number_posting]['name_shtrafa'] =  @$arr_shtraf_orders[$shoper_id_number][$number_posting]['name_shtrafa']. $name_strafa."<br>";
}
}


// echo "<pre>";
// print_r($arr_shtraf_orders);



$string_number=0;
echo <<<HTML
<div class="h80procentov">
<div class="table-container">
    <h3 class = "shapka_tabla"> Таблица штрафов </h3>
<table class="financial-table">
    <tr>
        <th>пп</th>
        <th>ID покупателя</th>
        <th>Номер заказа</th>
        <th>Сумма штрафа</th>
        <th>Причина штрафа</th>
        
    </tr>
HTML;  

foreach ($arr_shtraf_orders as $shoper_id_number=>$data_strafa){

    $count_shoper_penality_ZZZ = 0;
  $count_shoper_penality = count($data_strafa);

foreach ($data_strafa as $number_posting=>$one_shtraf_data){
// echo "<br> shoper_id_number $shoper_id_number - <br>";

  
   
  

       echo "<tr>";

    if ($count_shoper_penality_ZZZ != $count_shoper_penality )  {
            $string_number++;
           ($string_number % 2)?$string_number_color = "even_color": $string_number_color="oder_color";
           
            $count_shoper_penality_ZZZ = $count_shoper_penality;
            echo <<<HTML
                        <td rowspan="$count_shoper_penality" class ="$string_number_color"  >$string_number</td>
                        <td rowspan="$count_shoper_penality" class ="$string_number_color"  >$shoper_id_number</td>
            HTML;
    }
// $count_shoper_penality_ZZZ++;
echo <<<HTML
            <td class ="$string_number_color big_text" >
                <a href ="https://seller.ozon.ru/app/finances/accruals?search={$number_posting}&tab=ACCRUALS_DETAILS" 
                target ="_blank" >$number_posting </a>
            </td>
            <td class ="center_text $string_number_color" >{$one_shtraf_data['summa']}</td>
            <td class ="center_text $string_number_color" >{$one_shtraf_data['name_shtrafa']}</td>
           
    

</tr>

HTML;
}





}



echo <<<HTML
</table>
</div>
</div>
HTML;


