<?php

function sendmail($Zakaz_v_1c, $link_downloads_stikers, $link_downloads_qr_codes){
    require '../libs/mailer/phpmailer/PHPMailer.php';
    require '../libs/mailer/phpmailer/SMTP.php';
    require '../libs/mailer/phpmailer/Exception.php';


    $mail = new PHPMailer\PHPMailer\PHPMailer();
    
    try {

            $mail->CharSet = 'utf-8';
            $mail->isSMTP(); 
            $mail->Port = 25;  // NETANGELS
            $mail->Host = 'mail.netangels.ru';  // Specify main and backup SMTP servers
        
            $mail->SMTPAuth = true;                               // Enable SMTP authentication
            $mail->Username = 'markets@anmarkets.ru';             // Наш логин
            $mail->Password = 'pYeiobiC4ZiZL134';                 // Наш пароль от ящика
    
            $mail->setFrom('markets@anmarkets.ru', 'Разбор МП');   // От кого письмо 
    
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = "Разобрали МП: ($Zakaz_v_1c)"; // тема письма
            $mail->Body    = "Закончили разбор Макркетплэйсов";
            $mail->addAddress('dizel007@yandex.ru', 'BigBoom');     // Add a recipient
    
    
    // ************************* Цепляем файлы с КП  *************************************
          $mail->addAttachment($link_downloads_stikers);
          $mail->addAttachment($link_downloads_qr_codes);
     
    
    
        if ($mail->send()) 
            {
                echo "СООБЩЕНИЕ ОТПРАВЛЕНО ";
            } else {
                echo "ОШИБКА ОТПРАВКИ";
                die('<br>DIE BAD DIE<br>');
            }
    
    }
     catch (Exception $e) {
        $result = "error";
        $status = "Сообщение не было отправлено. Причина ошибки: {$mail->ErrorInfo}";
    }
 }
