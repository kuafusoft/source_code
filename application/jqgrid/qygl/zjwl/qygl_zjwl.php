<?php

require_once('jqgridmodel.php');

class qygl_zjwl extends jqGridModel{
    public function init($controller, array $options = null){
        $options['db'] = 'qygl';
        $options['table'] = 'zjwl';
        
        $options['relations']['belongsto'] = array('hzhb', 'zjzh', 'zjwl_fl', 'zj_fl');

        $options['columns'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            '*',
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

    public function contextMenu(){
        $menu = array('transfer'=>'Transfer');
        return array_merge($menu, parent::contextMenu());
    }
    
    public function transfer(){
        $params = $this->tool->parseParams();
//        print_r($params);
        if($this->controller->getRequest()->isPost()){
            $this->db->query('UPDATE zjzh SET remained=remained-'.$params['amount'].' WHERE id='.$params['element']);
            $this->db->query('UPDATE zjzh SET remained=remained+'.$params['amount'].' WHERE id='.$params['input_account']);
        }
        else{
            $view_params = array();
            $res = $this->db->query("SELECT * FROM zjzh");
            while($row = $res->fetch()){
                if ($row['id'] == $params['element']){
                    $view_params['original'] = $row;
                }
                else{
                    $view_params['accounts'][] = $row;
                }
            }
            $this->renderView('zjzh_transfer.php', $view_params);
        }
    }
    
}
