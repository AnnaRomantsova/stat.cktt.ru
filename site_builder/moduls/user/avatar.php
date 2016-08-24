<?php

  include('config.php');
  include($inc_path.'/img.php');
  include($inc_path.'/classes/class.BF.php');
  include($inc_path.'/admin_functions.php');

  $FILENAME = $front_html_path.'avatar.html';


  $message = '';
  unset($main);
  if (!(isset($_SESSION['user']))) {
      header ( "location: http://" . $_SERVER['HTTP_HOST']);
  };

  $main = new outTree($FILENAME);
  //если юзер нажал Сохранить в личном кабинете
  if (isset($_POST['save']))  {

         $values['id']  = $_SESSION['user'];
         $back = new BF($db,$modulName,$modulCaption,'users',$arFiles);
         $back->saveRecord($values,$values['id']);

         $r1 = new Select($db,'select * from users where id='.$values['id']);
         if ($r1->next_row()) {
            image_resize($r1->result('image1'),90,90);
         };

         header ( "location: http://" . $_SERVER['HTTP_HOST'].'/lk');

         //$message = 'Данные сохранены.';
         //if ($message!=='') $main->addField('message',$message);

  };

      //Удалить картинку
    if (isset($_POST['del']))  {

          $values['d_image1'] = "1";
          $back = new BF($db,$modulName,$modulCaption,'users',$arFiles);
          $back->saveRecord($values,$_SESSION['user']);
   };

  $r = new Select($db,'select * from users where id="'.$_SESSION['user'].'"');
  if ($r->next_row()) add_user_avatar($main,$r);

  $site->addField($GLOBALS['currentSection'],&$main);
  unset($main);

 ?>