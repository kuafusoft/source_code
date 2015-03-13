<?php
require_once(APPLICATION_PATH.'/jqgrid/action_list.php');

class qygl_yw_action_list extends action_list{
	protected function filterParams(){
		$params = parent::filterParams();
//print_r($params['searchConditions']);		
		foreach($params['searchConditions'] as &$v){
			switch($v['field']){
				case 'happen_date':
// print_r($v);				
					$v['op'] = 'between';
					$value = $v['value'];
					list($y, $m) = explode('-', $value);
					$min = $value.'-01';
					if(in_array($m, array('1', '3', '5', '7', '8', '10', 12)))
						$max = $value.'-31';
					else if ($m == '2')
						$max = $value.'-29';
					else
						$max = $value.'-30';
					$v['value'] = array('min'=>$min, 'max'=>$max);
					break;
			}
			
		}
		return $params;
	}
	
	protected function getUnknownInfoForRow($row, $fields){
// print_r($fields);		
// print_r($row);				
		foreach($fields as $field){
			switch($field){
				case 'detail':
					$sql = '';
					switch($row['yw_fl_id']){
						case YW_FL_CG: //下采购单
						case YW_FL_JIESHOUDINGDAN: //接订单
							$sql = "SELECT dingdan.*".
								" FROM dingdan".
								" WHERE dingdan.yw_id={$row['id']}";
							break;
						case YW_FL_YUNRU: //运输
						case YW_FL_YUNCHU: //运输
							$sql = "SELECT yunshu.*, dingdan.wz_id".
								" FROM yunshu left join dingdan on yunshu.dingdan_id=dingdan.id".
								" WHERE yunshu.yw_id={$row['id']}";
							break;
						case YW_FL_SCDJ:
							$sql = "SELECT scdj.gx_id, scdj.wz_id, pici.amount ".
								" FROM scdj left join pici on scdj.pici_id=pici.id".
								" WHERE scdj.yw_id={$row['id']}";
							break;
						default:
							// $sql = "SELECT * FROM {$r['item_table']} WHERE wz_package_id={$row['wz_package_id']}";
							break;
					}
					if(!empty($sql)){
			// print_r($sql);		
						$res = $this->db->query($sql);
						$row['detail'] = $res->fetchAll();
					}
					break;
			}
		
		}
		return $row;
	}
}

?>