<?php

require_once "../connect_db.php";

echo <<<HTML
<a href = "https://partner.market.yandex.ru/business/789064/payments-report?campaignId=22076999">ССылка для скачивания отчетов </a>
<form enctype="multipart/form-data" action="parce_excel_report.php" method="POST">
    
    <input type="hidden" name="MAX_FILE_SIZE" value="4000000" />
    
    Платёж по договору на размещение:(Большая сумма) <input name="userfile" type="file" />
    <br>    <br>
    <!-- Платёж по договору на продвижение:(Скидки МП) <input name="userfile_2" type="file" /> -->
    <br>    <br>
    <input type="submit" value="Отправить файл" />
</form>

</form>

HTML;

die();


