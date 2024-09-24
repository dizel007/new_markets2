<?php
$offset="../";
require_once $offset."connect_db.php";


echo <<< HTML
<html>
  <head>
    <meta charset="utf-8" />
    <title>Обновление остаток МП</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
	  <link rel="stylesheet" href="css/input_forma.css"/>
    
  </head>
  <body>
    <div class="container">
      <h1 class="form-title">Обновление остатков МП</h1>

      <form action= "get_all_ostatki_skladov_new_ALL.php" method="post" enctype="multipart/form-data"> 
	  
	  <div class="file_input_form">   
              <input  class="file_input_button" type="file" name="file_excel" accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet">
			  </div>
        <div class="form-submit-btn">
			<input type="submit" value="Обновить данные">	
        </div>

      </form>
    </div>
  </body>
</html>

HTML;