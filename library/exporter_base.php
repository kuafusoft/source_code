<?php
require_once('action_jqgrid.php');

interface iExporter{
	public function setOptions($action_jqgrid);
	public function export();
}

class exporter_base implements iExporter{
	protected $userAdmin = null;
    protected $userInfo = null;
	protected $fileName = '';
	public function __construct($params = array()){
		$this->init($params);
	}
	
	protected function init($params){
		$this->params = $params;
		if(isset($this->params['id']) && is_string($this->params['id']))
			$this->params['id'] = json_decode($this->params['id'], true);
// print_r($this->params);		
		$this->userInfo = $this->getCurrentUser();
		$this->fileName = $this->params['db'].'_'.$this->params['table'];
		$className = get_class($this);
		if (preg_match("/(.*)_exporter$/", $className, $matches)){
			$this->fileName = $matches[1];
		}
//		$this->fileName .= '_'.$this->params['id'];
	}
	
	private function getCurrentUser(){
		$userAdmin = new Application_Model_Useradmin(null);
		$userInfo = $userAdmin->getUserInfo();
		return $userInfo;
	}
	
	public function setOptions($action_jqgrid){

    }
    
	public function export(){
		$this->_export();
		return $this->save();
	}
	
	protected function _export(){
	
	}
	
	protected function save(){
        return $this->fileName;
	}
};

?>
