<?php
 //список всех мастеров
 include('config.php');


 unset($main);
 $FILENAME = $front_html_path.'front.html';
 $p1_FILENAME =  $front_html_path.'p1.html';
 $p2_FILENAME =  $front_html_path.'p2.html';
 $p3_FILENAME =  $front_html_path.'p3.html';
 $p4_FILENAME =  $front_html_path.'p4.html';
 $p5_FILENAME =  $front_html_path.'p5.html';

 //$main = &addInCurrentSection($FILENAME);
   if (isset($_SESSION['user'])) $user_id=$_SESSION['user'];  else $user_id=0;
 if ($user_id==0) {header('Location: /auth'); die;};
 include($inc_path.'/myfunc.php');
  $main = new outTree($FILENAME);

 // var_dump( $_GET);
 if (strpos($_SERVER['REQUEST_URI'],'print')) {
	
	If ($_GET['tab']>0) $print_FILENAME = "$front_html_path"."p".$_GET['tab'].".html"; //echo $print_FILENAME;
	$main = &addInCurrentSection($print_FILENAME);
 }  else $main = &addInCurrentSection($FILENAME);  
  
  //‘ильтры!!!
  //добавл€ем спровочник организаций
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
 // конец ‘ильтры!!!

 //условие после фильтров
 if (isset($_GET['org'])) {
                $wh = '';
		        foreach ($_GET['org'] as $val) {
		              $wh.= ','.$val;
		        };
		        $wh = substr($wh,1);
		        $where .= " and g.id in ($wh)";
 };
 if ($_GET['year']>0) {$year = $_GET['year']; $where_year = " and purchasePlacingTerm_year='$year'"; $where_year1 =" and year = '$year'";}
     else {$year = date("Y",time()); $where_year = " and purchasePlacingTerm_year='$year'"; };
