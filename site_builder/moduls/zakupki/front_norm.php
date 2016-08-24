<?php
 //список всех мастеров
 include('config.php');
 include($inc_path.'/service/class.pager.php');
 include('functions.php');
 unset($main);
 $FILENAME = $front_html_path.'front.html';
 $print_FILENAME = $front_html_path.'front_print.html';
// var_dump($_GET);

 include($inc_path.'/myfunc.php');
 if (strpos($_SERVER['REQUEST_URI'],'print'))  $main = &addInCurrentSection($print_FILENAME);
   else $main = &addInCurrentSection($FILENAME);

  $where = '1 = 1';
/*
 ///Отправка заявки

 if ($_POST['send_zayav']>0){
    require_once($inc_path."/phpmailer/func.mailViaSMTP.php");
    foreach ( $_POST as $key => $value)
               $$key=$value;

    $flag = true; $str_fieldsWF = '';
    foreach ( $fieldsWithoutFail as $value) {
         //echo "dd";
       $str_fieldsWF.= ('\''.$value.'\',');
       $flag = $flag && !empty($$value);
    }
   	if ($flag)  {

      if ($kod=='23') {
         //echo "s";
         $text = date('d.m.Y').' в '.date('H:i:s').' на сайте '.$_SERVER['SERVER_NAME'].' в разделе Закупки была отправлена заявка на тендер.
№ тендера: '.stripslashes($number).'
Email: '.stripslashes($email).'
Контактное лицо: '.stripslashes($name).'
Телефон: '.stripslashes($phone);

         $mail = &newViaSMTP('mail_feed');
         $mail->Subject = $tema;
         $subm = sendViaSMTP($mail,$text,false);
       //  header('Location: '.$GLOBALS['strPATH'].'?subm='.$subm.'#f');

         $mess = ($subm>0 ? 'ok' : 'er');
      } else $mess =  'er_kod';
    } else $mess =  'er_pole';
    $main->addField('message',$mess);

 };
 ///Конец Отправка заявки

 addSprav($main,'zakupki_zak',$_GET['zaktype_id'],'zak_list');
 addSprav($main,'zakupki_types',$_GET['type_id'],'type_list');
 addSprav($main,'region',$_GET['region_id'],'region_list');
 //$where = 'z.id_city='.$_COOKIE['id_city'].' and zt.id_zakaz = z.id '.$razdel;


 if ($_GET['zakaz_num'] >0 )  $where .= " and number like '%$_GET[zakaz_num]%'";
 if ($_GET['type_id'] >0 )  $where .= " and type = '$_GET[type_id]'";
 if ($_GET['region_id'] >0 )  $where .= " and region = '$_GET[region_id]'";
 if ($_GET['zaktype_id'] >0 )  $where .= " and zakon_type = '$_GET[zaktype_id]'";
  */
  addSprav_sql($main,'positions','placingWay_name',$_GET['placingWay_name'],'placingWay_name');

  //addSprav($main,'cnt_pages',$_GET['cnt_on_page'],'cnt_on_page');
  addSprav_sql($main,'cnt_pages','name',$_GET['cnt_on_page'],'cnt_on_page','id');
