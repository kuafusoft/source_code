<?php
require_once('table_desc.php');

class xt_testcase_ver extends table_desc{
	protected $prj_exist = false;
	protected $where_testcase_id = array();
    protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$cart_data = new stdClass;
		$cart_data->filters = '{"groupOp":"AND","rules":[{"field":"isactive","op":"eq","data":1}]}';
		$this->options['list'] = array(
			'id', 
			'testcase_id'=>array('hidden'=>true),
			'ver'=>array('formatter'=>'updateViewEditPage'), 
			'update_from',
			'objective', 
			'precondition', 
			'steps'=>array('rows'=>15), 
			'expected_result'=>array('label'=>'Expected Result'), 
			'command', 
			'resource_link'=>array('hidden'=>true), 
			'config'=>array('label'=>'Configure', 'hidden'=>true),
			'auto_level_id'=>array('label'=>'Auto Level'), 
			'testcase_priority_id'=>array('label'=>'Priority'), 
			'auto_run_minutes'=>array('hidden'=>true, 'label'=>'Auto Run Mins'), 
			'manual_run_minutes'=>array('hidden'=>true, 'label'=>'Manual Run Mins'),
			// 'os_id'=>array('editable'=>false),
			'prj_ids'=>array('label'=>'Project', 'name'=>'prj_ids', 'index'=>'prj_ids', 'editable'=>true, 'editrules'=>array('required'=>true), 'type'=>'cart', 'cart_db'=>'xt', 'cart_table'=>'zzvw_prj', 'cart_data'=>json_encode($cart_data)),
			'history_prj_ids'=>array('label'=>'Unlinked Project', 'name'=>'history_prj_ids', 'index'=>'history_prj_ids', 
				'editable'=>false, 'formatter'=>'ids', 'formatoptions'=>array('db'=>'xt', 'table'=>'prj'), 'data_source_db'=>'xt', 'data_source_table'=>'prj'),
			'comment',
			'issue_comment',
			'update_comment'=>array('hidden'=>true, 'editable'=>false), 
			'review_comment'=>array('hidden'=>true, 'editable'=>false), 
			'edit_status_id'=>array('label'=>'Edit Status', 'editable'=>false), 
			'owner_id'=>array('hidden'=>true), 
			'updater_id'=>array('hidden'=>true), 'updated'=>array('hidden'=>true), 
		);
		$this->options['edit'] = array(
			'ver'=>array('editable'=>false), 'objective', 'precondition', 'steps', 'expected_result', 'command',  
			'resource_link', 'config', 'auto_level_id', 'testcase_priority_id', 'auto_run_minutes', 'manual_run_minutes',
			'prj_ids', 'comment', 'issue_comment', 'update_comment', 'review_comment', 
			'owner_id', 'edit_status_id', 'updater_id', 'updated'
		);
		$this->options['caption'] = 'Testcase Version';
		$this->options['gridOptions']['inlineEdit'] = false;
		$this->parent_table = 'testcase';
		
