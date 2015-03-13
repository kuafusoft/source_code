<?php
defined('YWWL_FL_CG') || define('YWWL_FL_CG', 1); //采购
defined('YWWL_FL_FH') || define('YWWL_FL_FH', 2); //发货
defined('YWWL_FL_SCDJ') || define('YWWL_FL_SCDJ', 3);//生产登记
defined('YWWL_FL_ZF') || define('YWWL_FL_ZF', 4); //支付
defined('YWWL_FL_HK') || define('YWWL_FL_HK', 5); //回款
defined('YWWL_FL_TX') || define('YWWL_FL_TX', 6); //贴息
defined('YWWL_FL_JCK') || define('YWWL_FL_JCK', 7); //借出款项
defined('YWWL_FL_JRK') || define('YWWL_FL_JRK', 8); //借入款项
defined('YWWL_FL_GZ') || define('YWWL_FL_GZ', 9); //工资

defined('WZ_YUNSHU') || define('WZ_YUNSHU', 1);   //运输
defined('WZ_ZHUANGXIE') || define('WZ_ZHUANGXIE', 2); //装卸
    
defined('WZ_DL_YUANLIAO') || define('WZ_DL_YUANLIAO', 1); //原料
defined('WZ_DL_SHEBEI') || define('WZ_DL_SHEBEI', 2);     //设备
defined('WZ_DL_CHANPIN') || define('WZ_DL_CHANPIN', 3);     //产品
defined('WZ_DL_FUWU') || define('WZ_DL_FUWU', 4);     //服务

//合作伙伴
/*
    合作伙伴主要包括一下几类：
    1. 供应商，包括材料供应商、设备供应商、服务提供商
        主要的合作有：
        1）采购
        2）退货
        3）支付
        4）修改采购订单
    2. 客户
        主要的合作有：
        1）发货
        2）退货
        3）回款
        4）修改销售订单
        
    3. 员工
        主要的合作有：
        1）登记产量
        2）支付工资
        
    共同的合作有：
        1）借入资金
        2）借出资金
        3）交流
        
*/
class hzhb{
    var $db = NULL;
    var $currentUser = NULL;
    public function __construct($db, $currentUser){
        $this->db = $db;
        $this->currentUser = $currentUser;
    }
    
    public function action($act, $params = NULL){
        $ret = false;
        if (method_exists($this, $act)){
            $this->db->beginTransaction();
            try{
                $ret = $this->{$act}($params);
                $this->db->commit();
            }
            catch(Exception $e){
                $this->db->rollback();
                print_r($e->getMessage());
            }
        }
        return $ret;
    }
    
    public function info($params){
        // 基本信息
        $res = $this->db->query("SELECT * FROM vw_hzbh WHERE id={$params['hzhb_id']}");
        $info['base'] = $res->fetch();
        // 业务往来
        $res = $this->db->query("SELECT * FROM vw_ywwl WHERE hzhb_id={$params['hzhb_id']} ORDER BY happened_date DESC");
        $info['ywwl'] = $res->fetchAll();
        return $info;
    }
    //交流
    public function jl($params){
    
    }
    
    public function addRelatedWz($params){
        $id = 0;
        //首先检查hzhb_wz表里是否已经有了该连接，如果没有，则加入
        $res = $this->db->query("SELECT * FROM hzhb_wz WHERE hzbh_id={$params['hzhb_id']} AND wz_id={$params['wz_id']}");
        $row = $res->fetch();
        if (!$row){
            $this->db->insert('hzhb_wz', $params);
            $id = $this->db->lastInsertId();
        }
        else{
            $id = $row['id'];
        }
        return $id;
    }
    
    //获取关联的物资，对于供应商而言是可提供的原料或设备，对客户而言是需求的产品，对员工而言是生产的产品
    public function listRelatedWz($params){
        $res = $this->db->query("SELECT vw_wz.* FROM hzhb_wz LEFT JOIN vw_wz ON hzhb_wz.wz_id=vw_wz.id WHERE hzhb_wz.hzhb_id={$params['hzhb_id']}");
        return $res->fetchAll();                    
    }
    
    //借入资金
    protected function jr($params){
        return $this->zjwl($params, 1);
    }
    
    protectged function jc($params){
        return $this->zjwl($params, -1);
    }
    
