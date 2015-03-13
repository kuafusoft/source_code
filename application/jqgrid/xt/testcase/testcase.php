<?php
require_once('table_desc.php');

class xt_testcase extends table_desc{
	protected $prj_exist = false;
	protected $testcase_type_ids = '';
	protected $testcase_module_ids = '';
	protected $os_ids = '';
	protected $board_type_ids = '';
	protected $prj_ids = '';
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['linktype'] = 'infoLink_ver';
		$this->options['list'] = array('id'=>array('hidden'=>true), 
			'code'=>array('label'=>'Name'), 
			'summary', 
			'prj_ids'=>array('label'=>'Project', 'hidden'=>true), 
			'testcase_type_id'=>array('label'=>'Type', 'editrules'=>array('required'=>true)), 
			'testcase_source_id'=>array('label'=>'Source', 'hidden'=>true), 
			'testcase_category_id'=>array('label'=>'Category'), 
			'testcase_testpoint_id'=>array('label'=>'Testpoint', 'hidden'=>true), 
			'testcase_module_id'=>array('label'=>'Module'), 
			'auto_level_id'=>array('from'=>'xt.testcase_ver', 'label'=>'Auto Level', 'formatter'=>'ids'), 
			'testcase_priority_id'=>array('from'=>'xt.testcase_ver', 'label'=>'Priority', 'formatter'=>'ids'),
			'edit_status_id'=>array('from'=>'xt.testcase_ver', 'label'=>'Edit Status', 'formatter'=>'ids'),
			'ver_ids'=>array('label'=>'Versions', 'hidden'=>true, 'hidedlg'=>true, 'formatter'=>'text'),
			'owner_id'=>array('from'=>'xt.testcase_ver', 'label'=>'Owner', 'hidden'=>true, 'formatter'=>'ids', 'edittype'=>'select'),			
			'last_run'=>array('label'=>'Last Run Since'), 
			'command'=>array('from'=>'xt.testcase_ver', 'hidden'=>true), 
			'isactive'
		);
		$this->options['query'] = array(
			'buttons'=>array(
				'query_new'=>array('label'=>'New', 'onclick'=>'XT.go("/jqgrid/jqgrid/newpage/1/oper/information/db/xt/table/testcase/element/0")', 'title'=>'Create New Testcase'),
				'query_import'=>array('label'=>'Upload', 'onclick'=>'xt.testcase.import()', 'title'=>'Import Testcase'),
				'query_report'=>array('label'=>'Report', 'title'=>'Generate Reports'),
			), 
			'normal'=>array('key'=>array('label'=>'Keyword'), 'testcase_type_id', 
				'testcase_module_id'=>array('type'=>'single_multi', 'init_type'=>'single',
					'single_multi'=>array('db'=>$db, 'table'=>'testcase_module', 'label'=>'Testcase Module'), 
				), 
				'os_id'=>array('label'=>'OS'), 'chip_id', 'board_type_id'=>array('label'=>'Board Type'), 
				'testcase_category_id', 
				'prj_id'=>array('label'=>'Project', 'type'=>'single_multi', 'init_type'=>'single', 
					'single_multi'=>array('db'=>$db, 'table'=>'prj', 'label'=>'Project')), 
				'owner_id', 'last_run', 'isactive'), 
			'advanced'=>array('auto_level_id'=>array('label'=>'Auto Level', 'type'=>'checkbox', 'colspan'=>1, 'init_type'=>'single', 'single_multi'=>array('db'=>$db, 'table'=>'auto_level', 'label'=>'Auto Level')), 
				'edit_status_id'=>array('label'=>'Status', 'colspan'=>2), 
				'testcase_priority_id'=>array('label'=>'Priority', 'colspan'=>2))
		);
		if(isset($this->params['container'])){
			switch($this->params['container']){
				case 'div_case_add':
					unset($this->options['query']['buttons']);
					$this->options['query']['normal'] = array('key'=>array('label'=>'Keyword'), 'prj_id'=>array('label'=>'Project', 'type'=>'single_multi', 'init_type'=>'single', 
						'single_multi'=>array('db'=>$db, 'table'=>'prj', 'label'=>'Project')), 
						'testcase_type_id'=>array('type'=>'single_multi', 'init_type'=>'single',
							'single_multi'=>array('db'=>$db, 'table'=>'testcase_type', 'label'=>'Testcase Type'), 
						), 'testcase_category_id', 
						'testcase_module_id'=>array('type'=>'single_multi', 'init_type'=>'single',
							'single_multi'=>array('db'=>$db, 'table'=>'testcase_module', 'label'=>'Testcase Module'), 
						),
						'testcase_priority_id'=>array('label'=>'Priority')
					);
					$this->options['query']['advanced'] = array('auto_level_id'=>array('label'=>'Auto Level'), 'last_run', 'isactive', 'edit_status_id'=>array('label'=>'Status'));
					break;
				case 'div_stream_action':
					unset($this->options['query']['buttons']);
					$this->options['query']['normal'] = array('key'=>array('label'=>'Keyword'), 'prj_id', 'testcase_type_id',
						'testcase_category_id', 'testcase_module_id','testcase_priority_id'=>array('label'=>'Priority')
					);
					$this->options['query']['advanced'] = array('auto_level_id'=>array('label'=>'Auto Level'), 'last_run', 'isactive', 'edit_status_id'=>array('label'=>'Status'));
					break;
				case 'select_cart':
					$this->options['query']['normal'] = array('key'=>array('label'=>'Keyword'), 'testcase_category_id', 'testcase_module_id',
						'testcase_priority_id'=>array('label'=>'Priority'), 'auto_level_id'=>array('label'=>'Auto Level'), 'last_run', 'isactive', 
						'edit_status_id'=>array('label'=>'Status'));
					unset($this->options['query']['buttons']);
					unset($this->options['query']['advanced']);
					break;
			}
		}
		$this->options['edit'] = array('code'=>array('label'=>'Name'), 'summary', 'testcase_type_id', 'testcase_module_id', 'testcase_testpoint_id', 
				'testcase_category_id', 'testcase_source_id', 'isactive');
				
