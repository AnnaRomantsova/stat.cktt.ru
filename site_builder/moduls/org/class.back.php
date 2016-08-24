<?php
/**
 *  ������ � ������� �� ��������� ������� � ������ ��������� � �������.
 */

include ($inc_path.'/func.back.php');
include ($inc_path.'/img.php');
include ($inc_path.'/classes/class.BF_P.php');
include ($inc_path.'/myfunc.php');

class B_news_ {
 function deleteRecord(&$_this,$id) {
       //������ �����, ������� ���, ������
       //echo $id;die;
       $rs = new Select($_this->db,"delete from reklama_types where id_rek=$id");
       $rs1 = new Select($_this->db,"delete from reklama_city where id_reklama=$id");

       B_::deleteRecord($_this,$id);
 }


 function saveRecord(&$_this,&$values,$id) {

        //$_FILES['image1'] =  $_FILES['image2'];
       // var_dump($values);
        $rs = new Select($_this->db,"delete from reklama_city where id_reklama=$id");
        foreach ($_POST as $key => $value) {

           //������
           if (substr($key,0,4)=='city') {
               $page_id = substr($key,4);
               if ($value=='on') $rs = new Select($_this->db,"insert into reklama_city(id_reklama,id_city) values($id,$page_id)");
           };
        };


        //��������� ������� �����
         $r1 = new Select($_this->db,"delete from reklama_types where id_rek=$id");
         foreach ( $_POST as $key => $value) {
        // echo $key;
              if (strpos($key,'h_razdel')>0) {
                 $idr = substr($key,9);
                 $r1 = new Select($_this->db,"insert into reklama_types(id_rek,id_type) values($id,$idr)");
              };
         };
        if (!$values['zakaz']>0) $values['zakaz']=0;
        if (!$values['master']>0) $values['master']=0;
        if (!$values['lenta']>0) $values['lenta']=0;
        BF_::saveRecord($_this,$values,$id);


 }

  function saveNewRecord(&$_this,&$values) {

        //$_FILES['image1'] =  $_FILES['image2'];
        //echo_tree($values);
        $id = BF_::saveNewRecord($_this,$values,$id);

        foreach ($_POST as $key => $value) {
           //��������
           if (strpos($key,'h_razdel')>0) {
               $page_id = substr($key,9);
               if ($value=='on') $rs = new Select($_this->db,"insert into reklama_types(id_rek,id_type) values($id,$page_id)");
           };
           //������
           if (substr($key,0,4)=='city') {
               $page_id = substr($key,4);
               if ($value=='on') $rs = new Select($_this->db,"insert into reklama_city(id_reklama,id_city) values($id,$page_id)");
           };
        };



        $r1 = new Select($_this->db,'select * from '.$this->table.' where id='.$id);
        if ($r1->next_row()) {
            image_resize_admin( $r1->result('image1'),230,400);
        };
 }

 function addIfcAddRecord(&$_this,&$main) {
        $_FILENAME = B_::addIfcAddRecord($_this,$main);
  /*      $rs = new Select($_this->db,"select c.* from city c order by name");
    while ($rs->next_row()) {
           unset($str_sub);
           $str_sub = new outTree();
           $rs->addFields($str_sub,$ar=array('id','name'));

           $main->addField('city_sub',$str_sub);
    };
    */
    addSprav($sub,'grbs_sprav',0,'grbs');
	//echotree($sub);

    addRekSprav($main,0,'user_types');
    return $_FILENAME;
 }

 function addIfcEditRecord(&$_this,&$main,$id) {
    $_FILENAME = BF_::addIfcEditRecord($_this,$main,$id);
    //echo $id_grbs;
   $rs = new Select($_this->db,"select * from grbs where id=$id");
   $rs->next_row();
   $id_sel=$rs->result('id_grbs');


   // addRekSprav($main,$id,'user_types');
    addSprav($main,'grbs_sprav',$id_sel,'grbs');
    return $_FILENAME;
 }

 function addSub(&$_this,&$sub,&$r,$param) {
         B_::addSub($_this,$sub,$r,$param);
         $r1 = new Select($_this->db,'SHOW COLUMNS FROM grbs');
         unset($sub->id);
         while( $r1->next_row())  {
                 $sub->addField($r1->result('Field'),$r->result($r1->result('Field')));

		 };
		 $rs = new Select($_this->db,"select * from grbs_sprav where id=".$r->result('id_grbs'));
         $rs->next_row();
         $sub->addField('grbs',$rs->result('name'));
        //var_dump($_POST);
        /* if ($r->result('image1')!=='') $r->addField($sub,'image1');
         $rs = new Select($_this->db,'select * from grbs where id='.$r->result('id_grbs'));
         //if ($rs->next_row())
         $rs->addFields($sub,$ar=array('name_grbs'));
        */
  }

 function &getParamMngr(&$_this) {
         $param = &BP_::getParamMngr($_this);
         $param['order'] = 'name_pbs';

        // $param['where'] = ' ntype=1';
         return $param;
 }


}

class B_news extends BF_P {
 function &getParamMngr() {
           return B_news_::getParamMngr($this);
 }
 function addSub(&$sub,&$r,$param) {
           B_news_::addSub($this,$sub,$r,$param);
 }

 //function redactValues(&$values) {
 //        B_news_::redactValues($this,$values);
// }
/*
 function deleteRecord($id) {
          return B_news_::deleteRecord($this,$id);
 }
 function saveRecord(&$values,$id) {
         B_news_::saveRecord($this,$values,$id);
 }

 function saveNewRecord(&$values) {
         B_news_::saveNewRecord($this,$values);
 }*/
 function addIfcAddRecord(&$main) {
         return B_news_::addIfcAddRecord($this,$main);
 }

 function addIfcEditRecord(&$main,$id) {
         return B_news_::addIfcEditRecord($this,$main,$id);
 }





}

?>
