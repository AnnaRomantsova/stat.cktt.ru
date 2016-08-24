<?php
/**
 *  Классы и функции по обработке записей и выводу сущностей в шаблоны.
 */

include ($inc_path.'/func.back.php');
include ($inc_path.'/myfunc.php');
include ($inc_path.'/classes/class.BFO_OP.php');
//include($inc_path.'/classes/class.BF.php');
include ($inc_path.'/img.php');

class B_articles_ {

 function redactValues(&$_this,&$values) {
      if(!empty($values['pass_text']))
              $values['pass'] = md5( $values['pass_text'] );
      else unset($values['pass']);
      if ($values['is_master'] >0) {
         $values['pre_master']=0;
      } else {
          $values['is_master']=0;
          $values['pre_master']=1;
      };
     // var_dump($values);
     // die;

      B_::redactValues($_this,$values);
 }

 function saveRecord(&$_this,&$values,$id) {
        //var_dump($values);
        BF_::saveRecord($_this,$values,$id);


       //  var_dump($values);
       // echo ($values['is_master']);
       // if (!$values['is_master']>0) $r1 = new Select($_this->db,"update users set is_master=0, pre_master=1 where id=$id");
       // die;
        //сохраняем разделы юзера

  }

  function deleteRecord(&$_this,$id) {


       //echo "update users set is_master = 0, pre_master=0, about='' ,tel='',adress='',link='',image1='',skipe='',price='',oplata_type='',dostavka=''  where id=$id";

       /*
       $back_gal = new BF($db,'photo','photo','galery',$arFiles);
                     $r1 = new Select(($_this->db,"select * from galery where id_user=$id");
       while ($r1->next_row()) {
            $back_gal->deleteRecord($r1->result('id'));
       };
       */
       //die;

       //BF_::deleteRecord($_this,$id);
 }


 function addIfcEditRecord(&$_this,&$main,$id) {
    $_FILENAME = BF_::addIfcEditRecord($_this,$main,$id);

    addSprav($main,'city',$main->id_city,'city');
  //  addSprav($main,'time',$main->time,'time');

   // addUserSprav($main,$id,'user_types');


   // echo $main->act_category;

    //$rs->unset_();
    //$main->addField('is_parent','1');
         //$main->addField('date',date('d.m.Y', $main->datetime));
        //addCalend($main,1);
     //   echotree($main);
    return $_FILENAME;
 }


 function addSub(&$_this,&$sub,&$r,$param) {
         B_::addSub($_this,$sub,$r,$param);


         $sub->addField('date',date('d.m.y', $r->result('date')));
         $sub->addField('date_visit',date('d.m.y', $r->result('date_visit')));
         $r->addFields($sub,$ar=array('fio','email'));

 }



  function &getParamMngr(&$_this) {
         $param = BP_::getParamMngr($_this);

         $where ='1=1';
         $order = ' order by fio ';
         if (isset($_GET['order']))
            $order = " order by $_GET[order]";


         //по статусу
         if (isset($_GET['pay_active']))
           if ($_GET['pay_active']>=0) $where .= ' and u.pay_active='.$_GET['pay_active'];


         //по id
         if ($_GET['id']>0) $where = ' and u.id='.$_GET['id'];
         //$param['order'] = 'id_street,number,fract';


            $param['query'] = "select u.* from users u where  $where $order";

        // echo $param['query'];
         //var_dump($_SERVER);
         //$param['order'] = 'fio desc';

         return $param;
 }

 // формирует списки для фильтра
 function addManager(&$_this,&$main) {
         //echo $_GET['id_city'];
         //города


          if ($_GET['id']>0) $main->addField('id',$_GET['id']);

          //сортировки
          $q='?';
          $arr = explode('&',$_SERVER['argv'][0]);
          //var_dump($arr);
          foreach ($arr as $arg) {
           if ($arg!=='') {
            $str = explode ('=',$arg);
              if ($str[0] !=='order')
               $q.="&$arg";
           };
          };
          if ($q!=='?')  $q.='&';



           $fields=array('fio','email','c.name','is_master','date','date_visit','pre_master');
           foreach ($fields as $val)  {
                $main->addField($val.'_sort',"<a href='$q"."order=$val'><image src='/_images/back/button_down.gif'></a>
                                              <a href='$q"."order=$val desc'><image src='/_images/back/button_up.gif'></a>");
           };
          // echotree($main);

        return B_::addManager($_this,$main);
 }

}

class B_articles extends BFO_OP {
 function addSub(&$sub,&$r,$param) {
           B_articles_::addSub($this,$sub,$r,$param);
 }
  /*
 function addIfcEditRecord(&$main,$id) {
         return B_articles_::addIfcEditRecord($this,$main,$id);
 }

 function redactValues(&$values) {
         B_articles_::redactValues($this,$values);
 }



  function &getParamMngr() {
          return B_articles_::getParamMngr($this);
 }

 function addManager(&$main) {
           return B_articles_::addManager($this,$main);
 }

  function deleteRecord($id) {
          return B_articles_::deleteRecord($this,$id);
 }

 function saveRecord(&$values,$id) {
         B_articles_::saveRecord($this,$values,$id);
 }
 */
}

?>
