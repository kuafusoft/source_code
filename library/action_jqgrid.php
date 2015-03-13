<?php
require_once('action_base.php');
require_once('dbfactory.php');
require_once('toolfactory.php');
require_once('tabledescfactory.php');

class action_jqgrid extends action_base{
	protected $db = null;
	protected $db_name = '';
	protected $table_name = '';
	protected $tool = null;
	protected $configed = false;
	protected $options = array();
	protected $table_desc = null;
	protected $tool_name = 'db';
	protected function init(&$controller){
		parent::init($controller);
		// $this->getOptions();
// print_r($this->params)			;
// print_r($this->db_name);
	}
	
	public function setParams($params){
		parent::setParams($params);
		$this->db_name = $this->params['db'];
		$this->table_name = $this->params['table'];
		$this->db = dbFactory::get($this->params['db'], $realDbName);
		// $this->params['real_db'] = $realDbName;
		// $this->db_name = $realDbName;
// print_r($this->params);
// print_r("<<<<<<<<<<<<<<<<");		
		$this->table_desc = tableDescFactory::get($this->params['db'], $this->table_name, $this->params, $this);
		$this->getTool();
		$this->params['real_table'] = $this->params['table'];
		$real_table = $this->table_desc->get('real_table');
		if (!empty($real_table))
			$this->params['real_table'] = $real_table;
		// $this->getOptions();
	}
	
	protected function getRoles(){
		$roles = parent::getRoles();
// print_r($roles);		
		$row_roles = $this->table_desc->getRowRole($this->params);
// print_r($row_roles);		
		return array_unique(array_merge($roles, $row_roles));
		
		if(!empty($this->params['id'])){
			$strID = $this->params['id'];
			if(is_array($this->params['id']))
				$strID = implode(',', $this->params['id']);
			$res = $this->tool->query("SELECT * FROM {$this->table_name} WHERE id IN ($strID)");
			if($row = $res->fetch()){
				$matrix = $this->table_desc->getRowRoleMatrix();
				foreach($matrix as $field=>$role){
					if(isset($row[$field]) && $row[$field] == $this->userInfo->id){
						$roles[] = $role;
					}
				}
			}
		}
		return $roles;
	}
	
	protected function accessMatrix(){
		$defaultMatrix = array('all'=>array(
			'all'=>false, 
			'index'=>true, 'getGridOptions'=>true, 'list'=>true, 'saveCookie'=>true, 'client_export'=>true,
			'information'=>true, 'update_information_page'=>true,
			'checkUnique'=>true, 'columns'=>true, 'linkage'=>true, 
			'autocomplete'=>true, 'getchip'=>true, 'getboardtype'=>true, 
			'getos'=>true, 'fetch_tags'=>true, 'tag'=>true, 'skywalker'=>true));
		parent::accessMatrix();
		$tableMatrix = $this->transAccessMatrix();
		$tableMatrix['all'] = array_merge($defaultMatrix['all'], $tableMatrix['all']);
		$this->access_matrix[$this->params['db'].'_'.$this->table_name] = $tableMatrix;
	}
	
	protected function transAccessMatrix(){
		$dirs = array('/jqgrid', "/jqgrid/{$this->params['db']}", "/jqgrid/{$this->params['db']}/{$this->table_name}");
		$matrix = array('all'=>array('all'=>0), 'guest'=>array('all'=>0)); // 默认guest不允许任何操作
// print_r("+++++++++++++++++++++++");
		foreach($dirs as $dir){
			$access_file = APPLICATION_PATH.$dir."/access.xml";
			if(file_exists($access_file)){
				$access = new Zend_Config_Xml($access_file);
				$access = $access->toArray();
// print_r("in $access_file:");
// print_r($access);				
				foreach($access as $role=>$v){
					if(!isset($matrix[$role]))
						$matrix[$role] = $v;
					else
						$matrix[$role] = array_merge($matrix[$role], $v);
				}
			}
		}
// print_r($matrix);		
// print_r("+++++++++++++++++++++++");
		return $matrix;
	}

	public function getTable_desc(){
		return $this->table_desc;
	}
	
	protected function _execute(){
// print_r("\n action_jqgrid _execute<br>\n");	
// print_r($this->params);
		$this->tool->beginTransaction();
		try{
			$ret = parent::_execute();
			$this->tool->commit();
		}catch(Exception $e){
			$this->tool->rollback();
			$ret = $e->getMessage();
		}
// print_r("\n...action_jqgrid<br>\n");		
		return $ret;
	}
	
	protected function setTool($tool_name = 'db'){
		$this->tool_name = $tool_name;
	}
	
	protected function getTool(){
		$this->tool = toolFactory::get(array('tool'=>$this->tool_name, 'db'=>$this->params['db'], 'table'=>$this->table_name));
		$this->tool->setDb($this->params['db']);
		return $this->tool;
	}

	protected function config(){
		if ($this->configed)
			return;

		$this->_config();

		$this->configed = true;
	}
	
	protected function _config(){
        $this->options = $this->table_desc->getOptions(true, $this->params);
// print_r($this->options);		
	}
	
	protected function getOptions(){
// print_r("oper = {$this->oper}");	
		$this->config();
		return $this->options;
	}
	
	// protected function fetch_tags(){
		// return $this->table_desc->fetch_tags();
	// }
	
	public function trimButtons($buttons){
		// accessCenter::setAccessMatrix($this->access_matrix);
		$roles = $this->getRoles();
		foreach($buttons as $key=>$button){
			if(!accessCenter::canAccess('jqgrid', $key, $this->params, $roles)){
				unset($buttons[$key]);
			}
		}
// print_r(">>>>>>>>>>>");	
// print_r($roles);
// print_r($this->access_matrix);
// print_r("<<<<<<<");
		return $buttons;
	}
	
	protected function getViewParams($params){
		$view_params = parent::getViewParams($params);
		$view_params['view_file'] = $this->oper.'.phtml';
		$dirs = array("/jqgrid/{$this->params['db']}/{$this->params['table']}", "/jqgrid/{$this->params['db']}", '/jqgrid');
		foreach($dirs as $dir){
			$view_file = APPLICATION_PATH.$dir.'/'.$view_params['view_file'];
			if(file_exists($view_file)){
				$view_params['view_file_dir'] = $dir;
				break;
			}
		}
		return $view_params;
	}
	
	protected function parseParams(){
		parent::parseParams();
		unset($this->params['module']);
		unset($this->params['controller']);
		unset($this->params['action']);
		unset($this->params['oper']);
		return $this->params;
	}
}
?>