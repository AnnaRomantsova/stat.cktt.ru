<?php

   ini_set("max_execution_time", "1000000000");
   ini_set('display_errors', 1); error_reporting(E_ALL);
   include("db_connect.php");
   mysql_query("SET CHARACTER SET 'utf8'");

   function open_page($link){

   	           $fp1 = fsockopen ("zakupki.gov.ru", 80, $errno, $errstr);
               $posts = substr($link,21);
             //  $posts=$link;
               //echo $posts."<br>";
              // die;
             // echo $url;
               $url="zakupki.gov.ru";
 			  $query="GET ".$posts." HTTP/1.0\r\n".
                   "Host:$url\r\n".
                   "Proxy-Connection:keep-alive\r\n".
                   "Cache-Control:max-age=0\r\n".
                   "Proxy-Authorization:Basic cm9tYW50c292YV9hdTo0Yno1ZmpzMQ==\r\n".
                   "Accept:text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n".
                   "User-Agent:Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.22 Safari/537.36\r\n".
                   "Accept-Encoding:gzip, deflate, sdch\r\n".
                   "Accept-Language:ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4\r\n".
                   "Cookie: routeepz2=1; routeepz7=3; routeepz0=3; userQueryId=18bbe148-3acd-4bab-ba92-16e1fbd6f5be; contentFilter=fz44; _ym_visorc_17190094=b\r\n\r\n";

         //    $query = "GET /epz/organization/organization/extended/search/result.html?sortDirection=true&organizationSimpleSorting=PO_NAZVANIYU&recordsPerPage=_50&pageNumber=2&searchText=&strictEqual=false&morphology=false&placeOfSearch=FZ_94&registrationStatusType=REGISTERED&kpp=&custLev=S%2CM%2CNOT_FSM&organizationRoleList=&okvedCode=&okvedWithSubElements=false&districtIds=&regionIds=5277357&cityIds=&organizationTypeList=&spz=&withBlocked=false&customerIdentifyCode=&headAgencyCode=&headAgencyWithSubElements=false&organizationsWithBranches=false&legalEntitiesTypeList=&ppoWithSubElements=false&ppoCode=&address=&town=&publishedOrderClause=true&unpublishedOrderClause=true&bik=&bankRegNum=&bankIdCode= HTTP/1.1\r\n".
//"Host: zakupki.gov.ru\r\n".
//"Proxy-Connection: keep-alive\r\n".
//"Cache-Control: max-age=0\r\n".
//"Proxy-Authorization: Basic cm9tYW50c292YV9hdTo0Yno1ZmpzMQ==\r\n".
//"Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8\r\n".
//"User-Agent: Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/42.0.2311.22 Safari/537.36\r\n".
//"Accept-Encoding: gzip, deflate, sdch\r\n".
//"Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4\r\n".
//"Cookie: routeepz0=3; routeepz2=0; routeepz7=0; userQueryId=3cffa77a-c89f-44c9-9854-75567f0f03bd; _ym_visorc_17190094=b\r\n\r\n";

               fputs ($fp1, $query);
               stream_set_timeout($fp1, 300);
               //echo $data;
               $data =  fgets ($fp1);

               $info = stream_get_meta_data($fp1);
               if ($info['timed_out']) {
                  echo 'Сайт госзакупок опять временно недоступен!';
                  //mail('anutabur@mail.ru','Парсинг закупок cktt.ru',"Проблема загрузки страницы $link");
              //    die;
               };

               $data='';
//die;
               //читаем файл построчно
               $txt='';
               while (!feof($fp1)) {
                     $data =  fgets ($fp1);
                     if ("\r\n" !== $data) {
                         $txt.=$data;
                     };
                    // echo $data;
               };
              // die;
               fclose($fp1);
               return $txt;
   };


    function parse_elem($link,$id_grbs) {
      // die;
	   global $cnt_page;

       $page = open_page($link);
       //$page = file_get_contents($posts);
       //echo $page;
     // die;
       if(strpos($page,'403 ') == false) {

                 $page = strstr($page,'Код по СПЗ');// echo $page;
                 $page= strstr($page,'viewInfo');
                 $page= strstr($page,'<span>');
                 $spz= substr($page,6,strpos($page,'</span>')-6);
                 $page= strstr($page,'</span>');

                 //if (strlen($naimen)>0 ) mysql_query("insert into org(name,inn,kpp,spz) values ('$naimen','$inn','$kpp','$spz')");
                 if (strlen($spz)>0 ) mysql_query("update grbs set spz='$spz' where id = $id_grbs");
                 echo "update grbs set spz='$spz' where id = $id_grbs";
                 //echo "insert into org(name,inn,kpp,spz) values ('$naimen','$inn','$kpp','$spz')";

       } else mysql_query("insert into org_forbidden(link) values ('$link')");
       // die;
    };


    function parse($link,$id_grbs) {
      // die;
	   global $cnt_page;

       $page = open_page($link);
       echo $page;
             //  $i=1;
       while($page !='') {
                    // $data =  fgets ($fp);
                 $page = strstr($page,'td class="descriptTenderTd"');
                 $page= strstr($page,'reportBox');
                 $page= strstr($page,'http');
                // $link_elem = substr($page,0,strpos($page,'"'));

                 $pos = strpos($page,'" ');
                 $pos1 = strpos($page,"'");
                 if ($pos>$pos1) $cnt1 = $pos1; else $cnt1 = $pos;

                 $link_elem = substr($page,0,$cnt1);
                 echo "L=".$link_elem."<br>"; //


                 $page= strstr($page,'</td');
                 if (strpos($link_elem,'organizationId')>0) parse_elem($link_elem,$id_grbs);
                   else if (strpos($link_elem,'agencyId') >0) {
						$pos = strpos($page,',');
                	    $pos1 = strpos($page,"'");
                        if ($pos>$pos1) $cnt1 = $pos1; else $cnt1 = $pos;
                        $link_elem = substr($page,0,$cnt1);
                        echo "L1=".$link_elem."<br>";
                        if (strpos($link_elem,'organizationId')>0) parse_elem($link_elem,$id_grbs);
                   };
                   $page= strstr($page,'</td');
       };
      die;
    };

   //mysql_query("delete from org");
   //mysql_query("delete from org_forbidden");

   $start = microtime(true);

  $page = file_get_contents('log.txt');
 /*
  if ((int)$page>1) $p=$page; else $p=1;
//  echo $p; die;
  for ($i=$p; $i<=30;$i++) {
     $link="http://zakupki.gov.ru/epz/organization/organization/extended/search/result.html?sortDirection=true&organizationSimpleSorting=PO_NAZVANIYU&recordsPerPage=_50&pageNumber=$i&searchText=&strictEqual=false&morphology=false&placeOfSearch=FZ_94&registrationStatusType=ANY&kpp=&custLev=S%2CM%2CNOT_FSM&organizationRoleList=&okvedCode=&okvedWithSubElements=false&districtIds=&regionIds=5277357&cityIds=&organizationTypeList=&spz=&withBlocked=false&customerIdentifyCode=&headAgencyCode=&headAgencyWithSubElements=false&organizationsWithBranches=false&legalEntitiesTypeList=&ppoWithSubElements=false&ppoCode=&address=&town=&publishedOrderClause=true&unpublishedOrderClause=true&bik=&bankRegNum=&bankIdCode=";
   //  echo $link;
     parse($link);
     $f = fopen("log.txt", "w");
	 fwrite($f, "$i");
	 fclose($f);
  };
 */
