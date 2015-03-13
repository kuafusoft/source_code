<?php

require_once('jqgridmodel.php');

class qygl_vw_hzhb extends jqGridModel{
    public function init($controller, array $options = null){
        $options['db'] = 'qygl';
        $options['table'] = 'vw_hzhb';

        $options['columns'] = array(
            'id'=>array('editable'=>false, 'hidden'=>true),
            'name'=>array('label'=>'名称', 'editrules'=>array('required'=>true)),
            'hzhb_fl_id'=>array('label'=>'类别', 'editable'=>true),
            'hzhb_fl'=>array('label'=>'类别', 'editable'=>false),
            'identity_no'=>array('label'=>'证件号码'),
            'account_no'=>array('label'=>'账号'),
            'mobile_no'=>array('label'=>'手机'),
            'tele_no'=>array('label'=>'电话'),
            'other_contact_method'=>array('label'=>'其他联系方式'),
            'address'=>array('label'=>'地址'),
            'total_money'=>array('label'=>'应收/付金额'),
            'credit_level_id'=>array('label'=>'信用级别', 'hidden'=>true),
            'credit_level'=>array('label'=>'信用级别', 'editable'=>false),
            'credit_total'=>array('label'=>'信用额度', 'editable'=>false),
            'credit_duration'=>array('label'=>'账款周期', 'editable'=>false),
            'base_salary'=>array('label'=>'基本工资'),
            'ticheng_ratio'=>array('label'=>'提成比例'),
            'current_position_id'=>array('label'=>'工种', 'hidden'=>true),
            'position'=>array('label'=>'工种', 'editable'=>false),
            'isactive',
        );
        $options['ver'] = '1.0';
        $options['gridOptions']['subGrid'] = true;
        $options['navOptions']['edit'] = $options['navOptions']['del'] = false;
        $options['construct'] = 'vw_hzhb_construct';
//        ['onComplete'] = 'Just a Test';
//        $options['contextMenu'] = 'srs_contextMenu';
//print_r($options);
        parent::init($controller, $options);
    } 

    public function contextMenu(){
        $menu = array(
            'cg'=>'采购',
            'fh'=>'发货',
            'scdj'=>'生产登记',
            'th'=>'退货',
            'gz'=>'工资',
            'zj'=>'支付',
            'hk'=>'回款',
            'tj'=>'统计',
        );
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
    
    public function wl(){ // 资金往来
        $params = $this->tool->parseParams();
//        print_r($params);
        if($this->controller->getRequest()->isPost()){
            return $this->_wl($params);
        }
        else{
            $view_params = array();
            $res = $this->db->query("SELECT * FROM zjzh where id=".$params['element']);
            $view_params['zjzh'] = $res->fetch();
            
            $res = $this->db->query("SELECT * FROM hzhb where total_money<0");
            $view_params['hzhb'] = $res->fetchAll();
            
            $res = $this->db->query("SELECT * FROM zjwl_fl where in_out=-1");
            $view_params['zjwl_fl'] = $res->fetchAll();
            
            if($params['direct'] == '支付')
                $this->renderView('zjzh_zf.php', $view_params);
            else
                $this->renderView('zjzh_hk.php', $view_params);
        }
    }
    
    /*
    Params应包含以下信息：
    1. 支付对象：hzhb_id
    2. 支付数量：amount
    3. 资金帐户：zjzh_id
    4. 支付时间：happened_date
    5. 支付原因：zjwl_fl_id; 工资，采购款，货款
    6. 相关信息ID：related_id;当支付原因是采购款时，应将wzwl_id填入
    7. 经办人：jbr_id；
    8. 录入人：creater_id        
    */
    public function _wl($params){
        $this->db->insert('zjwl', $params);

        $res = $this->db->query("SELECT * FROM zjwl_fl WHERE id=".$params['zjwl_fl_id']);
        $zjwl_fl = $res->fetch();
        //更新帐户剩余款项信息
        $this->db->query("UPDATE zjzh SET remained=remained + :in_out * :amount where id=:id", array('in_out'=>$zjwl_fl['in_out'], 'amount'=>$params['amount'], 'id'=>$params['zjzh_id']));        

        // 更新hzhb的剩余款项信息
        $this->db->query("UPDATE hzhb SET total_money=total_money - :in_out * :amount WHERE id=:id", array('in_out'=>$zjwl_fl['in_out'], 'amount'=>$params['amount'], 'id'=>$params['hzhb_id']));

        return $this->db->lastInsertId();
    }
}
