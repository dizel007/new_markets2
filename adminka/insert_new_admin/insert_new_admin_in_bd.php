<?php
require_once("../../connect_db.php");
$err = [];

if(strlen($_POST['login']) < 3 or strlen($_POST['login']) > 10)
  {
      $err[] = "Логин должен быть не меньше 3-х символов и не больше 10";
  }
  if(!preg_match("/^[a-zA-Z0-9]+$/",$_POST['login']))
  {
      $err[] = "Логин может состоять только из букв английского алфавита и цифр";
  }

  $stmt = $pdo->prepare("SELECT * FROM users WHERE `user_login` = ?");
  $stmt->execute([$_POST['login']]);
  $arr = $stmt->fetchAll(PDO::FETCH_ASSOC);
   if(count($arr) > 0)
      {
          $err[] = "Пользователь с таким логином уже существует в базе данных";
      }

if ($_POST['password'] !== $_POST['repeat_password']) {
  $err[] = "Пароли не совпадают";
}

// проверям логин

// Если нет ошибок, то добавляем в БД нового пользователя
if(count($err) == 0)
{

$login = $_POST['login'];


    // Убераем лишние пробелы и делаем двойное хеширование
$password = md5(md5(trim($_POST['password'])));
$active_mode = 1;
$date_write =date('Y-m-d');

  	$stmt  = $pdo->prepare("INSERT INTO `users` (user_login, user_password, user_active, user_email, date_write)
                        VALUES (:user_login, :user_password, :user_active, :user_email, :date_write)");


        $stmt ->bindParam(':user_login', $login);
        $stmt ->bindParam(':user_password', $password);
        $stmt ->bindParam(':user_active', $active_mode);
        $stmt ->bindParam(':user_email', $_POST['user_email']);
        $stmt ->bindParam(':date_write', $date_write);

      // $stmt ->execute(); 
    
if (!$stmt ->execute())
 { 
  print_r($stmt->ErrorInfo()); 
   die("<br>Померли на вводе нового пользователя");
 }
            
//  Уходим в реестре
   header("Location: ../../index.php"); exit();
}
else
{
    print "<b>При регистрации произошли следующие ошибки:</b><br>";
    foreach($err AS $error)
    {
        print $error."<br>";
        

    }
    $refer = "../".$_SERVER["HTTP_REFERER"];
    echo "<a href=\"$refer\">Вернуться</a>";
}

