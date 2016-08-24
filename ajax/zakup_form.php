<?php

// Запрет на кэширование
header("Expires: Mon, 23 May 1995 02:00:00 GTM");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GTM");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
//
//В этом файле хранятся логин и пароль к БД
require_once("../setup.php");
include_once($inc_path.'/db_conect.php');
include_once($inc_path.'/func.front.php');

$shablon = 'front/zakupki/form.html';

$main = new outTree();

 $fieldsWithoutFail = array(
  'name','email','phone','kod'
 );

foreach ( $fieldsWithoutFail as $value) {
         //echo "dd";
       $str_fieldsWF.= ('\''.$value.'\',');
       $flag = $flag && !empty($$value);
};

$main->addField('fieldsWithoutFail',substr($str_fieldsWF,0,-1));

$id=$_POST['id'];
if ( $id >0 )
   {
       $main->addfield('id',$id);
       $r = new Select($db,'select * from site_pages where id= 147');
       if ( $r->next_row() ) {
                //$r->addFieldHTML($main,$ar=array('id'));
   		        $r->addFieldHTML($main,'content');


      };
     //echo "65645";
      out::_echo($main,$shablon);
  }





?>