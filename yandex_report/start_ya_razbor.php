<?php

require_once "../connect_db.php";

echo <<<HTML
<a target="_blank" href = "https://partner.market.yandex.ru/business/789064/payments-report?campaignId=22076999">Ссылка на отчеты в ЯМ</a>
<br>
<a target="_blank" href = "https://partner.market.yandex.ru/business/789064/finance-reports?campaignId=22076999&tab=payments&paymentFilterType=byOrderId&dateBase=BILLING_DATE&onlyActiveContract=true&bankOrderId=">Новая ccылка для скачивания EXCEL отчетов</a>
<br><br><br>
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


