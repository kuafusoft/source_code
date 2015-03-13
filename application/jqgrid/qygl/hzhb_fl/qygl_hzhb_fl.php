<?php

require_once('jqgridmodel.php');

class qygl_hzhb_fl extends jqGridModel{
    public function init($controller, array $options = null){
        $options['db'] = 'qygl';
        $options['table'] = 'hzhb_fl';
        
        $options['columns'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'??'),
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
