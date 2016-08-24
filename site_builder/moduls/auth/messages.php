<?php

include ('config.php');

$FILENAME = $front_html_path . 'auth.html';
$reg_FILENAME = $front_html_path . 'reg.html';
$new_reg_FILENAME = $front_html_path . 'new_reg.html';
$err_reg_FILENAME = $front_html_path . 'err_reg.html';
$forgot_FILENAME = $front_html_path.'forgot.html';


require_once($inc_path."/phpmailer/func.mailViaSMTP.php");

function generate_code($length = 8){
         $chars = 'abdefhiklmnopwrstyz123456789';
         $numChars = strlen($chars);
         $string = '';
         for ($i = 0; $i < $length; $i++) {
           $string .= substr($chars, rand(1, $numChars) - 1, 1);
         }
         return $string;
}

//require_once($inc_path."/phpmailer/func.mailViaSMTP.php");


$patch = $HTTP_SERVER_VARS [HTTP_REFERER];
//$main->addfield ( 'site_path', $patch );

 //если нажали выход
  if  (isset($_GET['exit'])) {

      //$_SESSION['vendor']=null;
      //$_SESSION['user']=null;
      $_SESSION = array ();
      setcookie("e_mail",'');
      setcookie("password",'');
      unset($_COOKIE['e_mail']);
      unset($_COOKIE['password']);
      //var_dump($_SESSION);
      header ( "location: http://" . $_SERVER['HTTP_HOST'] );
  };

  unset($main);
  $main = new outTree ( $FILENAME );

// echotree($main);
  if ($_GET['forgot']>0) {
       unset($main);
       $main = new outTree($forgot_FILENAME);
  }


