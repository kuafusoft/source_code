<?php

require_once('jqgridmodel.php');

class qygl_wz_fl extends jqGridModel{
    public function init($controller, array $options = null){
        $options['db'] = 'qygl';
        $options['table'] = 'wz_fl';
        
        $options['columns'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'名称'),
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
