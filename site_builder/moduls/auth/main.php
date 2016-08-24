<?php

include ('config.php');


if (!isset($_SESSION['user'])) {
     $FILENAME = $front_html_path.'auth.html';

     unset($main);
     $main = new outTree($FILENAME);
     $main->addField('not_log','');

     if (isset($main)) {
                $site->addField($GLOBALS['currentSection'],$main);
                unset($main);
     };
};
?>