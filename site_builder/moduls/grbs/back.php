<?
/**
 * �����������������

 */
// ini_set('display_errors', 1);error_reporting(E_ALL);
 session_start();

 if ($_SESSION['valid_user']=='admin')  {

         include($_SERVER['DOCUMENT_ROOT'].'/setup.php');
     include($inc_path.'/db_conect.php');
         include('config.php');
     include('class.back.php');

     $back = new B_news($db,$modulName,$modulCaption,$table_name,$arFiles);
     $back->getEvent();

 }
 else header('Location: '.$auth_path);
?>