// $where .= " and year='$year'";
 $main->addField('year_n',$year);
 $main->addField('tab',$_GET['tab']);

 //условие после фильтров
 $r=new Select($db,"select sum(contractMaxPrice) as summ from positions ");
 $r->next_row();  $all= $r->result('summ')/1000;

 $r=new Select($db," select gs.name,(select sum(sumPaymentsTotal) from plan p where  p.id_grbs=gs.id $where_year1)/1000 as summ 
                            from grbs_sprav gs order by summ desc ");

$i=1;
$color=  array('#FF5A5E','#46BFBD','#FDB45C','#949FB1','#4D5360','#791101','#5F6408','#E2EC1C','#8A7E80','#6060FF','#FF6063','#FFC760','#AC8080','#008000','#56F9B2','#F956B2','#800080','#B2F956');
  while ($r->next_row()) {
    if ($r->result('summ') >0) {
          $sub = new outTree();
          $r->addFields($sub,$ar=array('plan_id','name'));
          $sub->addField('summ',round($r->result('summ'),2));

          $sub->addField('color',$color[$i]);
          $sub->addField('i','√–Ѕ— '.$i);
          $main->addField('d1',$sub);
          unset($sub);
          $i++;
    };
  };


  //2 график

 $r=new Select($db,"select  (select name from grbs_sprav g where g.id=p.id_grbs ) as name, id_grbs as id,count(*) as cnt from plan p where 1=1 $where_year1
                        group by id_grbs order by cnt desc");
 $i=1;
 $max=0;
  while ($r->next_row()) {
   if ($r->result('cnt') >0) {
          $sub = new outTree();
          $r->addFields($sub,$ar=array('name'));
          $sub->addField('i','√–Ѕ— '.$i);
          if (round($r->result('cnt'),2) >$max) $max=round($r->result('cnt'),2);
          $sub->addField('cnt',$r->result('cnt'));

        
          $r1=new Select($db,"select count(*) as cnt2 from grbs where id_grbs=".$r->result('id') );
          $r1->next_row();  $cnt_real= $r1->result('cnt2');
      
          $sub->addField('cnt2',$cnt_real);

          if ($i!==$r->num_rows()) $sub->addField('zpt',',');
          $main->addField('d2',$sub);

          unset($sub);
          $i++;
    };
  };
  $max = ceil($max);
  $step=floor($max/100)*10;
  $main->addField('step2',10);
  //echotree($main->d2);

  //3 график
  $r=new Select($db," select okpd_name as name,okpd_code as code, sum(price)/1000 as cnt from products pr, positions p,grbs g where pr.position_id=p.id and g.id=p.id_pbs $where $where_year group by okpd_name order by cnt desc limit 10");
 // echo " select okpd_name as name,okpd_code as code, sum(price)/1000 as cnt from products pr, positions p,grbs g where pr.position_id=p.id and g.id=p.id_pbs $where $where_year group by okpd_name order by cnt desc limit 5";

$i=1;
  while ($r->next_row()) {
          $sub = new outTree();
          $r->addFields($sub,$ar=array('name','code'));
       
          $sub->addField('cnt',round($r->result('cnt'),2));
          $sub->addField('i',$i);
          $main->addField('d3',$sub);
           $sub->addField('color',$color[$i]);
          unset($sub);
          $i++;
  };


 // $year = date('Y',time());
  //echo $year;
 // $i=1;
 $max=0;

  $mon = array("€нварь","февраль","март","апрель","май","июнь","июль","август","сент€брь","окт€брь","но€брь","декабрь");
  for ($i=1; $i<=12;$i++){
         // echo "select (sum(contractMaxPrice))/1000 as summ from positions p,grbs g where p.id_pbs=g.id and purchasePlacingTerm_year='$year' and purchasePlacingTerm_month='$i' $where<br>";
          $r=new Select($db,"select (sum(contractMaxPrice))/1000 as summ from positions p,grbs g where p.id_pbs=g.id and purchasePlacingTerm_year='$year' and purchasePlacingTerm_month='$i' $where");
          //echo "select (sum(contractMaxPrice))/1000 as summ from positions where purchasePlacingTerm_year='$year' and purchasePlacingTerm_month='$i'<br>";
          $r->next_row();
          $sub = new outTree();
          $r->addFields($sub,$ar=array('name'));
         // $sub->addField('name','впрвапр апрпаро впрвпроа вопрвлопр лвоапр волпр #13#10 влаопр влопр олывапр влыоар ');
          $sub->addField('summ',round($r->result('summ'),2));
        //  $k=$i-1;
          $sub->addField('mon',$mon[$i-1]);
         // echo $mon[$i];
          $sub->addField('i',$i);
          if (round($r->result('summ'),2) >$max) $max=round($r->result('summ'),2);
          $main->addField('d4',$sub);
          $sub->addField('color',$color[$i]);

          unset($sub);
         // $i++;
  };
  $max = ceil($max);
  $step=floor($max/100)*10;
  $main->addField('step4',$step);

  $r=new Select($db,"select pw.name as name,(sum(contractMaxPrice) )/1000 as summ from positions p,placingWay pw,grbs g 
              where p.id_pbs=g.id and p.placingWay=pw.id $where $where_year group by placingWay order by summ desc");

$i=1;
  while ($r->next_row()) {
          $sub = new outTree();
          $r->addFields($sub,$ar=array('name','cnt'));
          $sub->addField('summ',round($r->result('summ'),2));
          $sub->addField('i',$i);
          $main->addField('d5',$sub);
           $sub->addField('color',$color[$i]);
            if ($i!==$r->num_rows()) $sub->addField('zpt',',');
          unset($sub);
          $i++;
  };
  //echo strpos($_SERVER['REQUEST_URI'],'?');
  if (strpos($_SERVER['REQUEST_URI'],'?') >0) $link =  $_SERVER['REQUEST_URI']."&print=1"; else $link =  $_SERVER['REQUEST_URI']."?print=1";
 $main->addField('print_link',$link);
// echotree($main);
  if (isset($main)) {
   //  $site->addField($GLOBALS['currentSection'],$main);
     unset($main);
  }

 ?>