    // 业务往来
    /* 
    所有的业务往来都针对合同，要么是签署合同，要么是实施合同。合同分为两类：
    1. 采购合同
        业务往来有：
        1）采购
        2）支付
        3）退货
        4）接收退货款
        
    2. 销售合同。
        业务往来有：
        1）发货
        2）收款
        3）接受退货
        4）支付退货款
        
    作为基类函数，ywwl操作包括：
    1. 在ywwl表中插入一条记录
    2. 更新合作伙伴的total_money
    3.         
    */
    protected function ywwl($ywwl, $in_out){
        $this->db->insert('ywwl', $ywwl);
        $this->db->query("UPDATE hzhb set total_money=total_money - ({$in_out} * {$ywwl['total_money']}) WHERE id={$ywwl['hzhb_id']}"); // 更新应付款
        return $this->db->lastInsertId();
    }
    
    
    //资金往来
    protected function zjwl($params, $in_out){
        $in_out_yw = array(1=>array(YWWL_FL_FH, YWWL_FL_JCK), -1=>array(YWWL_FL_CG, YWWL_FL_JRK, YWWL_FL_GZ));
        
        $ywwl = $params;
        $total_money = $params['total_money'];
        unset($ywwl['related_id']);
        //首先查找相应的业务往来记录
        //对于支付，则查找采购、借入款项等记录
        //对于回款，则查找发货、借出款项等记录
        $sql = "SELECT * FROM vw_ywwl WHERE hzhb_id={$params['hzhb_id']} AND paied_money<total_money".
            " AND ywwl_fl_id in (".implode(',', $in_out_yw[$in_out]).")";
        if(!empty($params['related_id']) && !is_array($params['related_id']))
            $params['related_id'] = array($params['related_id']);
        if (!empty($params['related_id']))
            $sql .= " AND id in (".implode(',', $params['related_id']).")";
        $sql .= " ORDER BY happened_date ASC";
        $res = $this->db->query($sql);
        while($row = $res->fetch()){
            if ($total_money <= 0)
                break;
            $ywwl['related_id'] = $row['id'];
            if($total_money >= $row['total_money'] - $row['paied_money']){
                $ywwl['total_momey'] = $row['total_money'] - $row['paied_money'];
            }
            else
                $ywwl['total_money'] = $total_money;
            $total_money -= $ywwl['total_money'];
            $this->db->insert('ywwl', $ywwl);
            $this->db->update('ywwl', array('paied_money'=>$row['paied_money'] + $ywwl['total_money']), "id=".$row['id']);
        }
        $this->db->query("UPDATE hzhb set total_money=total_money - (".$in_out * $params['total_money'].") WHERE id={$params['hzhb_id']}");
        $this->db->query("UPDATE zjzh SET remained=remained + (".$in_out * $params['total_money'].") WHERE id={$params['zjzh_id']}");
        return true;
    }
    
    //物资往来
    protected function wzwl($params, $in_out){
        $ywwl = $params;
        unset($ywwl['yunshu']);
        unset($ywwl['zhuangxie']);
        //可能同时有多种物资被采购/发送，需要进行处理
        $wz = $ywwl;
        unset($wz['wz']);
        $ids = array();
        foreach($ywwl['wz'] as $v){
            $wz['wz_id'] = $v['wz_id'];
            $wz['price'] = $v['price'];
            $wz['amount'] = $v['amount'];
            $wz['paied_money'] = $v['paied_money'];
            $wz['hzhb_id'] = $v['hzhb_id'];
    
            $ids[] = $this->_wzwl($wz, $in_out);
        }

        if (!empty($params['yunshu'])){
            $yunshu = $params['yunshu'];
            $yunshu['wz_id'] = WZ_YUNSHU;
            $yunshu['related_id'] = $ids[0];
            $yunshu['note'] = "对应的物资往来记录编号为：".implode(',', $ids);
            $this->_wzwl($yunshu, 1);
        }
        
        if (!empty($params['zhuangxie'])){
            $zhuangxie = $params['zhuangxie'];
            $yunshu['wz_id'] = WZ_ZHUANGXIE;
            $zhuangxie['related_id'] = $ids[0];
            $yunshu['note'] = "对应的物资往来记录编号为：".implode(',', $ids);
            $this->_wzwl($zhuangxie, 1);
        }
        return $ywwl['id'];
    }

