<?php

  include('config.php');

  $FILENAME = $front_html_path.'panel.html';

  unset($main);
  $main = new outTree($FILENAME);

  if (isset($_SESSION['user'])) {

      $main->addField('log','');
      $r = new Select($db,'select * from '.$GLOBALS['table_name'].' where id="'.$_SESSION['user'].'"');
      if ($r->next_row())
            $r->addFields($main,$ar=array('id','fio'));

      $r = new Select($db,"select count(*) as cnt from messages where user_to=$_SESSION[user] and is_read=0 ");
      $r->next_row();
      if ($r->result('cnt')>0) $main->addField('cnt',$r->result('cnt'));

  //   var_dump($_COOKIE);
  } else {
          $main->addField('not_log','');
  };



//echoTree($main);
  if (isset($main)) {
                $site->addField($GLOBALS['currentSection'],$main);
                unset($main);
  };
 ?>