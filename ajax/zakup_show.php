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
include($inc_path.'/myfunc.php');
$shablon = 'front/zakupki/show_one.html';

$main = new outTree();

 $id=$_POST['id'];
if ( $id >0 )
   {
       $where =  " pos.id=$id";
      // echo "select *  from positions pos,plan pl,products pr, grbs g where  g.spz=pl.regNum and pos.plan_id=pl.plan_id and pr.position_id=pos.id and $where ";
       $r = new Select($db,"select pos.id as pos_id, pos.*,pl.*,g.*  from positions pos,plan pl, grbs g where  g.spz=pl.regNum and pos.plan_id=pl.plan_id and  $where ");
       if ( $r->next_row() ) {
              //$r->addFields($main,$ar=array('id','customer_fullName','inn','responsible_lastName','responsible_firstName','responsible_middleName','responsible_fax'));

		      $r1 = new Select($db,'SHOW COLUMNS FROM positions ');
              while( $r1->next_row())  {
                 if ($r1->result('Field')=='positionPublishDate') $main->addField($r1->result('Field'),make_date_zak($r->result($r1->result('Field'))));
				 else $main->addField($r1->result('Field'),$r->result($r1->result('Field')));

		      };

		      $r1 = new Select($db,'SHOW COLUMNS FROM plan');
              while( $r1->next_row())  {
			     if ($r1->result('Field')=='confirmDate') $main->addField($r1->result('Field'),make_date_zak($r->result($r1->result('Field')))); 
				   elseif ($r1->result('Field')=='publishDate') $main->addField($r1->result('Field'),make_date_zak($r->result($r1->result('Field')))); 
                     else $main->addField($r1->result('Field'),$r->result($r1->result('Field')));

		      };

              $r1 = new Select($db,'SHOW COLUMNS FROM grbs');
              while( $r1->next_row())  {
                 $main->addField($r1->result('Field'),$r->result($r1->result('Field')));

		      };
		      $r2 = new Select($db,"select *  from products where position_id= ".$r->result('pos_id'));
              while ( $r2->next_row() ) {
                       unset($sub);
                       $sub = new outTree();
                        $r1 = new Select($db,'SHOW COLUMNS FROM products ');
		                while( $r1->next_row())  {
		                  $sub->addField($r1->result('Field'),$r2->result($r1->result('Field')));
				        };
				       $main->addField('prod',$sub);

              };
              $r1 = new Select($db,'select * FROM pos_preferenses pp, preferenses p where 
                                     pp.position_id = '.$r->result('pos_id').' and pp.id_preferenses = p.id' );
              while( $r1->next_row())  {
                         unset($sub);
                        $sub = new outTree();
                        $r1->addFields($sub,$ar=array('code','name'));
				        $main->addField('pref',$sub);

		      };
		      $r1 = new Select($db,'select * FROM requirement r,pos_requirement rr where rr.position_id ='.$r->result('pos_id').' 
                                  and r.id= rr.id_requirement');
              while( $r1->next_row())  {
                        unset($sub);
                        $sub = new outTree();
                        $r1->addFields($sub,$ar=array('code','name'));
				        $main->addField('req',$sub);

		      };
		     /*  $main->addfield('date_publ',to_date($r->result('date_publ')));
		        $main->addfield('srok_podachi',to_date($r->result('srok_podachi'),1));
		        $main->addfield('date_provedenia',to_date($r->result('date_provedenia'),1));
               // $main->addfield('first_price',number_format($r->result('first_price'), 2, ',', ' '));
		       if (strlen($r->result('summ_zayavka'))>0) $main->addfield('summ_zayavka',$r->result('summ_zayavka'));
		           else  $main->addfield('summ_zayavka','Нет данных');
		        if (strlen($r->result('summ_contract'))>0) $main->addfield('summ_contract',$r->result('summ_contract'));
		           else  $main->addfield('summ_contract','Нет данных');

		        $r1 = new Select($db,'select * from zakupki_zak where id="'.$r->result('zakon_type').'"');
		        $r1->next_row();
		        $main->addfield('zakon_type',$r1->result('name'));

				$r1 = new Select($db,'select * from zakupki_types where id="'.$r->result('type').'"');
		        $r1->next_row();
		        $main->addfield('type',$r1->result('name'));

		        $r1 = new Select($db,'select * from region where id="'.$r->result('region').'"');
		        $r1->next_row();
		        $main->addfield('region',$r1->result('name'));

		        $r1 = new Select($db,'select * from zakupki_okato where id="'.$r->result('okato').'"');
		        $r1->next_row(); */
		        //$main->addfield('okato',$r1->result('name'));

      };
     //echo "65645";
      out::_echo($main,$shablon);
  }





?>