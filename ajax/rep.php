<?

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
$shablon = 'front/zakupki/ajax_front.html';


function str_make($text)
{
  $text = str_replace("\r\n",'',$text);
  $text = str_replace("\n",'',$text);
  $text = str_replace('"','',$text);
  $text=trim($text);
  $text = preg_replace("/[\t\r\n]+/",' ',$text);
  return($text);
}

$main = new outTree();

    // and pl.plan_id='3484740'

 $query = "select *  from positions pos,plan pl, grbs g where g.spz=pl.regNum and pos.plan_id=pl.plan_id limit 3000";
 $r = new Select($db,$query);
 $i=1;
 while ($r->next_row()) {

        unset($sub);
        $sub = new outTree();
        $r->addFields($sub,$ar=array('id','contractMaxPrice','contractExecutionTerm_month','contractExecutionTerm_year','purchasePlacingTerm_year','purchasePlacingTerm_month','placingWay_name','positionNumber','publishDate','versionNumber'));
        $sub->addfield('number',str_replace('№','',trim(($r->result('number')))));
        //$sub->addfield('contractSubjectName',htmlspecialchars($r->result('contractSubjectName'),null, "windows-1251"));
        $sub->addfield('customer_fullName',str_make($r->result('customer_fullName')));
        $sub->addfield('contractSubjectName',str_make($r->result('contractSubjectName')));


        $sub->addfield('ppnum',$i);
        $sub->addfield('first_price',number_format((int)$r->result('first_price'), 2, ',', ' '));
        $sub->addfield('date_publ',to_date($r->result('date_publ')));
        $sub->addfield('srok_podachi',to_date($r->result('srok_podachi'),1));
        $sub->addfield('date_provedenia',to_date($r->result('date_provedenia'),1));

        $query = "select *  from products where position_id=".$r->result('id');
        $r_pr = new Select($db,$query);
       // $r_pr->next_row();
      //  $sub->addfield('name',str_make($r_pr->result('name')));
       // $sub->addfield('minRequirement',str_make($r_pr->result('minRequirement')));
       // $r_pr->addFields($sub,$ar=array('summax'));
        $prod="<table>";
        while ($r_pr->next_row()) {
             unset($sub_pr);
             $sub_pr = new outTree();
             $prod.="<tr>";
             $prod.="<td>".str_make($r_pr->result('name'))."</td><td>".$r_pr->result('summax')."</td><td>".str_make($r_pr->result('minRequirement'))."</td></tr>";
           /*  $r_pr->addFields($sub_pr,$ar=array('summax'));
             $sub_pr->addfield('name',str_make($r_pr->result('name')));
             $sub_pr->addfield('minRequirement',str_make($r_pr->result('minRequirement')));
             $sub->addField('sub_products',$sub_pr);
             $prod.="</tr>";
            */
        };
        $prod.="</table>";
        $sub->addField('products',$prod);

        if ($i <> $r->num_rows())   $sub->addField('zpt',',');

        $main->addField('sub',$sub);
        $i++;
        //echo $r->last();
   };

   out::_echo($main,$shablon);
?>