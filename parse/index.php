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

   //������� ��� �� $folder, ������ � ���������������� ������ �� $link, ���������� � ����� $folder
   function read_dir($link,$folder) {
	 // $row = mysql_query("delete from log"); 
      emptyDir('zakupki/');
      emptyDir('zak/');
      // die;
      $fp=opendir($link);
      while ( $file = readdir($fp)){
		  
	   $sql = "select count(*) as cnt from log where file='$file'"; // echo $sql."<br>";
       $r1= mysql_query($sql);
       if  ($row=mysql_fetch_array($r1, MYSQL_ASSOC));
	     if ($row['cnt'] ==0) {
		   copy ($link.$file,'zakupki/'.$file);
		   $arch = 'zakupki/'.$file;
		   if (strpos($arch,'zip')>0 ) {
					  $zip = new ZipArchive;
					  $res = $zip->open($arch);
					  if ($res === TRUE) {
						  echo $zip->extractTo('zak');
						  $zip->close();            
						  parse_dir('zak');
						  //������� ��������
						  parse_calcel_dir('zak');
						  emptyDir('zak/');
						
					  } else  echo 'failed'.$res;				 
			};
			unlink('zakupki/'.$file);
			$row = mysql_query("insert into log(file) values ('$file')");
			// die;
		 };
	  }
	  closedir($fp);

   }

  //��������� ���� � ���������� ������ xml
   function open_page($link){
   	      $text=file_get_contents($link);
          $text=str_replace('oos:','',$text);
          return $text;
   };

