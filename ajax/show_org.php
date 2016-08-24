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
$shablon = 'front/zakupki/show_org.html';

$main = new outTree();
// var_dump($_POST);
$id=$_POST['id'];

$id = substr($id,1);
/*
$q= '1 = 1';
foreach ($a as $val){
 if ($val!=='') $q .= " or (id_grbs = $val)";
}
*/
if ( strlen($id) >0 ) $sql =  "select *, 'checked' as checked from grbs where id_grbs in ($id) order by id_grbs,name_pbs";
    else $sql =  "select *, '' as checked from grbs order by id_grbs,name_pbs ";
  


      $r = new Select($db,$sql);
       while ( $r->next_row() ) {
             unset($sub);
             $sub = new outTree();
             $r->addFields($sub,$ar=array('id','name_pbs','checked'));
             $main->addField('sub',$sub);
       };

       out::_echo($main,$shablon);
 





?>