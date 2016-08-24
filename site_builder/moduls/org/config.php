<?php
     $GLOBALS['modulName'] = $modulName = 'org';
     $modulCaption = 'Организации и ГРБС';


     $back_html_path="back/$modulName/";
     $front_html_path="front/reklama/";

       //  $table_name = 'company';

     $acount = $GLOBALS['acount'] = $GLOBALS[$modulName.'_acount'];
    // $modulName$modulName $GLOBALS['fcount'] = $GLOBALS[$modulName.'_fcount'];
     $table_name = $GLOBALS['table_name'] = $GLOBALS['org_table'];

      $files_path = '/_files/Moduls/rek/images/';
      $extent = array('jpg','png','gif');



      $arFiles = array(
                 'image1' => array($extent,$files_path,'image'),
                // 'image2' => array($extent,$files_path,'image'),
                // 'image3' => array($extent,$files_path,'image'),
      );

?>
