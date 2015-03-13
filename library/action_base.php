<?php
require_once('accesscenter.php');
require_once('const_def.php');

interface iAction{
	public function execute();
}

class action_base implements iAction{
	protected $controller = null;
	protected $request = null;
	protected $params = array();
	protected $isPost = false;
	protected $userAdmin = null;
	protected $userInfo;
	protected $oper;
	protected $log_db;
	protected $access_matrix = array();
	public function __construct($controller){
		$this->init($controller);
	}
	
	protected function init(&$controller){
		$this->controller = $controller;
		$this->request = $this->controller->getRequest();
		$this->isPost = $this->request->isPost();
		$this->userAdmin = new Application_Model_Useradmin($this->controller);
		$this->userInfo = $this->userAdmin->getUserInfo();
		$this->log_db = dbFactory::get('useradmin');
	}
	
	public function setParams($params){
		$this->params = $params;
		$this->params = $this->parseParams();
	}
	
	protected function accessMatrix(){
		// $this->access_matrix['all'] = array(
			// 'all'=>array('index'=>true, 'getGridOptions'=>true, 'list'=>true, 'saveCookie'=>true,
				// 'information'=>true, 'update_information_page'=>true, 'export'=>true, 'beforeSave'=>true,
				// 'checkUnique'=>true, 'subscribe'=>true, 'tag'=>true, 'columns'=>true, 'linkage'=>true, 
				// 'autocomplete'=>true, 'getchip'=>true, 'getboardtype'=>true, 'getos'=>true, 'fetch_tags'=>true),
			// 'admin'=>array('all'=>true, 'add'=>true, 'removeFromTag'=>true, 'beforeSave'=>true, 'save'=>true, 'cloneit'=>true, 'view_edit_save'=>true, 'view_edit_cancel'=>true, 'view_edit_abort'=>true, 'view_edit_cloneit'=>true),
			// 'row_owner'=>array('beforeSave'=>true, 'save'=>true, 'view_edit_save'=>true, 'view_edit_cancel'=>true, 'view_edit_abort'=>true, 'view_edit_cloneit'=>true, 'cloneit'=>true),
			// 'normal'=>array('modifyTask'=>true)
		// );
		// $this->access_matrix['all']['assistant_admin'] = $this->access_matrix['all']['admin'];
	}
	
	protected function getRoles(){
		return $this->userInfo->roles;
	}
	
	final public function execute(){
		$ret = array('errCode'=>ERROR_OK);
		$allowed = $this->isAllowed();
// print_r("allowed = $allowed<<<<\n");
		if (!$allowed){
			if(empty($this->userInfo->id)){
				$ret['errCode'] = ERROR_INVALID_AUTHORITY;
			}
			else
				$ret['errCode'] = ERROR_INVALID_PRIVILEGE;
		}
		else{
			$this->preExecute();
			$ret = $this->_execute();
			$oper = $this->oper;
			$notLogActions = array('linkage', 'checkUnique', 'list', 'index', 'getGridOptions', 'getchip', 'getboardtype', 'autocomplete');
			if ($this->isPost){
				if (!in_array($this->oper, $notLogActions))
					$this->log($ret);
				if (in_array(strtolower($this->oper), array('save', 'review', 'publish', 'change_owner', 'inactivate', 'activate', 'cloneit'))){
					$dbTable = "`{$this->get('db')}`.`{$this->get('table')}`";
	//print_r("oper = $oper, dbTable = $dbTable, id={$this->get('id')}\n");
					$this->userAdmin->processSubscribe($dbTable, $this->get('id'), $oper);
				}
			}
		}
		return $ret;
	}
	
	protected function isAllowed(){
		$allowed = true;
        $controllerName = $this->request->getControllerName();
		$roles = $this->getRoles();
// print_r(">>>>>");
// print_r($roles);
// print_r("<<<<<<<");
		$this->accessMatrix();
// print_r($this->access_matrix);		
		accessCenter::setAccessMatrix($this->access_matrix);
		$allowed = accessCenter::canAccess($controllerName, $this->oper, $this->params, $roles);
// print_r(">>>allowed = $allowed<<<<");
		// if (!in_array('all', $this->roles)){
			// $hasRole = array_intersect($this->userInfo->roles, $this->roles);
			// if (empty($hasRole))
				// $allowed = false;
		// }
		return $allowed;
	}
	
	protected function preExecute(){
		return;
	}
	
	protected function get($name){
		return isset($this->params[$name]) ? $this->params[$name] : null;
	}
	
	protected function _execute(){
//print_r("base_action::_execute\n");		
		if ($this->isPost)
			$ret = $this->handlePost();
		else
			$ret = $this->handleGet();
		return $ret;
	}
	
	protected function log($ret){
		$log = array('db_table'=>$this->get('db').'.'.$this->get('table'), 'oper'=>$this->oper, 'params'=>json_encode($this->params), 'creater_id'=>$this->userInfo->id);
		// save the log
		$this->log_db->insert('log', $log);
	}
	
	protected function handlePost(){
		return 1004;
	}
	
	protected function handleGet(){
		$view_params = $this->getViewParams($this->params);
		if (!empty($view_params['view_file']))
			$this->renderView($view_params['view_file'], $view_params, $view_params['view_file_dir']);
		else
			return $view_params;
	}
	
	protected function getViewParams($params){
		$view_params = $params;
		return $view_params;
	}
	
	protected function getCookie($params){
		$cookie = $this->userAdmin->getCookie($params);
		return json_decode($cookie);
	}
	
	protected function saveCookie($params){
		if (empty($params['name']) && !empty($params['db']) && !empty($params['table']))
			$params['name'] = $params['db'].'_'.$params['table'];
		if (empty($params['type'])) $params['type'] = 'display';
		return $this->userAdmin->saveCookie($params);
	}
	
	protected function parseParams(){
		if (isset($this->params['element'])){
			$this->params['id'] = $this->params['element'];
			unset($this->params['element']);
		}
		// 转换id，如果是数字，则不处理，如果是字符串，则转换成数组
		if(isset($this->params['id']) && !is_numeric($this->params['id']) && is_string($this->params['id'])){
			$ret = json_decode($this->params['id'], true);
			if(!is_null($ret))
				$this->params['id'] = $ret;
		}
// print_r($params);
		$this->oper = isset($this->params['oper']) ? $this->params['oper'] : (isset($this->params['action']) ? $this->params['action'] : 'index');
		return $this->params;
	}
	
	private function preView($params, $path){
        $dbName = $this->get('db');
        $tableName = $this->get('table');
        if (empty($path)){
			$dirs = array("/jqgrid/$dbName/$tableName", "/jqgrid/$dbName", '/jqgrid');
			foreach($dirs as $dir){
				$view_file = APPLICATION_PATH.$dir.'/'.$params['view_file'];
// print_r($view_file);				
				if(file_exists($view_file)){
					$path = $dir;
					break;
				}
			}
		
			// if(!empty($dbName) && !empty($tableName))
				// $path = "/jqgrid/$dbName/$tableName";
			// else
				// $path = "/jqgrid";
		}
// print_r($path);		
        $this->controller->view->setScriptPath(APPLICATION_PATH.$path);
        foreach($params as $key=>&$v){
            $this->controller->view->$key = $v;
		}
	}
	
    public function genView($view, $params, $path = ''){
		$this->preView($params, $path);
        $html = $this->controller->view->render($view);
		return $html;
	}
	
    public function renderView($view, $params, $path = ''){
		$html = $this->genView($view, $params, $path);
		echo $html;
	}
	
	public function getActionName(){
		return $this->oper;
	}
}
?>