<?php

/**
 * сборщик фронта сайта
 * @package FRONT

 * @version 3.03.cms - 16.01.2008 15:00
 *
 * принципиальные отличия
 * .01 по умолчанию осуществляется переход на страницу error404 <br>
 * .01 в шаблоны добавлена page - "сквозная" переменная  <br>
 * .02 корректное определение первой страницы раздела <br>
 * .03 в шаблоны добавлена site - "сквозная" переменная - корень дерева сайта <br>
 */

session_start();
header('Last-modified: '.gmdate('D, d M Y H:i:s', time()-3600).' GMT');
header('Content-Language: ru');

ini_set('register_globals', 'off');
include($_SERVER['DOCUMENT_ROOT'].'/setup.php');
include($inc_path.'/initGET.php');
include_once($inc_path.'/db_conect.php');
include_once($inc_path.'/func.front.php');
include_once($inc_path.'/myfunc.php');

/*
if ($_POST['city_name'] !== '') {
    $r = new Select($db,'select *  from city where name = "'.$_POST['city_name'].'"');
    if ($r->next_row())  setcookie('id_city',$r->result('id'));
    echo $r->result('id');
};
 if (!isset($_COOKIE['id_city'])) {
    setcookie('id_city','0');
};
/*
 $_SESSION = array ();
 var_dump($_SESSION);
 die;
 */
$site = new outTree();

if ($page=='city') $page='index';
$site->pageid = $page;
//echo
/*
if ($_SESSION['user'] >0 && $page=='index') {
    $site->pageid = 'lk';
    $page='lk';
} else  if (!($_SESSION['user'] >0) && $page=='index') {
    $site->pageid = 'master';
    $page='master';
};
*/

//if ()
$mainOutTree->addField('site',$site);
$mainOutTree->addField('datetime',mktime());
$mainOutTree->addField('page',$page);
$mainOutTree->addField('SERVER_NAME',$_SERVER['HTTP_HOST']);



$site->addField('index'!=$page ? 'inner' : 'index','');


//if ($_POST['change_city']>0) setcookie('id_city', $_POST['change_city']);
// echo $_COOKIE['id_city'];


//var_dump($_POST);
//
 // echo $_COOKIE['id_city'];
// запрашиваем страницу в базе, если нет - линкуем другую
if ($page > '') {
        $r_sp = new Select($db,'select * from '.$site_table.' where page="'.$page.'" and section="0" and pabl="1"');
    $page_num = $r_sp->num_rows;
}

if (!$page_num) {
        $r_sp->unset_();
        $page='error404';
        $r_sp = new Select($db,'select * from '.$site_table.' where page="'.$page.'" and section="0"');
}

if (!$r_sp->next_row()) echo_error();

$parent_ = $r_sp->result('parent');


$r_ss  =  new Select($db,'select * from '.$site_table.' where id="'.$parent_.'" and pabl="1"');
if (!$r_ss->next_row())
        die('Internal site Error');

else {
// подсчет колличества страниц в разделе для меню
        $r_c = new Select($db,'select count(*) from '.$site_table.' where parent="'.$parent_.'" and pabl="1" and menu="1"');
        $count_ = $r_c->next_row() ? $r_c->result(0) : 0;
        $r_c->unset_();

// подсчет колличества страниц в разделе для следа
        $r_c = new Select($db,'select count(*) from '.$site_table.' where parent="'.$parent_.'" and pabl="1"');
        $pcount_ = $r_c->next_row() ? $r_c->result(0) : 0;
        $r_c->unset_();
}

$r_shablon = new Select($db,'select * from '.$shablons_table.' where id="'.$r_sp->result('shablon').'"');
if (!$r_shablon->next_row()) echo_error();

$site_FILENAME = 'front/'.$r_shablon->result('location');

//


$r_shablon->unset_();