//������� ���
   function empty_plan(){
        $sql = "delete from plan";  mysql_query($sql);
        $sql = "delete from products";  mysql_query($sql);
        $sql = "delete from positions";  mysql_query($sql);
        $sql = "delete from okpd";  mysql_query($sql);
        $sql = "delete from pos_preferenses";  mysql_query($sql);
        $sql = "delete from preferenses";  mysql_query($sql);
        $sql = "delete from requipment";  mysql_query($sql);
   }

   //������� 1 ����
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
		           $sql = "delete from pos_preferenses where position_id=$id_pos";  mysql_query($sql);
		           $sql = "delete from pos_requipment where position_id=$id_pos";  mysql_query($sql);
		          // echo $sql."<br>";
		       }
		        $sql = "delete from positions where plan_id=$id";  mysql_query($sql); //   echo $sql."<br>";
		        $sql = "delete from plan where plan_id = $id";  mysql_query($sql);    //   echo $sql."<br>";
       };

   }

  //���������� ������� � �������
   function add($table,$fields){
   	     $f = $v = "";
   	     foreach ($fields as $key =>$val) {   	     	$f.="$key,";
   	     	$v.="'$val',";   	     };
         $f=substr($f,0,-1);
         $v=substr($v,0,-1);
   	     $sql = "insert into $table ($f) values ($v)";
   	     $sql = iconv('utf-8','windows-1251',$sql);
   	    // echo $sql."<br>";

   	   //  mysql_query($sql);  die;
   	     if ($res = mysql_query($sql))
   	         return mysql_insert_id();
   	        else return false;
   };
   
   
   //���������� ������ � ���������� ��� ����� id ���� ����� ����
   function add_in_sprav($table,$field,$val){
      // echo "ggg=$val<br>";
         if (strlen($val)==0) return false;
   	     $sql = "select * from $table where $field = '$val'";  $sql = iconv('utf-8','windows-1251',$sql);
		     $r2= mysql_query($sql);
          //echo $sql."<br>";
		     if ($row2=mysql_fetch_array($r2, MYSQL_ASSOC))  {
		         $id = $row2['id'];
             return $id;
         } else {   
               $sql = "insert into $table ($field) values ('$val')";
   	           $sql = iconv('utf-8','windows-1251',$sql);
   	           //echo $sql."<br>";
               if ($res = mysql_query($sql)) return mysql_insert_id();
   	     };   
   };

   //������� ��������
   function parse_calcel($txt) {      // echo $txt;
	   $xml = simplexml_load_string($txt);

	   //����
	   $plan_info['plan_id']      = $xml->tenderPlanCancel->planNumber;
	   //echo "PLAN=".$plan_info['plan_id']."<br>" ;
	   if ($plan_info['plan_id'] >0 ) empty_one_plan($plan_info['plan_id']);


   };

   //�������
   function parse($txt) {      // echo $txt;
	   $xml = simplexml_load_string($txt);

	   //����
	   $plan_info['plan_id']      = $xml->tenderPlan->commonInfo->id;
	   $plan_info['planNumber'] = $xml->tenderPlan->commonInfo->planNumber;
	   $plan_info['versionNumber'] = $xml->tenderPlan->commonInfo->versionNumber;

        $nado = false;
	   //�������� �� ������, ���� ��� ���� ����� ������ ������, �� ������� �� ��� ����
       $row = mysql_query("SELECT versionNumber,planNumber FROM plan WHERE planNumber = '$plan_info[planNumber]'");
       if ($r1=mysql_fetch_array($row, MYSQL_ASSOC))  {
           if ($r1['versionNumber'] < $plan_info['versionNumber']) {
               empty_one_plan($r1['planNumber']);
               $nado=true;
           } $plan_info['id_grbs'] = $id_grbs;
       } else $nado=true;

       if ($nado==false) return;

	   $plan_info['year'] = $xml->tenderPlan->commonInfo->year;
	   //�������� ��� ���
	   $plan_info['regNum'] = $xml->tenderPlan->commonInfo->owner->regNum;
     $row = mysql_query("SELECT * FROM grbs WHERE spz = '$plan_info[regNum]'");
     if ($r1=mysql_fetch_array($row, MYSQL_ASSOC))  {
           $id_grbs = $r1['id_grbs'];
           $plan_info['id_grbs'] = $id_grbs;
           $plan_info['id_pbs'] = $r1['id'];
     } else $id_grbs=0;
       if ($id_grbs>0) {

					   //echo  $plan_info['regNum']."<br>";
					   $plan_info['fullName'] =$xml->tenderPlan->commonInfo->owner->fullName;
					   //���� � �����
					   $plan_info['createDate'] = $xml->tenderPlan->commonInfo->createDate;
					   $plan_info['confirmDate'] = $xml->tenderPlan->commonInfo->confirmDate;
					   $plan_info['publishDate'] = $xml->tenderPlan->commonInfo->publishDate;
					   //���� � ��������
					   $plan_info['customer_regNum'] = $xml->tenderPlan->customerInfo->customer->regNum;
					   $plan_info['customer_fullName'] = $xml->tenderPlan->customerInfo->customer->fullName;
					   //OKTMO
					   $plan_info['oktmo_code'] = $xml->tenderPlan->customerInfo->OKTMO->code;
					   $plan_info['oktmo_name'] = $xml->tenderPlan->customerInfo->OKTMO->name;
					   //responsibleContactInfo
					   $plan_info['responsible_lastName'] = $xml->tenderPlan->responsibleContactInfo->lastName;
					   $plan_info['responsible_firstName'] = $xml->tenderPlan->responsibleContactInfo->firstName;
					   $plan_info['responsible_middleName'] = $xml->tenderPlan->responsibleContactInfo->middleName;
					   $plan_info['responsible_phone'] = $xml->tenderPlan->responsibleContactInfo->phone;
					   $plan_info['responsible_fax'] = $xml->tenderPlan->responsibleContactInfo->fax;
					   $plan_info['responsible_email'] = $xml->tenderPlan->responsibleContactInfo->email;

                 $plan_info['sumPushaseSmallBusiness']    = $xml->tenderPlan->providedPurchases->finalPositions->outcomeIndicators->sumPushaseSmallBusiness;
    			       $plan_info['sumPushaseRequest']          = $xml->tenderPlan->providedPurchases->finalPositions->outcomeIndicators->sumPushaseRequest;
    			       $plan_info['sumContractMaxPrice']        = $xml->tenderPlan->providedPurchases->finalPositions->outcomeIndicators->sumContractMaxPrice;
    			       $plan_info['sumPaymentsTotal']           = $xml->tenderPlan->providedPurchases->finalPositions->outcomeIndicators->sumPaymentsTotal;
    			       $plan_info['sumPushaseSingleSupplier4']   = $xml->tenderPlan->providedPurchases->finalPositions->outcomeIndicators->sumPushaseSingleSupplier4;
    			       $plan_info['sumPushaseSingleSupplier5']   = $xml->tenderPlan->providedPurchases->finalPositions->outcomeIndicators->sumPushaseSingleSupplier5;


					   add('plan',$plan_info);
					  // var_dump($plan_info);
					   foreach ($xml->tenderPlan->providedPurchases->positions->position as $val) {
					   	   $position['plan_id']              = $plan_info['plan_id'];
                 $position['id_pbs']               = $plan_info['id_pbs'];
					   	   $position['positionNumber']       = $val->commonInfo->positionNumber;
					   	  // if ($val->commonInfo->amountKBKsYears !=='') {
					         $position['kbk_code']             = $val->commonInfo->amountKBKsYears->KBK->code;
					         if ($position['kbk_code'] == '')  $position['kbk_code'] = $val->commonInfo->amountKBKs->KBK->code;
					        // $position['kbk_year']             = $val->commonInfo->amountKBKsYears->KBK->yearsList->year;
					        // $position['yearAmount']           = $val->commonInfo->amountKBKsYears->KBK->yearsList->yearAmount;
                 // echo $position['kbk_code']  ; die;
					       //};
					       /*
					       if ($val->commonInfo->amountKOSGUsYears !=='') {
                   echo "!!!";
					         $position['kosgu_code']             = $val->commonInfo->amountKOSGUsYears->OKVEDS->OKVED->code;
					         $position['kosgu_year']             = $val->commonInfo->amountKOSGUsYears->KBK->yearsList->year;
					         $position['kosgu_yearAmount']       = $val->commonInfo->amountKOSGUsYears->KBK->yearsList->yearAmount;
					       };   */
                 foreach ($val->commonInfo->OKVEDs->OKVED as $okved) {
					          $position['OKVED_code']           = $okved->code;
					          $position['OKVED_name']           = $okved->name;
                 };
                 
					       $position['contractSubjectName']   = $val->commonInfo->contractSubjectName;
					       $position['contractMaxPrice']      = $val->commonInfo->contractMaxPrice;
					       $position['payments']              = $val->commonInfo->payments;
					       $position['contractCurrency_code'] = $val->commonInfo->contractCurrency->code;
					       $position['contractCurrency_name'] = $val->commonInfo->contractCurrency->name;
                
                 $placingWay_id = add_in_sprav('placingWay','name',$val->commonInfo->placingWay->name);
					       $position['placingWay']       = $placingWay_id;
                 
					       $position['jointBiddingInfo']       = $val->commonInfo->jointBiddingInfo;

					      // $position['changeReason']           = $val->commonInfo->positionModification->changeReason->name;
                 $chr_id = add_in_sprav('changeReason','name',$val->commonInfo->positionModification->changeReason->name);
					       $position['changeReason']       = $chr_id;

					       $position['positionPublishDate']           = $val->commonInfo->positionPublishDate;
					       $position['noPublicDiscussion']            = $val->commonInfo->noPublicDiscussion;
					       $position['purchasePlacingTerm_month']     = $val->purchaseConditions->purchaseGraph->purchasePlacingTerm->month;
					       $position['purchasePlacingTerm_year']      = $val->purchaseConditions->purchaseGraph->purchasePlacingTerm->year;
					       $position['contractExecutionTerm_month']   = $val->purchaseConditions->purchaseGraph->contractExecutionTerm->month;
					       $position['contractExecutionTerm_year']    = $val->purchaseConditions->purchaseGraph->contractExecutionTerm->year;
				         $position['periodicity']                   = $val->purchaseConditions->purchaseGraph->periodicity;

				         $position['purchaseFinCondition_amount']   = $val->purchaseConditions->purchaseFinCondition->amount;
						     $position['purchaseFinCondition_procedure']= $val->purchaseConditions->purchaseFinCondition->procedure;

						 //  $position['purchaseFinCondition']   = $val->purchaseConditions->purchaseFinCondition;
						     $position['advance']                       = $val->purchaseConditions->advance;
						     $position['prohibitions']                  = $val->purchaseConditions->prohibitions;
						     $position['contractFinCondition']          = $val->purchaseConditions->contractFinCondition;
                 
					       $pos_id = add('positions',$position);
                 // var_dump($position); 
                //  die;
                          // echo "pos_id1=$pos_id<br>";
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


					         //������� ����������� ����������� ���������� ���������:
					         $prod['contractGuarantee'] = $products->contractGuarantee->amount;

					         add('products',$prod);
					         //die;
					       };   

                 
                           //echo "pos_id2=$pos_id<br>";
                 //������������ ���������� �������:
					       $prefreq      = $val->purchaseConditions->preferensesRequirement;

					       foreach ($prefreq->preferenses->preferense as $preferense) {                   
					         $pref['position_id']    = $pos_id;
					         $pref['id_preferenses'] = add_in_sprav('preferenses','name',$preferense->name);  					      
					         add('pos_preferenses',$pref);
					       };

                 //���������� � ���������� �������:
					       foreach ($prefreq->requirements->requirement as $requp) {
                // echo "s";die;
					       	 $req['position_id']    = $pos_id;
					         $req['id_requirement'] = add_in_sprav('requirement','name',$requp->name); 
                   add('pos_requirement',$req); // var_dump($req); die; 				
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
	          // echo $file."<br>";
	           $text = open_page($link.'/'.$file);
	   	  //   echo $text;
	   	     parse($text);

	   	  };

	   };
	   closedir($fp1);

   };

   //������� ���
 // empty_plan();
   //  die;
  // ini_set('display_errors', 1);error_reporting(E_ALL);

 //$link=  'zakupki/';
  // $link = "ftp://free:free@ftp.zakupki.gov.ru/fcs_regions/Rostovskaja_obl/plangraphs/prevMonth/";  
   //��������� �� link ����� � ���� �����
  // read_dir($link,'zak/');
   
   $link = "ftp://free:free@ftp.zakupki.gov.ru/fcs_regions/Rostovskaja_obl/plangraphs/currMonth/";
    read_dir($link,'zak/');
   //������� ���������� �� ����� �����
  // parse_dir('zak');
   //������� ��������
   //parse_calcel_dir('zak');

    //
    $end = microtime(true);
    $time=$end-$start;
    $min=floor($time/60);
    $seconds= $time % 60;
    echo "Work time for region: $min min $seconds sec <br>";
   // echo "����� �������� �������: ".($new_cnt+$updated_cnt+$old_cnt);


///������������ ������ ����




?>
