<?
/**
 * администрирование

 */

 session_start();

 if ($_SESSION['valid_user']=='admin')  {

     include($_SERVER['DOCUMENT_ROOT'].'/setup.php');
     include($inc_path.'/db_conect.php');
     include_once($inc_path.'/func.front.php');
     $shablon = 'back/sendmail/back.html';
      require_once($inc_path."/phpmailer/func.mailViaSMTP.php");

     $main = new outTree();

     if (isset($_POST['send'])) {

           foreach ( $_POST as $key => $value)
               $$key=$value;


              $ok = 0; $err=0;
              if ($is_master >= 0) $where = " where is_master = $is_master";
              $r = new Select($db,"select * from users $where");
              while ( $r->next_row() ) {
                  unset($mail);
                  $mail = &newViaSMTP('mail_rassilka');
                  $mail->Subject = $tema;
                  $mail->AddAddress($r->result('email'));
                  $subm = sendViaSMTP($mail,$text,true);
                  if ($subm >0) $ok ++; else $err++;
              };
              $summ = $ok+$err;
              $mess = "Отправлено писем: $summ. Из их успешно: $ok, неудачно: $err";
    };


    $main->addField('message',$mess);


     out::_echo($main,$shablon);

 }
 else header('Location: '.$auth_path);
?>
