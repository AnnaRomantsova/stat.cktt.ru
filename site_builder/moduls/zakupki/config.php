<?php
     $GLOBALS['modulName'] = $modulName = 'zakupki';
     $modulCaption = 'Закупки';

     $table_name = $GLOBALS['table_name'] = $GLOBALS[$modulName.'_table'];
      $fcount = $GLOBALS['fcount'] = $GLOBALS[$modulName.'_fcount'];
         $back_html_path='back/'.$modulName.'/';
         $front_html_path='front/'.$modulName.'/';

       //$table_name = 'company';



  // обязательные поля
 $fieldsWithoutFail = array(
  'name','email','phone','kod'
 );

?>
