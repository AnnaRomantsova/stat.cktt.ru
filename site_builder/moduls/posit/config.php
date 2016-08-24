<?php
     $GLOBALS['modulName'] = $modulName = 'posit';
     $modulCaption = 'Позиции план-графиков';

     $table_name = $GLOBALS['table_name'] = $GLOBALS['zakupki'.'_table'];
     $fcount = $GLOBALS['fcount'] = $GLOBALS['zakupki_fcount'];
     $back_html_path='back/'.$modulName.'/';
     $front_html_path='front/'.$modulName.'/';

       //$table_name = 'company';



  // обязательные поля
 $fieldsWithoutFail = array(
  'name','email','phone','kod'
 );

?>
