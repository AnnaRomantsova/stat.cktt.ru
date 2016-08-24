<?php

  include('config.php');
  include($inc_path.'/classes/class.BF.php');
  //include($inc_path.'/admin_functions.php');
  //include($inc_path.'/img.php');

  //echo $front_html_path;
  $lk_filename = $front_html_path.'panel.html';
  $lic_filename = $front_html_path.'lic.html';
  $lk_user_filename = $front_html_path.'user_panel.html';
  $profile_filename = $front_html_path.'master_profile.html';
  $user_filename = $front_html_path.'user_profile.html';

 // echo "!!!!!!!!";
//die;
  //var_dump($_POST);

  $patch=$HTTP_SERVER_VARS[HTTP_REFERER];

  $message = '';
  unset($main);
  if (!(isset($_SESSION['user']))) {
      header ( "location: http://" . $_SERVER['HTTP_HOST']);
  };

  //редактирование
  if ($_GET['edit']>0) {
         $r = new Select($db,'select * from users where id="'.$_SESSION['user'].'"');
         $r->next_row();
         if ($r->result('is_master')>0 or $r->result('pre_master')>0) { $is_master=$r->result('is_master'); $prof_master=1;};
         if ($_GET['master'] >0) $prof_master=1;
        //echo $prof_master;
          //если юхер-мастер
          if ($prof_master>0) {
                  $main = &addInCurrentSection($lk_filename);
          } else {
                  $main = &addInCurrentSection($lk_user_filename);
          };

          // $main = new outTree($lk_filename);
            //если юзер нажал Сохранить в личном кабинете
                   if (isset($_POST['save']))  {
                          //echo "kk";
                          foreach ( $_POST as $key => $value)
                                $$key= htmlspecialchars ( addslashes ($value), ENT_QUOTES, $encoding = "cp1251");

                          if ($prof_master == 1) $where = ",pre_master=1,is_master=0"; else $where='';
                          $r = new Select($db,"update users set fio='$fio',
                                                skipe='$skipe',link='$link',
                                                tel='$tel',id_city=$city,about='$about',
                                                time='$time' $where
                                                where id=$_SESSION[user]");




                          SaveRazdel('user_zakon','akon_razd',$_SESSION['user']) ;
                          SaveRazdel('user_service','ervice_razd',$_SESSION['user']) ;


                          $message = 'Данные сохранены.';
                          if ($prof_master == 1) $message.="<br>Профиль мастера активируется после просмотра модератором.";

                          if ($message!=='') $main->addField('message',$message);

                   };

                    $r = new Select($db,'select * from users where id="'.$_SESSION['user'].'"');
                    $r->next_row();
                    $r->addFields($main,$ar=array('id','name','email','fio','skipe','link','adress','tel','price','experience','grafic'));

                   // $main->addfield('about',htmlspecialchars_decode($r->result('about')));

                    addSprav($main,'city',$r->result('id_city'),'city');
                    addSpravM($main,'zakupki_zak',getRazdel('user_zakon',$_SESSION['user']),'zakon') ;
                    addSpravM($main,'type_service',getRazdel('user_service',$_SESSION['user']),'service') ;
         //echotree($main);
                   // addUserSprav($main,$_SESSION['user'],'user_types');
  //просмотр инфы о добавлении профиля мастера
  } else if ($_GET['lic']>0) {
      $main = &addInCurrentSection($lic_filename);
      $r = new Select ( $db, 'select * from site_pages where id =6' );
      if ($r->next_row() > 0) {
             $main->addField('license',strip_tags($r->result('content')));
      };
      $r = new Select ( $db, 'select * from site_pages where id =140' );
      if ($r->next_row() > 0) {
             $main->addField('text',strip_tags($r->result('content')));
      };
///просмотр своего профиля
  } else {
          $r = new Select($db,'select * from users where id="'.$_SESSION['user'].'"');
          if ($r->next_row()) $is_master=$r->result('is_master');


                  $main = &addInCurrentSection($user_filename);


  };
  //$site->addField($GLOBALS['currentSection'],&$main);
  unset($main);

 ?>