//   function addSprav_sql(&$main,$table,$field,$selected,$sub_name) {

 $r1 = new Select($db,'SHOW COLUMNS FROM plan');
 while( $r1->next_row())   $fields[] = $r1->result('Field');

 $r1 = new Select($db,'SHOW COLUMNS FROM positions');
 while( $r1->next_row())   $fields[] = $r1->result('Field');

 $r1 = new Select($db,'SHOW COLUMNS FROM products');
 while( $r1->next_row())   $fields[] = $r1->result('Field');

 $r1 = new Select($db,'SHOW COLUMNS FROM grbs');
 while( $r1->next_row())   $fields[] = $r1->result('Field');

  $query = "select *  from positions pos,plan pl, grbs g where g.spz=pl.regNum and pos.plan_id=pl.plan_id and ";
 // $itog_query = "select sum(pos.contractMaxPrice) as sum  from positions pos,plan pl, grbs g where g.spz=pl.regNum and pos.plan_id=pl.plan_id and ";
  //фильтры
  $spiski =  array('id_grbs','placingWay_name','okpd_code','org');
  foreach ($_GET as $key =>$val) {
		 if ( in_array($key,$fields) && strlen($val) >0 ) {
             if (in_array($key,$spiski) && (strlen($val) >0)) {

                //if ($key=='id_grbs') $where.= " and pl.id_grbs = $val";
                if ($key == 'okpd_code') {
                    $query="select *  from positions pos,plan pl, grbs g,products pr where pr.position_id=pos.id and g.spz=pl.regNum and pos.plan_id=pl.plan_id and ";
                    $where.= " and pr.okpd_code like '%$val%'";
                } else if (strlen($val) >0) $where.= " and $key = '$val'";

             } else {
		             $word=$val;
				    // $word= htmlspecialchars ( addslashes (urldecode($val)));
				    // $word=trim(preg_replace('/\s+/', ' ', $word));
				     $words= explode(' ', $word);
				     $wh ='';
			         foreach ( $words as $sw ) {
				            $wh .= "and $key LIKE '%$sw%'";
			         };

				     $where .= ' and ('.substr($wh, 3).')';
		     };
		 };
         if (strlen($key) >0 ) $main->addField($key,$val);
         $link="/word/".urlencode($word);
 };

//добавляем спровочник организаций
//  addSprav($main,'grbs_sprav',$_GET['id_grbs'],'grbs');
  addSprav_list($main,"select * from grbs_sprav order by name",'name',$_GET['grbs'],'grbs_list');
  if (isset($_GET['grbs'])) {

    foreach ($_GET['grbs'] as $val) {
        $wh.= ','.$val;
    };
    $wh = substr($wh,1);
    $sql = "select id,name_pbs as name from grbs where  id_grbs in ($wh)";
    addSprav_list($main,$sql,'name',$_GET['org'],'org_list') ;
 } else {
     $sql = "select id,name_pbs as name from grbs order by name_pbs limit 100";
     addSprav_list($main,$sql,'name',$_GET['org'],'org_list') ;
 };

 if (isset($_GET['org'])) {
    $wh = '';
    foreach ($_GET['org'] as $val) {
        $wh.= ','.$val;
    };
    $wh = substr($wh,1);
    $where .= " and g.id in ($wh)";
 }

 //echo $where;
 $link='';

 if (strlen($_GET['orderBy'])>0) {
    if ($_GET['orderBy'] == 'purchasePlacingTerm') { $order = " order by purchasePlacingTerm_year $_GET[orderType],purchasePlacingTerm_month $_GET[orderType]";
      } else if ($_GET['orderBy'] == 'contractExecutionTerm') { $order = " order by contractExecutionTerm_year $_GET[orderType],contractExecutionTerm_month $_GET[orderType]"; }
        else $order = " order by $_GET[orderBy] $_GET[orderType]";

   // echo $order;
    $link = "/orderBy/$_GET[orderBy]/orderType/$_GET[orderType]";
    if ($_GET['orderType'] == 'asc') {
        $main->addfield($_GET['orderBy'].'_arr','<img src="/i/arr_down.gif">');
       // $main->addfield($_GET['orderBy'].'_ord','desc');
    }  else {
        $main->addfield($_GET['orderBy'].'_arr','<img src="/i/arr_up.gif">');
       // $main->addfield($_GET['orderBy'].'_ord','asc');
    };
 }  else  $order = " ";

 //$query = "select *  from positions pos,plan pl,products pr, org o where o.spz=pl.regNum and pos.plan_id=pl.plan_id and pr.position_id=pos.id and $where $order";

 $query.= " $where $order";
 //echo $query;
 $itog_query = str_replace('*',' sum(pos.contractMaxPrice) as sum ',$query) ;
 if ((int)$_GET['cp']>0) $page_num=$_GET['cp']; else $page_num=0;

