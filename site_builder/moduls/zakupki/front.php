<?php
 //список всех мастеров
 include('config.php');
 include($inc_path.'/service/class.pager.php');
// include('functions.php');
 unset($main);
 $FILENAME = $front_html_path.'front.html';
 $print_FILENAME = $front_html_path.'front_print.html';
// var_dump($_GET);

  if (isset($_SESSION['user'])) $user_id=$_SESSION['user'];  else $user_id=0;
  if ($user_id==0) {header('Location: /auth'); die;};


 include($inc_path.'/myfunc.php');
 if (strpos($_SERVER['REQUEST_URI'],'print'))  {
	header('Content-Type: application/vnd.ms-excel; charset=utf-8');
	header("Content-Disposition: attachment;filename=".date("d-m-Y")."-export.xls");
	header("Content-Transfer-Encoding: binary ");
	$main = &addInCurrentSection($print_FILENAME);
 } else $main = &addInCurrentSection($FILENAME);

  $where = '1 = 1';

  //addSprav($main,'placingWay',$_GET['placingWay'],'placingWay_name');
  addSprav($main,'changeReason',$_GET['changeReason'],'changeReason');
  addSprav($main,'placingWay',$_GET['placingWay'],'placingWay_name');
  addSprav($main,'preferenses',$_GET['preferenses'],'preferenses');
  addSprav($main,'requirement',$_GET['requirement'],'requirement');  
  addSprav_sql($main,'cnt_pages','name',$_GET['cnt_on_page'],'cnt_on_page','id');


 $r1 = new Select($db,'SHOW COLUMNS FROM plan');
 while( $r1->next_row())   $fields[] = $r1->result('Field');

 $r1 = new Select($db,'SHOW COLUMNS FROM positions');
 while( $r1->next_row())   $fields[] = $r1->result('Field');

 $r1 = new Select($db,'SHOW COLUMNS FROM products');
 while( $r1->next_row())   $fields[] = $r1->result('Field');

 $r1 = new Select($db,'SHOW COLUMNS FROM grbs');
 while( $r1->next_row())   $fields[] = $r1->result('Field');

 $fields[] = 'no_ident';
 $fields[] = 'date1';
 $fields[] = 'date2';
 $fields[] = 'date_zak1';
 $fields[] = 'date_zak2';
 $fields[] = 'date_cont1';
 $fields[] = 'date_cont2';
 $fields[] = 'predmet_tru';
 $fields[] = 'requirement';
 $fields[] = 'preferenses'; 
 $fields[] = 'contractMaxPrice1';
 $fields[] = 'contractMaxPrice2'; 
  
 $from = "  positions pos, plan pl, grbs g   ";
 $where = " where g.spz=pl.regNum and pos.plan_id=pl.plan_id  ";
 $what = " * ";
 $groupby  = "";



