<?
  include('config.php');

 require_once($inc_path."/phpmailer/func.mailViaSMTP.php");

 unset($main);
 $FILENAME = $front_html_path.'front.html';
 $main = &addInCurrentSection($FILENAME);

 //echotree($main);
 foreach ( $_POST as $key => $value)
               $$key=$value;

 // строка для JavaScript и проверка наличия обязательных полей
 $flag = true; $str_fieldsWF = '';
 foreach ( $fieldsWithoutFail as $value) {
         //echo "dd";
       $str_fieldsWF.= ('\''.$value.'\',');
       $flag = $flag && !empty($$value);
 }

  // if ($r->next_row()) $r->addFieldHTML($main,'content');


 $main->addField('fieldsWithoutFail',substr($str_fieldsWF,0,-1));

 if (isset($_POST['send']) && $flag)  {

 $text = date('d.m.Y').' в '.date('H:i:s').' на сайте '.$_SERVER['SERVER_NAME'].'в разделе Консультации было отправлено сообщение.
От: '.stripslashes($fio).'
Email: '.stripslashes($email).'
Номер договора: '.stripslashes($number).'
Текст: '.stripslashes($text);

         $mail = &newViaSMTP('mail_feed');
         $mail->Subject = $tema;
         $subm = sendViaSMTP($mail,$text,false);
       //  header('Location: '.$GLOBALS['strPATH'].'?subm='.$subm.'#f');



// if (isset($subm))
    $mess = ($subm>0 ? 'ok' : 'er');

    $main->addField('message',$mess);

 }

 unset($main);

?>

