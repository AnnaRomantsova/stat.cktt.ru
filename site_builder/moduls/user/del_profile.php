<?php

  include('config.php');
  include($inc_path.'/img.php');
  include($inc_path.'/classes/class.BF.php');
  include($inc_path.'/admin_functions.php');

  $FILENAME = $front_html_path.'del_profile.html';


  $message = '';
  unset($main);
  if (!(isset($_SESSION['user']))) {
      header ( "location: http://" . $_SERVER['HTTP_HOST']);
  };
  //echo $_SESSION ['user'];
  $main = new outTree($FILENAME);
  //если юзер нажал Сохранить в личном кабинете
  if (isset($_POST['yes']))  {
       $id= $_SESSION['user'];
       $rs = new Select($db,"delete from zakaz_types where id_zakaz in (select id from zakaz where id_user=$id)");
       $rs = new Select($db,"delete from zakaz where id_user=$id");

       $rs = new Select($db,"delete from likes where id_user=$id");
       $rs = new Select($db,"delete from messages where user_from=$id or user_to=$id");
       $rs = new Select($db,"delete from user_types where id_user=$id");
       $rs = new Select($db,"delete from users where id=$id");

       $back = new BF($db,$modulName,$modulCaption,'galery',$arFiles);


       $r1 = new Select($db,"select * from galery where id_user=$id");
       while ($r1->next_row()) {
            $back->deleteRecord($r1->result('id'));
       };
       // $rs = new Select($_this->db,"delete from galery where id_user=$id");
       $_SESSION = array ();

       header ( "location: http://" . $_SERVER['HTTP_HOST']);
  };



  $site->addField($GLOBALS['currentSection'],&$main);
  unset($main);

 ?>