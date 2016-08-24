<?php
/**
 * @package FRONT
 */

 // $user_company = 0;
//

              if ( $_SESSION ['user']>0){
                $FILENAME = $front_html_path.'menu_rep.html';
                $main = &addInCurrentSection($FILENAME);
                unset($sub);

                $r1 = new Select($db,"select * from reports");
                while ($r1->next_row()) {

                   unset($sub);
    	           $sub = new outTree();
   			       $r1->addFields($sub,$ar=array('id','name','link'));
   			       $sub->addField('T','A');
				   $main->addField('sub',$sub);
                };

              };
            //  $site->menu_sub->menu->addField('sub',$sub);
//$site->menu_user->menu->sub[5]=$sub;
//unset($site->menu_user->menu->sub[0]);

//echotree($site->menu_user->menu);

    //            $site->menu_sub->menu->sub[5]->addfield('sep','');

  // };

?>
