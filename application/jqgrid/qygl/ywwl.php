<?php
defined(YWWL_FL_ZF) || define('YWWL_FL_CG', 1); //采购
defined(YWWL_FL_ZF) || define('YWWL_FL_FH', 2); //发货
defined(YWWL_FL_ZF) || define('YWWL_FL_SCDJ', 3);//生产登记
defined(YWWL_FL_ZF) || define('YWWL_FL_ZF', 4); //支付
defined(YWWL_FL_ZF) || define('YWWL_FL_HK', 5); //回款
defined(YWWL_FL_ZF) || define('YWWL_FL_TX', 6); //贴息

defined(WZ_YUNSHU) || define('WZ_YUNSHU', 1);   //运输
defined(WZ_ZHUANGXIE) || define('WZ_ZHUANGXIE', 2); //装卸
    
defined(WZ_DL_YUANLIAO) || define('WZ_DL_YUANLIAO', 1); //原料
defined(WZ_DL_SHEBEI) || define('WZ_DL_SHEBEI', 2);     //设备
defined(WZ_DL_CHANPIN) || define('WZ_DL_CHANPIN', 3);     //产品
    
class ywwl{
    var $db;
    public function __construct($db){
        $this->db = $db;
    }
    
    /*
    采购：
    涉及的表：
        ywwl--需要增加一条采购记录
        ywwl_wz--需要增加一条采购对应的详细记录
        wz--更新购入物资的库存量
        hzhb--需要更改Total_money字段
        zjzh--如果有付款，则需要一次支付操作
    需要的参数：
        1. 支付对象：hzhb_id
        2. 支付金额：total_money
        3. 支付日期：happened_date
        4. 对应的采购记录：【】或related_id
        5. 经办人：jbr_id
        6. 付款凭证编号：ticket_no
        7. 付款凭证扫描件：ticket_img
        8. 录入人：creater_id
        9. 采购的物资：wz_id
        10. 采购单价：price
        11. 采购数量：amount
    */
    public function cg($params){ //
        $ret = true;
        $this->db->beginTransaction();
        try{
            $params['ywwl_fl_id'] = YWWL_FL_CG;
            $this->wzwl($params, 1);
            $this->db->commit();
        }
        catch(Exception $e){
            $this->db->rollback();
            $ret = false;
            print_r($e->getMessage());
        }
        return $ret;
    }
    
    /*
    发货：
    涉及的表：
        ywwl--需要增加一条发货记录
        ywwl_wz--需要增加一条发货对应的详细记录
        wz--更新发货物资的库存量
        hzhb--需要更改Total_money字段
        zjzh--如果有回款，则需要一次回款操作
    需要的参数：
        1. 发货对象：hzhb_id
        2. 发货金额：total_money
        3. 发货日期：happened_date
        5. 经办人：jbr_id
        6. 发货凭证编号：ticket_no
        7. 发货凭证扫描件：ticket_img
        8. 录入人：creater_id
        9. 发货的物资：wz_id
        10. 发货单价：price
        11. 发货数量：amount
    */
    public function fh($params){ //发货
        $ret = true;
        $this->db->beginTransaction();
        try{
            $params['ywwl_fl_id'] = YWWL_FL_FH;
            $this->wzwl($params, -1);
            $this->db->commit();
        }
        catch(catch(Exception $e){
            $this->db->rollback();
            $ret = false;
            print_r($e->getMessage());
        }
        return $ret;
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
    }
    
    /*
    支付：
    涉及的表：
        ywwl--需要增加一条支付记录
            --需要更新支付对应的采购记录的Paied_money字段
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
    public function zf($params){ //支付
        $params['ywwl_fl_id'] = YWWL_FL_ZF;
        return $this->zjwl($params, 1);
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
    
    protected function zjwl($params, $in_out){
        $ret = true;
        $this->db->beginTransaction();
        try{
            $ywwl = $params;
            $total_money = $params['total_money'];
            unset($ywwl['related_id']);
            //首先查找相应的物资往来记录
            //对于支付，则查找采购记录
            //对于回款，则查找发货记录
            $sql = "SELECT * FROM vw_ywwl WHERE hzhb_id={$params['hzhb_id']} AND paied_money<total_money";
            if ($in_out == 1){ //回款
                $sql .= " AND ywwl_fl_id in (".YWWL_FL_FH.")"; //发货记录
            }
            else{
                $sql .= " AND ywwl_fl_id in (".YWWL_FL_CG.")";
            }
            if(!empty($params['related_id'] && !is_array($params['related_id']))
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
            $this->db->commit();
        }
        catch(catch(Exception $e){
            $this->db->rollback();
            $ret = false;
            print_r($e->getMessage());
        }
        return $ret;
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
        $ywwl['id'] = $this->_ywwl($ywwl, $in_out);

        $paied_money = $ywwl_wz['paied_money'];
        unset($ywwl_wz['paied_money']); //付款部分由支付部分处理
        $this->db->insert('ywwl_wz', $params);
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
    
    protected function _ywwl($ywwl, $in_out){
        $this->db->insert('ywwl', $ywwl);
        $this->db->query("UPDATE hzhb set total_money=total_money - ({$in_out * $ywwl['total_money']}) WHERE id={$ywwl['hzhb_id']}"); // 更新应付款
        return $this->db->lastInsertId();
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
                $while($row = $res->fetch()){
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
    }
    
}