//echo $query;
 //кол-во записей на страницу
 if ($_GET['cnt_on_page']>0) $cnt_on_page = $_GET['cnt_on_page'];
    else if ($_GET['cnt_on_page']=='все') $cnt_on_page = 1000000;
      else $cnt_on_page = $GLOBALS[$modulName.'_fcount'];
 if (strpos($_SERVER['REQUEST_URI'],'print')) $cnt_on_page = 100000000000000000000;

 // echo $cnt_on_page;
 if ($_GET['cp'] > 0 ) $p = $_GET['cp'] ; else $p=0;
 if ($pg = Pagers::DA($db,'','', $cnt_on_page,$p,'/'.$site->pageid.$link,null,null,$query)) {


   $pg->addPAGER($main);
   $r = $pg->r;
   //echotree($main);

   //echo $sql_itog;
   $r_itog= new Select($db,$itog_query );
   if ( $r_itog->next_row())  $main->addField('itogo_contractMaxPrice',round($r_itog->result('sum'),2));


   $i=($page_num )*$cnt_on_page+1;

   $main->addField('cnt',$r->num_rows());
   if ($r->num_rows() >0) {
           $main->addField('tbl','');
		   $fields = array('contractSubjectName','contractMaxPrice','name','minRequirement','summax','placingWay_name','positionNumber','publishDate','versionNumber','customer_fullName','contractExecutionTerm','purchasePlacingTerm');
		   foreach ($fields as $key) {

				if ($_GET['orderBy']==$key && $_GET['orderType']=='asc')
		          $main->addfield($key.'_ord','desc');
		        else  $main->addfield($key.'_ord','asc');

		   };
   };

   ///дерево ОКПД

   $tree_FILENAME = $front_html_path.'form_tree.html';


  //вывод дерево КБК
  //$tree = new outTree($tree_FILENAME);
  //$sub = new outTree();
 // ShowTree($sub, 1);
  //$tree->addField('sub_tree',&$sub);
  //unset($sub);
   //echotree($sub);

  $main->addField('tree1',$sub);
  //$itog=0;
  while ($r->next_row()) {

        unset($sub);
        $sub = new outTree();
        $r->addFields($sub,$ar=array('id','contractMaxPrice','contractExecutionTerm_month','contractExecutionTerm_year','purchasePlacingTerm_year','purchasePlacingTerm_month','placingWay_name','positionNumber','publishDate','versionNumber','customer_fullName'));
        $sub->addfield('number',str_replace('№','',trim(($r->result('number')))));
        $sub->addfield('contractSubjectName',htmlspecialchars($r->result('contractSubjectName'),null, "windows-1251"));
        //$sub->addfield('name',str_replace('amp;','',str_replace('#','',str_replace('&','',htmlspecialchars($r->result('name'),null, "windows-1251")))));
       // $name= str_replace('#','',htmlspecialchars($r->result('name')));
       // echo str_replace('&','',htmlspecialchars($name));
        $sub->addfield('ppnum',$i);
        $sub->addfield('first_price',number_format((int)$r->result('first_price'), 2, ',', ' '));
        $sub->addfield('date_publ',to_date($r->result('date_publ')));
        $sub->addfield('srok_podachi',to_date($r->result('srok_podachi'),1));
        $sub->addfield('date_provedenia',to_date($r->result('date_provedenia'),1));

        $query = "select *  from products where position_id=".$r->result('id');
        $r_pr = new Select($db,$query);
        $r_pr->next_row();
        $r_pr->addFields($sub,$ar=array('minRequirement','name','summax'));
        while ($r_pr->next_row()) {

             unset($sub_pr);
             $sub_pr = new outTree();
             $r_pr->addFields($sub_pr,$ar=array('minRequirement','name','summax'));
             $sub->addField('sub_products',$sub_pr);

        };
        $sub->addField('cnt_prod',$r_pr->num_rows());
        $main->addField('sub',$sub);
        $i++;
        //$itog+=$r->result('contractMaxPrice');
   };
 //  $main->addField('itogo_contractMaxPrice',round($itog,2));
 } else  {

      $main->addField('no_sub','');
      $main->addField('cnt','0');
  };
  //echotree($main);
 $link =  $_SERVER['REQUEST_URI']."/print=1";
 $main->addField('print_link',$link);
  //$main->addField('site',&$sub);
 ?>

