<?php

 /**
  * служебные функции - могут зависеть от сайта
  * @package FRONT
  * @version 3.03.argentum - 23.10.2007 10:30
  */

include($inc_path.'/service/class.output.php');
include($inc_path.'/service/func.service.php');




/**
 * Добавляет модули и страницы в секции
 *
 * @param outTree $site дерево сайта
 * @param array  $param массив с именами секций
 * @version 1.01
 *
 * добавляет поле ITEMTYPE в каждый проинициализированный узел <br>
 * ВНИМАНИЕ! теперь в таблице модулей прописывается только относительный путь файла - где лежит модуль <br>
 *  <br>
 * ВНИМАНИЕ! шаблоны применяются к страницам с id > 11 <br>
 * .01 к каким страницам применять шаблоны - необязательный параметр <br>
 */
function addSections($site,$sections,$number = 11)  {
 extract($GLOBALS);
 foreach ($sections as $param) {
        $GLOBALS['currentSection'] = $param;

    $c_ = trim($r_sp->result($param));
    $comand = ( '' == $c_ ? trim($r_ss->result($param)) : $c_) ;

        if ( $comand > '' ) {
        $ex = explode(";", stripslashes($comand)); $c_ex = count($ex);

                for ( $main_i = 0; $main_i < $c_ex; $main_i++ ) {
                    $ex1 = explode("=", $ex[$main_i]);
                    $GLOBALS['current_N'] = $N = intval(trim($ex1[1]));
                    if ( trim($ex1[0]) == 'modul' ) {
                            $rMod = new Select($db,'select * from '.$moduls_table.' where id="'.$N.'"');
                            $count = count($site->$param);
                            if ($rMod->next_row())
                                include($moduls_root.$rMod->result('command'));
                            $rMod->unset_();

                            if (count($site->$param)>$count) {
                               if (is_array($site->$param))
                                  $ot = $site->{$param}[$count];
                               else
                                  $ot = $site->$param;
                               $ot->ITEMTYPE = 'modul'.$N;
                            }

                    }

                    elseif ( trim($ex1[0]) == 'page' ) {
                            $rPage = new Select($db,'select * from '.$pages_table.' where id="'.$N.'"');
                            if ($rPage->next_row()) {
                                    unset($main);
                                $main =  new outTree();
                                if ($number < $N) {
                                        $main->addField('path',$GLOBALS['path']);
                                        $main->TEMPLATE = '/front/content.html';
                                }
                                else {
                                        $main->TEMPLATE_NOT_FILE=1;
                                        $main->TEMPLATE = '[%content%]';
                                }
                                    $main->ITEMTYPE = 'page'.$N;
                                    $rPage->addFieldHTML($main,'content');
                                $site->addField($param,$main);
                                    unset($main);
                            }
                            $rPage->unset_();
                    }
            }
        }

 }

}

/**
 * Подключает новый шаблон к последней ветке в секции и возвращет её <br>
 * Внимание!! Нет гарантии, что вернет объект типа outTree
 *
 * @uses $GLOBALS['currentSection']
 *
 * @param string $TEMPLATE путь к новому шаблону
 * @return outTree
 */
function &incPage($TEMPLATE) {
         global $site;
         $c = count($site->$GLOBALS['currentSection']);
         switch ($c) {
                 case 0:
                         return null;
                 case 1:
                         $main = $site->$GLOBALS['currentSection'];
                         break;

                 default:
                         $main = $site->{$GLOBALS['currentSection']}[$c-1];
                         break;
         }

//         echoTree($main);
         unset($main->TEMPLATE_NOT_FILE);
         $main->TEMPLATE = $TEMPLATE;
         $main->ITEMTYPE = 'modul'.$GLOBALS['current_N'];
         return $main;
}


