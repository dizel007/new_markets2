<?php


require_once '../../connect_db.php';

echo <<<HTML
 <link rel="stylesheet" href="css/input_new_admin.css">
<div class="wrapper">
  <form action="insert_new_admin_in_bd.php" class="login" method="post">
    <p class="title">Добавление нового АДМИНА</p>
    <input type="text" placeholder="Логин"  name="login"  autofocus/>
    <i class="fa fa-user"></i>
    <input type="email" placeholder="Email"name="user_email" />
    <i class="fa fa-user"></i>
    <input type="password" placeholder="Пароль" name="password" />
    <i class="fa fa-key"></i>
    <input type="password" placeholder="Повтор пароля" name="repeat_password"/>
   

    <button>
      <i class="spinner"></i>
      <span class="state">Добавить</span>
    </button>
  </form>
  </p>
<a href="../../index.php"> Вернуться на главную станицу</a>
</div>

HTML;




