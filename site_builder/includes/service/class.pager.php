<?

 /**
  * @package ALL
  */

/**
 * Организует постраничный вывод
 * @uses Select
 * @uses outTree
 *

 * @version 1.03 - 01.08.2007 14:45
 *
 * .01 конструктор Pager вызывает метод initBorder() - интерфейс не изменился <br>
 * .01 добавлен класс PagerQuery extends Pager <br>
 * .02 исправлен глюк с выставлением на нужную страницу по колонке <br>
 * .03 нулевая страница -  та, что по умолчанию (без /cp/0)
 *
 */
 class Pager {
   var  $allCount = 0,
        $table,       //таблица в которой производить выборку
        $count,       //число записей на страницу
        $jumpValue = null,   //значение в колонке на которое надо перейти
        $curPage,
        $field;

   /**
    * запрос
    *
    * @var Select
    */
   var $r;

   /**
    * c какой позиции стартовать
    *
    * @var int
    */
   var $startIndex = 0;

   /**
    * ветка типа Pager
    *
    * @var outTree
    */
   var $ot;


   /**
   * @param Db $_db подключение к базе данных
   * @param string $_table имя таблицы
   * @param int $_count количество записей на страницу
   * @param int $_curPage какую страницу показывать
   * @param mixed $_jumpValue значение поля записи, которую отображать (приоритетней $_curPage)
   * @param string $_where условия
   * @param string $_order порядок
   * @param string $_field имя поля, со значением $_jumpValue которого проводится сравнение.
   * @return Pager
   */
   function Pager($_db,$_table,$_count = 10,$_curPage = 0,$_jumpValue,$_where,$_order,$_field='id',$myquery='') {
      $this->db = $_db;
      $this->table = $_table;
      $this->count = $_count;
       $this->curPage = $_curPage;
     //  echo "curpage=".$this->curPage;
      $this->jumpValue = $_jumpValue;
      $this->field = $_field;
     // echo $myquery;
      if ($myquery !== '') $r = new Select($this->db,$myquery);

      else
      $r = new Select($this->db,'select * from '.$this->table.($_where ? ' where '.$_where: '').($_order ? ' order by '.$_order: ''));
             $this->r = &$r;
//             echo 'select * from '.$this->table.($_where ? ' where '.$_where: '').($_order ? ' order by '.$_order: '');
      $this->initBorder();
   }

   /**
    * выставляет границы запросу
    */
   function initBorder() {
      $r = &$this->r;
      $this->allCount = $r->num_rows;
      if (isset($this->jumpValue)) {

          while ($r->next_row()) {

             if ($this->jumpValue==$r->result($this->field) ) {
                $this->curPage=intval($r->result_row/$this->count);
                $this->curLine=$r->result_row%$this->count;
                break;
             }
          }
       }
      // echo "cp=".$this->curPage."<br>";
       if ($this->allCount) {
           // if ($this->curPage > ($curpage = floor(($this->allCount-1)/$this->count)) )
           //         $this->curPage = $curpage;
           //     if ($this->curPage<0)
           //             $this->curPage=0;

            $this->startIndex = $r->result_row = $this->curPage*$this->count-1;
            $r->end = $this->endIndex = $this->startIndex+$this->count;
       }
        //   echo "cp=".$this->curPage."<br>";
   }

    /**
     * инициализирует поля типа Pager в $ot
     * @param string $href адрес корневой ссылки
     * @param bool $SA выставлять ссылку текущей странице
     * @param bool $asGET постраничный просмотр через запрос GET  (а не ЧПУ) - нужен в BACK
     * @return bool проиницализировал или нет
     */
     function add_pages_from_to($from,$to,$cnt_all,$curPage, &$pager,$strcp,$strcp0){
           for ($i=$from; $i <= $to; $i++) {
		               $sub = new outTree();
		               $sub->addField('T', ( $i -1 == $curPage ? ($SA ? 'SA' :'S') : 'A' ) );
		               $sub->addField('href', $href.($i ? $strcp.($i-1) : $strcp0));
		               $sub->addField('page', $i);
		               if ($i < ($cnt_all/2))
		                  $sub->addField('separator', '');
		               $sub->ITEMTYPE = $sub->T;
		               $pager->addField('sub',$sub);
		               unset($sub);
           } ;
     }

    function initPAGER($href = '', $SA = null, $asGET = false) {
       if ( $this->allCount/$this->count > 1 ) {
            //echo "cp=".$this->curPage;
         // if ($_SERVER["QUERY_STRING"] !=='') $qur= $_SERVER["QUERY_STRING"].'&';
          $q='?';
          $arr = explode('&',$_SERVER['QUERY_STRING']);
         // var_dump($_SERVER);
          foreach ($arr as $arg) {
           if ($arg!=='') {
            $str = explode ('=',$arg);
              if ($str[0]!=='cp')
               $q.="&$arg";
           };
          }
        //  echo $q;
          if ($q!=='?')  $q.='&';
         // echo $this->allCount;
         $asGET = true;
          $strcp = isset($asGET) ? $q."cp=" : $q.'/cp/' ;
          $strcp0 = isset($asGET) ? $q."cp=0" : $q.'' ;
          $pager = new outTree();
          $c_p = ceil($this->allCount/$this->count);

          $cnt_all = 10;
          if ($c_p>$cnt_all) {

             //добавляем первую половину страниц
              if (($c_p - $this->curPage) > $cnt_all) {
                  if (($this->curPage - $cnt_all/4) >0) {
                             $from1 = $this->curPage -  ceil($cnt_all/4);  $to1 =  $this->curPage +  ceil($cnt_all/4);
                             $from2 = $c_p - ceil($cnt_all/2);  $to2 =  $c_p;
                            //echo "певрый";
                  } else {
                             $from1 = 1;  $to1 =  $cnt_all/2;
                             $from2 = $c_p - $cnt_all/2;  $to2 =  $c_p;
                           //  echo "вт";
                  }
             } else {
             //echo "3";
                  if (($this->curPage + $cnt_all/4) <= $c_p) {
                             $from1 = 1;  $to1 =  $cnt_all/2;
                             $from2 = $this->curPage -  ceil($cnt_all/4);  $to2 = $this->curPage +  ceil($cnt_all/4);
                            // echo "тр";
                  } else {
                             $from1 = 1;  $to1 =   ceil($cnt_all/2);
                             $from2 = $this->curPage -  ceil($cnt_all/2);  $to2 =  $c_p;
                             //echo "чт";
                  }


              };
             // echo "file1=$from1<br>file2=$to1";
              //ссылка на первую
               if ($from1 >5 ) {
                       $this->add_pages_from_to(1,1,$cnt_all,$this->curPage,$pager,$strcp,$strcp0);
		               //добавляем ..
		               $sub = new outTree();
		               $sub->addField('T', 'P' );
		               $sub->addField('page', '...');
		               $sub->ITEMTYPE = $sub->T;
		               $pager->addField('sub',$sub);
		               unset($sub);
		       };
              $this->add_pages_from_to($from1,$to1,$cnt_all,$this->curPage,$pager,$strcp,$strcp0);
              //добавляем ..
               $sub = new outTree();
               $sub->addField('T', 'P' );
               $sub->addField('page', '...');
               $sub->ITEMTYPE = $sub->T;
               $pager->addField('sub',$sub);
               unset($sub);
              //добавляем сторую половину страниц
               $this->add_pages_from_to($from2,$to2,$cnt_all,$this->curPage,$pager,$strcp,$strcp0);



          } else  $this->add_pages_from_to(1,$c_p,$cnt_all,$this->curPage, $pager,$strcp,$strcp0);

         /* for ($i=1; $i <= $c_p; $i++) {
               // echo $href.($i ? $strcp.$i : $strcp0)."<br>";
               $sub = new outTree();
               $sub->addField('T', ( $i == $this->curPage ? ($SA ? 'SA' :'S') : 'A' ) );
               $sub->addField('href', $href.($i ? $strcp.$i : $strcp0));
               $sub->addField('page', $i+1);
               if ($i < ($c_p-1))
                  $sub->addField('separator', '');
               $sub->ITEMTYPE = $sub->T;
               $pager->addField('sub',$sub);
               unset($sub);
          }

         */



          if ($this->curPage > 0) {
              $page = $this->curPage-1;
              $pager->addField('prev', $href.($page ? $strcp.$page : $strcp0));
              $pager->addField('init_prev', '');
          }

          if ( ($this->curPage+1)<($this->allCount/$this->count) ) {
              $page = $this->curPage+1;
              $pager->addField('next', $href.($page ? $strcp.$page : $strcp0) );
              $pager->addField('init_next', '');
          }
          $pager->addField('curpage', $this->curPage+1);
          $pager->addField('total_pages', $c_p);
          $pager->addField('pages', '');

       }

       if (isset($pager)) {
                       $this->ot = &$pager;
                       return true;
       }

       return false;
    }

    /**
     * добавляет ветку типа Pager в дерево
     *
     * @param outTree $tree дерево
     * @param string $field имя поля, с каким добавлять
     */
    function addPAGER(&$tree,$field = 'pager') {
            if (isset($this->ot))
                    $tree->addField($field,$this->ot);
    }

    /**
     * статическая функция для создания объекта Pager
     * и инициализации дерева страниц
     * (выставляет страницу по полю 'id')
     *
     * @return Pager
     */
    static function &newPager(&$_db,$_table,$_count = 10,$_curPage = 0,&$param) {
               //
                if (strlen($param['query']) >0) {

                     $pg = new Pager($_db,$_table,$_count,$_curPage,$param['jumpValue'],$param['where'],$param['order'],'id',$param['query']);
                }
                else
                $pg = new Pager($_db,$_table,$_count,$_curPage,$param['jumpValue'],$param['where'],$param['order']);
                if ($pg->allCount) {
                        $pg->initPAGER($param['href'],$param['SA'],$param['asGET']);
                        return $pg;
                }
                return null;
         }


  }

 /**
  * Работает с готовым запросом
  */
 class PagerQuery extends Pager {

   /**
    * Запрос
    *
    * @var Select
    */
   var  $r;

   /**
    *
    * @param Select $_r готовый запрос к базе
    * @param int $_count количество записей на страницу
    * @param int $_curPage страница, которую показывать
    * @param mixed $_jumpValue значение поля записи, которую отображать (приоритетней $_curPage)
    * @param string $_field имя поля, со значением $_jumpValue которого проводится сравнение.
    * @return PagerQuery
    */
        function PagerQuery(&$_r,$_count = 10,$_curPage = 0,$_jumpValue,$_field='id') {
      $this->count = $_count;
      $this->curPage = $_curPage;
      $this->jumpValue = $_jumpValue;
      $this->r = &$_r;
      $this->initBorder();
        }


    /**
     * статическая функция для создания объекта PagerQuery
     * и инициализации дерева страниц
     * (выставляет страницу по полю 'id')
     *
     * @return PagerQuery
     */
     static    function &new_(&$r,$_count,$_curPage,&$param) {
                 $pg = new PagerQuery($r,$_count,$_curPage,$param['jumpValue']);
                if ($pg->allCount) {
                        $pg->initPAGER($param['href'],$param['SA'],$param['asGET']);
                        return $pg;
                }
                return null;
         }
 }

 /**
  * осуществляет постраничный вывод различных конфигураций
  */
 class Pagers {

         /**
         * отсортированный по полю sort
          */
    function &So(&$_db,$_table,$_count = 10,$_curPage = 0,$href,$SA = null,$_jumpValue=null) {
            return Pager::newPager($_db,$_table,$_count,$_curPage,
                      $ar=array('href'=>$href,
                                               'SA'=>$SA,
                                               'jumpValue'=>$_jumpValue,
                                               'order'=>'sort',
                                               'where'=>'pabl="1"'
                                               ));
         }
   function &PrSoZakup(&$_db,$_table,$where,$_count = 10,$_curPage = 0,$href,$field,$order,$SA = null,$_jumpValue=null) {
            return Pager::newPager($_db,$_table,$_count,$_curPage,
                      $ar=array('href'=>$href,
                                               'SA'=>$SA,
                                               'jumpValue'=>$_jumpValue,
                                               'order'=>$field.' '.$order,
                                               'where' => $where
                                               ));
         }
         /**
         * отсортированный по полю sort и отфильтрованный по полю parent
          */
    function &PrSo(&$_db,$_table,$_parent,$_count = 10,$_curPage = 0,$href,$SA = null,$_jumpValue=null) {
            return Pager::newPager($_db,$_table,$_count,$_curPage,
                      $ar=array('href'=>$href,
                                               'SA'=>$SA,
                                               'jumpValue'=>$_jumpValue,
                                               'order'=>'sort',
                                               'where' => 'pabl="1" AND parent="'.$_parent.'"'
                                               ));
         }

       function &PrSoCt(&$_db,$_table,$_parent,$_count = 10,$_curPage = 0,$href,$field,$order,$SA = null,$_jumpValue=null) {
            return Pager::newPager($_db,$_table,$_count,$_curPage,
                      $ar=array('href'=>$href,
                                               'SA'=>$SA,
                                               'jumpValue'=>$_jumpValue,
                                               'order'=>$field.' '.$order,
                                               'where' => 'pabl="1" AND parent="'.$_parent.'"'
                                               ));
         }

      function &PrSoSearch(&$_db,$_table,$where,$_count = 10,$_curPage = 0,$href,$field,$order,$SA = null,$_jumpValue=null) {
            return Pager::newPager($_db,$_table,$_count,$_curPage,
                      $ar=array('href'=>$href,
                                               'SA'=>$SA,
                                               'jumpValue'=>$_jumpValue,
                                               'order'=>$field.' '.$order,
                                               'where' => 'pabl="1" '.$where
                                               ));
         }

         /**
         * отсортированный по полю datetime desc
          */
        static function &Da(&$_db,$_table,$where,$_count = 10,$_curPage = 0,$href,$SA = null,$_jumpValue=null,$myquery=null) {
            //echo strlen($myquery);

            if (strlen($myquery)>0)
                  return Pager::newPager($_db,$_table,$_count,$_curPage,
                            $ar=array('href'=>$href,
                                                     'SA'=>$SA,
                                                     'jumpValue'=>$_jumpValue,
                                                     'order' => 'date desc',
                                                     'where' => $where,
                                                     'query' => $myquery
                                                     ));
            else {
                 $ar=array('href'=>$href,
                                                     'SA'=>$SA,
                                                     'jumpValue'=>$_jumpValue,
                                                     'order' => 'date desc',
                                                     'where' => $where
                                                     );
                 return Pager::newPager($_db,$_table,$_count,$_curPage,$ar );
            };
         }
 }

?>