//если нажали "Войти' и ввели логин, пароль
  if (isset ( $_POST ['log_in'] )) {
        //проверочки
        $r = new Select ( $db, 'select * from users where email="' . htmlspecialchars ( addslashes ( $_POST ['email'] ) , ENT_QUOTES, $encoding = "cp1251") . '" and pass="' . md5 ( $_POST ['password'] ) . '"' );
        if ($r->next_row ()) {
                $_SESSION = array ();
                $_SESSION ['user'] = $r->result ( 'id' );

                //если юзер нажал "запомнить меня"
                if (isset ( $_POST ['save-me'] )) {
                        $username = "" . addslashes ( $_POST ["email"] ) . "";
                        $passw = "" . addslashes ( $_POST ["password"] ) . "";
                        $pasw = md5 ( $passw );
                        setcookie ( 'e_mail', $username, time () + 864000 );
                        setcookie ( 'password', $pasw, time () + 864000 );
                } ;
                $r1 = new Select ( $db, 'update users set date_visit='.time().' where id='.$_SESSION ['user'] );
                //если председатель идем в ред. страницы о нас личного кабинета.
                header ( "location: http://" . $_SERVER['HTTP_HOST']);
                //если поставщик то идем в личный кабинет
               //  else header ( "location: http://" . $_SERVER['HTTP_HOST'].'/lk');

        } else {
                //сохраняем введенные параметры
                foreach ( $_POST as $pkey => $val ) {
                        if ($_POST [$pkey] != '' ) {
                                $par .= '/' . $pkey . '/' . htmlspecialchars ( strip_tags ( stripslashes ( urldecode ( $val ) ) ), ENT_QUOTES, $encoding = "cp1251" );
                        };
                };
               unset($main);
               $main = new outTree ( $FILENAME );
               foreach ( $_POST as $pkey => $val ) {
                        if (($pkey =='email') or ($pkey =='password') )
                                $main->addField ( $pkey, urldecode ( $val ) );
               };
               $message = 'Логин или пароль введен не правильно!';
               $main->addField ( 'message', 'Ошибка! ' . $message );

        };
 //активация профиля
 } else if  (isset ( $_GET ['activate'] ))  {
        $error='';
        if (!$_GET['id']>0 || !strlen($_GET['code'])>0) {
            // echo "s";
             $error = 'Ошибка! Не верные параметры активации профиля.';
        };

        $r = new Select ( $db, "select * from reg where id=$_GET[id] and code='$_GET[code]'");
        //echo $r->num_rows();
        if ($r->num_rows() == 1 && $error=='') {
              $r->next_row();

              $r1 = new Select ( $db, "select * from users where email ='".$r->result('email')."'");
              //echo $r1->num_rows();
              if ($r1->num_rows() >0 ) {
                 $error = 'Ошибка! Профиль уже был активирован ранее.';
              } else {
                    $pass1 = $r->result('pass');
                    $fio  =  $r->result('fio');
                    $email = $r->result('email');

                    $r1 = new Select ( $db, "delete from reg where id=$_GET[id] and code='$_GET[code]'");

                    $r1 = new Select ( $db, "insert into users (pass,pass_text,fio,email,date,date_visit,new_comments,new_messages,watch)
                              values('".md5($pass1)."','$pass1','$fio','$email',".time().",".time().",1,1,0)");



                    //$main->addField ( 'log', '' );
                    $_SESSION ['user'] = mysql_insert_id ();
                    //письмо на мыло
                    $letter = "Поздравляем! Вы успешно активировали свой профиль на сайте " . $GLOBALS ['mainOutTree']->SERVER_NAME."!
Благодарим Вас за внимание, надеемся наш проект будет для Вас полезным.";
                    $mail = &newViaSMTP('mail_register');
                    $mail->Subject = "Регистрация на сайте " . $GLOBALS ['mainOutTree']->SERVER_NAME;
                    unset($mail->to[0]);
                    $mail->AddAddress($email);
                    $subm = sendViaSMTP($mail,$letter,false);

                     header ( "location: /lk/edit/1" );
                };
        };
        if ($error!=='') {
              unset($main);

              $main = new outTree ( $err_reg_FILENAME );
              $main->addField('error',$error);
        };

//если нажали "Регистрация"
} else if (isset ( $_POST ['register'] ))  {

        //проверочки
        $err = '';
        if (strip_tags ( addslashes ( $_POST ['email'] ) ) == '' or strip_tags ( addslashes ( $_POST ['pass1'] ) ) =='' or strip_tags ( addslashes ( $_POST ['pass2'] ) ) == '')
                $err = 1;
        if (! preg_match ( "([0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-wyz][a-z](fo|g|l|m|mes|o|op|pa|ro|seum|t|u|v|z)?)", $_POST ['email'] ) and $_POST ['email'] != "") {
                $err = 2;
        };
        if ($_POST['pass1'] !== $_POST['pass2']) $err=4;

        $r = new Select ( $db, 'select * from users where email="' . strip_tags ( addslashes ( $_POST ['email'] ) ) . '"' );
        if ($r->num_rows > 0)
                $err = 3;
        //удачно
        if ($err == '') {

              foreach ( $_POST as $key => $value)
                $$key= htmlspecialchars ( addslashes ($value), ENT_QUOTES, $encoding = "cp1251");
                $code = generate_code(20);
                $r1 = new Select ( $db, "insert into reg (email,pass,code,fio,date)
                                                  values('$email','$pass1','$code','$fio',".time().")");

               $id=$db->insert_id();
                //letter
                $letter = "Спасибо за регистрацию на сайте " . $GLOBALS ['mainOutTree']->SERVER_NAME."
Для активации профиля пожалуйста перейдите по ссылке http://". $GLOBALS ['mainOutTree']->SERVER_NAME."/auth/activate/1/id/$id/code/$code";
              $mail = &newViaSMTP('mail_register');
              $mail->Subject = "Регистрация на сайте " . $GLOBALS ['mainOutTree']->SERVER_NAME;
              unset($mail->to[0]);
              $mail->AddAddress($email);
              $subm = sendViaSMTP($mail,$letter,false);

              unset($main);
              $main = new outTree ( $new_reg_FILENAME );
              /*

              */
              //header ( "location: /" );
        } else {

                unset($main);
                $main = new outTree ( $reg_FILENAME );

                switch ($err) {
                  case '1' :
                          $message = "Вы не ввели E-mail или пароль";
                          break;
                  case '2' :
                          $message = "Введите корректный E-mail адрес!";
                          break;
                  case '3' :
                          $message = 'Такой E-mail уже существует!';
                          break;
                  case '4' :
                          $message = 'Пароли не совпадают!';
                          break;
                };
                if ($err>0) $main->addField ( 'message', 'Ошибка! ' . $message );


                foreach ( $_POST as $pkey => $val )
                    $main->addField ( $pkey, urldecode ( $val ) );
                /*
                $r = new Select ( $db, 'select * from site_pages where id =5' );
                if ($r->next_row() > 0) {
                       $main->addField('license',strip_tags($r->result('content')));
                };
                */
        };
//регистрация
} else if  ($_GET ['register'] >0) {
    unset($main);
    $main = new outTree ( $reg_FILENAME );
    /* $r = new Select ( $db, 'select * from city order by name' );
     while ($r->next_row() > 0) {
         unset($sub);
         $sub = new outTree();
         $r->addFields($sub,$ar=array('id','name'));
         if ($r->result('id')==$city) $sub->addfield('selected','selected');
         $main->addField('city',$sub);
     };
     $r = new Select ( $db, 'select * from site_pages where id =5' );
     if ($r->next_row() > 0) {
         $main->addField('license',strip_tags($r->result('content')));
     };
     */
} else if (isset ( $_POST ['repair'] )) {
        unset($main);
        $main = new outTree ( $forgot_FILENAME );
        //проверочки
        if (! preg_match ( "/^([0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-wyz][a-z](fo|g|l|m|mes|o|op|pa|ro|seum|t|u|v|z)?)$/", htmlspecialchars ( stripslashes ( $_POST ['email'] ), ENT_QUOTES, $encoding = "cp1251" ) ) and htmlspecialchars ( stripslashes ( $_POST ['email'] ) , ENT_QUOTES, $encoding = "cp1251") != "")
             $main->addField ( 'message', 'Введите корректный E-mail адрес!' );
        else {
             if ( $_POST ['email'] !=='') {
              $r = new Select ( $db, 'select * from users where email="' . strip_tags ( addslashes ( $_POST ['email'] ) ) . '"' );
              $r->next_row ();
              if ($r->num_rows == 1) {

                 $letter = "Ваш пароль : " . $r->result ( 'pass_text' );
                 $mail = &newViaSMTP('mail_register');
                 $mail->Subject = 'Восстановление пароля на сайте ' . $GLOBALS ['mainOutTree']->SERVER_NAME;
                 $subm = sendViaSMTP($mail,$letter,false);

                 if ($subm>0) {
                         unset($main);
                         $main = new outTree ($FILENAME );
                         $main->addField ( 'message', 'Письмо c паролем отправлено на Ваш E-mail.' );
                   }
                   else $main->addField ( 'message', 'Ошибка отправки письма.' );

              } else $main->addField ( 'message','Указанный E-mail не зарегестрирован на нашем сайте.');
             };
       }

       foreach ( $_POST as $pkey => $val ) {
                if (($pkey =='email') or ($pkey =='password') )
                        $main->addField ( $pkey, urldecode ( $val ) );
       };
};

if (isset ( $main )) {
        $site->addField ( $GLOBALS ['currentSection'], $main );
        unset ( $main );
}

?>