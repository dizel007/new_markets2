<?php
/**********************************************************************************************************
 *     ***************    Получаем массив всех транзакций
*********************************************************************************************************/

require_once "../connect_db.php";

require_once "../mp_functions/ozon_api_functions.php";

require_once "../pdo_functions/pdo_functions.php";


 $ozon_shop = $_GET['ozon_shop'];
 if ($_GET['ozon_shop'] == 'ozon_anmaks') {
        $token =  $token_ozon;
        $client_id =  $client_id_ozon;
  
    }
        
 elseif ($_GET['ozon_shop'] == 'ozon_ip_zel') {
        $token =  $token_ozon_ip;
        $client_id =  $client_id_ozon_ip;
  } else {
        die ('МАГАЗИН НЕ ВЫБРАН');
  }





echo <<<HTML
<head>
<link rel="stylesheet" href="../css/main_ozon.css">

</head>
HTML;



if (isset($_GET['dateFrom'])) {
    $date_from = $_GET['dateFrom'];
} else {
    $date_from = false;
}

if (isset($_GET['dateTo'])) {
    $date_to = $_GET['dateTo'];
} else {
    $date_to = false;
}


echo <<<HTML
<head>
<link rel="stylesheet" href="css/main_table.css">

</head>
<body>

<form action="#" method="get">
<label>Магазин</label>
<select required name="ozon">
    <option value = "1">OZON</option>
</select>


<label>дата начала</label>
<input required type="date" name = "dateFrom" value="$date_from">
<label>дата окончания</label>
<input required type="date" name = "dateTo" value="$date_to">

<input hidden type="text" name = "ozon_shop" value="$ozon_shop">
<input type="submit"  value="START">
</form>
HTML;

if (($date_from == false) or ($date_to == false)) {
    die ('Нужно выбрать даты');
    } 

// $date_from = "2023-08-19";
// $date_to = "2023-09-19";
echo "Период запроса с ($date_from) по  ($date_to)<br>";
$ozon_link = 'v3/finance/transaction/list';
$send_data = array(
    "filter" => array(
        "date" => array (
            "from" => $date_from."T00:00:00.000Z",
            "to"=> $date_to."T00:00:00.000Z"
    ),
        "operation_type" => [],
        "posting_number" => "",
        "transaction_type" => "all"
    ),
    "page" => 1,
    "page_size" => 1000
);
$send_data = json_encode($send_data);

$res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_link );
$page_count = $res['result']['page_count'];
$row_count = $res['result']['row_count'];
echo $page_count ." ". $row_count;

// echo "<pre>";
// print_r($res);
// die('fffffffffffffffffffffffffffffffffffffffffffffffffffffff');

for ($i=1; $i <=$page_count; $i ++) {
    $send_data = array(
        "filter" => array(
            "date" => array (
                "from" => $date_from."T00:00:00.000Z",
                "to"=> $date_to."T00:00:00.000Z"
        ),
            "operation_type" => [],
            "posting_number" => "",
            "transaction_type" => "all"
        ),
        "page" => $i,
        "page_size" => 1000
    );
    $send_data = json_encode($send_data);
    $res = send_injection_on_ozon($token, $client_id, $send_data, $ozon_link );
    $prod_array[] = $res['result']['operations'];
    echo "*<br>";


}


// echo "<pre>";
// print_r ($prod_array);

// die(); ///////////////////////// DELETEE ********************

require_once "razbor_dannih.php";

die();
// require_once "ozon_get_trans_1.php";
// require_once "ozon_get_trans_2.php";
