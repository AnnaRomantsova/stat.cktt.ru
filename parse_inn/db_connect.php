<?php
     $db_server = 'localhost';
     $db_user   = 'dba0006';
     $db_pass   = 'd4cwHZff';
     $db_base   = 'demo';
     //echo "dd";
   ///*
     $db_user   = 'dba0006';
     $db_pass   = 'd4cwHZff';
     $db_base   = 'dba0006_1';

   //*/
     //���������� � ��
     if (!mysql_connect ($db_server, $db_user, $db_pass)) die('��� ���������� � �������� ��� ������');
     if (!mysql_select_db($db_base)) die('��� ���������� � ����� ������');

?>
