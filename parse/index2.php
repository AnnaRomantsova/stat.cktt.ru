<?php

   ini_set("max_execution_time", "1000000000");
   ini_set('display_errors',1);
   error_reporting(E_ALL);
   include "db_connect.php";

   mysql_query ("SET CHARACTER SET 'cp1251'");

   $start = microtime(true);

   function emptyDir($folder){
       $fp=opendir($folder);
	   while ( $file = readdir($fp)){
			//echo $file;
			unlink($folder.$file);

	   };
       closedir($fp);
   }

   function make_okpd_tree(){
      $row = mysql_query("SELECT distinct okpd_code FROM products order by LENGTH(okpd_code),okpd_code ");
       if ($r1=mysql_fetch_array($row, MYSQL_ASSOC))  {
           $id_grbs = $r1['id_grbs'];
           $plan_info['id_grbs'] = $id_grbs;
       } else $id_grbs=0;
   }

   //удаляет все из $folder, чтение и разархивиронание файлов из $link, складывает в папку $folder
   function read_dir($link,$folder) {
      emptyDir('zakupki/');
      emptyDir('zak/');
      // die;
      $fp=opendir($link);
        while ( $file = readdir($fp)){
			 copy ($link.$file,'zakupki/'.$file);
	   }
	   closedir($fp);

       $fp=opendir('zakupki/');
	   while ( $file = readdir($fp)){
			if (strpos($file,'zip')>0 ) {
			      echo  'zakupki/'.$file."<br>";			      $zip = new ZipArchive;
				  $res = $zip->open('zakupki/'.$file);
				  if ($res === TRUE) {
					  echo $zip->extractTo('zak');
					  $zip->close();
					  echo 'ok';
				  } else  echo 'failed'.$res;
				  //die;
		    };
	   }
	   closedir($fp);

   }

  //открывает файл и возвращает строку xml
   function open_page($link){
   	      $text=file_get_contents($link);
          $text=str_replace('oos:','',$text);
          return $text;
   };

