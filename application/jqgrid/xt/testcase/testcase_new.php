<?php
require_once('table_desc.php');

class xt_testcase extends table_desc{
	protected $prj_exist = false;
	protected function init($db, $table, $params = array()){
		parent::init($db, $table, $params);
		$this->options['linktype'] = 'infoLink_ver';
		$this->options['list'] = array('id'=>array('hidden'=>true), 
			'code'=>array('label'=>'Name'), 
			'summary', 
			'prj_ids'=>array('label'=>'Project', 'hidden'=>true), 
			'testcase_type_id'=>array('label'=>'Type'), 
			'testcase_source_id'=>array('label'=>'Source', 'hidden'=>true), 
			'testcase_category_id'=>array('label'=>'Category'), 
			'testcase_testpoint_id'=>array('label'=>'Testpoint', 'hidden'=>true), 
			'testcase_module_id'=>array('label'=>'Module'), 
			'auto_level_ids'=>array('label'=>'Auto Level'), 
			'testcase_priority_ids'=>array('label'=>'Priority'),
			'ver_ids'=>array('label'=>'Versions', 'hidden'=>true, 'hidedlg'=>true, 'formatter'=>'text'),
			'owner_ids'=>array('label'=>'Owner', 'hidden'=>true),			
			'last_run'=>array('label'=>'Last Run Since'), 
			'command'=>array('hidden'=>true), 
			'isactive'
		);
		$this->options['query'] = array(
			'buttons'=>array(
				'new'=>array('label'=>'New', 'onclick'=>'XT.go("/jqgrid/jqgrid/newpage/1/oper/information/db/xt/table/testcase/element/0")', 'title'=>'Create New Testcase'),
				'import'=>array('label'=>'Upload', 'onclick'=>'xt.testcase.import()', 'title'=>'Import Testcase'),
			), 
			'normal'=>array('key'=>array('label'=>'Keyword'), 'testcase_type_id', 
				'testcase_module_id'=>array('type'=>'single_multi', 'init_type'=>'single',
					'single_multi'=>array('db'=>$db, 'table'=>'testcase_module', 'label'=>'Testcase Module'), 
				), 
				'os_id'=>array('label'=>'OS'), 'chip_id', 'board_type_id'=>array('label'=>'Board Type'), 
				'testcase_category_id', 
				'prj_id'=>array('label'=>'Project', 'type'=>'single_multi', 'init_type'=>'single', 
					'single_multi'=>array('db'=>$db, 'table'=>'prj', 'label'=>'Project')), 
				'owner_id'), 
			'advanced'=>array('auto_level_id'=>array('label'=>'Auto Level'), 'last_run', 'isactive', 'edit_status_id'=>array('label'=>'Status', 'colspan'=>2), 'testcase_priority_id'=>array('label'=>'Priority', 'colspan'=>2))
		);
		if(isset($this->params['container'])){
			if($this->params['container'] == 'div_case_add' || $this->params['container'] == 'div_stream_action'){
				unset($this->options['query']['buttons']);
				$this->options['query']['normal'] = array('key'=>array('label'=>'Keyword'), 'prj_id'=>array('label'=>'Project'), 
					'testcase_type_id', 'testcase_category_id', 
					'testcase_module_id'=>array('type'=>'single_multi', 'init_type'=>'single',
						'single_multi'=>array('db'=>$db, 'table'=>'testcase_module', 'label'=>'Testcase Module'), 
					),
					'testcase_priority_id'=>array('label'=>'Priority')
				);
				$this->options['query']['advanced'] = array('auto_level_id'=>array('label'=>'Auto Level'), 'last_run', 'isactive', 'edit_status_id'=>array('label'=>'Status'));
			}
			else if($this->params['container'] == 'select_cart'){
				$this->options['query']['normal'] = array('key'=>array('label'=>'Keyword'), 'testcase_category_id', 
					'testcase_priority_id'=>array('label'=>'Priority'), 'auto_level_id'=>array('label'=>'Auto Level'), 'last_run', 'isactive', 
					'edit_status_id'=>array('label'=>'Status'));
				unset($this->options['query']['buttons']);
				unset($this->options['query']['advanced']);
			}
		}
		$this->options['edit'] = array('code'=>array('label'=>'Name'), 'summary', 'testcase_type_id', 'testcase_module_id', 'testcase_testpoint_id', 
				'testcase_category_id', 'testcase_source_id', 'isactive');
				
		$this->options['navOptions']['refresh'] = false;
		
		$this->parent_table = 'testcase_testpoint';
		$this->parent_field = 'testcase_testpoint_id';
	}
	
	protected function getQueryFields(){
//print_r($this->options['gridOptions']['colModel']);	
		parent::getQueryFields();
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
	
	protected function getListFields(){
		return parent::getListFields();
		
	}
	
	public function calcSqlComponents($params, $limited = true){
		$components = parent::calcSqlComponents($params, $limited);
		$components['main']['from'] .= " LEFT JOIN testcase_ver on testcase.id=testcase_ver.testcase_id ".
			" LEFT JOIN prj_testcase_ver on testcase_ver.id=prj_testcase_ver.testcase_ver_id";
		$components['main']['fields'] .= ", group_concat(DISTINCT testcase_ver.id) as ver_ids, ".
				" group_concat(DISTINCT prj_testcase_ver.prj_id) as prj_ids, ".
				" group_concat(distinct testcase_ver.auto_level_id) as auto_level_ids, ".
				" group_concat(distinct testcase_ver.testcase_priority_id) as testcase_priority_ids, ".
				" group_concat(distinct testcase_ver.owner_id) as owner_ids,".
				" GROUP_CONCAT(distinct prj_testcase_ver.testcase_ver_id) as linked_ver_ids, ".
				" group_concat(distinct command separator '\\n') as command";
		$components['group'] = 'testcase.id';
		return $components;
	}
	
	public function getMoreInfoForRow($row){
		if(!empty($row['linked_ver_ids']))
			$row['ver_ids'] = $row['linked_ver_ids'];
		return $row;
	}
	
	protected function getSpecialFilters(){
		return array('os_id', 'board_type_id', 'chip_id', 'prj_id', 'edit_status_id', 'owner_id', 'testcase_priority_id', 'auto_level_id', 'command');
	}
	
	protected function specialSql($special, &$ret){
		$this->prj_exist = count($special);
		if ($this->prj_exist){
			$ret['group'] = 'testcase.id';
			$prj_id = false;
			$prj_where = '1';
			$prj_cond = array();
			foreach($special as $c){
				switch($c['field']){
					case 'prj_id':
						$prj_id = true;
						$ret['where'] .= ' AND '.$this->tool->generateLeafWhere($c);
						break;
					case 'os_id':
					case 'chip_id':
					case 'board_type_id':
						$prj_where .= ' AND '.$this->tool->generateLeafWhere($c);
						break;
					default:
						$c['field'] = 'testcase_ver.'.$c['field'];
						$ret['where'] .= ' AND '.$this->tool->generateLeafWhere($c);
				}
			}
			if (!$prj_id && $prj_where != '1'){
				$res = $this->db->query("SELECT GROUP_CONCAT(id) as ids FROM prj WHERE $prj_where");
				$row = $res->fetch();
				if (empty($row)){
					$ret['where'] .= ' AND 0';
				}
				else{
					$ret['where'] .= ' AND '.$this->tool->generateLeafWhere(array('field'=>'prj_id', 'op'=>'IN', 'value'=>$row['ids']));
				}
			}
		}
	}
	// End of Calc SQL

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