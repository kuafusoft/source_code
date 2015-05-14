<?php 
require_once(APPLICATION_PATH.'/jqgrid/action_save.php');
require_once('const_def_qygl.php');

class qygl_zzvw_yw_fh_detail_action_save extends action_save{
	protected function newRecord($db, $table, $pair){
// print_r($pair);		
		$affectedID = 0;
		$this->fillDefaultValues('new', $pair, $db, $table);
		$real_table = $this->table_desc->get('real_table');
		switch($pair['pici_id']){
			case -1: //所有批次的都发走
				//查找所有批次
				$sql = "SELECT zzvw_pici_scdj.*, dingdan.price ".
					"FROM zzvw_pici_scdj left join dingdan on zzvw_pici_scdj.wz_id=dingdan.wz_id AND zzvw_pici_scdj.gx_id=".GX_LAST;
// print_r($sql);
				$res = $this->tool->query($sql);
				while($row = $res->fetch()){
// print_r($row);
					$each = $pair;
					$each['pici_id'] = $row['id'];
					$each['amount'] = $row['remained'];
// print_r($each);					
					$affectedID = $this->tool->insert($real_table, $each, $db);
					$this->tool->update('pici', array('id'=>$row['id'], 'remained'=>0));
					//更新客户应收款情况
					$total_money = $row['price'] * $row['remained'];
					$this->tool->query("UPDATE hb set account_receivable=account_receivable + $total_money WHERE id={$pair['hb_id']}");
				}
				break;
			case -2: //先旧后新
			case -3: //先新后旧
				//查找所有批次
				$remained = $pair['amount'];
				$sql = "SELECT zzvw_pici_scdj.*, dingdan.price FROM zzvw_pici_scdj left join dingdan on zzvw_pici_scdj.wz_id=dingdan.wz_id AND zzvw_pici_scdj.gx_id=".GX_LAST;
				if($pair['pici_id'] == -2)
					$sql .= " ORDER BY zzvw_pici_scdj.happen_date ASC";
				else
					$sql .= " ORDER BY zzvw_pici_scdj.happen_date DESC";
					
				$res = $this->tool->query($sql);
				while($row = $res->fetch()){
					$each = $pair;
					$each['pici_id'] = $row['id'];
					$used = 0;
					if($row['remained'] <= $remained){
						$used = $row['remained'];
					}
					else{
						$used = $remained;
					}
					$each['amount'] -= $used;
					$remained -= $used;
					$affectedID = $this->tool->insert($real_table, $each, $db);
					$this->tool->query("update pici set remained=remained-$used WHERE id={$row['id']}");
					//更新客户应收款情况
					$total_money = $dingdan['price'] * $used;
					$this->tool->query("UPDATE hb set account_receivable=account_receivable + $total_money WHERE id={$pair['hb_id']}");
					if($remained <= 0)
						break;
				}
				break;
			default:
				$affectedID = $this->tool->insert($real_table, $pair, $db);
				break;
		}
		
// // print_r($pair);		
		// $this->db->insert($db.'.'.$table, $pair);
		// $affectedID = $this->db->lastInsertId();
		return $affectedID;
	}
}

?>