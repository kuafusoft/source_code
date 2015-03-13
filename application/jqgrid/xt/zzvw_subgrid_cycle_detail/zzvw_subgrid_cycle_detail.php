<?php
require_once(APPLICATION_PATH.'/jqgrid/xt/zzvw_cycle_detail/zzvw_cycle_detail.php');

class xt_zzvw_subgrid_cycle_detail extends xt_zzvw_cycle_detail{
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['linktype'] = 'infoLink';
		unset($this->options['list']['act']);
		$this->options['edit'] = array('codec_stream_id'=>array('label'=>'Stream'), 'd_code', 'ver', 'summary', 'precondition', 'steps', 'expected_result', 'auto_level_id', 'test_env_id', 
			'result_type_id', 'defect_ids', 'comment', 'issue_comment', 'new_issue_comment'=>array('edittype'=>'textarea'), 'duration_minutes');
        $this->options['gridOptions']['inlineEdit'] = false;
		$this->options['gridOptions']['subGrid'] = false;
        $this->options['ver'] = '1.0';
    }
	
	protected function getCond(){
		$cond['field'] = 'cycle_id';
		if(!empty($this->params['parent'])){
			$cond['value'] = $this->params['parent'];
		}
		else if(!empty($this->params['filters'])){
			$filter = json_decode($this->params['filters']);
			foreach($filter as $k=>$v){
				if($v != 'AND'){
					foreach($v as $val){
						$f = 0;
						foreach($val as $kkk=>$data){
							if($data == $cond['field'])
								$f = 1;
							else if($data == 'id')
								$f = 2;			
							if($kkk == 'data'){
								if($f == 1)
									$cond['value'] = $data;
								else if($f == 2){
									$res = $this->db->query("select cycle_id from cycle_detail where id=".$data);
									if($row = $res->fetch())
										$cond['value'] = $row['cycle_id'];
								}
							}
						}
					}
				}
			}
		}
		else if($this->params['id']){
// print_r($this->params['id']);
			if(!(is_int($this->params['id'])) && !(is_array($this->params['id'])))
				$this->params['id'] = json_decode($this->params['id']);
// return;
			if(is_array($this->params['id']))
				$sql = "select cycle_id from ".$this->get('real_table')." where id in (".implode(", ", $this->params['id']).")";
			else
				$sql = "select cycle_id from ".$this->get('real_table')." where id = ".$this->params['id'];
			$res = $this->db->query($sql);
			if($info = $res->fetch())
				$cond['value'] = $info['cycle_id'];
		}
		$this->params['cond'] = $cond;
// // print_r($cond);
		// return $cond;
	}
	
	public function getButtons(){
		$btns = parent::getButtons();
		if(isset($this->params['cond']['value'])){
			if(($this->params['cond']['field'] == 'cycle_id') && $this->params['cond']['value']){
				$cycle = '';
				$roleAndStatus = $this->roleAndStatus('cycle', $this->params['cond']['value'], 0, array('status'=>'cycle_status_id', 'assistant_owner'=>'assistant_owner_id'));
				$role = $roleAndStatus['role'];
				if(isset($roleAndStatus['status'])){
					$status = $roleAndStatus['status'];
					if($status == CYCLE_STATUS_ONGOING){
						switch($role){								
							case 'tester':
								if(!empty($roleAndStatus['assistant_owner']) && $roleAndStatus['assistant_owner'] == $this->userInfo->id){
									unset($btns['add_del_trickmode']);
									unset($btns['add_del_env']);
									unset($btns['update_ver']);
								}
								break;
								
							case 'owner':
							case 'admin':
								unset($btns['add_del_trickmode']);
								unset($btns['add_del_env']);
								unset($btns['update_ver']);
								break;
						}	
					}	
				}
			}
		}
		return $btns;
	}
	
	public function fillOptions(&$columnDef, $db, $table){
// print_r($this->params);
		if(!empty($this->params['condMap'])){
			foreach($this->params['condMap'] as $k=>$v){
				if($k == 'id'){
					$id = $v['value'];
				}
			}
		}
		$sql = "SELECT DISTINCT {$table}_id FROM ".$this->get('table')." WHERE ".$this->params['cond']['field']." = ".$this->params['cond']['value'];
		$res = $this->db->query("select codec_stream_id from cycle_detail where id = ".$id);	
		$this->_fillOptions($columnDef, $db, $table, $sql);
	}
}

?>