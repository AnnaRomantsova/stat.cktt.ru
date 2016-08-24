<?php
/**
 * @package FRONT
 */

 // $user_company = 0;
//

              $menuName = 'menu_education';
              unset($menu);
              $menu = new Menu('front/menu/'.$menuName.'.html',161,0);
              $menu->addMenu($site,$menuName); //echotree($menu);
             // if ($user_company == 0) $site->menu_sub->menu->sub[4]->href='zakupki';

            //  $site->menu_sub->menu->addField('sub',$sub);
//$site->menu_user->menu->sub[5]=$sub;
//unset($site->menu_user->menu->sub[0]);

//echotree($site->menu_user->menu);

    //            $site->menu_sub->menu->sub[5]->addfield('sep','');

  // };

?>
