<?php
require_once "connect_db.php";

echo <<<HTML
<form action= "autosklad/get_all_ostatki_skladov_new_ALL.php" method="post" enctype="multipart/form-data"> 




<span>Выберите файл</span>
	<!-- <input required type="file" name="file_excel" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">		 -->
	<input  type="file" name="file_excel" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">		
	
 	
        
<hr>

 <input type="submit" value="ЗАПУСК">	

</form>



HTML;