		$this->options['navOptions']['refresh'] = false;
		
		$this->linkTables = array(
			'ver'=>array('testcase_ver')
		);
		$this->parent_table = 'testcase_testpoint';
		$this->parent_field = 'testcase_testpoint_id';
	}
	
	protected function getRowForRole($table_name = '', $id = 0){
		$row = array();
		if(!empty($this->params['ver'])){
			$ver = $this->params['ver'];
			if(!is_numeric($ver) && is_string($ver)){
				$ver = json_decode($ver, true);
			}
			if(is_array($ver))
				$ver = $ver[0];
			if(is_numeric($ver)){
				$res = $this->tool->query("SELECT * FROM testcase_ver WHERE id=$ver");
				$row = $res->fetch();
			}
		}
		return $row;
	}
	
	protected function getRowRoleMatrix($row){
		$matrix = parent::getRowRoleMatrix($row);
		unset($matrix['row_owner']);
		$userId = $this->userInfo->id;
		if($row['edit_status_id'] != EDIT_STATUS_PUBLISHED && $row['edit_status_id'] != EDIT_STATUS_GOLDEN){
			if($row['updater_id'] == $userId)
				$matrix[] = 'row_owner';
		}
		elseif(in_array('tester', $this->userInfo->roles)){ //只有publish的Version可以编辑，从而生成一个新的Version
			$matrix[] = 'row_ver_newer';
		}
		return $matrix;
	}
	
	public function getRowRole($table_name = '', $id = 0){
		$role = parent::getRowRole($table_name, $id);
// print_r("role = ");
// print_r($role);		
		if(!empty($this->params['ver'])){
			$ver = $this->params['ver'];
			if(!is_numeric($ver) && is_string($ver)){
				$ver = json_decode($ver, true);
			}
			if(is_array($ver))
				$ver = $ver[0];
			if(is_numeric($ver)){
				$res = $this->tool->query("SELECT * FROM testcase_ver WHERE id=$ver");
				if($row = $res->fetch()){
					$userId = $this->userInfo->id;
					if($row['edit_status_id'] != EDIT_STATUS_PUBLISHED && $row['edit_status_id'] != EDIT_STATUS_GOLDEN){
						if($row['updater_id'] == $userId)
							$role[] = 'row_owner';
					}
					elseif(in_array('tester', $this->userInfo->roles)){ //只有publish的Version可以编辑，从而生成一个新的Version
						$role[] = 'row_ver_newer';
					}
				}
			}
		}
		// if(empty($role)){
			// if(in_array('tester', $this->userInfo->roles)){ //只有publish的Version可以编辑，从而生成一个新的Version
				// $role = 'row_ver_newer';
			// }
		// }
// print_r("role = ".$role);		
		return $role;
	}

	// public function accessMatrix(){
		// // $access_matrix = array('tester'=>array('all'=>false));
		// $access_matrix = parent::accessMatrix();
		// //检测是否存在access.xml
		// // $access_file = realpath(dirname(__FILE__))."/access.xml";
		// // if(file_exists($access_file)){
			// // $access = new Zend_Config_Xml($access_file);
			// // $access_matrix = array_merge($access_matrix, $access);
		// // }
		// // return $access_matrix;
		
		// $access_matrix['Dev'] = $access_matrix['normal'] = 
			// array('all'=>false, 'index'=>true, 'query'=>true, 'list'=>true, 'information'=>true, 'update_information_page'=>true);
		// $access_matrix['tester']['view_edit_abort'] = $access_matrix['tester']['view_edit_ask2review'] = 
			// $access_matrix['tester']['view_edit_publish'] = $access_matrix['tester']['view_edit_edit'] = false;
		// $access_matrix['row_ver_newer'] = $access_matrix['tester'];
		// $access_matrix['row_ver_newer']['view_edit_edit'] = true;
		// $access_matrix['row_ver_newer']['query_new'] = true;
		// $access_matrix['row_ver_newer']['query_import'] = true;
		// $access_matrix['row_ver_newer']['query_report'] = true;
		// return $access_matrix;
	// }

	protected function setFieldsLimit(){//如果有最大范围限制，则应返回id的列表，如1,2,3,4等
		$limites = array();

		$params = array('xt'=>array(
			'testcase_type'=>array(),
			'testcase_module'=>array('testcase_type_id'),
			'os'=>array('testcase_type_id'),
			'prj'=>array('os_id')
			)
		);
		foreach($params as $db=>$db_data){
			foreach($db_data as $tb=>$tb_data){
				$p = array('db'=>$db, 'table'=>$tb);
				if(!empty($tb_data)){
					foreach($tb_data as $field=>$data){
						if(is_int($field)){
							$field = $data;
							$data = $limits[$field];
						}
						$p[$field] = $data;
					}
				}
				$list_action = actionFactory::get(null, 'list', $p);
				$list_action->setParams($p);
				$ret = $list_action->getList();
				foreach($ret as $row){
					$limits[$tb.'_id'][] = $row['id'];
					if($tb == 'prj'){
						$limits['chip_id'][] = $row['chip_id'];
						$limits['board_type_id'][] = $row['board_type_id'];
					}
				}
			}
		}
		
		foreach($limits as $field=>$d){
			$limits[$field] = array_unique($d);
		}
// print_r($limits);		
		return $limits;
	}

	protected function handleFillOptionCondition(){
		//根据用户所在的group来确定testcase_type的可选择范围
		$res = $this->tool->query("SELECT GROUP_CONCAT(distinct testcase_type_id) as testcase_type_ids FROM group_testcase_type WHERE group_id in ({$this->userInfo->group_ids})");
		$row = $res->fetch();
		$this->testcase_type_ids = $row['testcase_type_ids'];
		$this->fillOptionConditions['testcase_type_id'] = array(array('field'=>'id', 'op'=>'in', 'value'=>$row['testcase_type_ids']));
		//根据testcase_type来确定testcase_module的可选择范围
		$testcase_module_ids = array();
		if(!empty($this->testcase_type_ids)){
			$res = $this->tool->query("SELECT DISTINCT testcase_module_id from testcase_module_testcase_type WHERE testcase_type_id in ({$this->testcase_type_ids})");
			while($row = $res->fetch())
				$testcase_module_ids[] = $row['testcase_module_id'];
		}
		$this->testcase_module_ids = implode(',', $testcase_module_ids);
		$this->fillOptionConditions['testcase_module_id'] = array(array('field'=>'id', 'op'=>'in', 'value'=>$testcase_module_ids));
		//根据testcase_type来确定os的可选择范围
		$os_ids = array();
		if(!empty($this->testcase_type_ids)){
			$res = $this->tool->query("SELECT DISTINCT os_id from os_testcase_type WHERE testcase_type_id in ({$this->testcase_type_ids})");
			while($row = $res->fetch())
				$os_ids[] = $row['os_id'];
		}
		$this->os_ids = implode(',', $os_ids);
		$this->fillOptionConditions['os_id'] = array(array('field'=>'id', 'op'=>'in', 'value'=>$os_ids));
		// 根据os来确定Project以及相关的chip和board_type的选择范围
		$chip_ids = array();
		$board_type_ids = array();
		$prj_ids = array();
		if(!empty($os_ids)){
			$res = $this->tool->query("SELECT * FROM prj where os_id in (".implode(',', $os_ids).")");
			while($row = $res->fetch()){
				$chip_ids[] = $row['chip_id'];
				$board_type_ids[] = $row['board_type_id'];
				$prj_ids[] = $row['id'];
			}
			$chip_ids = array_unique($chip_ids);
			$board_type_ids = array_unique($board_type_ids);
			$prj_ids = array_unique($prj_ids);
		}
		$this->chip_ids = implode(',', $chip_ids);
		$this->board_type_ids = implode(',', $board_type_ids);
		$this->prj_ids = implode(',', $prj_ids);
		$this->fillOptionConditions['chip_id'] = array(array('field'=>'id', 'op'=>'in', 'value'=>$chip_ids));
		$this->fillOptionConditions['prj_id'] = array(array('field'=>'id', 'op'=>'in', 'value'=>$prj_ids));
		$this->fillOptionConditions['board_type_id'] = array(array('field'=>'id', 'op'=>'in', 'value'=>$board_type_ids));
// print_r($this->fillOptionConditions)		;
	}

	protected function getQueryFields($params){
//print_r($this->options['gridOptions']['colModel']);	
		parent::getQueryFields($params);
		if(!empty($this->options['query']['advanced']['testcase_priority_id'])){
			$this->options['query']['advanced']['testcase_priority_id']['edittype'] = 'checkbox';
			$this->options['query']['advanced']['testcase_priority_id']['cols'] = 6;
			$this->options['query']['advanced']['testcase_priority_id']['queryoptions']['value'] = '1,2,3';
		}
		else if(!empty($this->options['query']['normal']['testcase_priority_id'])){
			$this->options['query']['normal']['testcase_priority_id']['edittype'] = 'checkbox';
			$this->options['query']['normal']['testcase_priority_id']['cols'] = 6;
			$this->options['query']['normal']['testcase_priority_id']['queryoptions']['value'] = '1,2,3';
		}
		if(!empty($this->options['query']['advanced']['edit_status_id'])){
			$this->options['query']['advanced']['edit_status_id']['edittype'] = 'checkbox';
			$this->options['query']['advanced']['edit_status_id']['cols'] = 6;
			$this->options['query']['advanced']['edit_status_id']['queryoptions']['value'] = '1,2';
			if(isset($this->params['container'])){
				if($this->params['container'] == 'div_case_add' || $this->params['container'] == 'div_stream_action'){
					$this->options['query']['advanced']['edit_status_id']['cols'] = 2;
					$this->options['query']['advanced']['edit_status_id']['searchoptions']['value'] = array(2=>'Golden', 1=>'Published');
					$this->options['query']['advanced']['edit_status_id']['queryoptions']['value'] = '1,2';
				}
			}
		}
		if(!empty($this->params['container']) && $this->params['container'] == 'div_case_add' && !empty($this->params['parent'])){
			$res = $this->db->query("select prj_ids, testcase_type_ids from cycle where id=".$this->params['parent']);
			if($row = $res->fetch()){
				$cart_data = new stdClass;
				$cart_data->filters =  '{"groupOp":"AND","rules":[{"field":"id","op":"in","data":"'.$row['prj_ids'].'"}]}';
				$this->options['query']['normal']['prj_id']['single_multi']['data'] = json_encode($cart_data);
				$res0 = $this->db->query("select id, name from prj where id in (".$row['prj_ids'].")");
				$prj[0] = '';
				while($row0 = $res0->fetch())
					$prj[$row0['id']] = $row0['name'];
				if(!empty($this->options['query']['normal']['prj_id']))
					$this->options['query']['normal']['prj_id']['searchoptions']['value'] = $prj;
				$cart_data->filters =  '{"groupOp":"AND","rules":[{"field":"id","op":"in","data":"'.$row['testcase_type_ids'].'"}]}';
				$this->options['query']['normal']['testcase_type_id']['single_multi']['data'] = json_encode($cart_data);
				$res0 = $this->db->query("select id, name from testcase_type where id in (".$row['testcase_type_ids'].")");
				$type[0] = '';
				while($row0 = $res0->fetch())
					$type[$row0['id']] = $row0['name'];
				if(!empty($this->options['query']['normal']['testcase_type_id']))
					$this->options['query']['normal']['testcase_type_id']['searchoptions']['value'] = $type;
			}
			
		}
		else if(!empty($this->options['query']['normal']['edit_status_id'])){
			$this->options['query']['normal']['edit_status_id']['edittype'] = 'checkbox';
			$this->options['query']['normal']['edit_status_id']['cols'] = 2;
			$this->options['query']['normal']['edit_status_id']['searchoptions']['value'] = array(2=>'Golden', 1=>'Published');
			$this->options['query']['normal']['edit_status_id']['queryoptions']['value'] = '1,2';
		}
		if(!empty($this->options['query']['advanced']['isactive']))
			$this->options['query']['advanced']['isactive']['queryoptions']['value'] = '1';
		if(!empty($this->options['query']['normal']['isactive']))
			$this->options['query']['normal']['isactive']['queryoptions']['value'] = '1';
		return $this->options['query'];
	}
	
	protected function getButtons(){
        $buttons = array(
            'link2prj'=>array('caption'=>'Link to Projects',
                'buttonimg'=>'',
                'title'=>'Link to Projects or Drop from Projects'),
			'unlinkfromprj'=>array('caption'=>'Unlink From Projects'),
			'publish'=>array('caption'=>'Publish'),
			'change_owner'=>array('caption'=>'Change Owner', 'buttonimg'=>'', 'title'=>'Change the owner for the selected items'),
			'coversrs'=>array('caption'=>'Cover SRS')
			
//			'batch_edit'=>array('caption'=>'Batch Edit', 'title'=>'Batch Edit'),
        );
        $buttons = array_merge($buttons, parent::getButtons());
		unset($buttons['add']);
		return $buttons;
	}
}
?>