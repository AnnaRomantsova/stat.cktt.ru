<?

/**
 * настройки сайта
 * подключение к базе, пароль к системе администрирования
 * @package ALL
 */


//администрирование
 $adminpass='1';      //пароль администратора

 $document_root=$_SERVER['DOCUMENT_ROOT'];
 $moduls_root=$document_root.'/site_builder/moduls';
 $inc_path=$document_root.'/site_builder/includes';

 $auth_path='/admin/exit.php';

//база данных
 $db_host='localhost';
 $db_user='root';
 $db_password='';
 $db_name='parse';


//база данных
$db_host='localhost';
 $db_user='dba0006';
 $db_password='d4cwHZff';
 $db_name='dba0006_1';

//error_reporting(E_ALL & E_NOTICE);
error_reporting(1);
// ini_set('session.use_trans_sid','0');

?>