//условия where после применения фильтров   
    if ($_GET['no_ident'] || $_GET['okpd_code'] || $_GET['predmet_tru']) {
         $from = "  positions pos,plan pl,products pr, grbs g";
         $what = " pr.position_id,  sum(pr.summax) as summ,pos.contractMaxPrice,pos.*,pl.*,g.*,pos.contractMaxPrice ";
         $where =  " where pos.plan_id=pl.plan_id and pos.id=pr.position_id and g.spz=pl.regNum  ";
         $groupby = " group by pr.position_id ";
         if ($_GET['no_ident']) $groupby .= "having summ<>pos.contractMaxPrice";
         if ($_GET['okpd_code'])  $where .= " and pr.okpd_code = '$_GET[okpd_code]'"; 
         if ($_GET['predmet_tru'])  $where .= " and REPLACE(pr.name, '\"', '') like '%$_GET[predmet_tru]%'"; 
    };
    foreach ($_GET as $key =>$val) {
		 if ( in_array($key,$fields) && strlen($val) >0 ) {

            switch ($key) {
                 case 'contractMaxPrice1': {
                         if (is_numeric($val)) $where.= " and contractMaxPrice >='$val' ";
		                     break; };
		             case 'contractMaxPrice2': {
                         if (is_numeric($val)) $where.= " and contractMaxPrice<='$val' ";
		                     break; };            
				         case 'date1': {
                         $d=make_date_to_zak($val,false);    // echo $d1;die;
		                     $where.= " and publishDate>='$d' ";
		                     break; };
		             case 'date2': {
                         $d=make_date_to_zak($val,true);    // echo $d1;die;
		                     $where.= " and publishDate>='$d' ";
		                     break; };
                 case 'date_zak1': {
                         $m= substr($val,0,2); $y= substr($val,3,4);                           
		                     $where.= " and (purchasePlacingTerm_year>'$y' or (purchasePlacingTerm_year='$y' and purchasePlacingTerm_month>='$m'))";
                       //  echo $where;
		                     break; };
		             case 'date_zak2': {
                         $m= substr($val,0,2); $y= substr($val,3,4);                           
		                     $where.= " and (purchasePlacingTerm_year<'$y' or (purchasePlacingTerm_year='$y' and purchasePlacingTerm_month<='$m'))";
		                     break; };
                 case 'date_cont1': {
                         $m= substr($val,0,2); $y= substr($val,3,4);                           
		                     $where.= " and (contractExecutionTerm_year>'$y' or (contractExecutionTerm_year='$y' and contractExecutionTerm_month>='$m'))";		                    
		                     break; };
		             case 'date_cont2': {
                         $m= substr($val,0,2); $y= substr($val,3,4);                           
		                     $where.= " and (contractExecutionTerm_year<'$y' or (contractExecutionTerm_year='$y' and contractExecutionTerm_month<='$m'))";	
		                     break; };                
					       case   'year': {  
					               $where.= " and pl.year like '%$val%' ";
		                     break; }	
                 case   'requirement': {  
                         $from .= " ,pos_requirement vr,requirement pref ";
					               $where.= " and vr.position_id = pos.id and vr.id_requirement=pref.id and pref.id= '$val' ";
		                     break; }	
                /* case   'okpd_code': { 
                         $what = " pos.*, distinct pos.position_id ";                        
                         $from .= " ,products pr ";
					               $where.= " and pr.position_id = pos.id and pr.okpd_code like %'$val'% ";
		                     break; }*/	        	
                 case   'preferenses': {  
                         $from .= " ,pos_preferenses vp,preferenses pref ";
					               $where.= " and vp.position_id = pos.id and vp.id_preferenses=pref.id and pref.id= '$val' "; 
		                     break; }	 
                 case   'prohibitions': {                        
					               $where.= " and prohibitions <> '' "; 
		                     break; }	
                 case   'advance': {                        
					               $where.= " and advance <> '' "; 
		                     break; }	        
                 case   'jointBiddingInfo': {                        
					               $where.= " and jointBiddingInfo <> '' ";  
		                     break; }	
                 case   'noPublicDiscussion': {                        
					               $where.= " and noPublicDiscussion <> 'false' ";  
		                     break; }		            
		             case   'placingWay_name':
		             case   'id_grbs':  {
		                     $where.= " and $key = '$val'";
		                     break; };
                 case   'predmet_tru': 		                          
		             case   'no_ident':
                 case   'okpd_code':  
                         {   break; };
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
   // echo $where;
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
  addSpravYear($main,$_GET['year'],'year');
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
      else $cnt_on_page = $GLOBALS[$modulName.'_fcount'];
 if (strpos($_SERVER['REQUEST_URI'],'print')) $cnt_on_page = 100000000000000000000;

 if ($_GET['cp'] > 0 ) $p = $_GET['cp'] ; else $p=0;
 if ($pg = Pagers::DA($db,'','', $cnt_on_page,$p,'/'.$site->pageid.$link,null,null,$query)) {


   $pg->addPAGER($main);
   $r = $pg->r;

   //echo $sql_itog;
 //ИТОГ
  if ($_GET['no_ident'] || $_GET['okpd_code']) $itog_query =  "select sum(contractMaxPrice) as sum from (select contractMaxPrice,sum(pr.summax) as summ from  $from $where $groupby ) t";
  else  $itog_query = "select sum(contractMaxPrice) as sum from $from $where $groupby $order";
  $r_itog= new Select($db,$itog_query );
  if ( $r_itog->next_row())  $main->addField('itogo_contractMaxPrice',round($r_itog->result('sum'),2));
 //конец ИТОГ

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
        $r->addFields($sub,$ar=array('id','contractMaxPrice','contractExecutionTerm_month','contractExecutionTerm_year','purchasePlacingTerm_year','purchasePlacingTerm_month','positionNumber','versionNumber','name_pbs','purchaseFinCondition_amount','contractFinCondition','advance'));
        $sub->addfield('number',str_replace('№','',trim(($r->result('number')))));
        $sub->addfield('contractSubjectName',htmlspecialchars($r->result('contractSubjectName'),null, "windows-1251"));
        //$sub->addfield('name',str_replace('amp;','',str_replace('#','',str_replace('&','',htmlspecialchars($r->result('name'),null, "windows-1251")))));
       // $name= str_replace('#','',htmlspecialchars($r->result('name')));
       // echo str_replace('&','',htmlspecialchars($name));
	      $sub->addfield('confirmDate',make_date_zak($r->result('confirmDate')));
        $sub->addfield('publishDate',make_date_zak($r->result('publishDate')));
        $sub->addfield('ppnum',$i);
        $sub->addfield('first_price',number_format((int)$r->result('first_price'), 2, ',', ' '));
        $sub->addfield('date_publ',to_date($r->result('date_publ')));
        $sub->addfield('srok_podachi',to_date($r->result('srok_podachi'),1));
        $sub->addfield('date_provedenia',to_date($r->result('date_provedenia'),1));
        
        $sub->addfield('placingWay_name',get_sprav_val(placingWay,'name',$r->result('placingWay')));         
        $sub->addfield('changeReason',get_sprav_val(changeReason,'name',$r->result('changeReason')));
        
        
        
/*
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

