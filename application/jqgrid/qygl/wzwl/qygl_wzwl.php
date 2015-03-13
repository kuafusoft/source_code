<?php

require_once('jqgridmodel.php');

class qygl_wzwl extends jqGridModel{
    public function init($controller, array $options = null){
        $options['db'] = 'qygl';
        $options['table'] = 'wzwl';
        $options['relations']['belongsto'] = array('wzwl_fl', 'hzhb', 'wz');
        
        $options['columns'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'wzwl_fl_id'=>array('label'=>'往来类别'),
            'happened_date'=>array('label'=>'发生日期'),
            'hzhb_id'=>array('label'=>'相关方'),
            'wz_id'=>array('label'=>'物资'),
            'amount'=>array('label'=>'数量'),
            'price'=>array('label'=>'单价'),
            'total'=>array('label'=>'总金额', 'editable'=>false),
            'ticket_no'=>array('label'=>'凭证编号'),
            'ticket_img'=>array('label'=>'凭证文件'),
            'note'=>array('label'=>'备注'),
            'jbr_id'=>array('label'=>'经办人'),
            'creater_id'=>array('label'=>'录入人', 'editable'=>false),
            'updated'=>array('label'=>'录入时间')
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
