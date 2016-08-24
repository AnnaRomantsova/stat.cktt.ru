<?php

 include('config.php');
 unset($main);
 $main_FILENAME = $front_html_path.'panel.html';

 //$main = &addInCurrentSection($main_FILENAME);
 $main = new outTree($main_FILENAME);


  $sel_city_id =0;
  $sel_city_name = 'Все города';

  $cookie=$_COOKIE['id_city'];

  $r = new Select($db,'select * from city order by name ');
  //$i=1;
  while ( $r->next_row() ) {
      unset($sub);
      if ( $r->result('id')==$cookie) {
         $sel_city_id = $r->result('id');
         $sel_city_name = $r->result('name');
         //$sel_city_tel = $r->result('tel');
         //$sel_city_work = $r->result('work');
      };
      $sub = new outTree();
      $r->addFields($sub, $ar=array('id') );
      $sub->addField('name',str_pad($r->result('name'),8));
      $main->addField('city',$sub );
      $i++;
  };


 $main->addField('sel_city_id',$sel_city_id);
 $main->addField('sel_city_name',$sel_city_name);
 //$main->addField('sel_city_tel',$sel_city_tel);
 //$main->addField('sel_city_work',$sel_city_work);
// echotree($main);
 $site->addField($GLOBALS['currentSection'],&$main);
// echotree($site);
?>