    protected function _wzwl($ywwl, $in_out){
        $wz_fields = array('wz_id', 'price', 'amount', 'paied_money');
        $ywwl_wz = array();
        foreach($wz_fields as $v){
            $ywwl_wz[$v] = $ywwl[$v];
            unset($ywwl[$v]);
        }
        $ywwl['id'] = $this->ywwl($ywwl, $in_out);

        $paied_money = $ywwl_wz['paied_money'];
        unset($ywwl_wz['paied_money']); //付款部分由支付部分处理
        $this->db->insert('ywwl_wz', $ywwl_wz);
        if (!empty($paied_money)){
            $zf = $ywwl;
            $zf['related_id'] = $ywwl['id'];
            $zf['total_money'] = $paied_money;
            $this->zf($zf);
        }
        // 更新库存信息
        $this->in_out($ywwl_wz, $in_out);

        return $ywwl['id'];
    }
    
    protected function in_out($ywwl_wz, $in_out){
        //如果物资是原料、设备、产品，则更新物资的库存量
        $res = $this->db->query("SELECT * FROM vw_wz WHERE id=".$ywwl_wz['wz_id']);
        $row = $res->fetch();
        if (in_array($row['wz_dl_id'], array(WZ_DL_CHANPIN, WZ_DL_SHEBEI, WZ_DL_YUANLIAO))){
            $updated = array('remained'=>$row['remained'] + $in_out * $ywwl_wz['amount']);
            $this->db->update('wz', $updated, "id=".$ywwl_wz['wz_id']);
        }
    
        if ($row['wz_dl_id'] == WZ_DL_CHANPIN){
            // 更新wz_cp表数据
            $updated = array();
            if ($in_out == 1) //入库
                $updated['produced'] =  $row['produced'] + $ywwl_wz['amount'];
            else // 发货
                $updated['sended'] =  $row['sended'] + $ywwl_wz['amount'];
            $this->db->update('wz_cp', $updated, "id=".$row['wz_cp_id']);

            if ($in_out == -1){ //发货,则还需要更新合同相关信息
                $sql = "SELECT ht_item.*, ht.fh_money FROM ht_item LEFT JOIN ht ON ht.id=ht_item.ht_id ".
                    " WHERE ht_item.wz_id={$ywwl_wz['wz_id']} AND ht.hzhb_id={$ywwl_wz['hzhb_id']} AND ht_item.amount > ht_item.fh_amount";
                if (empty($ywwl_wz['ht_item']))
                    $sql .= " AND item_id in (".implode(',', $ywwl_wz['ht_item']).")";
                $sql .= " ORDER BY ht.happened_date ASC";                
                $res = $this->db->query($sql);
                $cp_amount = $ywwl_wz['amount'];
                while($row = $res->fetch()){
                    if ($cp_amount < 0)
                        break;
                    if ($row['amount'] - $row['fh_amount'] > $cp_amount)
                        $sended = $cp_amount;
                    else
                        $sended = $row['amount'] - $row['fh_amount'];
                    $updated = array('fh_amount'=>$sended + $row['fh_amount']);
                    $this->db->update('ht_item', $updated, "id=".$row['id']);
                    $this->db->update('ht', array('fh_money'=>$row['fh_money'] + $sended * $row['price']), "id=".$row['ht_id']);
                    $cp_amount -= $sended;
                }
            }
        }
        return true;
    }
    
}

//供应商
class gys extends hzhb{
    /*
    采购：
    涉及的表：
        ywwl--需要增加一条采购记录
        ywwl_wz--需要增加一条采购对应的详细记录
        wz--更新购入物资的库存量
        hzhb--需要更改Total_money字段
        zjzh--如果有付款，则需要一次支付操作
    需要的参数：
        1. 采购对象：hzhb_id
        2. 采购金额：total_money
        3. 采购日期：happened_date
        4. 对应的采购记录：【】或related_id
        5. 经办人：jbr_id
        6. 付款凭证编号：ticket_no
        7. 付款凭证扫描件：ticket_img
        8. 录入人：creater_id
        9. 采购的物资：wz_id
        10. 采购单价：price
        11. 采购数量：amount
    */
    public function cg($params){
        $params['ywwl_fl_id'] = YWWL_FL_CG;
        return $this->wzwl($params, 1);
    }
    //支付采购款
    public function zf($params){
        $params['ywwl_fl_id'] = YWWL_FL_ZF;
        return $this->zjwl($params, 1);
    }
}

