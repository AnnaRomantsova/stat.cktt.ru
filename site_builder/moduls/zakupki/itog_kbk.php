<?php

 include('config.php');
 include($inc_path.'/service/class.pager.php');
 include('functions.php');
 unset($main);
 $FILENAME = $front_html_path.'itog_kbk.html';
 $print_FILENAME = $front_html_path.'front_print.html';
// var_dump($_GET);

  if (isset($_SESSION['user'])) $user_id=$_SESSION['user'];  else $user_id=0;
  if ($user_id==0) {header('Location: /error404'); die;};


 include($inc_path.'/myfunc.php');
 if (strpos($_SERVER['REQUEST_URI'],'print'))  $main = &addInCurrentSection($print_FILENAME);
   else $main = &addInCurrentSection($FILENAME);

  $where = '1 = 1';

  //addSprav_sql($main,'positions','placingWay_name',$_GET['placingWay_name'],'placingWay_name');
  addSpravYear($main,$_GET['year'],'year');
  //addSprav($main,'cnt_pages',$_GET['cnt_on_page'],'cnt_on_page');
  addSprav_sql($main,'cnt_pages','name',$_GET['cnt_on_page'],'cnt_on_page','id');
//   function addSprav_sql(&$main,$table,$field,$selected,$sub_name) {

 $r1 = new Select($db,'SHOW COLUMNS FROM plan');
 while( $r1->next_row())   $fields[] = $r1->result('Field');

 $r1 = new Select($db,'SHOW COLUMNS FROM grbs');
 while( $r1->next_row())   $fields[] = $r1->result('Field');

 $fields[] = 'no_ident';
 $fields[] = 'date1';
 $fields[] = 'date2';


 $from = " plan pl, grbs g   ";
 $where = " where g.spz=pl.regNum   ";
 $what = " * ";
 $groupby  = "";


//условия where после применения фильтров
    foreach ($_GET as $key =>$val) {
		 if ( in_array($key,$fields) && strlen($val) >0 ) {

            switch ($key) {
                     case 'date1': {
                             $d=make_date_to_zak($val,false);    // echo $d1;die;
		                     $where.= " and publishDate>='$d' ";
		                     break; };
		             case 'date2': {
                             $d=make_date_to_zak($val,true);    // echo $d1;die;
		                     $where.= " and publishDate>='$d' ";
		                     break; };
		             case 'okpd_code': {
		                     $where.= " and pr.okpd_code like '%$val%' ";//or pr.okpd_name like '%val%'
		                     break; };
		             case  'placingWay_name':
		             case  'id_grbs':  {
		                     $where.= " and $key = '$val'";
		                     break; };
		             case  'no_ident': {   break; };
		             default: {
				             $word=$val;
						     $words= explode(' ', $word);
						     $wh ='';
					         foreach ( $words as $sw ) {
						            $wh .= "and $key LIKE '%$sw%'";
					         };
						     $where .= ' and ('.substr($wh, 3).')';
				     };
		    };

            $main->addField($key,$val);
            $link="/word/".urlencode($word);
        };
    };
   if (isset($_GET['org'])) {
                $wh = '';
		        foreach ($_GET['org'] as $val) {
		              $wh.= ','.$val;
		        };
		        $wh = substr($wh,1);
		        $where .= " and g.id in ($wh)";
    };
//--Конец условия where после применения фильтров


//добавляем спровочник организаций
//  addSprav($main,'grbs_sprav',$_GET['id_grbs'],'grbs');
  addSprav_list($main,"select * from grbs_sprav order by name",'name',$_GET['grbs'],'grbs_list');
  if (isset($_GET['grbs'])) {
    $wh='';
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
 }

 //echo $where;
 $link='';

//сортировки
 if (strlen($_GET['orderBy'])>0) {
    if ($_GET['orderBy'] == 'purchasePlacingTerm') { $order = " order by purchasePlacingTerm_year $_GET[orderType],purchasePlacingTerm_month $_GET[orderType]";
      } else if ($_GET['orderBy'] == 'contractExecutionTerm') { $order = " order by contractExecutionTerm_year $_GET[orderType],contractExecutionTerm_month $_GET[orderType]"; }
        else $order = " order by $_GET[orderBy] $_GET[orderType]";

    $link = "/orderBy/$_GET[orderBy]/orderType/$_GET[orderType]";
    if ($_GET['orderType'] == 'asc') {
        $main->addfield($_GET['orderBy'].'_arr','<img src="/i/arr_down.gif">');
    }  else {
        $main->addfield($_GET['orderBy'].'_arr','<img src="/i/arr_up.gif">');
    };
 }  else  $order = " ";
//--сортировки


 $query= "select $what from $from $where $groupby $order";


 if ((int)$_GET['cp']>0) $page_num=$_GET['cp']; else $page_num=0;

//echo $query;

 //кол-во записей на страницу
 if ($_GET['cnt_on_page']>0) $cnt_on_page = $_GET['cnt_on_page'];
    else if ($_GET['cnt_on_page']=='все') $cnt_on_page = 1000000;
      else $cnt_on_page = 30;//$GLOBALS[$modulName.'_fcount'];
 if (strpos($_SERVER['REQUEST_URI'],'print')) $cnt_on_page = 100000000000000000000;

 if ($_GET['cp'] > 0 ) $p = $_GET['cp'] ; else $p=0;
 if ($pg = Pagers::DA($db,'','', $cnt_on_page,$p,'/'.$site->pageid.$link,null,null,$query)) {


   $pg->addPAGER($main);
   $r = $pg->r;

   //echo $sql_itog;
 /*//ИТОГ
  if ($_GET['no_ident'] || $_GET['okpd_code']) $itog_query =  "select sum(contractMaxPrice) as sum from (select contractMaxPrice,sum(pr.summax) as summ from  $from $where $groupby ) t";
  else  $itog_query = "select sum(contractMaxPrice) as sum from $from $where $groupby $order";
  $r_itog= new Select($db,$itog_query );
  if ( $r_itog->next_row())  $main->addField('itogo_contractMaxPrice',round($r_itog->result('sum'),2));
 *///конец ИТОГ

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


  $main->addField('tree1',$sub);
  //$itog=0;
  while ($r->next_row()) {

        unset($sub);
        $sub = new outTree();
        $r->addFields($sub,$ar=array('id','cnt_pos','planNumber','versionNumber','customer_fullName','sumPaymentsTotal','sumContractMaxPrice','sumPushaseSmallBusiness','sumPushaseSingleSupplier5','sumPushaseSingleSupplier4','sumPushaseRequest'));
        //$sub->addfield('number',str_replace('№','',trim(($r->result('number')))));
        $sub->addfield('confirmDate',make_date_zak($r->result('confirmDate')));
        $sub->addfield('publishDate',make_date_zak($r->result('publishDate')));
        $sub->addfield('ppnum',$i);
        /*$sub->addfield('first_price',number_format((int)$r->result('first_price'), 2, ',', ' '));
        $sub->addfield('date_publ',to_date($r->result('date_publ')));
        $sub->addfield('srok_podachi',to_date($r->result('srok_podachi'),1));
        $sub->addfield('date_provedenia',to_date($r->result('date_provedenia'),1));
         */

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