///**
// * добавляет календарик
// * @param outTree $main узел
// * @param int     $n    номер календарика
// * @return void
// */
// function addCalend($main,$n) {
//         $path = 'front/calend/';
//
//         $ot = new outTree();
//         $ot->addField('n',$n);
//
//         $ot_inc = new outTree($path.'include.html');
//         $ot_inc->addField('yc',date('Y'));
//         $ot_inc->addField('mc',intval(date('n'))-1);
//         $ot_inc->addField('dc',date('j'));
//         $ot->addField('include',$ot_inc);
//
//         $ot_but = new outTree($path.'button.html');
//         $ot_but->addField('n',$n);
//         $ot->addField('button',$ot_but);
//
//         $ot_calend = new outTree($path.'calend.html');
//         $ot_calend->addField('n',$n);
//         $ot_calend->addField('imgPath','/i/calend/');
//         $ot->addField('calend',$ot_calend);
//
//         $main->addField('calend'.$n,$ot);
// }

 /**
  * добавляет поле типа last в дерево пути
  *
  * @param outTree $path дерево пути
  * @param string $name c каким именем добавлять
  * @param string $href c какой ссылкой добавлять, по умолчанию $GLOBALS['modulName']
  */
 function addLast($path,$name,$href=null) {
        $path->last->addField('href',isset($href) ? $href : $GLOBALS['modulName']);
        $path->last->addField('T','A');
        $path->last->addField('separator','');
        $path->addField('sub',$path->last);
        unset($path->last);
        $ot_last = new outTree();
        $ot_last->addField('name',$name);
        $path->addField('last',$ot_last);
 }

 /**
 * инициализирует полe в $outTree "page$_id", используя таблицу site_pages
 * @param outTree $outTree дерево
 * @param int $_id id страницы, которую добавляем
 * @return void
 */
 function addPage($outTree,$_id) {
         global $db;
         $r = new Select($db,'select * from site_pages where id="'.$_id.'"');
         if ($r->next_row()) {
            $outTree->addField('page'.$_id,$r->result('content'));
         }
         $r->unset_();
 }


/**
 * возвращает ветку, присоединенную к текущей секции <br>
 * в зависимости от параметра $inc  <br>
 * true: возвращает последнюю ветку в секции с новым шаблоном <br>
 * false: создает новую ветку <br>
 * если нет предыдущей ветки -  создает новую
 *
 * @uses $GLOBALS['currentSection']
 *
 * @param string $TEMPLATE путь к новому шаблону
 * @param bool $inc "склеивать" ветки - или нет
 * @return outTree
 */
function &addInCurrentSection($TEMPLATE,$inc = true) {
         if (!$inc  || !($m = &incPage($TEMPLATE)) ) {
                 $main = new outTree($TEMPLATE);
                 $GLOBALS['site']->addField($GLOBALS['currentSection'],$main);
                 return $main;
         }
         else
                 return $m;
}

/**
 * отсоединяет последнюю ветку в текущей секции
 * @uses $GLOBALS['currentSection']
 */
function unsetLastInCurrentSection() {
         global $site;
         $c = count($site->$GLOBALS['currentSection']);
         switch ($c) {
                 case 1:
                         unset($site->$GLOBALS['currentSection']);
                         break;

                 default:
                         array_pop($site->{$GLOBALS['currentSection']});
                         break;
         }
}

/**
 * добавляет календарик
 * @param outTree $main дерево
 * @param int $n номер календарика
 */
 function addCalend($main,$n) {
         $path = 'back/calend/';

         $ot = new outTree();
         $ot->addField('n',$n);

         $ot_inc = new outTree($path.'include.html');
         $ot_inc->addField('yc',date('Y'));
         $ot_inc->addField('mc',intval(date('n'))-1);
         $ot_inc->addField('dc',date('j'));
         $ot->addField('include',$ot_inc);

         $ot_but = new outTree($path.'button.html');
         $ot_but->addField('n',$n);
         $ot->addField('button',$ot_but);

         $ot_calend = new outTree($path.'calend.html');
         $ot_calend->addField('n',$n);
         $ot_calend->addField('imgPath','/_images/calend/');
         $ot->addField('calend',$ot_calend);

         $main->addField('calend'.$n,$ot);
 }

  function to_date($in,$time=false){
  if (strlen($in)>0 ) {
		  if (!$time)
			return substr($in,8,2).".".substr($in,5,2).".".substr($in,0,4);
		  else {
		     $str = substr($in,8,2).".".substr($in,5,2).".".substr($in,0,4);
		     if (strlen(substr($in,11,2))>0 && strlen(substr($in,14,2))>0) $str.=" в ".substr($in,11,2).":".substr($in,14,2);
		     return $str;
		  };
  };
};

?>
