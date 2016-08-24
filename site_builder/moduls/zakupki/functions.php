 <?




 /**  формирует дерево для нижестоящих узлов узла $node_id
 */
 function ShowTree(&$sub, $node_id) {
    global $db;
    $sub2= new outTree();
    $r = new Select($db,"select * from okpd where id=$node_id"); $r->next_row();
    $r->addFields($sub2,$ar=array('id','name'));
   // $r->_unset();

    $r = new Select($db,"select * from okpd where parent=$node_id");
    while ($r->next_row()) {
       $sub1 = new outTree();
       $r->addFields($sub1,$ar=array('id','name'));
       //if ($r->isEOF()) $sub1->addField('IsLast','IsLast');
       $sub->addField('sub',$sub1);
       unset ($sub1);
       ShowTree($sub,$r->result('id'));

    };
    $sub->addField('sub',$sub2);
    unset ($sub2);
 }

 ?>
