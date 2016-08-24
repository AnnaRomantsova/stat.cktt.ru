<?php

// Запрет на кэширование
header("Expires: Mon, 23 May 1995 02:00:00 GTM");
header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GTM");
header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
//

if (!($_POST['user'])>0) {
      die;
};

if (!($_POST['id_like'] )>0) {
      die;
};

if (!($_POST['id_type'] )>0) {
      die;
};

var_dump($_SESSION);

//В этом файле хранятся логин и пароль к БД
require_once("../setup.php");
include_once($inc_path.'/db_conect.php');
include_once($inc_path.'/func.front.php');
/*
//Подключаемся к базе
function con_bd($Host, $User, $Passwd, $dbname){
@MYSQL_CONNECT($Host, $User, $Passwd) or die("Ошибка при соединении с Базой MySQL!!!");
@MYSQL_SELECT_DB($dbname) or die("Не могу выбрать таблицу $dbname");
@mysql_query("SET CHARACTER SET cp1251;") or die("Invalid query: ". mysql_error());
}
//echo "ddd";
con_bd($db_host,$db_user,$db_password,$db_name);

    //Проверка правильность имени
    if(!$from>=1)
    {
      $log.="Неправильно заполнено поле 'Ваше имя' (3-15 символов)!";
      $eierr="yes";
    }

*/

   $r = new Select($db,"select * from likes where id_user=$_POST[user] and id_like = $_POST[id_like] and id_type = $_POST[id_type]");
   if ($r->next_row())
       $r1 = new Select($db,"delete from likes where id_user=$_POST[user] and id_like = $_POST[id_like] and id_type = $_POST[id_type]");
    else $r1 = new Select($db,"insert into likes(id_user,id_like,id_type) values ($_POST[user],$_POST[id_like],$_POST[id_type])");

 // var_dump($main);
 //  $site_FILENAME = 'front/news/panel_ajax.html';
 //  out::_echo($main,$site_FILENAME);
?>