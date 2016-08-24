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

function formatj($str){
   $str = str_replace('(','',$str);   $str = str_replace(')','',$str);     $str =trim($str);   $str= str_replace("\n", "", $str);
   return str_replace('"','',$str);
}

$query = iconv('utf-8', 'cp1251', $_GET['term']);  
$suggest = array(); 
$data = array();   
$s='';
switch ($_GET['mod']){
  	case 'contractSubjectName': 	  
    $r = new Select($db,"select distinct contractSubjectName from positions where contractSubjectName like '%".$query."%' limit 5 ");    
    while ( $r->next_row() )  
  	   $s .= '{"value":"'.formatj($r->result('contractSubjectName')).'","query":"'.formatj($query).'"},';
       
    $s=substr($s,0,-1);
		break;
	case 'predmet_tru': 	  
    $r = new Select($db,"select distinct name from products where name like '%".$query."%' limit 10");    
    while ( $r->next_row() )  
  	   $s .= '{"value":"'.formatj($r->result('name')).'","query":"'.formatj($query).'"},';
       
    $s=substr($s,0,-1);
		break;
  case 'kbk':
    $r = new Select($db,"select distinct kbk_code from positions where kbk_code like '%".$query."%' limit 10");    
    while ( $r->next_row() )  
  	   $s .= '{"value":"'.formatj($r->result('kbk_code')).'","query":"'.formatj($query).'"},';
       
    $s=substr($s,0,-1);
		break;
 case 'okved':
    $r = new Select($db,"select distinct okved_code,okved_name from positions where okved_code like '%".$query."%'  or okved_name like '%".$query."%' limit 6");    
    while ( $r->next_row() )  
  	   $s .= '{"value":"'.formatj($r->result('okved_code')." : ".$r->result('okved_name')).'","query":"'.formatj($query).'"},';
       
    $s=substr($s,0,-1);
		break; 
  case 'okpd_code':
    $r = new Select($db,"select distinct okpd_code,okpd_name from products where okpd_code like '%".$query."%'  or okpd_name like '%".$query."%' limit 5");    
    while ( $r->next_row() )  
  	   $s .= '{"value":"'.formatj($r->result('okpd_code')." : ".$r->result('okpd_name')).'","query":"'.formatj($query).'"},';
       
    $s=substr($s,0,-1);
		break;      
}


if(isset($s)){
	header('Content-Type: application/json; charset=windows-1251');
  echo "[".$s."]";
}
?>