		$this->listTags = false;
		$this->linkTables = array('node_ver_m2m'=>array('prj'));
    } 
	
	public function getRowRole($table_name = '', $id = 0){
		$role = parent::getRowRole($table_name, $id);
		$userId = $this->userInfo->id;
		$row = array();
		if(empty($id))
			$id = isset($this->params['ver']) ? $this->params['ver'] : (isset($this->params['id']) ? $this->params['id'] : 0);
		$res = $this->tool->query("SELECT * FROM testcase_ver WHERE id=$id");
		if($row = $res->fetch()){
			$userId = $this->userInfo->id;
			if($row['edit_status_id'] != EDIT_STATUS_PUBLISHED && $row['edit_status_id'] != EDIT_STATUS_GOLDEN && $row['updater_id'] == $userId)
				$role = 'row_owner';
		}
// print_r($role);
		return $role;
	}

	public function accessMatrix(){
		$access_matrix = parent::accessMatrix();
		$access_matrix['admin']['ver_abort'] = $access_matrix['admin']['ver_ask2review'] = $access_matrix['admin']['publish'] = 
			$access_matrix['admin']['ask2review'] = $access_matrix['admin']['review'] = true;
		$access_matrix['row_owner'] = $access_matrix['admin'];
		$access_matrix['all']['ver_diff'] = $access_matrix['all']['diff'] = true;
		return $access_matrix;
	}
	
	protected function handleFillOptionCondition(){
		$where_testcase_id = array();
		if(!empty($this->params['searchConditions'])){
			$searchConditions = $this->params['searchConditions'];
			foreach($searchConditions as $condition){
				switch($condition['field']){
					case 'testcase_id':
						$where_testcase_id = $condition;
						break;
				}
			}
		}
		if(!empty($where_testcase_id)){
			// testcase_id
			$condition = $where_testcase_id;
			$condition['field'] = 'id';
			$this->fillOptionConditions['testcase_id'] = array($condition);
			
			// // prj_id
			$vp = $this->tool->getFieldValues('prj_id', 'prj_testcase_ver', "testcase_id in ({$where_testcase_id['value']})");
			$condition = array('field'=>'id', 'op'=>'IN', 'value'=>$vp);
			$this->fillOptionConditions['prj_ids'] = array($condition);
			
			// // owner
			$vp = $this->tool->getFieldValues('owner_id', 'testcase_ver', "testcase_id in ({$where_testcase_id['value']})");
			$condition = array('field'=>'id', 'op'=>'IN', 'value'=>$vp);
			$this->fillOptionConditions['owner_id'] = array($condition);
			
			// // updater
			// $vp = $this->getFieldValues('updater_id', 'testcase_ver', "testcase_id in ({$where_testcase_id['value']})");
			// $condition = array('field'=>'id', 'op'=>'IN', 'value'=>$vp);
			// $this->fillOptionConditions['updater_id'] = array($condition);
			
			// // priority_id
			$vp = $this->tool->getFieldValues('testcase_priority_id', 'testcase_ver', "testcase_id in ({$where_testcase_id['value']})");
			$condition = array('field'=>'id', 'op'=>'IN', 'value'=>$vp);
			$this->fillOptionConditions['testcase_priority_id'] = array($condition);
			
			// // auto level
			$vp = $this->tool->getFieldValues('auto_level_id', 'testcase_ver', "testcase_id in ({$where_testcase_id['value']})");
			$condition = array('field'=>'id', 'op'=>'IN', 'value'=>$vp);
			$this->fillOptionConditions['auto_level_id'] = array($condition);

			// // edit_status
			// $vp = $this->getFieldValues('edit_status_id', 'testcase_ver', "testcase_id in ({$where_testcase_id['value']})");
			// $condition = array('field'=>'id', 'op'=>'IN', 'value'=>$vp);
			// $this->fillOptionConditions['edit_status_id'] = array($condition);
		}
	}

    public function getMoreInfoForRow($row){
		if (!$this->prj_exist){
			$res = $this->tool->query("SELECT group_concat(prj_id) as prj_ids FROM prj_testcase_ver WHERE testcase_ver_id=".$row['id']);
			$prj = $res->fetch();
			$row['prj_ids'] = $prj['prj_ids'];
		}
		$res = $this->tool->query("SELECT group_concat(distinct prj_id) as history_prj_ids FROM prj_testcase_ver_history WHERE testcase_ver_id={$row['id']} AND act='remove'");
		if($his = $res->fetch()){
			$row['history_prj_ids'] = $his['history_prj_ids'];
		}
		return $row;
	}

	public function calcSqlComponents($params, $limited = true){
		$components = parent::calcSqlComponents($params, $limited);
		if(empty($components['order']))
			$components['order'] = 'id desc';
// print_r($components)			;
		return $components;
	}
	
	protected function getSpecialFilters(){
		return array('prj_testcase_ver.prj_id');
	}
	
	protected function specialSql($special, &$ret){
		foreach($special as $c){
			switch($c['field']){
				case 'prj_testcase_ver.prj_id':
					$this->prj_exist = true;
					$ret['main']['from'] .= " LEFT JOIN prj_testcase_ver ON testcase_ver.id=prj_testcase_ver.testcase_ver_id";
					$ret['main']['fields'] .= ", group_concat(distinct prj_testcase_ver.prj_id) as prj_ids";
					$ret['group'] = 'prj_testcase_ver.testcase_id';
					$ret['where'] .= ' AND '.$this->tool->generateLeafWhere($c);
					break;
			}
		}
	}
	// End of Calc SQL

    public function getButtons(){
        $buttons = array(
			'link2prj'=>array('caption'=>'Link to projects'),
			'unlinkfromprj'=>array('caption'=>'Unlink from projects'),
			'ver_abort'=>array('caption'=>'Abort the Versions'),
			'ver_ask2review'=>array('caption'=>'Ask to Review'),
			'ver_diff'=>array('caption'=>'Tell the difference'),
        );
        $buttons = array_merge($buttons, parent::getButtons());
		unset($buttons['tag']);
		unset($buttons['subscribe']);
		unset($buttons['add']);
		// unset($buttons['change_owner']);
		unset($buttons['removeFromTag']);
		unset($buttons['export']);
		return $buttons;
    }
}