/* $row = mysql_query("select * from org_forbidden where s=0");
 while ($r1=mysql_fetch_array($row, MYSQL_ASSOC))
 {
   echo $r1['link'];
 };
 */

 $row = mysql_query("SELECT * FROM grbs WHERE spz = '' order by rand()");
 while ($r1=mysql_fetch_array($row, MYSQL_ASSOC))
 {
   $inn= $r1['inn_pbs'];


  $link="http://zakupki.gov.ru/epz/organization/organization/extended/search/result.html?placeOfSearch=EVERYWHERE&registrationStatusType=ANY&kpp=&_custLev=on&_custLev=on&_custLev=on&_custLev=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_organizationRoleList=on&_okvedWithSubElements=on&okvedCode=&ppoCode=&address=&regionIds=5277357&bik=&bankRegNum=&bankIdCode=&town=&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&_organizationTypeList=on&spz=&_withBlocked=on&customerIdentifyCode=&_headAgencyWithSubElements=on&headAgencyCode=&_organizationsWithBranches=on&_legalEntitiesTypeList=on&_legalEntitiesTypeList=on&_legalEntitiesTypeList=on&_legalEntitiesTypeList=on&_legalEntitiesTypeList=on&_legalEntitiesTypeList=on&_legalEntitiesTypeList=on&_legalEntitiesTypeList=on&_legalEntitiesTypeList=on&_legalEntitiesTypeList=on&_legalEntitiesTypeList=on&publishedOrderClause=true&_publishedOrderClause=on&unpublishedOrderClause=true&_unpublishedOrderClause=on&pageNumber=1&searchText=$inn&strictEqual=false&morphology=false&recordsPerPage=_50&organizationSimpleSorting=PO_NAZVANIYU";


   parse("$link",$r1['id']);

 };


//  parse("$link");
    $end = microtime(true);
    $time=$end-$start;
    $min=floor($time/60);
    $seconds= $time % 60;
    echo "Work time for region: $min min $seconds sec &lt;br&gt;";
   // echo "Всего пройдено закупок: ".($new_cnt+$updated_cnt+$old_cnt);



?>
