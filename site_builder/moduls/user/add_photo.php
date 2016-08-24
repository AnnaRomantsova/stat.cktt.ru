<?php

  include('config.php');
  include($inc_path.'/img.php');
  include($inc_path.'/classes/class.BF.php');
  include($inc_path.'/admin_functions.php');

  $FILENAME = $front_html_path.'add.html';


  $message = '';
  unset($main);
  if (!(isset($_SESSION['user']))) {
      header ( "location: http://" . $_SERVER['HTTP_HOST']);
  };

  $main = new outTree($FILENAME);
  //если юзер нажал Сохранить в личном кабинете
  if (isset($_POST['save']))  {
         $_FILES['image2'] =  $_FILES['image1'];
         $values['about']  = $_POST['about'];
         $values['id_user']  =$_SESSION['user'];
         $values['pabl']  =   $_POST['pabl'];
         $values['price']  =   $_POST['price'];
         $values['id_type']  =   $_POST['zakaz_types'];
        // var_dump($values);
         $back = new BF($db,$modulName,$modulCaption,'galery',$arFiles);
         $back->saveNewRecord($values);

         $r1 = new Select($db,'select * from galery where id='.$values['id']);
         if ($r1->next_row()) {
            if (strlen($r1->result('image1')) >0) {
	            image_resize($r1->result('image1'),150,137);
    	        image_resize($r1->result('image2'),600,600);
    	    } else
    	        $r1 = new Select($db,'delete from galery where id='.$values['id']);
         };

         $main->addField('mode','mode_edit');
        $r = new Select($db,"update users set pre_master=1, is_master=0  where id=$_SESSION[user]");
         header ( "location: http://" . $_SERVER['HTTP_HOST'].'/lk/edit/1/master/1');

         //$message = 'Данные сохранены.';
         //if ($message!=='') $main->addField('message',$message);

  };


  addZakazSprav($main,0,'zakaz_types');
  $site->addField($GLOBALS['currentSection'],&$main);
  unset($main);

 ?>