if($_SESSION['user'] >0)  {	$site->addField( 'user',$_SESSION['user']);
	$rm = new Select($db,"select count(*) as cnt from messages where user_to=$_SESSION[user] and is_read=0 ");
    $rm->next_row();
    if ($rm->result('cnt')>0) $site->addField('msg_cnt','<span style="color:#a0ce47">('.$rm->result('cnt').')</span>');
      else $site->addField('msg_cnt','<span>('.$rm->result('cnt').')</span>');
};

//город
$city = '';
if ($_GET['city']>0)  {	$site->addField('city_l','/city/'.$_GET['city']);
	//if ($page=='index') $site_FILENAME = str_replace('index','inner',$site_FILENAME );
   // echo "!";
	$r = new Select($db,'select * from city where id="'.$_GET['city'].'"');
    if ($r->next_row()) {
           $site->addfield('city_name',$r->result('name'));
           $site->addfield('city_name_kogo',' города '.$r->result('kogo'));
           //echo ' города '.$r->result('kogo');
           $city = ' '.$r->result('kogo');
           $site->addfield('city_name_gde',' городе '.$r->result('komu'));
    };
};

// добавление ссылок
$apages = array('index','contacts','map','prices','order','delivery', 'sandwiches', 'refrigerators');
foreach($apages as $value)
  $site->addField( $value!=$page ? 'a_'.$value : 'noa_'.$value, '');



//инициализация следа
 if ('index' !=$page ) {
        $flagNameFirstPage = true;
        $path = new outTree();
        $path->addField('first','');

        $ot_last = new outTree();
        $ot_last->addField('name', textFormat(1 == $pcount_ ? $r_ss->result('name') : $r_sp->result('name')) );
        $path->addField('last',$ot_last);

        if ($pcount_) {
                $rt = new Select($db,'select page from '.$site_table.' where parent="'.$parent_.'" and pabl="1" order by sort limit 1');
                if ($rt->next_row()) {
                    $pageFirst = $rt->result('page');
                        if ($pageFirst == $page) {
                                $path->last->name =  textFormat($flagNameFirstPage ? $r_sp->result('name') : $r_ss->result('name'));
                        }
                        else {
                          $sub = new outTree();
                          $sub->addField('name',textFormat($r_ss->result('name')));
                          $sub->addField('href',$pageFirst);
                      $sub->addField('T','A');
                          $sub->addField('separator','');
                      $path->addField('sub',$sub);
                          unset($sub);
                        }
                }
                $rt->unset_();
        }

        $site->addField('path',$path);
 }

// инициализация меню
 include($moduls_root.'/menu/panel.php');
  //include($moduls_root.'/menu/menu_sub.php');


// инициализация секций, порядок важен!
 addSections($site,$ar = array('main_section','section1','section2','section3','section4','section5','section6'));

 //if($page == 'cabinet' or $page =='basket')
 // include($moduls_root.'/menu/menu_sub.php');
 //echo $page;
// занесение неинициализированных полей
  $params = array('title','description','keywords');
  $params = array('title','description');
  $keyw = "Парикмахер$city, маникюр$city, педикюр$city, макияж$city, наращивание волос$city, наращивание ногтей$city, фитнес$city";
  $title = $keyw;
  $description = "Сайт для мастеров и салонов красоты";
  $site->addField('keywords',$keyw);

 // не проинициализированы в модулях - берем из страницы
 $paramsNotInit = array();
 foreach($params as $value)
        if (empty($site->$value)) { $paramsNotInit[]=$value; unset($site->$value); }

 $r_sp->addFields($site,$paramsNotInit);


 //var_dump($paramsNotInit);
 // не проинициализированы в странице - берем из раздела
 $paramsNotInit = array();
 foreach($params as $value)
        if (empty($site->$value)) { $paramsNotInit[]=$value; unset($site->$value); }

 $r_ss->addFields($site,$paramsNotInit);
 //echotree($site);

 out::_echo($site,$site_FILENAME);

?>