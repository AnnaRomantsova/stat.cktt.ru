<?php

  include('config.php');

  $FILENAME = $front_html_path.'settings.html';

  $message = '';

  unset($main);
  if (!(isset($_SESSION['user']))) {
      header ( "location: http://" . $_SERVER['HTTP_HOST']);
  };

  $main = new outTree($FILENAME);

  $r = new Select ( $db, "select * from users where id=$_SESSION[user]");
  if ($r->next_row ()) {
         foreach ( $_POST as $key => $value)
                    $$key= htmlspecialchars ( addslashes ($value));
         if (isset($_POST['save'])) {


               $err = '';
               if ( $email  == ''  )
                       $err = 1;
               if (! preg_match ( "([0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-wyz][a-z](fo|g|l|m|mes|o|op|pa|ro|seum|t|u|v|z)?)", $email ) and $email != "") {
                       $err = 2;
               };

               $r1 = new Select ( $db, "select * from users where id<>$_SESSION[user] and email='$email'");
               if ($r->next_row ()) $err = 3;

               if ($pass1    !== $pass2 && $pass1 !== '' ) $err=4;
               if ($old_pass !== $r->result('pass_text') && $pass1 !== '') $err=5;

               switch ($err) {
                  case '1' :
                          $message = "E-mail не может быть пустым";
                          break;
                  case '2' :
                          $message = "Введите корректный E-mail!";
                          break;
                  case '3' :
                          $message = 'Такой E-mail уже существует!';
                          break;
                  case '4' :
                          $message = 'Пароли не совпадают!';
                          break;
                  case '5' :
                          $message = 'Старый пароль введен не верно!';
                          break;
                };
                if ($err>0) $main->addField ( 'message', 'Ошибка! ' . $message );
                  else {
                       if ($pass1 !== '') $sql = ",pass = '". md5($pass1) ."',pass_text='$pass1'";
                       $r1 = new Select ( $db, "update users set email='$email' $sql where id=$_SESSION[user]");
                       $message = 'Данные сохранены.';
                       $main->addField ( 'message', $message );
                  };


               // header ( "location: http://" . $_SERVER['HTTP_HOST'].'/lk');
       };


       if (isset($_POST['save_mess'])) {
           $r1 = new Select ( $db, "update users set new_comments='$new_comments',new_messages ='$new_messages' where id=$_SESSION[user]");
 //          echo "update users set new_comments='$new_comments',new_messages ='$new_messages' where id=$_SESSION[user]";
       };

       $r = new Select($db,'select * from users where id="'.$_SESSION['user'].'"');
       $r->next_row();
       $r->addFields($main,$ar=array('id','email'));
       if ($r->result('new_comments') >0 ) $main->addfield('new_comments','checked');
       if ($r->result('new_messages') >0 ) $main->addfield('new_messages','checked');
  };



//echoTree($main);
  if (isset($main)) {
                $site->addField($GLOBALS['currentSection'],$main);
//echoTree($site);
                unset($main);
  };
 ?>