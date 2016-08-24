<?php
/**
 * @package FRONT
 */
 $FILENAME = 'front/menu/menu_sub.html';

 $main = &addInCurrentSection($FILENAME);
 unset($main->content);


//var_dump(get_id_from_post('service',false));
 addSprav($main,'city',$_GET['id_city'],'city');
 addSpravM($main,'zakupki_zak',get_id_from_post('zakon',false),'zakon');
 addSpravM($main,'type_service',get_id_from_post('service',false),'service');
// echotree($main->service);

?>
