<?php
/**
 * @package FRONT
 */

 include_once($inc_path."/service/class.menu.php");

// главное меню
 $menuName = 'menu_main';
 unset($menu);
 $menu = new Menu('front/menu/'.$menuName.'.html');
 $menu->addMenu($site,$menuName);



// if (1 < $count_) {
//         // подменю
//          $menuName = 'menu_sub';
//          unset($menu);
//         $menu = new Menu('front/menu/'.$menuName.'.html',$parent_,0);

// }
 //$site->menu_main->menu->sub[0]->addfield('css',$site->menu_main->menu->sub[0]->href);
 //$site->menu_main->menu->sub[1]->addfield('css',$site->menu_main->menu->sub[1]->href);
 //$site->menu_main->menu->sub[2]->addfield('css',$site->menu_main->menu->sub[2]->href);
 //$site->menu_main->menu->sub[3]->addfield('css','bricks');
//echotree($site->menu_main);

?>