//客户
class kh extends hzhb{
    //发货
    public function fh($params){
        $params['ywwl_fl_id'] = YWWL_FL_FH;
        return $this->wzwl($params, -1);
    }
    
    //退货
    public function th($params){
    
    }
    
    /*
    回款：
    涉及的表：
        ywwl--需要增加一条回款记录
            --需要更新支付对应的发货记录的Paied_money字段
        hzhb--需要更改Total_money字段
        zjzh--需要更新对应资金账号的remained字段
    需要的参数：
        1. 支付对象：hzhb_id
        2. 支付金额：total_money
        3. 支付日期：happened_date
        4. 对应的采购记录：【】或related_id
        5. 经办人：jbr_id
        6. 付款凭证编号：ticket_no
        7. 付款凭证扫描件：ticket_img
        8. 录入人：creater_id
    注意点：
        一次可以支付多条采购记录，如果Total_money可能并不是多条记录的总和，需要进行处理。
    */
    public function hk($params){ //
        $params['ywwl_fl_id'] = YWWL_FL_HK;
        return $this->zjwl($params, -1);
    }
    
    //订单处理，主要为增加订单或减少订单
    
    /*
    一般签订一个生产合同会确定一个总量，之后按期交付。这个交付方式如何表达？
    
    参数：
    1. hzhb_id
    2. wz_id
    3. amount
    4. price
    5. last_day
    6. fh_fl_id
    7. next_fh_date
    8. fh_days
    */
    public function ht($params){
        if (!empty($params['id'])){ // 修改合同
            
            
        
        }
        else{ //新增合同
        
        }
    }
}

//员工
class yg extends hzhb{
    //详细信息
    public function info($params){
        //基本信息
        $info = parent::info($params);
        //技能
        $res = $this->db->query("SELECT hzhb_yg_skill.*, skill.name as skill, grade.name as grade ".
            " FROM hzhb_yg_skill left join skill ON hzhb_yg_skill.skill_id=skill.id".
            " left join grade ON hzhb_yg_skill.grade_id=grade.id".
            " where hzhb_yg_skill.hzhb_id={$params['hzhb_id']}");
        $info['skill'] = $res->fetchAll();
        //
        
        return $info;
    }
    
    /*
    生产登记:
        生产过程的数据首先保存在sc_daily里，而不是ywwl表，主要是因为ywwl没有维护工序信息，而同一个产品在不同的工序有不同
        形态和价格，用ywwl表维护似乎不太合适。暂时就决定用ywwl表维护可销售的产品，而从生产信息表带ywwl表需要一个入库动作。
        
        涉及的表：
            wz:更新相关物资的库存量，主要是原材料，中间产品的库存情况保存在sc_ck表里
            hzhb:更新Total_money
            sc_daily:记录每天的生产数据
            sc_ck:产品的当前库存
            
        需要的参数信息：
            1. 生产者：hzhb_id
            2. 工序：gx_id 
            3. 产品：wz_id
            4. 数量：good_amount, inferio_amount, bad_amount
            7. 日期：happened_date
            8. 经办人：jbr_id
            9. 录入人：creater_id
        
        处理要点：
            1. 增加产品数量
            2. 对于生产过程中使用的物资减少相应数量      
    */
    public function scdj($params){ //生产登记
        $res = $this->db->query("SELECT * FROM vw_gx_wz_price WHERE gx_id=:gx_id AND wz_id=:wz_id", $params);
        $gx_wz_price = $res->fetch();
        $params['gx_wz_price_history_id'] = $gx_wz_price['gx_wz_price_history_id'];
        $this->db->insert('sc_daily', $params);
        $id = $this->db->lastInsertId();
        // 更新生产库存，检验合格后更新物资库存
        $this->db->query("UPDATE sc_ck SET good_amount=good_amount + :good_amount, inferio_amount=inferio_amount + :inferio_amount WHERE gx_id=:gx_id AND wz_id=:wz_id", $params);
        // 更新工资信息？
//        $this->db->
        return $id;
    }
    
    //生成工资单
    /*
    hzhb_id
    from_date
    end_date
    original
    finally
    note
    */
    public function payslip($params){
        
    }
    
    //支付工资
    public function pay($params){
        
    }
}

?>