//удаляет все
   function empty_plan(){
        $sql = "delete from plan";  mysql_query($sql);
        $sql = "delete from products";  mysql_query($sql);
        $sql = "delete from positions";  mysql_query($sql);
        $sql = "delete from okpd";  mysql_query($sql);
   }

   //удаляет 1 план
   function empty_one_plan($number){
       $sql = "select * from plan where planNumber='$number'"; // echo $sql."<br>";
       $r1= mysql_query($sql);
       if  ($row=mysql_fetch_array($r1, MYSQL_ASSOC))  $id = $row['plan_id'];
       //echo "id=".$id;
       if ($id>0) {
		        $sql = "select * from positions where plan_id=$id";
		        $r2= mysql_query($sql);
		        while ($row2=mysql_fetch_array($r2, MYSQL_ASSOC))  {
		           $id_pos = $row2['id'];
		           $sql = "delete from products where position_id=$id_pos";  mysql_query($sql);
		          // echo $sql."<br>";
		       }
		        $sql = "delete from positions where plan_id=$id";  mysql_query($sql); //   echo $sql."<br>";
		        $sql = "delete from plan where plan_id = $id";  mysql_query($sql);    //   echo $sql."<br>";
       };

   }

  //добавление записей в таблицу
   function add($table,$fields){
   	     $f = $v = "";
   	     foreach ($fields as $key =>$val) {   	     	$f.="$key,";
   	     	$v.="'$val',";   	     };
         $f=substr($f,0,-1);
         $v=substr($v,0,-1);
   	     $sql = "insert into $table ($f) values ($v)";
   	     $sql = iconv('utf-8','windows-1251',$sql);
   	  //   echo $sql."<br>";
   	   //  mysql_query($sql);  die;
   	     if ($res = mysql_query($sql))
   	         return mysql_insert_id();
   	        else return false;
   };

   //парсинг КАНСЕЛОВ
   function parse_calcel($txt) {      // echo $txt;
	   $xml = simplexml_load_string($txt);

	   //план
	   $plan_info['plan_id']      = $xml->tenderPlanCancel->planNumber;
	   echo "PLAN=".$plan_info['plan_id']."<br>" ;
	   if ($plan_info['plan_id'] >0 ) empty_one_plan($plan_info['plan_id']);


   };

   //парсинг
   function parse($txt) {      // echo $txt;
	   $xml = simplexml_load_string($txt);

	   //план
	   $plan_info['plan_id']      = $xml->tenderPlan->commonInfo->id;
	   $plan_info['planNumber'] = $xml->tenderPlan->commonInfo->planNumber;
	   $plan_info['versionNumber'] = $xml->tenderPlan->commonInfo->versionNumber;

        $nado = false;
	   //проверка на версию, если уже была более ранняя версия, то удаляем ту что была
       $row = mysql_query("SELECT versionNumber,planNumber FROM plan WHERE planNumber = '$plan_info[planNumber]'");
       if ($r1=mysql_fetch_array($row, MYSQL_ASSOC))  {
           if ($r1['versionNumber'] < $plan_info['versionNumber']) {
               empty_one_plan($r1['planNumber']);
               $nado=true;
           } $plan_info['id_grbs'] = $id_grbs;
       } else $nado=true;

       if ($nado==false) return;

	   $plan_info['year'] = $xml->tenderPlan->commonInfo->year;
	   //Владелец Код ПБС
	   $plan_info['regNum'] = $xml->tenderPlan->commonInfo->owner->regNum;
       $row = mysql_query("SELECT * FROM grbs WHERE spz = '$plan_info[regNum]'");
       if ($r1=mysql_fetch_array($row, MYSQL_ASSOC))  {
           $id_grbs = $r1['id_grbs'];
           $plan_info['id_grbs'] = $id_grbs;
       } else $id_grbs=0;
       if ($id_grbs>0) {

					   //echo  $plan_info['regNum']."<br>";
					   $plan_info['fullName'] =$xml->tenderPlan->commonInfo->owner->fullName;
					   //инфа о плане
					   $plan_info['createDate'] = $xml->tenderPlan->commonInfo->createDate;
					   $plan_info['confirmDate'] = $xml->tenderPlan->commonInfo->confirmDate;
					   $plan_info['publishDate'] = $xml->tenderPlan->commonInfo->publishDate;
					   //инфа о продавце
					   $plan_info['customer_regNum'] = $xml->tenderPlan->customerInfo->customer->regNum;
					   $plan_info['customer_fullName'] = $xml->tenderPlan->customerInfo->customer->fullName;
					   //OKTMO
					   $plan_info['oktmo_code'] = $xml->tenderPlan->customerInfo->OKTMO->code;
					   $plan_info['oktmo_name'] = $xml->tenderPlan->customerInfo->OKTMO->name;
					   //responsibleContactInfo
					   $plan_info['responsible_lastName'] = $xml->tenderPlan->responsibleContactInfo->lastName;
					   $plan_info['responsible_firstName'] = $xml->tenderPlan->responsibleContactInfo->firstName;
					   $plan_info['responsible_middleName'] = $xml->tenderPlan->responsibleContactInfo->middleName;
					   $plan_info['responsible_fax'] = $xml->tenderPlan->responsibleContactInfo->fax;
					   $plan_info['responsible_email'] = $xml->tenderPlan->responsibleContactInfo->email;


					   add('plan',$plan_info);
					  // var_dump($plan_info);
					   foreach ($xml->tenderPlan->providedPurchases->positions->position as $val) {
					   	   $position['plan_id']              = $plan_info['plan_id'];
					   	   $position['positionNumber']       = $val->commonInfo->positionNumber;
					   	   if ($val->commonInfo->amountKBKsYears !=='') {
					         $position['kbk_code']             = $val->commonInfo->amountKBKsYears->KBK->code;
					         $position['year']                 = $val->commonInfo->amountKBKsYears->KBK->yearsList->year;
					         $position['yearAmount']           = $val->commonInfo->amountKBKsYears->KBK->yearsList->yearAmount;
					       };
					       if ($val->commonInfo->amountKOSGUsYears !=='') {
					         $position['kbk_code']             = $val->commonInfo->amountKOSGUsYears->OKVEDS->OKVED->code;
					         $position['year']                 = $val->commonInfo->amountKOSGUsYears->KBK->yearsList->year;
					         $position['yearAmount']           = $val->commonInfo->amountKOSGUsYears->KBK->yearsList->yearAmount;
					       };
					       $position['OKVED_code']           = $val->commonInfo->OKVEDs->OKVED->code;
					       $position['OKVED_name']           = $val->commonInfo->OKVEDs->OKVED->name;

					       $position['contractSubjectName']   = $val->commonInfo->contractSubjectName;
					       $position['contractMaxPrice']      = $val->commonInfo->contractMaxPrice;
					       $position['payments']              = $val->commonInfo->payments;
					       $position['contractCurrency_code'] = $val->commonInfo->contractCurrency->code;
					       $position['contractCurrency_name'] = $val->commonInfo->contractCurrency->name;
					       $position['placingWay_name']       = $val->commonInfo->placingWay->name;

					       $position['positionPublishDate']   = $val->commonInfo->positionPublishDate;
					       $position['noPublicDiscussion']    = $val->commonInfo->noPublicDiscussion;
					       $position['purchasePlacingTerm_month']     = $val->purchaseConditions->purchaseGraph->purchasePlacingTerm->month;
					       $position['purchasePlacingTerm_year']      = $val->purchaseConditions->purchaseGraph->purchasePlacingTerm->year;
					       $position['contractExecutionTerm_month']   = $val->purchaseConditions->purchaseGraph->contractExecutionTerm->month;
					       $position['contractExecutionTerm_year']    = $val->purchaseConditions->purchaseGraph->contractExecutionTerm->year;
				           $position['periodicity']                   = $val->purchaseConditions->purchaseGraph->periodicity;



					       $pos_id = add('positions',$position);
                           //echo "pos_id=$pos_id<br>";
					       foreach ($val->products->product as $products) {
					       	 $prod['position_id']    = $pos_id;
					         $prod['okpd_code']      = $products->OKPD->code;
					         $prod['okpd_name']      = $products->OKPD->name;
					         $prod['name']           = $products->name;
					        // echo "prod=$pos_id<br>";
					         $prod['minRequirement'] = $products->minRequirement;
					         $prod['okei_code']      = $products->OKEI->code;
					         $prod['okei_name']      = $products->OKEI->name;
					         $prod['summax']         = $products->sumMax;
					         $prod['price']          = $products->price;
					         $prod['quantity']       = $products->quantity;
					         $prod['quantityCurrentYear'] = $products->quantityCurrentYear;
					         add('products',$prod);
					       };


	   	  // die;
	   	               };
	   };

   };

   function parse_calcel_dir($link){

       $fp1=opendir($link);
	   while ( $file = readdir($fp1)){

          //echo strpos($file,'Unstructured');
	      if (strpos($file,'tenderPlanCancel_') === 0 ) {
	          // echo $file."<br>";
	           $text = open_page($link.'/'.$file);
	   	     //echo $text;
	   	       parse_calcel($text);

	   	  };

	   };
	   closedir($fp1);

   };

   function parse_dir($link){

       $fp1=opendir($link);
	   while ( $file = readdir($fp1)){

          //echo strpos($file,'Unstructured');
	      if (strpos($file,'tenderPlan_') === 0 ) {
	           echo $file."<br>";
	           $text = open_page($link.'/'.$file);
	   	     //echo $text;
	   	     parse($text);

	   	  };

	   };
	   closedir($fp1);

   };

   //очищаем все
 // empty_plan();

   $link = "ftp://free:free@ftp.zakupki.gov.ru/fcs_regions/Rostovskaja_obl/plangraphs/currMonth/";  //$link=  'zakupki/';
   //переносим из link файлы в свою папку
   read_dir($link,'zak/');
   //парсинг документов из своей папки
   parse_dir('zak');
   //парсинг КАНСЕЛОВ
   parse_calcel_dir('zak');

    //
    $end = microtime(true);
    $time=$end-$start;
    $min=floor($time/60);
    $seconds= $time % 60;
    echo "Work time for region: $min min $seconds sec <br>";
   // echo "Всего пройдено закупок: ".($new_cnt+$updated_cnt+$old_cnt);


///формирование дерева ОКДП




?>
