<?php

require_once('jqgridmodel.php');

class qygl_wzwl_fl extends jqGridModel{
    public function init($controller, array $options = null){
        $options['db'] = 'qygl';
        $options['table'] = 'wzwl_fl';
        
        $options['columns'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'名称'),
            'in_out'=>array('label'=>'进-出', 'formatter'=>'select', 'formatoptions'=>array('value'=>array(1=>'进', -1=>'出')),
                'edittype'=>'select', 'editoptions'=>array('value'=>array(1=>'进', -1=>'出')),
                'stype'=>'select', 'searchoptions'=>array('value'=>array(0=>' ', 1=>'进', -1=>'出')),
                ),
        );
        $options['ver'] = '1.0';
//        $options['gridOptions']['subGrid'] = true;
        $options['navOptions']['edit'] = $options['navOptions']['del'] = false;
//        $options['construct'] = 'zjwl_construct';
//        ['onComplete'] = 'Just a Test';
//        $options['contextMenu'] = 'srs_contextMenu';
//print_r($options);
        parent::init($controller, $options);
    } 
}
