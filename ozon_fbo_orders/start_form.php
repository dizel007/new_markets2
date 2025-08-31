<?php

// if (!isset($_GET['ozon_shop'])) {
//    $_GET['ozon_shop'] = 'ozon_anmaks';
// } else {
//    $ozon_shop = $_GET['ozon_shop'];
// }

// if ($_GET['ozon_shop'] == 'ozon_anmaks') {
//        $token =  $token_ozon;
//        $client_id =  $client_id_ozon;
//        $name_mp_shop = 'OZON ООО АНМАКС';
 
//    }
       
// elseif ($_GET['ozon_shop'] == 'ozon_ip_zel') {
//        $token =  $token_ozon_ip;
//        $client_id =  $client_id_ozon_ip;
//        $name_mp_shop = 'OZON ИП ЗЕЛ';
//  } else {
//        die ('МАГАЗИН НЕ ВЫБРАН');
//  }

// ООО АНМ
       $token_anmaks =  $token_ozon;
       $client_id_anmaks =  $client_id_ozon;
       $name_mp_shop_anmaks = 'OZON ООО АНМАКС';
// ИП ЗЕЛ
       $token_ip_zel =  $token_ozon_ip;
       $client_id_ip_zel =  $client_id_ozon_ip;
       $name_mp_shop_ip_zel = 'OZON ИП ЗЕЛ';



echo <<<HTML
<head>
<link rel="stylesheet" href="../css/main_ozon.css">
</head>
HTML;


if (isset($_GET['dateFrom'])) {
   $date_from = $_GET['dateFrom'];
} else {
   $date_from = date('Y-m-d');
}

if (isset($_GET['dateTo'])) {
   $date_to = $_GET['dateTo'];
} else {
   $date_to = date('Y-m-d');
}


echo <<<HTML
<head>
<link rel="stylesheet" href="css/main_table.css">


<!-- CSS -->
<!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css"> -->
<!-- JS -->
<!-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> -->




</head>
<body>

<form  action="#" method="get">
<!-- <label>Магазин</label> -->
<!-- <select required name="ozon_shop"> -->
HTML;
// if ($_GET['ozon_shop'] == 'ozon_anmaks') {
//   echo "<option selected value = \"ozon_anmaks\">OZON</option>";
//   echo "<option value = \"ozon_ip_zel\">OZON ИП ЗЕЛ</option>";
// } else {
//    echo "<option  value = \"ozon_anmaks\">OZON</option>";
//    echo "<option selected value = \"ozon_ip_zel\">OZON ИП ЗЕЛ</option>";
// }
   

echo <<<HTML
<!-- </select> -->


<label>дата начала</label>
<input required type="date" name = "dateFrom" value="$date_from">
<label>дата окончания</label>
<input required type="date" name = "dateTo" value="$date_to">



<!-- <label for="dateRange">Выберите диапазон дат:</label>
    <input type="text" id="dateRange" name="date_range" placeholder="YYYY-MM-DD to YYYY-MM-DD" required>
    <button type="submit">Отправить</button> -->

  <input type="submit"  value="START">




</form>

<!-- <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    flatpickr("#dateRange", {
      mode: "range",
      dateFormat: "Y-m-d"
    });
  </script> -->

HTML;

if (($date_from == false) or ($date_to == false)) {
   die ('Нужно выбрать даты');
   } 



//    if ($_SERVER["REQUEST_METHOD"] == "GET") {
//       if (!empty($_GET["date_range"])) {
//           $range = $_GET["date_range"]; // формат: "2025-04-01 to 2025-04-10"
  
//           // Разделим на начальную и конечную дату
//           $dates = explode(" to ", $range);
//           $dateFdate_fromrom = $dates[0];
//           $date_to = isset($dates[1]) ? $dates[1] : $dates[0]; // если выбрана только одна дата
  
//           echo "Вы выбрали диапазон с $date_from по $date_to";
//       } else {
//           echo "Диапазон дат не выбран.";
//       }
//   }


// echo "<pre>";
// print_r($_GET);
// die();